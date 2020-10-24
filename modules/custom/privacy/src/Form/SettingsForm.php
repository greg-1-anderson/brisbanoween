<?php

namespace Drupal\privacy\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Brisbanoween settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'privacy_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['privacy.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['privacy_disabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Session Tracking'),
      '#description' => $this->t("Don't show the privacy policy, and dont issue any new sessions."),
      '#default_value' => $this->config('privacy.settings')->get('privacy_disabled') ? $this->config('privacy.settings')->get('privacy_disabled') : false
    ];
    $form['privacy_cookie_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy Preference Cookie Name'),
      '#description' => $this->t("The name of the cookie to store the user's privacy choice in"),
      '#default_value' => $this->config('multiplex.settings')->get('unidentified_user_path') ? $this->config('multiplex.settings')->get('unidentified_user_path') : 'cookie-agreed',
    ];
    $form['session_cookie_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Session Cookie Name'),
      '#description' => $this->t("The name of the session cookie to issue if the user accepts the privacy policy"),
      '#default_value' => $this->config('multiplex.settings')->get('session_cookie_name') ? $this->config('multiplex.settings')->get('session_cookie_name') : 'STYXKEY_visitor',
    ];
    $form['privacy_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy Title'),
      '#description' => $this->t("The title of the cookie acceptance dialog"),
      '#default_value' => $this->config('privacy.settings')->get('privacy_title') ? $this->config('privacy.settings')->get('privacy_title') : 'Welcome!'
    ];
    $form['privacy_message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Privacy Message'),
      '#format' => 'full_html',
      '#description' => $this->t("The body of the cookie acceptance dialog.  Keep it short, there isn't much space on a mobile screen."),
      '#default_value' => $this->config('privacy.settings')->get('privacy_message') ? $this->config('privacy.settings')->get('privacy_message') : '<p>This is an interactive Halloween experience!  No personal information will be stored, but we do use cookies to track your progress and location while playing.</p>'
    ];
    $form['privacy_accept_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy Accept Button Label'),
      '#description' => $this->t("The label text for the accept button"),
      '#default_value' => $this->config('privacy.settings')->get('privacy_accept_button') ? $this->config('privacy.settings')->get('privacy_accept_button') : 'I Agree!'
    ];
    $form['privacy_reject_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy Reject Button Label'),
      '#description' => $this->t("The label text for the reject button"),
      '#default_value' => $this->config('privacy.settings')->get('privacy_reject_button') ? $this->config('privacy.settings')->get('privacy_reject_button') : 'No thanks.'
    ];
    $form['privacy_policy_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy Policy Page'),
      '#description' => $this->t("The pretty printed relative URL of the privacy page (ex: /privacy-policy), where the 'Manage Privacy Settings' button will be appended.  Optionally you can add JS to whatever page you want to insert the button and leave this blank."),
      '#default_value' => $this->config('privacy.settings')->get('privacy_policy_page') ? $this->config('privacy.settings')->get('privacy_policy_page') : '/privacy-policy'
    ];
    $form['privacy_auto_accept'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Default Accept'),
      '#description' => $this->t("Assume the user will allow cookies, until they explicitly say they dont want to.  The dialog will not show by default."),
      '#default_value' => $this->config('privacy.settings')->get('privacy_auto_accept') ? $this->config('privacy.settings')->get('privacy_auto_accept') : false
    ];
    $form['privacy_reload_after_accept'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reload After Accept'),
      '#description' => $this->t("A session cookie is not issued until the user accepts cookies.  This means if the page the user is on, would have been different had they had a session cookie, a reload would be needed to show the correct content after dismissing the cookie dialog.  If the page is not dynamic, this is unnecessary."),
      '#default_value' => $this->config('privacy.settings')->get('privacy_reload_after_accept') ? $this->config('privacy.settings')->get('privacy_reload_after_accept') : false
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  	$form_fields = array(
  		'privacy_cookie_name', 'session_cookie_name', 'session_cookie_name', 'privacy_title', 'privacy_message', 'privacy_accept_button', 'privacy_reject_button',
  		'privacy_auto_accept', 'privacy_policy_page', 'privacy_reload_after_accept', 'privacy_disabled'
  	);

  	foreach ($form_fields as $f) {
  		$useValue = $form_state->getValue($f);
  		if ($f == "privacy_message") {
  			$useValue = $useValue['value'];
  		}
		$this->config('privacy.settings')
			->set($f, $useValue)
			->save();
    }

    parent::submitForm($form, $form_state);
  }
}
