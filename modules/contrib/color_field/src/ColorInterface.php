<?php

namespace Drupal\color_field;

/**
 * Defines a common interface for color classes.
 */
interface ColorInterface {

  public function toString();

  public function toHex();

  public function toRGB();

  //public function toHSV();

  //public function toHSL();

  //public function toRGB();

  //public function toCMYK();

  //public function toCSS();

}
