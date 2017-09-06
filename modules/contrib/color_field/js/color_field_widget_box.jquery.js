/**
 * @file
 * Attaches behaviors for Drupal's color field.
 */

(function ($, Drupal) {

    'use strict';

    /**
     * Enables box widget on color elements.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *   Attaches a box widget to a color input element.
     */
    Drupal.behaviors.color_field = {
        attach: function (context, settings) {

            var $context = $(context);

            var default_colors = settings.color_field.color_field_widget_box.settings.default_colors;

            $context.find('.color-field-widget-box-form').each(function (index, element) {
                var $element = $(element);
                var $input = $element.prev().find('input');
                $element.empty().addColorPicker({
                    currentColor: $input.val(),
                    colors: default_colors,
                    blotchClass:'color_field_widget_box__square',
                    blotchTransparentClass:'color_field_widget_box__square--transparent',
                    clickCallback: function(color) {
                        $input.val(color).trigger('change');
                    }
                });
            });

        },
    };

})(jQuery, Drupal);
