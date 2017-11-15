<?php

namespace Drupal\Tests\durhamatletico_core\Unit;

use Drupal\durhamatletico_core\GoldenBoot;
use Drupal\Tests\UnitTestCase;

/**
 * Class GoldenBootTest.
 *
 * @coversDefaultClass Drupal\durhamatletico_core\GoldenBoot
 * @group durhamatletico_core
 */
class GoldenBootTest extends UnitTestCase {

  /**
   * Test player display name.
   */
  public function testGetPlayerDisplayName() {
    $field = $this->prophesize('Drupal\Core\Field\FieldItemListInterface');
    $field->getString()->willReturn('0');
    $node = $this->prophesize('Drupal\node\Entity\Node');
    $node->get('field_player_who_scored')->willReturn($field->reveal());
    $entityTypeManager = $this->prophesize('Drupal\Core\Entity\EntityTypeManager');
    $goldenBootService = new GoldenBoot($entityTypeManager->reveal());
    $this->assertFalse($goldenBootService->getPlayerDisplayName($node->reveal()));
  }
}
