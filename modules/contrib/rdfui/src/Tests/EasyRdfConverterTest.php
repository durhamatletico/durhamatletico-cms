<?php

/**
 * @file
 * Contains \Drupal\rdfui\Tests\EasyRdfConverterTest.
 */

namespace Drupal\rdfui\Tests;

use Drupal\rdfui\SchemaOrgConverter;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests the Easy Rdf Converter Class & SchemaOrgConverter class.
 *
 * @group RDF UI
 */
class EasyRdfConverterTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('rdf', 'rdfui');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->graph = new SchemaOrgConverter();
  }

  /**
   * Tests that Schema.org types are correctly loaded.
   */
  protected function testSchemaTypes() {
    $types = $this->graph->getListTypes();
    $this->assertTrue(in_array("Person", $types), 'Schema.Org types loaded correctly');
    $this->assertTrue(in_array("Event", $types), 'Schema.Org types loaded correctly');
    $this->assertTrue(in_array("Recipe", $types), 'Schema.Org types loaded correctly');
    $this->assertFalse(in_array("name", $types), 'Properties are not in the list of Types');
  }

  /**
   * Tests that Schema.org properties are correctly loaded.
   */
  protected function testSchemaProperty() {
    $properties = $this->graph->getListProperties();
    $this->assertTrue(in_array("name", $properties), 'Schema.Org properties loaded correctly');
    $this->assertTrue(in_array("url", $properties), 'Schema.Org properties loaded correctly');
    $this->assertTrue(in_array("image", $properties), 'Schema.Org properties loaded correctly');
    $this->assertFalse(in_array("Person", $properties), 'Types are not in the list of Properties');
  }

  /**
   * Tests that correct properties are returned for a given type.
   */
  protected function testPropertiesOfType() {
    $properties = $this->graph->getTypeProperties("schema:Article");
    $this->assertTrue(in_array("wordCount", $properties), 'Properties of Type(Article) loaded.');
    $this->assertTrue(in_array("author", $properties), 'Properties of parent Type(CreativeWork)loaded.');
    $this->assertTrue(in_array("name", $properties), 'Properties of base Type(Thing) loaded.');
    $this->assertFalse(in_array("birthDate", $properties), 'Properties not in the Type are not loaded.');

    // Test that deprecated properties are not listed.
    $properties = $this->graph->getTypeProperties("schema:Event");
    $this->assertTrue(in_array("attendee", $properties), 'Property "attendee" of Type "Event" is listed.');
    $this->assertFalse(in_array("attendees", $properties), 'Deprecated property "attendees" of Type "Event" is not listed.');
  }

}
