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

  public function __construct($pathValidator, MultiplexService $multiplexService, KillSwitch $pageCacheKillSwitch, \Drupal\Core\Config\ConfigFactoryInterface $config) {
    $this->pathValidator = $pathValidator;
    $this->multiplexService = $multiplexService;
    $this->pageCacheKillSwitch = $pageCacheKillSwitch;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.validator'),
      $container->get('multiplex.multiplex'),
      $container->get('page_cache_kill_switch'),
      $container->get('config.factory')
    );
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

    // Paths must start with "/", but $path from the route does not.
    $node = $this->getNodeFromPath("/$path");
    if (!$node) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    // Get identifier for visiting user.
    $cookie = $this->config->get('multiplex.settings')->get('cookie');
    if (\Drupal::moduleHandler()->moduleExists('guest_upload')) {
      $cookie = $this->config->get('guest_upload.settings')->get('cookie');
    }
    $who = $_COOKIE[$cookie] ?? '';

    // Look for redirection rules attached to the entity at "$node".
    // If there are any that match, then redirect to the multiplexed location.
    $target = $this->multiplexService->findMultiplexLocation($who, $node);

    // It is also an error if the target does not exist; we return page not found.
    $target_node = $this->getNodeFromPath($target);
    if (!$target_node) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $object = $this->checkObject($target_node);

    // Redirect to the target.
    $response = new RedirectResponse($target_node->Url(), 302);

    $cookie = new Cookie('STXKEY_objects', $object, 0, '/', null, false, false);
    $response->headers->setCookie($cookie);

    return $response;
  }

  protected function getNodeFromPath($path) {
    if (empty($path)) {
      return null;
    }
    $params = \Drupal\Core\Url::fromUserInput($path)->getRouteParameters();
    if (!isset($params['node'])) {
      return null;
    }

    return \Drupal\node\Entity\Node::load($params['node']);
  }

  protected function checkObject($node) {
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

}
