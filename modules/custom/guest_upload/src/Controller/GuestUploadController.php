<?php

namespace Drupal\guest_upload\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Guest Upload routes.
 */
class GuestUploadController extends ControllerBase {

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
