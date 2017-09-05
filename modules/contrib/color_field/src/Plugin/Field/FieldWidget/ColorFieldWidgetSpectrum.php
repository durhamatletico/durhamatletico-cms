<?php

/**
 * @file
 * Contains Drupal\color_field\Plugin\Field\FieldWidget\ColorFieldWidgetSpectrum.
 */

namespace Drupal\color_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the color_field spectrum widget.
 *
 * @FieldWidget(
 *   id = "color_field_widget_spectrum",
 *   module = "color_field",
 *   label = @Translation("Color spectrum"),
 *   field_types = {
 *     "color_field_type"
 *   }
 * )
 */
class ColorFieldWidgetSpectrum extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'show_input' => FALSE,
      'show_palette' => FALSE,
      'palette' => '',
      'show_palette_only' => TRUE,
      'show_buttons' => FALSE,
      'allow_empty' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['show_input'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show Input'),
      '#default_value' => $this->getSetting('show_input'),
      '#description' => t('Allow free form typing.'),
    );
    $element['show_palette'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show Palette'),
      '#default_value' => $this->getSetting('show_palette'),
      '#description' => t('Show or hide Palette in Spectrum Widget'),
    );
    $element['palette'] = array(
      '#type' => 'textarea',
      '#title' => t('Color Palette'),
      '#default_value' => $this->getSetting('palette'),
      '#description' => t('Selectable color palette to accompany the Spectrum Widget'),
      '#states' => array(
        'visible' => array(
          ':input[name="field[settings][show_palette]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $element['show_palette_only'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show Palette Only'),
      '#default_value' => $this->getSetting('show_palette_only'),
      '#description' => t('Only show thePalette in Spectrum Widget and nothing else'),
    );
    $element['show_buttons'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show Buttons'),
      '#default_value' => $this->getSetting('show_buttons'),
      '#description' => t('Add Cancel/Confirm Button.'),
    );
    $element['allow_empty'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow Empty'),
      '#default_value' => $this->getSetting('allow_empty'),
      '#description' => t('Allow empty value.'),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // We are nesting some sub-elements inside the parent, so we need a wrapper.
    // We also need to add another #title attribute at the top level for ease in
    // identifying this item in error messages. We do not want to display this
    // title because the actual title display is handled at a higher level by
    // the Field module.

    $element['#theme_wrappers'] = array('color_field_widget_spectrum');

    $element['#attached']['library'][] = 'color_field/color-field-widget-spectrum';

    // Set Drupal settings.
    $settings = $this->getSettings();
    $settings['show_alpha'] = $this->getFieldSetting('opacity');
    $element['#attached']['drupalSettings']['color_field']['color_field_widget_spectrum'] = $settings;

    // Prepare color.
    $color = NULL;
    if (isset($items[$delta]->color)) {
      $color = $items[$delta]->color;
      if (substr($color, 0, 1) !== '#') {
        $color = '#' . $color;
      }
    }

    $element['color'] = array(
      '#type' => 'textfield',
      '#maxlength' => 7,
      '#size' => 7,
      '#required' => $element['#required'],
      '#default_value' => $color,
      '#attributes' => array('class' => array('js-color-field-widget-spectrum__color')),
    );

    if ($this->getFieldSetting('opacity')) {
      $element['opacity'] = array(
        '#type' => 'textfield',
        '#maxlength' => 4,
        '#size' => 4,
        '#required' => $element['#required'],
        '#default_value' => isset($items[$delta]->opacity) ? $items[$delta]->opacity : NULL,
        '#attributes' => array('class' => array('js-color-field-widget-spectrum__opacity', 'visually-hidden')),
      );
    }

    return $element;
  }

}
