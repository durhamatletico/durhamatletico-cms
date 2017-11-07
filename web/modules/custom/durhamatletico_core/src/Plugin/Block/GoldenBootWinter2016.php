<?php

namespace Drupal\durhamatletico_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'GoldenBootWinter2016' block.
 *
 * @Block(
 *  id = "golden_boot_winter2016",
 *  admin_label = @Translation("Golden boot winter2016"),
 * )
 */
class GoldenBootWinter2016 extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::service('durhamatletico_core.goldenboot')->getGoldenBootForDivision(177);
  }

}
