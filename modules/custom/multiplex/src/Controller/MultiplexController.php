<?php

namespace Drupal\multiplex\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Returns responses for Multiplex routes.
 */
class MultiplexController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build($target) {

    if (!$target) {
      $build['content'] = [
        '#type' => 'item',
        '#markup' => $this->t('No target provided'),
      ];
      return $build;
    }

    $url_object = \Drupal::service('path.validator')->getUrlIfValid("/$target");

    if (!$url_object) {
      $build['content'] = [
        '#type' => 'item',
        '#markup' => $this->t('Target %target does not exist', ['%target' => $target]),
      ];
      return $build;
    }

    $route_name = $url_object->getRouteName();
    $route_parameters = $url_object->getrouteParameters();

    // This sets the 'no-cache' header by default
    return $this->redirect($route_name, $route_parameters);
  }

}
