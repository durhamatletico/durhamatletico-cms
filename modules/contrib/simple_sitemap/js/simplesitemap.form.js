/**
 * @file
 * Attaches simplesitemap behaviors to the entity form.
 *
 * @todo: Tidy up.
 */
(function($) {

  "use strict";

  // Hide the 'Regenerate sitemap' field to only display it if settings have changed.
  $('.form-item-simplesitemap-regenerate-now').hide();

  Drupal.behaviors.simplesitemapForm = {
    attach: function(context) {
        if ($(context).find('#edit-simplesitemap-index-content').is(':checked')) {
          // Show 'Priority' field if 'Index sitemap' is ticked.
          $('.form-item-simplesitemap-priority').show();
        }
        else {  // Hide 'Priority' field if 'Index sitemap' is unticked.
          $('.form-item-simplesitemap-priority').hide();
        }

        // Show 'Regenerate sitemap' field if setting has changed.
        $( "#edit-simplesitemap-index-content" ).change(function() {
          $('.form-item-simplesitemap-regenerate-now').show();
          if ($(context).find('#edit-simplesitemap-index-content').is(':checked')) {
            // Show 'Priority' field if 'Index sitemap' is ticked.
            $('.form-item-simplesitemap-priority').show();
          }
          else {  // Hide 'Priority' field if 'Index sitemap' is unticked.
            $('.form-item-simplesitemap-priority').hide();
          }
        });

        // Show 'Regenerate sitemap' field if setting has changed.
        $( "#edit-simplesitemap-priority" ).change(function() {
          $('.form-item-simplesitemap-regenerate-now').show();
        });
    }
  };
})(jQuery);
