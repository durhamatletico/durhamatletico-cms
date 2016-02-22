<?php

/**
 * @file
 * Contains \Drupal\rdfui\Form\FieldMappings.
 */

namespace Drupal\rdfui\Form;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\field\FieldConfigInterface;
use Drupal\rdfui\SchemaOrgConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RDF UI Field Mapping form.
 */
class FieldMappings extends FormBase {

  /**
   * The EasyRdfConverter.
   *
   * @var \Drupal\rdfui\EasyRdfConverter
   */
  protected $rdfConverter;

  protected $displayContext = 'form';

  protected $entityTypeId;

  protected $bundle;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.field.widget')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL, $bundle = NULL) {
    $this->entityTypeId = $entity_type_id;
    $this->bundle = $bundle;

    $this->rdfConverter = new SchemaOrgConverter();
    // Gather bundle information.
    $instances = array_filter(\Drupal::entityManager()
      ->getFieldDefinitions($entity_type_id, $bundle), function ($field_definition) {
      return $field_definition instanceof FieldConfigInterface;
    });

    $mappings = rdf_get_mapping($this->entityTypeId, $this->bundle);
    $options = NULL;
    $bundle_mapping = $mappings->getBundleMapping();

    if (!empty($bundle_mapping)) {
      $type = $bundle_mapping['types']['0'];
      $options = $this->rdfConverter->getTypeProperties($type);
    } else {
      $options = $this->rdfConverter->getListProperties();
    }

    $form += array(
      '#entity_type' => $this->entityTypeId,
      '#bundle' => $this->bundle,
      '#fields' => array_keys($instances),
    );

    $table = array(
      '#type' => 'table',
      '#tree' => TRUE,
      '#header' => array(
        $this->t('Label'),
        $this->t('RDF Property'),
        $this->t('Data Type'),
        $this->t('Status'),
      ),
      '#regions' => $this->getRegions(),
      '#attributes' => array(
        'class' => array('rdfui-field-mappings'),
        'id' => Html::getUniqueId('rdf-mapping'),
      ),
    );

    // Fields.
    foreach ($instances as $name => $instance) {
      $property = $mappings->getFieldMapping($name);
      $table[$name] = array(
        '#attributes' => array(
          'id' => Html::getUniqueId($name),
        ),
        'label' => array(
          '#markup' => $this->t($instance->getLabel()),
        ),
        'rdf-predicate' => array(
          '#id' => 'rdf-predicate',
          '#type' => 'select',
          '#title' => $this->t('RDF Property'),
          '#title_display' => 'invisible',
          '#options' => $options,
          '#empty_option' => '',
          '#attached' => array(
            'library' => array(
              'rdfui/drupal.rdfui.autocomplete',
            ),
          ),
          '#default_value' => !empty($property) ? $property['properties'][0] : '',
        ),
        'type' => array(
          '#title' => $this->t('Data Type'),
          '#title_display' => 'invisible',
          '#markup' => $this->t('Text'),
        ),
        'status' => array(
          '#title' => $this->t('Status'),
          '#title_display' => 'invisible',
          '#markup' => !empty($property['properties'][0]) ? 'Mapped' : 'Unmapped',
        ),
      );
    }

    $table['#regions']['content']['rows_order'] = array();
    foreach (Element::children($table) as $name) {
      $table['#regions']['content']['rows_order'][] = $name;
    }

    $form['fields'] = $table;

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegions() {
    return array(
      'content' => array(
        'title' => $this->t('Content'),
        'invisible' => TRUE,
        // @todo Bring back this message in https://drupal.org/node/1963340.
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @TODO implement method if validation is required.
  }

  /**
   * Overrides \Drupal\field_ui\FormDisplayOverview::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $form_values = $form_state->getValue('fields');
    $mapping = rdf_get_mapping($this->entityTypeId, $this->bundle);

    // Add mapping for title field.
    if ($this->entityTypeId === 'node') {
      $type = $mapping->getFieldMapping('title');
      if (empty($type)) {
        $mapping->setFieldMapping('title', array(
            'properties' => array('schema:name'),
          )
        );
      }
    }

    foreach ($form_values as $key => $value) {
      $mapping->setFieldMapping($key, array(
          'properties' => array($value['rdf-predicate']),
        )
      );
    }
    $mapping->save();

    drupal_set_message($this->t('Your settings have been saved.'));
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return "rdfui_field_mapping_form";
  }

}
