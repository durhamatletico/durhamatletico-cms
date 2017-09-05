/**
 * @file
 * Javascript for Color Field.
 */
(function ($, Drupal) {

  'use strict';

  /**
   * Enables grid widget on color elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches a grid widget to a color input element.
   */
  Drupal.behaviors.color_field_jquery_simple_color = {
    attach: function (context, settings) {
      var $context = $(context);
      var settings = settings.color_field.color_field_widget_grid;

      $context.find('.js-color-field-widget-grid__color').each(function (index, element) {
        var $element = $(element);

        $element.simpleColor({
          cellWidth: settings.cell_width,
          cellHeight: settings.cell_height,
          cellMargin: settings.cell_margin,
          boxWidth: settings.box_width,
          boxHeight: settings.box_height
        });

      });

    }
  };

})(jQuery, Drupal);
