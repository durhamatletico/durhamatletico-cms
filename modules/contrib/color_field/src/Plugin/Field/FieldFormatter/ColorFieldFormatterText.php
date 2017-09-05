<?php

/**
 * @file
 * Contains Drupal\color_field\Plugin\Field\FieldFormatter\ColorFieldFormatterText.
 */

namespace Drupal\color_field\Plugin\Field\FieldFormatter;

use Drupal\color_field\Plugin\Field\FieldType\ColorFieldType;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\color_field\ColorHex;

/**
 * Plugin implementation of the color_field text formatter.
 *
 * @FieldFormatter(
 *   id = "color_field_formatter_text",
 *   module = "color_field",
 *   label = @Translation("Color text"),
 *   field_types = {
 *     "color_field_type"
 *   }
 * )
 */
class ColorFieldFormatterText extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'format' => 'hex',
      'opacity' => TRUE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $opacity = $this->getFieldSetting('opacity');

    $elements = [];

    $elements['format'] = array(
      '#type' => 'select',
      '#title' => t('Format'),
      '#options' => $this->getColorFormat(),
      '#default_value' => $this->getSetting('format'),
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
   * @param string $format
   * @return array|string
   */
  protected function getColorFormat($format = NULL) {
    $formats = [];
    $formats['hex'] = $this->t('Hex triplet');
    $formats['rgb'] = $this->t('RGB Decimal');

    if ($format) {
      return $formats[$format];
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

    $summary[] = t('@format', array(
      '@format' => $this->getColorFormat($settings['format']),
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
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
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

    switch ($settings['format']) {
      case 'hex':
        if ($opacity && $settings['opacity']) {
          $output = $color_hex->toString(TRUE);
        } else {
          $output = $color_hex->toString(FALSE);
        }
        break;

      case 'rgb':
        if ($opacity && $settings['opacity']) {
          $output = $color_hex->toRGB()->toString(TRUE);
        } else {
          $output = $color_hex->toRGB()->toString(FALSE);
        }
        break;
    }

    return $output;
  }

}
