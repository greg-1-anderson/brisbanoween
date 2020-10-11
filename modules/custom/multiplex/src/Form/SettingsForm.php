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
    $form['cookie'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie'),
      '#description' => $this->t("ID of cookie that identifies visitor's identity."),
      '#default_value' => $this->config('multiplex.settings')->get('cookie'),
    ];
    $form['unidentified_user_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unidentified User Path'),
      '#description' => $this->t("Page to redirect to if an unidentified visitor (no cookie set) goes to a random multiplex path. If empty, will pass through."),
      '#default_value' => $this->config('multiplex.settings')->get('unidentified_user_path'),
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
