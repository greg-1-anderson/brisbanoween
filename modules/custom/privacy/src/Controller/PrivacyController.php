<?php

namespace Drupal\privacy\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Brisbanoween routes.
 */
class PrivacyController extends ControllerBase {

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
