<?php

namespace Drupal\stripe_checkout\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'stripe_checkout' formatter.
 *
 * @FieldFormatter(
 *   id = "stripe_checkout",
 *   label = @Translation("Stripe checkout"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class StripeCheckoutFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'stripe_checkout_description' => '',
      'stripe_checkout_currency' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $entity_text_fields = [];
    foreach ($form['#fields'] as $field) {
      $config = \Drupal::config('field.field.' . $form['#entity_type'] . '.' . $form['#bundle'] . '.' . $field);
      if ($config->get('field_type') == 'string') {
        $entity_text_fields[$field] = $config->get('label');
      }
    }

    $elements['stripe_checkout_description'] = [
      '#type' => 'select',
      '#title' => t('Description'),
      '#options' => [
        '' => 'none',
        'title' => 'Entity title',
      ] + $entity_text_fields,
      '#default_value' => $this->getSetting('stripe_checkout_description'),
      '#description' => t('Select the source for the description text.'),
    ];

    $currency = \Drupal::config('stripe_checkout.settings')->get('stripe_checkout_currency');

    $elements['stripe_checkout_currency'] = [
      '#type' => 'textfield',
      '#title' => t('Currency'),
      '#size' => 3,
      '#default_value' => $this->getSetting('stripe_checkout_currency'),
      '#description' => t('Override the default currency for this field only, if you wish. Current default is <strong>@currency</strong>', ['@currency' => $currency]),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $description = $this->getSetting('stripe_checkout_description');
    $summary[] = t('Description source: @description', ['@description' => $description]);

    $currency = $this->getSetting('stripe_checkout_currency');
    $summary[] = t('Currency: @currency', ['@currency' => $currency]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareView(array $entities_items) {}

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $element = [];
    $settings = $this->getSettings();

    $currency = \Drupal::config('stripe_checkout.settings')->get('stripe_checkout_currency');

    $description = '';

    if ($settings['stripe_checkout_description'] == 'title') {
      $description = $items->getEntity()->getTitle();
    }
    elseif ($settings['stripe_checkout_description'] != '') {
      // Get the value of the field specified by the display setting.
      $field_items = $items->getEntity()->get($settings['stripe_checkout_description'])->getValue();
      foreach ($field_items as $item) {
        $description = $item['value'];
      }
    }

    $nid = $items->getEntity()->id();

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'stripe_checkout_simple',
        '#description' => $description,
        '#amount' => $item->value,
        '#currency' => ($settings['stripe_checkout_currency'] ? $settings['stripe_checkout_currency'] : $currency),
        '#nid' => $nid,
      ];
    }
    return $element;
  }

}
