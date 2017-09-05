<?php

namespace Drupal\color_field;

/**
 * RGB represents the RGB color format
 */
class ColorRGB extends ColorBase {

  /**
   * The red value (0-255)
   * @var float
   */
  private $red;

  /**
   * The green value (0-255)
   * @var float
   */
  private $green;

  /**
   * The blue value (0-255)
   * @var float
   */
  private $blue;

  /**
   * Create a new RGB color
   *
   * @param int $red
   *   The red (0-255)
   * @param int $green
   *   The green (0-255)
   * @param int $blue
   *   The blue (0-255)
   * @param float $opacity
   *   The opacity
   *
   * @throws Exception
   */
  public function __construct($red, $green, $blue, $opacity) {

    if ($red < 0 || $red > 255) {
      // @throws exception.
    }
    if ($green < 0 || $green > 255) {
      // @throws exception.
    }
    if ($blue < 0 || $blue > 255) {
      // @throws exception.
    }

    $this->red = $red;
    $this->green = $green;
    $this->blue = $blue;
    $this->opacity = floatval($opacity);
  }

  /**
   * Get the red value (rounded)
   *
   * @return int The red value
   */
  public function getRed() {
    return (0.5 + $this->red) | 0;
  }

  /**
   * Get the green value (rounded)
   *
   * @return int The green value
   */
  public function getGreen() {
    return (0.5 + $this->green) | 0;
  }

  /**
   * Get the blue value (rounded)
   *
   * @return int The blue value
   */
  public function getBlue() {
    return (0.5 + $this->blue) | 0;
  }

  /**
   * A string representation of this color in the current format
   *
   * @param bool $opacity
   *   Whether or not to display the opacity.
   *
   * @return string
   *   The color in format: #RRGGBB
   */
  public function toString($opacity = TRUE) {
    if ($opacity) {
      $output = 'rgba(' . $this->getRed() . ',' . $this->getGreen() . ',' . $this->getBlue() . ',' . $this->getOpacity() . ')';
    }
    else {
      $output = 'rgb(' . $this->getRed() . ',' . $this->getGreen() . ',' . $this->getBlue() . ')';
    }
    return strtoupper($output);
  }

  /**
   * {@inheritdoc}
   */
  public function toHex() {
    return new ColorHex($this->getRed() << 16 | $this->getGreen() << 8 | $this->getBlue(), $this->getOpacity());
  }

  /**
   * {@inheritdoc}
   */
  public function toRGB() {
    return $this;
  }

}
