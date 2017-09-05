<?php

/**
 * @file
 * Contains Drupal\color_field\Plugin\Field\FieldWidget\ColorFieldWidgetDefault.
 */

namespace Drupal\color_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the color_widget default input widget.
 *
 * @FieldWidget(
 *   id = "color_field_widget_default",
 *   module = "color_field",
 *   label = @Translation("Color default"),
 *   field_types = {
 *     "color_field_type"
 *   }
 * )
 */
class ColorFieldWidgetDefault extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'placeholder_color' => '',
      'placeholder_opacity' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['placeholder_color'] = array(
      '#type' => 'textfield',
      '#title' => t('Color placeholder'),
      '#default_value' => $this->getSetting('placeholder_color'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    );
    $element['placeholder_opacity'] = array(
      '#type' => 'textfield',
      '#title' => t('Opacity placeholder'),
      '#default_value' => $this->getSetting('placeholder_opacity'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $placeholder_color = $this->getSetting('placeholder_color');
    $placeholder_opacity = $this->getSetting('placeholder_opacity');

    if (!empty($placeholder_color)) {
      $summary[] = t('Color placeholder: @placeholder_color', array('@placeholder_color' => $placeholder_color));
    }

    if (!empty($placeholder_opacity)) {
      $summary[] = t('Opacity placeholder: @placeholder_opacity', array('@placeholder_opacity' => $placeholder_opacity));
    }

    if (empty($summary)) {
      $summary[] = t('No placeholder');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $label = $this->fieldDefinition->getLabel();

    $element['color'] = array(
      '#title' => t($label),
      '#type' => 'textfield',
      '#maxlength' => 7,
      '#size' => 7,
      '#required' => $element['#required'],
      '#placeholder' => $this->getSetting('placeholder_color'),
      '#default_value' => isset($items[$delta]->color) ? $items[$delta]->color : NULL,
    );

    if ($this->getFieldSetting('opacity')) {
      $element['color']['#prefix'] = '<div class="container-inline">';

      $element['opacity'] = array(
        '#title' => t('Opacity'),
        '#type' => 'textfield',
        '#maxlength' => 4,
        '#size' => 4,
        '#required' => $element['#required'],
        '#placeholder' => $this->getSetting('placeholder_opacity'),
        '#default_value' => isset($items[$delta]->opacity) ? $items[$delta]->opacity : NULL,
        '#suffix' => '</div>',
      );
    }

    return $element;
  }

}
