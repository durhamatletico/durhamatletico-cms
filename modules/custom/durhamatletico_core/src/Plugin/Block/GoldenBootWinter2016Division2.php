<?php

/**
 * @file
 * Contains \Drupal\durhamatletico_core\Plugin\Block\GoldenBootWinter2016Division2.
 */

namespace Drupal\durhamatletico_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'GoldenBootWinter2016Division2' block.
 *
 * @Block(
 *  id = "golden_boot_winter2016division2",
 *  admin_label = @Translation("Golden boot winter2016division2"),
 * )
 */
class GoldenBootWinter2016Division2 extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::service('durhamatletico_core.goldenboot')->getGoldenBootForDivision(178);
  }

}
