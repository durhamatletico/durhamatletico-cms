<?php

/**
 * @file
 * Contains \Drupal\rdfui\EasyRdfConverter.
 */

namespace Drupal\rdfui;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

/**
 * Extracts details of RDF resources from an RDFa document.
 */
abstract class EasyRdfConverter {

  /**
   * EasyRdf Graph of the loaded resource.
   *
   * @var \EasyRdf_Graph
   */
  protected $graph;

  /**
   * List of Types specified in Schema.org as string.
   *
   * @var array
   */
  protected $listTypes;

  /**
   * List of Properties specified in Schema.org as string.
   *
   * @var array
   */
  protected $listProperties;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->listProperties = array();
    $this->listTypes = array();
  }

  /**
   * Creates an EasyRdf_Graph object from the given URI.
   *
   * @param string $uri
   *     URL of a web resource or path of the cached file.
   * @param string $type
   *    Format of the document.
   *
   * @throws \Doctrine\Common\Proxy\Exception\InvalidArgumentException
   *    If invalid type or URL is passed as parameters.
   */
  protected function createGraph($uri, $type) {
    /*
     * Initialize an EasyRdf_Graph object using
     * _construct(string $uri = null, string $data = null,
     *     string $format = null)
     */
    if (!is_string($type) or $type == NULL or $type == '') {
      throw new InvalidArgumentException("\$type should be a string and cannot be null or empty");
    }
    if (!is_string($uri) or $uri == NULL or $uri == '') {
      throw new InvalidArgumentException("\$uri should be a string and cannot be null or empty");
    }

    try {
      if (preg_match('#^http#i', $uri) === 1) {
        $this->graph = new \EasyRdf_Graph($uri, NULL, $type);
        $this->graph->load();
      }
      else {
        $this->graph = new \EasyRdf_Graph(NULL);
        $this->graph->parseFile($uri);
      }
      $this->iterateGraph();
    }
    catch (\Exception $e) {
      throw new InvalidArgumentException("Invalid uri + $e");
    }

  }

  /**
   * Identifies all types and properties of the graph separately.
   */
  private function iterateGraph() {
    $resource_list = $this->graph->resources();

    foreach ($resource_list as $value) {
      if ($value->prefix() !== "schema") {
        continue;
      }
      if ($value->isA("rdf:Property") || $value->isA("rdfs:Property")) {
        $this->addProperties($value);
      }
      else {
        $this->addType($value);
      }
    }
  }

  /**
   * Adds Property label to list.
   *
   * @param \EasyRdf_Resource $value
   *   An EasyRdf_Resource which is a property.
   */
  private function addProperties(\EasyRdf_Resource $value) {
    if ($value != NULL) {
      // Omit deprecated properties.
      if ($value->get("schema:supersededBy")) {
        return;
      }
      $this->listProperties[$value->shorten()] = $value->label();
    }
  }

  /**
   * Adds Type label to list.
   *
   * @param \EasyRdf_Resource $type
   *   An EasyRdf_Resource which is a type.
   */
  private function addType(\EasyRdf_Resource $type) {
    if ($type != NULL) {
      // Omit deprecated types.
      if ($type->get("schema:supersededBy")) {
        return;
      }
      $this->listTypes[$type->shorten()] = $type->label();
    }
  }

  /**
   * Gets a list of Schema.org properties.
   *
   * @return array
   *    Array of all properties in the graph.
   */
  public function getListProperties() {
    return $this->listProperties;
  }

  /**
   * Gets a list of Schema.org types.
   *
   * @return array
   *    Array of all types in the graph.
   */
  public function getListTypes() {
    return $this->listTypes;
  }

  /**
   * Extracts properties of a given type.
   *
   * @param string $type
   *   Schema.Org type of which the properties should be listed.
   *   (eg. "schema:Person").
   *
   * @return array|null
   *   List of properties.
   */
  public function getTypeProperties($type) {
    $tokens = explode(":", $type);
    $prefixes = rdf_get_namespaces();
    $uri = $prefixes[$tokens[0]] . $tokens[1];

    $options = array();
    $options += $this->getProperties($uri);
    asort($options);
    return $options;
  }

  /**
   * Recursive function to extract properties.
   *
   * @param string $uri
   *   URI of schema type.
   *
   * @return array|null
   *   Array of properties of the type and all parent types.
   */
  private function getProperties($uri) {
    $resource = array("type" => "uri", "value" => $uri);
    $property_list = $this->graph->resourcesMatching("http://schema.org/domainIncludes", $resource);
    $options = array();

    foreach ($property_list as $value) {
      // Omit deprecated properties.
      if ($value->get("schema:supersededBy")) {
        continue;
      }
      $options[$value->shorten()] = $value->get("rdfs:label")->getValue();
    }

    $parents = $this->graph->all($uri, "rdfs:subClassOf");
    foreach ($parents as $value) {
      $options += $this->getProperties($value->getUri());
    }
    return $options;
  }

  /**
   * Gets the description of the resource.
   *
   * @param string $uri
   *   URI of the resource (eg: schema:Person).
   *
   * @return mixed
   *   Description of the resource or null.
   */
  public function description($uri) {
    if (empty($uri)) {
      drupal_set_message($this->t("Invalid uri"));
      return NULL;
    }

    $comment = $this->graph->get($uri, "rdfs:comment");
    if (!empty($comment)) {
      return $comment->getValue();
    }
    return NULL;
  }

  /**
   * Gets label of the resource.
   *
   * @param string $uri
   *   URI of the resource (eg: schema:Person).
   *
   * @return string
   *   Label of the resource, if not shortened name.
   */
  public function label($uri) {
    if (empty($uri)) {
      drupal_set_message($this->t("Invalid uri"));
      return NULL;
    }
    $label = $this->graph->label($uri);
    if (!empty($label)) {
      return $label->getValue();
    }

    $names = explode(":", $uri);
    return $names[1];
  }

  /**
   * Gets data types in range of the property.
   *
   * @param string $uri
   *   URI of the resource (eg: schema:name).
   *
   * @return null|array
   *   Array containing URIs of the datatype, if not null.
   */
  public function getRangeDataTypes($uri) {
    if (empty($uri)) {
      drupal_set_message($this->t("Invalid URI"));
      return NULL;
    }
    $range_datatypes = $this->graph->allResources($uri, "schema:rangeIncludes");
    if (!empty($range_datatypes)) {
      $range_datatype_uris = array();
      foreach ($range_datatypes as $type) {
        array_push($range_datatype_uris, $type->getUri());
      }
      return $range_datatype_uris;
    }
    return NULL;
  }

}
