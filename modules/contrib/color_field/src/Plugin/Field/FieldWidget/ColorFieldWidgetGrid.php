<?php

/**
 * @file
 * Contains Drupal\color_field\Plugin\Field\FieldWidget\ColorFieldWidgetGrid.
 */

namespace Drupal\color_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'color_field_default' widget.
 *
 * @FieldWidget(
 *   id = "color_field_widget_grid",
 *   module = "color_field",
 *   label = @Translation("Color grid"),
 *   field_types = {
 *     "color_field_type"
 *   }
 * )
 */
class ColorFieldWidgetGrid extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'cell_width' => 10,
      'cell_height' => 10,
      'cell_margin' => 1,
      'box_width' => 115,
      'box_height' => 20,
      'columns' => 16,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['cell_width'] = array(
      '#type' => 'textfield',
      '#title' => t('Cell width'),
      '#default_value' => $this->getSetting('cell_width'),
      '#required' => TRUE,
      '#description' => t('Width of each individual color cell.'),
    );
    $element['cell_height'] = array(
      '#type' => 'textfield',
      '#title' => t('Height width'),
      '#default_value' => $this->getSetting('cell_height'),
      '#required' => TRUE,
      '#description' => t('Height of each individual color cell.'),
    );
    $element['cell_margin'] = array(
      '#type' => 'textfield',
      '#title' => t('Cell margin'),
      '#default_value' => $this->getSetting('cell_margin'),
      '#required' => TRUE,
      '#description' => t('Margin of each individual color cell.'),
    );
    $element['box_width'] = array(
      '#type' => 'textfield',
      '#title' => t('Box width'),
      '#default_value' => $this->getSetting('box_width'),
      '#required' => TRUE,
      '#description' => t('Width of the color display box.'),
    );
    $element['box_height'] = array(
      '#type' => 'textfield',
      '#title' => t('Box height'),
      '#default_value' => $this->getSetting('box_height'),
      '#required' => TRUE,
      '#description' => t('Height of the color display box.'),
    );
    $element['columns'] = array(
      '#type' => 'textfield',
      '#title' => t('Columns number'),
      '#default_value' => $this->getSetting('columns'),
      '#required' => TRUE,
      '#description' => t('Number of columns to display. Color order may look strange if this is altered.'),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $cell_width = $this->getSetting('cell_width');
    $cell_height = $this->getSetting('cell_height');
    $cell_margin = $this->getSetting('cell_margin');
    $box_width = $this->getSetting('box_width');
    $box_height = $this->getSetting('box_height');
    $columns = $this->getSetting('columns');

    if (!empty($cell_width)) {
      $summary[] = t('Cell width: @cell_width', array('@cell_width' => $cell_width));
    }

    if (!empty($cell_height)) {
      $summary[] = t('Cell height: @cell_height', array('@cell_height' => $cell_height));
    }

    if (!empty($cell_margin)) {
      $summary[] = t('Cell margin: @cell_margin', array('@cell_margin' => $cell_margin));
    }

    if (!empty($box_width)) {
      $summary[] = t('Box width: @box_width', array('@box_width' => $box_width));
    }

    if (!empty($box_height)) {
      $summary[] = t('Box height: @box_height', array('@box_height' => $box_height));
    }

    if (!empty($columns)) {
      $summary[] = t('Columns: @columns', array('@columns' => $columns));
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
    // We are nesting some sub-elements inside the parent, so we need a wrapper.
    // We also need to add another #title attribute at the top level for ease in
    // identifying this item in error messages. We do not want to display this
    // title because the actual title display is handled at a higher level by
    // the Field module.

    //$element['#theme_wrappers'] = array('color_field_widget_grid');

    $element['#attached']['library'][] = 'color_field/color-field-widget-grid';

    // Set Drupal settings.
    $settings = $this->getSettings();
    $element['#attached']['drupalSettings']['color_field']['color_field_widget_grid'] = $settings;

    // Prepare color.
    $color = NULL;
    if (isset($items[$delta]->color)) {
      $color = $items[$delta]->color;
      if (substr($color, 0, 1) !== '#') {
        $color = '#' . $color;
      }
    }

    $element['color'] = array(
      '#title' => t('Color'),
      '#type' => 'textfield',
      '#maxlength' => 7,
      '#size' => 7,
      '#required' => $element['#required'],
      '#default_value' => $color,
      '#attributes' => array('class' => array('js-color-field-widget-grid__color')),
    );

    if ($this->getFieldSetting('opacity')) {
      $element['color']['#prefix'] = '<div class="container-inline">';

      $element['opacity'] = array(
        '#title' => t('Opacity'),
        '#type' => 'textfield',
        '#maxlength' => 4,
        '#size' => 4,
        '#required' => $element['#required'],
        '#default_value' => isset($items[$delta]->opacity) ? $items[$delta]->opacity : NULL,
        '#suffix' => '</div>',
      );
    }

    return $element;
  }

}
