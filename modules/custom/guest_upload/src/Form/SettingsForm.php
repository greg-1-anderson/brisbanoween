<?php

namespace Drupal\guest_upload\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\block_upload\BlockUploadManager;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Configure Guest Upload settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'guest_upload_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['guest_upload.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get a list of all available image reference fields
    $fields = static::getImageFieldList();

    $form['guest_image_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field'),
      '#description' => $this->t('Select field you wish to use to store all of the images uploaded by one guest.'),
      '#options' => $fields,
      '#default_value' => $this->config('guest_upload.settings')->get('guest_image_field') ?: '',
    ];

    $form['guest_page_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Guest Page type'),
      '#description' => $this->t('Select the content type to use for guest pages.'),
      '#options' => node_type_get_names(),
      '#default_value' => $this->config('guest_upload.settings')->get('guest_page_type') ?: '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*
    if ($form_state->getValue('example') != 'example') {
      $form_state->setErrorByName('example', $this->t('The value is not correct.'));
    }
    */
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('guest_upload.settings')
      ->set('guest_image_field', $form_state->getValue('guest_image_field'))
      ->save();

    $this->config('guest_upload.settings')
      ->set('guest_page_type', $form_state->getValue('guest_page_type'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Convenience routine from block_upload, modified to return only image fields.
   *
   * @return array
   *   Field list.
   */
  public static function getImageFieldList() {
    $fields = [];
    $results = \Drupal::entityQuery('field_storage_config')->execute();
    foreach ($results as $result) {
      $field = FieldStorageConfig::loadByName(explode('.', $result)[0], explode('.', $result)[1]);
      if ($field->getType() == 'image') {
        $fields[$result] = $result;
      }
    }
    return $fields;
  }

}
