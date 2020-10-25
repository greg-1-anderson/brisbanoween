<?php

namespace Drupal\multiplex\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\multiplex\Service\VisitData;
use Drupal\multiplex\Service\MultiplexService;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;

use Drupal\multiplex\Service\VisitationService;

/**
 * Returns responses for Multiplex routes.
 */
class MultiplexController extends ControllerBase {

  protected $pathValidator;

  /** @var \Drupal\multiplex\Service\MultiplexService */
  protected $multiplexService;

  /** @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch */
  protected $pageCacheKillSwitch;

  /** \Drupal\Core\Config\ConfigFactoryInterface */
  protected $config;

  /** @var \Drupal\multiplex\Service\VisitationService */
  protected $visitationService;

  public function __construct($pathValidator, MultiplexService $multiplexService, KillSwitch $pageCacheKillSwitch, \Drupal\Core\Config\ConfigFactoryInterface $config, VisitationService $visitationService) {
    $this->pathValidator = $pathValidator;
    $this->multiplexService = $multiplexService;
    $this->pageCacheKillSwitch = $pageCacheKillSwitch;
    $this->config = $config;
    $this->visitationService = $visitationService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.validator'),
      $container->get('multiplex.multiplex'),
      $container->get('page_cache_kill_switch'),
      $container->get('config.factory'),
      $container->get('multiplex.visitation')
    );
  }

  /**
   * Host page for the map controls
   */
  public function map() {
    return [
      '#markup' => 'Opening map, please wait... [sanity check]',
    ];
  }

  /**
   * Host page for the map controls (which accept a parameter to pass through to the API)
   */
  public function specialMap($map_type=NULL) {
    $page = [
      '#markup' => 'Opening map, please wait...',
    ];
    return $page;
  }

  public function waitForGameToStartPage($path=NULL) {
  	// We dont need to show anything, multiplex.module injects the counter on this page automatically.
    return [
      '#markup' => '',
    ];
  }

  /**
   * Builds the response, which is usually a redirect.
   *
   * The route to this controller is `/to/{path}`. If there are
   * no rules in effect to cause the user to be redirected to some
   * other destination, then by default they will go to `{path}`.
   *
   * The redirection rules are typically expressed via hidden fields
   * in the the entity at `{path}`. These rules will be evaluated
   * to result in a final target to bring the user to.
   *
   * Entity paths with no rules are possible; these sorts locations are
   * useful because only pages visited through a multiplex route
   * are recorded as a "visited" location that may be used in a
   * "visited" rule.
   */
  public function build($path) {

    // It is an error if there is no target. Return 404.
    if (!$path) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    // Prevent the redirect from being stored in the page cache.
    $this->pageCacheKillSwitch->trigger();

    // Get identifier for visiting user.
    $who = multiplex_get_visitor_cookie_value();

    // Make sure the game has started before continuing
    if (!$this->gameInProgress()) {
    	return new RedirectResponse("/wait/$path", 302);
    }

    // If the user doesnt have the session cookie yet, we need to return a placeholder page so that the redirect doesnt happen and the user
    // has a chance to accept the privacy policy.  Once they do, the page will reload with the session cookie and this conditional
    // will be skipped allowing the original redirect to occur.  Note however, that if the user rejects the privacy policy, whatever is returned
    // here, is what they will see.
    if (!$who) {
       $build['content'] = [
         '#type' => 'item',
         '#markup' => 'Please accept the privacy policy to continue...',
       ];
       return $build;
    }

    // Debug code for inspection
    if ($path == "debug") {
       $build['content'] = [
         '#type' => 'item',
         '#markup' => 'Visitor: ' . $who,
       ];
       return $build;
    }

    // Paths must start with "/", but $path from the route does not.
    $node = $this->getNodeFromPath("/$path");
    if (!$node) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $target_node = $this->findTargetNode($who, $node);
    // TODO: Fix hack that works around bug.
    // Refactor of findTargetNode not great, need to re-do it later.
    if ($target_node instanceof RedirectResponse) {
      return $target_node;
    }
    $redirect_url = $this->redirectUrl($who, $target_node);

    // Redirect to the target.
    $response = new RedirectResponse($redirect_url, 302);

	// See if the destination is going to give the user an object
    $object = $this->checkObject($target_node);
    if ($object) {
    	// It will, so see if the user already has the object
    	$currentInventory = multiplex_get_user_inventory();
    	if (!in_array($object, $currentInventory)) {
    		// They dont, so add it to the collection now (at the end)
    		array_push($currentInventory, $object);

			// Update the cookie with the new inventory items
    		$inventory_cookie = \Drupal::config('multiplex.settings')->get('inventory_cookie');
   			$update_inventory = new Cookie($inventory_cookie, implode(',', $currentInventory), 0, '/', null, false, false);
    		$response->headers->setCookie($update_inventory);

			// Update the cookie that specifies when the last item was obtained (in milliseconds)
    		$inventory_added_cookie = \Drupal::config('multiplex.settings')->get('inventory_added_cookie');
   			$update_inventory_added = new Cookie($inventory_added_cookie, (time() * 1000), 0, '/', null, false, false);
    		$response->headers->setCookie($update_inventory_added);
    	}
    }

    return $response;
  }

  protected function findTargetNode($who, $node) {
    // If the requested node is not a QR node, then evaluate it directly.
    // TODO: What's the best way to identify qr_code nodes?
    if ($node->bundle() != 'qr_code') {
      // TODO: Maybe we should only allow admins to do this. Not sure
      // if a player will ever follow this code path. Maybe from the map.
      return $this->multiplexService->findMultiplexLocationFromRules($who, $node);
    }

    $qr_node = $node;

    // Return a 404 if there is no story page.
    $qr_code_target = $qr_node->get('field_story_page')->getValue();
    if (empty($qr_code_target[0]['target_id'])) {
      // If the user is an admin, go to edit page instead
      $user = \Drupal::currentUser();
      if ($user->hasPermission('access administration menu')) {
        $nid = $qr_node->id();
        return new RedirectResponse("/node/$nid/edit");
      }

      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $this->visitationService->recordMapMarker($who, $qr_node);
    $node = \Drupal\node\Entity\Node::load($qr_code_target[0]['target_id']);

    // What's the best way to identify a multiplex destinations node?
    if ($node->bundle() == 'multiplex_dest') {
      $target_node = $this->multiplexService->findMultiplexLocation($who, $qr_node, $node);
    }
    else {
      // Look for redirection rules attached to the entity at "$node".
      // If there are any that match, then redirect to the multiplexed location.
      $target_node = $this->multiplexService->findMultiplexLocationFromRules($who, $node);
    }

    $this->addMapHints($who, $target_node, $qr_node);

    return $target_node;
  }


  protected function addMapHints($who, $target_node, $qr_node) {
    // Ignore the node if it has no hints; also skip if we did not get here from a scan
    if (!$qr_node || !$target_node || !$target_node->hasField('field_story_hints')) {
      return ;
    }

    $hints = $this->loadHints($target_node, 'field_story_hints', $qr_node);
    $new_hint = false;
    foreach ($hints as $hint_node) {
      $new_hint |= $this->visitationService->recordMapMarker($who, $hint_node, false);
    }

    if ($new_hint) {
      \Drupal::messenger()->addStatus('Check your map; new hints were added!');
    }
  }

  protected function loadHints($target_node, $field, $scanned_qr_node) {
    $field_data = $target_node->get($field)->getValue();
    if (empty($field_data)) {
      return [];
    }

    $targets = array_filter(
      array_map(
        function ($item) {
          $story_page_id = $item['target_id'];

          $query = \Drupal::entityQuery('node')
            ->condition('type', 'qr_code')
            ->condition('field_story_page', $story_page_id);
          $results = $query->execute();

          // TODO: If there is no qr_code pointing to the
          // hinted story node, then look for a multiplex node
          // and do some magic
          if (empty($results)) {
            return null;
          }

          // TODO: If there are multiple results, then
          // return the one that is CLOSEST to $scanned_qr_node
          $hint_qr_code_id = array_pop($results);

          return \Drupal\node\Entity\Node::load($hint_qr_code_id);
        },
        $field_data
      )
    );

    return $targets;
  }

  protected function redirectUrl($who, $target_node) {
    // If we found a target, return its URL
    if ($target_node) {
      return $target_node->Url();
    }

    // If we did not find a target, go to the configured
    // "sorry, you must accept the privacy policy to play" page
    if (empty($who)) {
      $unidentified_user_path = $this->config->get('multiplex.settings')->get('unidentified_user_path');
      if (!empty($unidentified_user_path)) {
        return $unidentified_user_path;
      }
    }

    // If we have neither a target nor a configured "sorry" page, then just 404.
    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
  }

  protected function getNodeFromPath($path) {
    try {
      $params = \Drupal\Core\Url::fromUserInput($path)->getRouteParameters();
      if (isset($params['node'])) {
        return \Drupal\node\Entity\Node::load($params['node']);
      }

    } catch(\Exception $e) {}

    return null;
  }

  protected function checkObject($node) {
    if (!$node) {
      return '';
    }

    if (!$node->hasField('field_object')) {
      return '';
    }
    $object_data = $node->get('field_object')->getValue();
    if (!empty($object_data)) {
      $object_name = $object_data[0]['value'];
      return $object_name;
    }
    return '';
  }

  protected function gameInProgress() {
    // Admins always get to play
    $user = \Drupal::currentUser();
    if ($user->hasPermission('access administration menu')) {
      return true;
    }

    // If the game start time hasn't arrived yet, the game has not started.
    $game_start_timestamp = $this->config('multiplex.settings')->get('game_start_time');
    if ((intval($game_start_timestamp) - 60) > time()) {
      return false;
    }
    return true;
  }
}
