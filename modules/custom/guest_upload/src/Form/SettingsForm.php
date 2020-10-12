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

    // TODO: Inject service into form
    $moduleHandler = \Drupal::service('module_handler');
    $has_eu_compliance_module = $moduleHandler->moduleExists('eu_cookie_compliance');

    $form['cookie'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie'),
      '#description' => $this->t("ID of cookie that identifies guest's identity."),
      '#default_value' => $this->config('guest_upload.settings')->get('cookie'),
    ];

    $form['automatic_visitor_id'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically set visitor id'),
      '#description' => $this->t("Set a random cookie value for each guest."),
      '#default_value' => $this->config('guest_upload.settings')->get('automatic_visitor_id'),
    ];

    $form['use_eu_cookie_compliance_module'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Do not set visitor cookie until policy accepted'),
      '#description' => $this->t("Requires the <a href='https://drupal.org/project/eu_cookie_compliance'>EU Cookie Compliance</a> module."),
      '#default_value' => $this->config('guest_upload.settings')->get('use_eu_cookie_compliance_module'),
      '#disabled' => !$has_eu_compliance_module,
    ];

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
      ->set('cookie', $form_state->getValue('cookie'))
      ->save();

    $this->config('guest_upload.settings')
      ->set('guest_image_field', $form_state->getValue('guest_image_field'))
      ->save();

    $this->config('guest_upload.settings')
      ->set('guest_page_type', $form_state->getValue('guest_page_type'))
      ->save();

    $this->config('guest_upload.settings')
      ->set('automatic_visitor_id', $form_state->getValue('automatic_visitor_id'))
      ->save();

    $this->config('guest_upload.settings')
      ->set('use_eu_cookie_compliance_module', $form_state->getValue('use_eu_cookie_compliance_module'))
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
