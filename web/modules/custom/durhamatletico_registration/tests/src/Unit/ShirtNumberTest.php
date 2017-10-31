<?php

namespace Drupal\Tests\durhamatletico_registration\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\views\ResultRow;

/**
 * @coversDefaultClass Drupal\durhamatletico_registration\Plugin\views\field\ShirtNumber
 * @group durhamatletico_registration
 */
class ShirtNumberTest extends UnitTestCase {

  public function testShirtNumberRetrieval() {
    $entity_type_manager = $this->prophesize('Drupal\Core\Entity\EntityTypeManager');
    $result_row = new ResultRow(
      [
        'users_field_data_node__field_players_uid' => 2,
        'nid' => 234
      ]
    );
    $registration_service = $this->prophesize(
      'Drupal\durhamatletico_registration\RegistrationService'
    );
    $shirt_number = $this->getMockBuilder('Drupal\durhamatletico_registration\Plugin\views\field\ShirtNumber')
      ->setMethods(['getRegistrationNode'])
      ->setConstructorArgs([[], 'shirt_number', [], $registration_service->reveal(), $entity_type_manager->reveal()])
      ->getMock();
    $field = $this->prophesize('Drupal\Core\Field\FieldItemListInterface');
    $field->getString()->willReturn('55');
    $node = $this->prophesize('Drupal\node\Entity\node');

    $node->get('field_registration_shirt_number')->willReturn($field->reveal());
    $shirt_number->expects($this->once())
      ->method('getRegistrationNode')->willReturn($node->reveal());
    $render = $shirt_number->render($result_row);
    $this->assertTrue($render == '55');
    // Check if registration node is not found.
     $shirt_number = $this->getMockBuilder('Drupal\durhamatletico_registration\Plugin\views\field\ShirtNumber')
      ->setMethods(['getRegistrationNode'])
      ->setConstructorArgs([[], 'shirt_number', [], $registration_service->reveal(), $entity_type_manager->reveal()])
      ->getMock();
    $shirt_number->expects($this->once())
      ->method('getRegistrationNode')->willReturn(NULL);
    $render = $shirt_number->render($result_row);
    $this->assertTrue($render == '99');
  }
}


