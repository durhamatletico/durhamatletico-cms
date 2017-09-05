<?php

/**
 * @file
 * Contains Drupal\color_field\Plugin\Field\FieldFormatter\ColorFieldFormatterCss.
 */

namespace Drupal\color_field\Plugin\Field\FieldFormatter;

use Drupal\color_field\Plugin\Field\FieldType\ColorFieldType;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\color_field\ColorHex;

/**
 * Plugin implementation of the color_field css declaration formatter.
 *
 * @FieldFormatter(
 *   id = "color_field_formatter_css",
 *   module = "color_field",
 *   label = @Translation("Color CSS declaration"),
 *   field_types = {
 *     "color_field_type"
 *   }
 * )
 */
class ColorFieldFormatterCss extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'selector' => 'body',
      'property' => 'background-color',
      'important' => TRUE,
      'opacity' => TRUE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $opacity = $this->getFieldSetting('opacity');

    $elements = [];

    $elements['selector'] = array(
      '#title' => t('Selector'),
      '#description' => t('A valid CSS selector such as <code>.links > li > a, #logo</code>.'),
      '#type' => 'textarea',
      '#rows' => '1',
      '#default_value' => $this->getSetting('selector'),
      '#required' => TRUE,
      '#placeholder' => 'body > div > a',
    );
    //$element['token'] = array(
    //  '#theme' => 'token_tree',
    //  '#token_types' => array($instance['entity_type']),
    //  '#dialog' => TRUE,
    //);
    $elements['property'] = array(
      '#title' => t('Property'),
      '#description' => t(''),
      '#type' => 'select',
      '#default_value' => $this->getSetting('property'),
      '#required' => TRUE,
      '#options' => array(
        'background-color' => t('Background color'),
        'color' => t('Text color'),
      ),
    );
    $elements['important'] = array(
      '#title' => t('Important'),
      '#description' => t('Whenever this declaration is more important than others.'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('important'),
    );

    if ($opacity) {
      $elements['opacity'] = array(
        '#type' => 'checkbox',
        '#title' => t('Display opacity'),
        '#default_value' => $this->getSetting('opacity'),
      );
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $opacity = $this->getFieldSetting('opacity');
    $settings = $this->getSettings();

    $summary = [];

    $summary[] = t('CSS selector : @css_selector', array('@css_selector' => $settings['selector']));
    $summary[] = t('CSS property : @css_property', array('@css_property' => $settings['property']));
    $summary[] = t('!important declaration : @important_declaration', array('@important_declaration' => (($settings['important']) ? t('Yes') : t('No'))));

    if ($opacity && $settings['opacity']) {
      $summary[] = t('Display with opacity.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $settings = $this->getSettings();

    $elements = [];

    foreach ($items as $delta => $item) {

      $value = $this->viewValue($item);
      $selector = $settings['selector'];
      $important = ($settings['important']) ? ' !important' : '';
      $property = $settings['property'];

      $inline_css = $selector . ' { ' . $property . ': ' . $value . $important . '; }';

      // @todo: Not sure this is the best way.
      // https://www.drupal.org/node/2391025
      // https://www.drupal.org/node/2274843
      $elements['#attached']['html_head'][] = [[
        '#tag' => 'style',
        '#value' => $inline_css,
      ], 'colorfield_css'];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewValue(ColorFieldType $item) {
    $opacity = $this->getFieldSetting('opacity');
    $settings = $this->getSettings();

    $color_hex = new ColorHex($item->color, $item->opacity);

    if ($opacity && $settings['opacity']) {
      $rgbtext = $color_hex->toRGB()->toString(TRUE);
    } else {
      $rgbtext = $color_hex->toRGB()->toString(FALSE);
    }

    return $rgbtext;
  }

}
