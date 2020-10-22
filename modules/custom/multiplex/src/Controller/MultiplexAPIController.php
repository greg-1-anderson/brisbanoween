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

/**
 * Returns responses for Multiplex routes.
 */
class MultiplexAPIController extends ControllerBase {

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
   * @return JsonResponse
   */
  public function locations() {
    return new JsonResponse([ 'data' => $this->getLocationData(), 'method' => 'GET', 'status'=> 200]);
  }

  /**
   * This is "edit mode" only.
   *
   * @return array
   */
  public function getLocationData() {
    $user = \Drupal::currentUser();
    if ($user->hasPermission('access administration menu')) {
      return $this->getEditModeLocationData();
    }
    return $this->getVisitedLocationData();
  }

  protected function getVisitedLocationData() {
    return [];
  }

  protected function getEditModeLocationData() {
    $locations=[];
    // TODO: How to add a condition on field_geolocation to test of lat/lng are populated?
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
            "position" => [
              floatval($geo[0]['lat']),
              floatval($geo[0]['lng']),
            ],
            "legendId" => empty($story) ? "unvisited" : "visited",
            "visited" => true,
          ];
        }
      }
    }

    // TODO: Get this from configuration or something.
    $legend = [
      [
        'id' => 'visited',
        'name' => 'Visited',
        'icon' => 'visited.png',
      ],
      [
        'id' => 'unvisited',
        'name' => 'Unvisited',
        'icon' => 'unvisited.png',
      ],
    ];

    return [
      'yay' => 'woo hoo',
      'legend' => $legend,
      'locations' => $locations,
    ];
  }

}
