<?php

namespace Drupal\multiplex\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\multiplex\Service\VisitData;
use Drupal\multiplex\Service\MultiplexService;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;

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

    // Be consistent: paths should start with "/"
    $target = "/$path";

    // Get identifier for visiting user.
    $cookie = $this->config->get('multiplex.settings')->get('cookie');
    if (\Drupal::moduleHandler()->moduleExists('guest_upload')) {
      $cookie = $this->config->get('guest_upload.settings')->get('cookie');
    }
    $who = $_COOKIE[$cookie] ?? '';

    // Look for redirection rules attached to the entity at "$target".
    // If there are any that match, then redirect to the multiplexed location.
    $target = $this->multiplexService->findMultiplexLocation($who, $target);

    // It is also an error if the target does not exist; we return page not found.
    $url_object = $this->pathValidator->getUrlIfValid("$target");
    if (!$url_object) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    // Redirect to the target.
    $route_name = $url_object->getRouteName();
    $route_parameters = $url_object->getrouteParameters();

    // This sets the 'no-cache' header by default
    return $this->redirect($route_name, $route_parameters);
  }

}
