<?php

/**
 * @file
 * Contains \Drupal\rdf_builder\Form\ContentBuilderForm.
 */

namespace Drupal\rdf_builder\Form;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\rdfui\SchemaOrgConverter;

class ContentBuilderForm extends FormBase {

  /**
   * Easy_RDF Converter from rdfui.
   *
   * @var /Drupal/rdfui/EasyRdfConverter
   */
  protected $converter;

  /**
   * The field type manager.
   *
   * @var \Drupal\node\Entity\NodeType
   */
  protected $entity;

  /**
   * List of properties selected by user.
   *
   * @var array
   */
  protected $properties;

  /**
   * Existing or created RDF Mapping.
   *
   * @var \Drupal\rdf\Entity\RdfMapping
   */
  protected $rdfMapping;

  /**
   * Prefix for the content type.
   *
   * @var string
   */
  private $prefix;

  /**
   * Array mapping schema.org data types to field types.
   *
   * @var array
   */
  protected $datatype_field_mappings;

  /**
   * Constructs a new ContentBuilder.
   */
  public function __construct() {
    $this->converter = new SchemaOrgConverter();
    $this->datatype_field_mappings = array(
      'http://schema.org/Text' => 'string',
      'http://schema.org/PostalAddress' => 'string_long',
      'http://schema.org/Number' => 'integer',
      'http://schema.org/MediaObject' => 'file',
      'http://schema.org/AudioObject' => 'file',
      'http://schema.org/DateTime' => 'datetime',
      'http://schema.org/Date' => 'datetime',
      'http://schema.org/Integer' => 'integer',
      'http://schema.org/Time' => 'datetime',
      'http://schema.org/ImageObject' => 'image',
      'http://schema.org/Boolean' => 'boolean',
    );
  }

  /**
   * Submit handler for Content Builder next button.
   * Capture the values from page one and store them away so they can be used
   * at final submit time.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function nextSubmit(array &$form, FormStateInterface &$form_state) {

    $form_state->set(['page_values', 1], $form_state->getValues());

    if ($form_state->has(['page_values', 2])) {
      $form_state->setValues($form_state->get(['page_values', 2]));
    }

    // When form rebuilds, build method would be chosen based on to page_num.
    $form_state->set('page_num', 2);
    $form_state->setRebuild();
  }

  /**
   * @inheritdoc
   */
  public function getFormId() {
    return "rdf_builder_content_builder_form";
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Display page 2 if $form_state->get('page_num') == 2.
    if ($form_state->has('page_num') && $form_state->get('page_num') == 2) {
      return $this->buildFormPageTwo($form, $form_state);
    }

    // Otherwise build page 1.
    $form_state->set('page_num', 1);

    $form['#title'] = $this->t('Content types');
    $form['description'] = array(
      '#type' => 'item',
      '#title' => $this->t('Create a content type by importing Schema.Org entity type.'),
    );

    $form['rdf-type'] = array(
      '#title' => $this->t('Type'),
      '#id' => 'rdf-predicate',
      '#type' => 'select',
      '#required' => TRUE,
      '#options' => $this->converter->getListTypes(),
      '#empty_option' => '',
      '#default_value' => $form_state->getValue('rdf-type', ''),
      '#attached' => array(
        'library' => array(
          'rdfui/drupal.rdfui.autocomplete',
        ),
      ),
      '#description' => $this->t('Specify the type you want to associated to this content type e.g. Article, Blog, etc.'),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['next'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Next >>'),
      '#button_type' => 'primary',
      '#submit' => array(array($this, 'nextSubmit')),
      '#validate' => array(array($this, 'nextValidate')),
    );
    return $form;
  }

  /**
   * Returns the form for the second page.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  protected function buildFormPageTwo(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Content types');
    $form['description'] = array(
      '#type' => 'item',
      '#title' => $this->t('Choose fields to start with.'),
    );

    $rdf_type = $form_state->get(['page_values', 1, 'rdf-type']);
    $properties = $this->converter->getTypeProperties($rdf_type);
    $field_types = \Drupal::service('plugin.manager.field.field_type')
      ->getUiDefinitions();

    $field_type_options = array();
    foreach ($field_types as $name => $field_type) {
      // Skip field types which should not be added via user interface.
      if (empty($field_type['no_ui'])) {
        $field_type_options[$name] = $field_type['label'];
      }
    }
    asort($field_type_options);

    $table = array(
      '#type' => 'table',
      '#tree' => TRUE,
      '#header' => array(
        $this->t('Enable'),
        $this->t('Property'),
        $this->t('Data Type'),
      ),
      '#regions' => array(),
      '#attributes' => array(
        'class' => array('rdfui-field-mappings'),
        'id' => Html::getId('rdf-builder'),
      ),
    );

    foreach ($properties as $key => $value) {
      $table[$key] = array(
        '#attributes' => array(
          'id' => Html::getClass($key),
        ),
        'enable' => array(
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#title_display' => 'invisible',
        ),
        'property' => array(
          '#markup' => SafeMarkup::checkPlain($value),
        ),
        'type' => array(
          '#type' => 'select',
          '#title' => $this->t('Data Type'),
          '#title_display' => 'invisible',
          '#options' => $field_type_options,
          '#default_value' => $this->getDefaultFieldType($key),
          '#empty_option' => $this->t('- Select a field type -'),
          '#attributes' => array('class' => array('field-type-select')),
          '#cell_attributes' => array('colspan' => 2),
        ),
      );
    }
    // Fields.
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

    $form['actions']['previous'] = array(
      '#type' => 'submit',
      '#value' => $this->t('< Back'),
      '#submit' => array(array($this, 'pageTwoBackSubmit')),
      '#limit_validation_errors' => array(),
      '#validate' => array(array($this, 'pageTwoBackValidate')),
      '#weight' => -1,
    );
    return $form;
  }

  /**
   * Validate handler for the next button on first page.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function nextValidate(array $form, FormStateInterface $form_state) {
    // @TODO validate if required.
  }

  /**
   * Validate handler for the back button on second page.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function pageTwoBackValidate(array $form, FormStateInterface $form_state) {
    // @TODO validate if required.
  }

  /**
   * Back button handler submit handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function pageTwoBackSubmit(array &$form, FormStateInterface &$form_state) {
    $form_state->setValues($form_state->get(['page_values', 1]));
    $form_state->set('page_num', 1);
    $form_state->setRebuild();
  }

  /**
   * @inheritdoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('fields') as $key => $property) {
      if ($property['enable'] == 1) {
        if (empty($property['type'])) {
          $form_state->setErrorByName('fields][$key][type', $this->t('Create field: you need to provide a data type for %field.', array('%field' => explode(':', $key)[1])));
        }
      }
    }

  }

  /**
   * @inheritdoc
   *
   * Final submit handler- gather all data together and create new content type.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->prefix = $this->randomString(4);
    $this->properties = array();
    foreach ($form_state->getValue('fields') as $key => $property) {
      if ($property['enable'] == 1) {
        $this->properties[$key] = $property;
      }
    }

    $page_one_values = $form_state->get(['page_values', 1]);
    $rdf_type = $page_one_values['rdf-type'];

    $this->createNodeType($rdf_type);

    $this->rdfMapping = rdf_get_mapping('node', $this->entity->id());
    $this->rdfMapping->setBundleMapping(array('types' => array($rdf_type)));

    $this->createField();
    $this->rdfMapping->save();

    drupal_set_message($this->t('Content Type %label created', array('%label' => $this->entity->label())));
    /*@TODO Revert all saved content type and fields in case of error*/
    $form_state->setRedirectUrl(new Url('entity.node.field_ui_fields', array(
      'node_type' => $this->entity->id(),
    )));
  }

  /**
   * Creates a new node_type.
   *
   * @param string $rdf_type
   *   URI of the resource.
   */
  protected function createNodeType($rdf_type) {
    $type = explode(':', $rdf_type);
    $type = $this->prefix . $type[1];

    // Truncate if machine_name is longer than 32 char.
    if (strlen($type) > 32) {
      $type = substr($type, 0, 32);
    }

    $values = array(
      'name' => $this->converter->label($rdf_type),
      'type' => strtolower($type),
      'description' => $this->converter->description($rdf_type),
    );

    try {
      $this->entity =  \Drupal::entityTypeManager()->getStorage('node_type')->create($values);
      $this->entity->save();
    }
    catch (\Exception $e) {
      drupal_set_message('type', $this->t("Error saving content type %invalid.", array('%invalid' => $rdf_type)));
    }
  }

  /**
   * Create fields for the selected properties.
   */
  protected function createField() {
    $entity_type = 'node';
    $bundle = $this->entity->id();
    foreach ($this->properties as $key => $value) {
      $label = $this->converter->label($key);

      // Add the field prefix and truncate if longer than 32 char.
      $field_name = $this->prefix . strtolower($label);
      if (strlen($field_name) > 32) {
        $field_name = substr($field_name, 0, 32);
      }

      $field_storage = array(
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'type' => $value['type'],
        'translatable' => TRUE,
      );
      $instance = array(
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'bundle' => $bundle,
        'label' => $label,
        // Field translatability should be explicitly enabled by the users.
        'translatable' => FALSE,
      );

      // Create the field and instance.
      try {
        \Drupal::entityTypeManager()->getStorage('field_storage_config')->create($field_storage)->save();
        \Drupal::entityTypeManager()->getStorage('field_config')->create($instance)->save();

        // Make sure the field is displayed in the 'default' form mode (using
        // default widget and settings). It stays hidden for other form modes
        // until it is explicitly configured.
        entity_get_form_display($entity_type, $bundle, 'default')
          ->setComponent($field_name)
          ->save();

        // Make sure the field is displayed in the 'default' view mode (using
        // default formatter and settings). It stays hidden for other view
        // modes until it is explicitly configured.
        entity_get_display($entity_type, $bundle, 'default')
          ->setComponent($field_name)
          ->save();

        // RDF Mapping.
        $this->rdfMapping->setFieldMapping($field_name, array(
            'properties' => array($key),
          )
        );
      }
      catch (\Exception $e) {
        drupal_set_message($this->t('There was a problem creating field %label: !message', array(
          '%label' => $instance['label'],
          '!message' => $e->getMessage(),
        )), 'error');
      }
    }
  }

  /**
   * Generates a random string of lower case letters of a given length.
   *
   * @param int $length
   *   Length of the random string.
   *
   * @return string
   *   Return a random string.
   */
  private function randomString($length = 4) {
    $result = '';

    for ($i = 0; $i < $length; $i++) {
      $num = rand(97, 122);
      $result .= chr($num);
    }

    $result = $result . '_';
    return $result;
  }

  /**
   * Gets default datatype for a given URI.
   *
   * @param string $uri
   *   URI of Schema.org property.
   *
   * @return string
   *   Default field type or text if there is no better match.
   */
  protected function getDefaultFieldType($uri) {
    $range_datatypes = $this->converter->getRangeDataTypes($uri);

    foreach ($range_datatypes as $datatype) {
      if (array_key_exists($datatype, $this->datatype_field_mappings)) {
        return $this->datatype_field_mappings[$datatype];
      }
    }
    return 'string';
  }
}
