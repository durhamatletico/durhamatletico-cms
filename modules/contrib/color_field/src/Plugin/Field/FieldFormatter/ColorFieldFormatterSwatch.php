<?php

/**
 * @file
 * Contains Drupal\color_field\Plugin\Field\FieldFormatter\ColorFieldFormatterSwatch.
 */

namespace Drupal\color_field\Plugin\Field\FieldFormatter;

use Drupal\color_field\Plugin\Field\FieldType\ColorFieldType;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\color_field\ColorHex;

/**
 * Plugin implementation of the color_field swatch formatter.
 *
 * @FieldFormatter(
 *   id = "color_field_formatter_swatch",
 *   module = "color_field",
 *   label = @Translation("Color swatch"),
 *   field_types = {
 *     "color_field_type"
 *   }
 * )
 */
class ColorFieldFormatterSwatch extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'shape' => 'square',
      'width' => 50,
      'height' => 50,
      'opacity' => TRUE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $opacity = $this->getFieldSetting('opacity');

    $elements = [];

    $elements['shape'] = array(
      '#type' => 'select',
      '#title' => t('Shape'),
      '#options' => $this->getShape(),
      '#default_value' => $this->getSetting('shape'),
      '#description' => t(''),
    );
    $elements['width'] = array(
      '#type' => 'number',
      '#title' => t('Width'),
      '#default_value' => $this->getSetting('width'),
      '#min' => 1,
      '#description' => t(''),
    );
    $elements['height'] = array(
      '#type' => 'number',
      '#title' => t('Height'),
      '#default_value' => $this->getSetting('height'),
      '#min' => 1,
      '#description' => t(''),
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
   * @param string $shape
   * @return array|string
   */
  protected function getShape($shape = NULL) {
    $formats = [];
    $formats['square'] = $this->t('Square');
    $formats['circle'] = $this->t('Circle');
    $formats['parallelogram'] = $this->t('Parallelogram');

    if ($shape) {
      return $formats[$shape];
    }
    return $formats;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $opacity = $this->getFieldSetting('opacity');
    $settings = $this->getSettings();

    $summary = [];

    $summary[] = t('@shape', array(
      '@shape' => $this->getShape($settings['shape']),
    ));

    $summary[] = t('Width: @width Height: @height', array(
      '@width' => $settings['width'],
      '@height' => $settings['height']
    ));

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

    $elements['#attached']['library'][] = 'color_field/color-field-formatter-swatch';

    foreach ($items as $delta => $item) {
      $elements[$delta] = array(
        '#theme' => 'color_field_formatter_swatch',
        '#color' => $this->viewValue($item),
        '#shape' => $settings['shape'],
        '#width' => $settings['width'],
        '#height' => $settings['height'],
      );
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
