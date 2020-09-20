<?php

namespace Drupal\multiplex\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Multiplex routes.
 */
class MultiplexController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    // Determine default target from potential locations.
    $target = '/privacy';

    // Redirect if target exists. It should always exist.
    $destination = Url::fromUserInput($target);
    if ($destination->isRouted()) {
      return $this->redirect($destination->getRouteName());
    }

    // Oh snap.
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Attempted to route to non-existant path %target', ['@target' => $target]),
    ];

    return $build;

  }

}
