<?php

/**
 * @file
 * Contains Drupal\color_field\Plugin\Field\FieldType\ColorFieldType.
 */

namespace Drupal\color_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\color_field\ColorHex;

/**
 * Plugin implementation of the 'color_type' field type.
 *
 * @FieldType(
 *   id = "color_field_type",
 *   label = @Translation("Color"),
 *   description = @Translation("Create and store color value."),
 *   default_widget = "color_field_widget_default",
 *   default_formatter = "color_field_formatter_text"
 * )
 */
class ColorFieldType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return array(
      'opacity' => TRUE,
    ) + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'format' => '#HEXHEX',
    ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];

    $element['format'] = array(
      '#type' => 'select',
      '#title' => t('Format storage'),
      '#description' => t('Choose how to store the color.'),
      '#default_value' => $this->getSetting('format'),
      '#options' => array(
        '#HEXHEX' => t('#123ABC'),
        'HEXHEX' => t('123ABC'),
        '#hexhex' => t('#123abc'),
        'hexhex' => t('123abc'),
      ),
    );

    $element += parent::storageSettingsForm($form, $form_state, $has_data);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['opacity'] = array(
      '#type' => 'checkbox',
      '#title' => t('Record opacity'),
      '#description' => t('Whether or not to record.'),
      '#default_value' => $this->getSetting('opacity'),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $format = $field_definition->getSetting('format');
    $color_length = isset($format) ? strlen($format) : 7 ;
    return array(
      'columns' => array(
        'color' => array(
          'description' => 'The color value',
          'type' => 'varchar',
          'length' => $color_length,
          'not null' => FALSE,
        ),
        'opacity' => array(
          'description' => 'The opacity/alphavalue property',
          'type' => 'float',
          'size' => 'tiny',
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'color' => array('color'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['color'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Color'));

    $properties['opacity'] = DataDefinition::create('float')
      ->setLabel(new TranslatableMarkup('Opacity'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('color')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $label = $this->getFieldDefinition()->getLabel();

    $constraints[] = $constraint_manager->create('ComplexData', array(
      'color' => array(
        'Regex' => array(
          'pattern' => '/^#?(([0-9a-fA-F]{2}){3}|([0-9a-fA-F]){3})$/i',
        )
      ),
    ));

    if ($opacity = $this->getSetting('opacity')) {
      $min = 0;
      $constraints[] = $constraint_manager->create('ComplexData', array(
        'opacity' => array(
          'Range' => array(
            'min' => $min,
            'minMessage' => t('%name: the opacity may be no less than %min.', array('%name' => $label, '%min' => $min)),
          )
        ),
      ));

      $max = 1;
      $constraints[] = $constraint_manager->create('ComplexData', array(
        'opacity' => array(
          'Range' => array(
            'max' => $max,
            'maxMessage' => t('%name: the opacity may be no greater than %max.', array('%name' => $label, '%max' => $max)),
          )
        ),
      ));
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $settings = $field_definition->getSettings();

    if ($format = $settings['format']) {
      switch ($format) {
        case '#HEXHEX':
          $values['color'] = '#111AAA';
          break;
        case 'HEXHEX':
          $values['color'] = '111111';
          break;
        case '#hexhex':
          $values['color'] = '#111aaa';
          break;
        case 'hexhex':
          $values['color'] = '111111';
          break;
      }
    }

    $values['opacity'] = 1;

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();

    if ($format = $this->getSetting('format')) {
      $color = $this->color;

      // Clean up data and format it.
      $color = trim($color);

      if (substr($color, 0, 1) === '#') {
        $color = substr($color, 1);
      }

      switch ($format) {
        case '#HEXHEX':
          $color = '#' . strtoupper($color);
          break;
        case 'HEXHEX':
          $color = strtoupper($color);
          break;
        case '#hexhex':
          $color = '#' . strtolower($color);
          break;
        case 'hexhex':
          $color = strtolower($color);
          break;
      }

      $this->color = $color;
    }
  }

}
