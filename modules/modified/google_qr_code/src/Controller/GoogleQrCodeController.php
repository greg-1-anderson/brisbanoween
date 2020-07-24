<?php

namespace Drupal\google_qr_code\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Google QR Code routes.
 */
class GoogleQrCodeController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build($subpath) {

    // To allow subpaths with slashes, see:
    //  - https://api.drupal.org/api/drupal/core%21modules%21system%21src%21PathProcessor%21PathProcessorFiles.php/class/PathProcessorFiles/8.2.x
    //  - https://api.drupal.org/api/drupal/core%21modules%21system%21system.services.yml/8.2.x
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $modified_url = "$host/$subpath";

    $qr_code_height = \Drupal::config('google_qr_code.settings')->get('height');
    $qr_code_width = \Drupal::config('google_qr_code.settings')->get('width');

    // Get the google charts API image URL.
    $google_qr_image_url = "https://chart.googleapis.com/chart?chs=" .
      $qr_code_width . "x" . $qr_code_height
      . "&cht=qr&chl=" . $modified_url . '&chld=H|0';

    // Write the alternate description for the QR image.
    $google_qr_alt = $this->t('QR Code for @url', array('@url' => $modified_url));

    // Return markup, and return the block as being cached per URL path.
    $build['qr'] = [
      '#theme' => 'image',
      '#uri' => $google_qr_image_url,
      '#width' => $qr_code_width,
      '#height' => $qr_code_height,
      '#alt' => $google_qr_alt,
      '#class' => 'google-qr-code-image',
      '#cache' => [
        'contexts' => ['url.path'],
      ],
    ];

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t($modified_url),
    ];

    return $build;
  }

}
