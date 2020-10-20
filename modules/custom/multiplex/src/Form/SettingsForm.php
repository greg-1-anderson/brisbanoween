<?php

namespace Drupal\multiplex\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Multiplex settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multiplex_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['multiplex.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // TODO: Inject service into form
    $moduleHandler = \Drupal::service('module_handler');
    $has_guest_upload_module = $moduleHandler->moduleExists('guest_upload');

    $cookie_value = $this->config('multiplex.settings')->get('cookie');
    if ($has_guest_upload_module) {
      $cookie_value = $this->config('guest_upload.settings')->get('cookie');
    }

    $form['cookie'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie'),
      '#description' => $this->t("ID of cookie that identifies visitor's identity. If guest_upload module is enabled, its cookie will always be used."),
      '#default_value' => $cookie_value,
      '#disabled' => $has_guest_upload_module,
    ];
    $form['unidentified_user_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unidentified User Path'),
      '#description' => $this->t("Page to redirect to if an unidentified visitor (no cookie set) goes to a random multiplex path. If empty, will pass through."),
      '#default_value' => $this->config('multiplex.settings')->get('unidentified_user_path'),
    ];
    $form['inventory_cookie'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory Cookie Name'),
      '#description' => $this->t("ID of cookie that contains the user's inventory."),
      '#default_value' => "STYXKEY_inventory"
    ];
    $form['inventory_added_cookie'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory Added Cookie Name'),
      '#description' => $this->t("ID of cookie that contains the unix timestamp (in milliseconds) of the last item added to inventory."),
      '#default_value' => "STYXKEY_inventory_added"
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*
    if ($form_state->getValue('cookie') == 'validation-test') {
      $form_state->setErrorByName('cookie', $this->t('The value is not correct.'));
    }
    */
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('multiplex.settings')
      ->set('cookie', $form_state->getValue('cookie'))
      ->save();
    $this->config('multiplex.settings')
      ->set('unidentified_user_path', $form_state->getValue('unidentified_user_path'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
