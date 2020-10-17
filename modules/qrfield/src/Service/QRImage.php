<?php

namespace Drupal\qrfield\Service;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\token\Token;

/**
 * QRImage service.
 *
 * This service is used for generating QR images based
 * on the 'qrfield' plugin.
 */
class QRImage implements QRImageInterface {

  use StringTranslationTrait;

  /**
   * QR code plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Plugin ID to use for generating image.
   *
   * @var string
   *   Default: "qchart".
   */
  protected $pluginId = 'gchart';

  /**
   * Token service.
   *
   * @var \Drupal\token\Token
   */
  protected $token;

  /**
   * Constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $pluginManager
   *   QR code plugin manager.
   * @param \Drupal\token\Token $token
   *   Token service.
   */
  public function __construct(PluginManagerInterface $pluginManager, Token $token) {
    $this->pluginManager = $pluginManager;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $data, $width, $height) {
    $build = [];
    if ($this->pluginManager->hasDefinition($this->pluginId)) {
      $text = $this->token->replace(
        $data['text'] ?? $this->t('Missing QR data text.'),
        $data['objects'] ?? []
      );
      $pluginInstance = $this->pluginManager->createInstance($this->pluginId, [
        'data' => $text,
        'image_width' => $width,
        'image_height' => $height,
      ]);
      $build['#theme'] = 'image';
      $build['#uri'] = $pluginInstance->getUrl()->toString();
      $build['#alt'] = $text;
    }
    else {
      $build['#markup'] = $this->t('Failed to render QR image using plugin: @plugin.', [
        '@plugin' => $this->pluginId,
      ]);
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function setPlugin($pluginId) {
    $this->pluginId = $pluginId;
    return $this;
  }

}
