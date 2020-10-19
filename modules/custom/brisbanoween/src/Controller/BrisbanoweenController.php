<?php

namespace Drupal\brisbanoween\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Brisbanoween routes.
 */
class BrisbanoweenController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
