<?php

namespace Drupal\stripe_checkout\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Youtube settings for this site.
 */
class StripeCheckoutSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'stripe_checkout_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['stripe_checkout.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('stripe_checkout.settings');
    $form['text'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . t('The following settings will be used as default values
        on all fields formatted as Stripe Checkout.') . '</p>',
    ];
    $form['stripe_checkout_name'] = [
      '#type' => 'textfield',
      '#title' => t('Company name'),
      '#description' => t('The name of your company or website.'),
      '#default_value' => $config->get('stripe_checkout_name'),
    ];
    $form['stripe_checkout_key_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Secret key'),
      '#description' => t('Your secret key (test or live).'),
      '#default_value' => $config->get('stripe_checkout_key_secret'),
    ];
    $form['stripe_checkout_key_public'] = [
      '#type' => 'textfield',
      '#title' => t('Public key'),
      '#description' => t('Your publishable key (test or live).'),
      '#default_value' => $config->get('stripe_checkout_key_public'),
    ];
    $form['stripe_checkout_currency'] = [
      '#type' => 'textfield',
      '#title' => t('Default currency'),
      '#description' => t('The currency of the amount (3-letter ISO code). May later be overridden
        on a per-field basis.'),
      '#default_value' => $config->get('stripe_checkout_currency'),
    ];
    $form['stripe_checkout_icon'] = [
      '#type' => 'textfield',
      '#title' => t('Icon path'),
      '#description' => t('Show the icon at the following path in the credit card input screen.'),
      '#default_value' => $config->get('stripe_checkout_icon'),
    ];
    $form['stripe_checkout_enforce_csp'] = [
      '#type' => 'checkbox',
      '#title' => t('Enforce Content Security Policy (CSP)'),
      '#description' => t('Limits pages with a Stripe Checkout button to only load resources from this site and checkout.stripe.com. No other embedded content will be allowed.'),
      '#default_value' => $config->get('stripe_checkout_enforce_csp'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    \Drupal::configFactory()->getEditable('stripe_checkout.settings')
      ->set('stripe_checkout_name', $values['stripe_checkout_name'])
      ->set('stripe_checkout_key_secret', $values['stripe_checkout_key_secret'])
      ->set('stripe_checkout_key_public', $values['stripe_checkout_key_public'])
      ->set('stripe_checkout_currency', $values['stripe_checkout_currency'])
      ->set('stripe_checkout_icon', $values['stripe_checkout_icon'])
      ->set('stripe_checkout_enforce_csp', $values['stripe_checkout_enforce_csp'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
