<?php

namespace Drupal\durhamatletico_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'GoldenBootFall2017Division1Futsal' block.
 *
 * @Block(
 *  id = "golden_boot_fall2017_division1_futsal",
 *  admin_label = @Translation("Golden boot fall2017 division 1 futsal"),
 * )
 */
class GoldenBootFall2017Division1Futsal extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::service('durhamatletico_core.goldenboot')->getGoldenBootForDivision(2485);
  }

}
