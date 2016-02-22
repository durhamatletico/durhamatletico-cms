<?php

/**
 * @file
 * Contains \Drupal\rdfui\Tests\RdfUiFieldMappingTest.
 */

namespace Drupal\rdfui\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\field_ui\Tests\FieldUiTestTrait;

/**
 * Tests the functionality of the RDF UI Field Mapping form.
 *
 * @group RDF UI
 */
class RdfUiFieldMappingTest extends WebTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   */
  public static $modules = array('rdfui', 'rdf', 'field', 'field_ui');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'RDF UI Field Mapping',
      'description' => 'Tests the functionality of the FieldMapping Form.',
      'group' => 'RDF UI',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create test user.
    $admin_user = $this->drupalCreateUser(array('access content', 'administer content types', 'administer node fields'));
    $this->drupalLogin($admin_user);

    // Create content type, with underscores.
    $type_name = strtolower($this->randomMachineName(8)) . '_test';
    $type = $this->drupalCreateContentType(array('name' => $type_name, 'type' => $type_name));
    $this->type = $type->id();
  }

  /**
   * Tests Field Mapping Form and save function for unmapped content type.
   */
  protected function testUnmappedTypeFieldUI() {
    echo "in first test case";
    $manage_fields = 'admin/structure/types/manage/' . $this->type;
    $rdf_mappings = $manage_fields . '/fields/rdf';

    $label = 'Test field';
    $this->fieldUIAddNewField($manage_fields, 'test', $label, 'text');

    // Display the "Manage fields RDF" screen and check that the expected fields
    // are displayed.
    $this->drupalGet($rdf_mappings);
    $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr/td[1]', $label, 'Field is displayed in manage field RDF page.');

    $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr[@id="field-test"]/td[4]', 'Unmapped', 'Status displayed correctly when field is unmapped.');

    // Add RDF property and save.
    $mapped_value = 'schema:name';
    $edit = array('fields[field_test][rdf-predicate]' => $mapped_value);
    $this->drupalPostForm($rdf_mappings, $edit, t('Save'));
    $this->assertRaw(t('Your settings have been saved.'), 'Manage Field RDF page reloaded.');
    $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr[@id="field-test"]/td[4]', 'Mapped', 'Status displayed correctly when field is mapped.');

    $mapping = rdf_get_mapping('node', $this->type);
    $bundle_mapping = $mapping->getFieldMapping('field_test');
    $this->assertEqual($bundle_mapping['properties'][0], $mapped_value, "Selected RDF mappings saved.");
  }

  /**
   * Tests FieldMappingForm for content type mapped to a RDF type.
   */
  protected function testMappedTypeFieldUI() {
    $mapping = rdf_get_mapping('node', $this->type);
    $mapping->setBundleMapping(array('types' => array("schema:Person")))
      ->save();

    $manage_fields = 'admin/structure/types/manage/' . $this->type;
    $rdf_mappings = $manage_fields . '/fields/rdf';

    // Create a field, and a node with some data for the field.
    $label = 'Test field';
    $this->fieldUIAddNewField($manage_fields, 'test', $label, 'text');

    // Display the "Manage fields RDF" screen and check that the expected fields
    // are displayed.
    $this->drupalGet($rdf_mappings);
    $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr/td[1]', $label, 'Field is displayed in manage field RDF page.');

    $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr[@id="field-test"]/td[4]', 'Unmapped', 'Status displayed correctly when field is unmapped.');

    // Add RDF property and save.
    $mapped_value = 'schema:birthDate';
    $edit = array('fields[field_test][rdf-predicate]' => $mapped_value);
    $this->drupalPostForm($rdf_mappings, $edit, t('Save'));
    $this->assertRaw(t('Your settings have been saved.'), 'Manage Field RDF page reloaded.');
    $this->assertFieldByXPath('//table[@id="rdf-mapping"]//tr[@id="field-test"]/td[4]', 'Mapped', 'Status displayed correctly when field is mapped.');

    $mapping = rdf_get_mapping('node', $this->type);
    $bundle_mapping = $mapping->getFieldMapping('field_test');
    $this->assertEqual($bundle_mapping['properties'][0], $mapped_value, "Selected RDF mappings saved.");
  }
}
