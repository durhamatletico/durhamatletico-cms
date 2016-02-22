<?php

/**
 * @file
 * Contains \Drupal\rdf_builder\Tests\ContentTypeBuilderTest.
 */

namespace Drupal\rdf_builder\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the Content Type Builder.
 *
 * @group RDF UI Builder
 */
class ContentTypeBuilderTest extends WebTestBase {

  /**
   * Modules to enable.
   */
  public static $modules = array(
    'rdf_builder',
    'rdfui',
    'rdf',
    'field',
    'node',
    'field_ui',
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Schema.Org driven Content Type Builder',
      'description' => 'Tests the functionality of the ContentBuilder Form.',
      'group' => 'RDF UI Builder',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create test user.
    $this->admin_user = $this->drupalCreateUser(array('administer content types'));
    $this->drupalLogin($this->admin_user);
  }

  /**
   * Tests submission of Content Type Builder and creation of content type.
   */
  protected function testContentTypeCreate() {
    $this->editFormOne();

    foreach (array('email', 'name') as $element) {
      $this->assertText($element, format_string('property "@element" of "@type" was found.', array(
        '@element' => $element,
        '@type' => $this->rdf_type,
      )));
    }

    $this->assertFieldByName('fields[schema:email][enable]', NULL, 'Checkbox for property found');
    $this->assertFieldByName('fields[schema:email][type]', NULL, 'Dropdown list for data type found.');

    $edit = array(
      'fields[schema:email][enable]' => '1',
      'fields[schema:email][type]' => 'email',
      'fields[schema:name][enable]' => '1',
      'fields[schema:name][type]' => '',
    );

    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText("Create field: you need to provide a data type for name", 'Form validated and errors displayed.');
    $this->assertUrl($this->uri, array(), 'Stayed on same page after incorrect submission.');

    $edit['fields[schema:name][type]'] = 'text';

    $this->drupalPostForm(NULL, $edit, t('Save'));
    //$this->assertUrl('admin/structure/types', array(), 'Redirected to correct url upon correct submission.');
    $this->assertText('Content Type Person created', 'Successful content type creation message displayed');
  }

  /**
   * Tests first form of Content Type Builder and its submission.
   */
  protected function editFormOne() {
    $this->uri = 'admin/structure/types/rdf';
    $this->drupalGet($this->uri);
    $this->assertRaw('Create a content type by importing Schema.Org entity type.', "Form one displayed correctly.");

    $this->rdf_type = "schema:Person";

    $edit = array(
      'rdf-type' => $this->rdf_type,
    );

    $this->drupalPostForm(NULL, $edit, t('Next >>'));
    $this->assertRaw('Choose fields to start with.', 'Navigated to page two of the form.');
  }

  /**
   * Tests back button of second form in Content Type Builder.
   */
  protected function testNavigateBack() {
    $this->editFormOne();
    $this->drupalPostForm(NULL, array(), t('< Back'));
    $this->assertRaw("Create a content type by importing Schema.Org entity type.", "Navigated back to form one.");
    // Test default option.
  }

}
