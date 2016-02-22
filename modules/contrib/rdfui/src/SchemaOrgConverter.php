<?php

/**
 * @file
 * Contains \Drupal\rdfui\SchemaOrgConverter.
 */

namespace Drupal\rdfui;

/**
 * Extracts details of RDF resources from Schema.org.
 */
class SchemaOrgConverter extends EasyRdfConverter {
  /**
   * Cache id.
   *
   * @var string
   */
  private $cid = 'schema.org_converter';

  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->create();
  }

  /**
   * {@inheritdoc}
   */
  private function create() {
    $uri = "http://schema.org/docs/schema_org_rdfa.html";
    $type = "rdfa";

    if ($cache = \Drupal::cache()->get($this->cid)) {
      // Fetch cached copy of graph & lists.
      $data = $cache->data;
      $this->graph = $data['graph'];
      $this->listProperties = $data['listProperties'];
      $this->listTypes = $data['listTypes'];
    }
    else {
      $this->createGraph($uri, $type);
      $data = array(
        'graph' => $this->graph,
        'listProperties' => $this->listProperties,
        'listTypes' => $this->listTypes,
      );
      \Drupal::cache()->set($this->cid, $data);
    }
  }
}
