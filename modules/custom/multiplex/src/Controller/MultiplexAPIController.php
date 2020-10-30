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

use Symfony\Component\HttpFoundation\JsonResponse;

use Drupal\multiplex\Service\VisitationService;

/**
 * Returns responses for Multiplex routes.
 */
class MultiplexAPIController extends ControllerBase {

  protected $pathValidator;

  /** @var \Drupal\multiplex\Service\MultiplexService */
  protected $multiplexService;

  /** @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch */
  protected $pageCacheKillSwitch;

  /** @var \Drupal\Core\Config\ConfigFactoryInterface */
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
   * @return JsonResponse
   */
  public function locations() {
    $path = \Drupal::request()->query->get('path') ?: 'default';

    return new JsonResponse([ 'data' => $this->getLocationData($path), 'method' => 'GET', 'status'=> 200]);
  }

  /**
   * This is "edit mode" only.
   *
   * @return array
   */
  public function getLocationData($path) {
    if ($path == 'edit') {
      return $this->getEditModeLocationData();
    }

    $who = multiplex_get_visitor_cookie_value();

    // TODO: Get this from configuration or something.
    $legend = [
      [
        'id' => 'visited',
        'name' => 'Visited',
        'icon' => 'halloween-ghost-48.png',
      ],
      [
        'id' => 'unvisited',
        'name' => 'Unvisited',
        'icon' => 'question-48.png',
      ],
    ];

    $result = [
      'legend' => $legend,
      'locations' => $this->visitationService->getVisitedLocationData($who),
    ];

    $recent = $this->visitationService->mostRecent($who);
    if ($recent) {
      $geo = $recent->get('field_geolocation')->getValue();
      if (!empty($geo[0])) {
        $result['recent'] = [
          'id' => $recent->id(),
          'code' => trim($recent->Url(), '/'),
          'lat' => floatval($geo[0]['lat']),
          'lng' => floatval($geo[0]['lng']),
        ];
      }
    }

    return $result;
  }


  protected function getEditModeLocationData() {
    $user = \Drupal::currentUser();
    if (!$user->hasPermission('access administration menu')) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $locations = [];
    // TODO: How to add a search condition on field_geolocation to test of lat/lng are populated?
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'qr_code')
      ->sort('title', 'DESC');
    $nodes_ids = $query->execute();
    if ($nodes_ids) {
      foreach ($nodes_ids as $node_id) {
        $node = \Drupal\node\Entity\Node::load($node_id);

        $story = $node->get('field_story_page')->getValue();

        // We'll just skip nodes without lat/lng
        $geo = $node->get('field_geolocation')->getValue();
        if (!empty($geo[0])) {
          $locations[] = [
            "id" => $node->id(),
            "code" => $node->getTitle(),
            "text" => $node->getTitle(),
            "position" => [
              floatval($geo[0]['lat']),
              floatval($geo[0]['lng']),
            ],
            "legendId" => empty($story) ? "unassigned" : "assigned",
            "visited" => true,
          ];
        }
      }
    }

    // TODO: Get this from configuration or something.
    $legend = [
      [
        'id' => 'assigned',
        'name' => 'Assigned',
        'icon' => 'code-green-16.png',
      ],
      [
        'id' => 'unassigned',
        'name' => 'Unassigned',
        'icon' => 'code-red-16.png',
      ],
    ];

    return [
      'legend' => $legend,
      'locations' => $locations,
    ];
  }

}
