/**
 * @file
 * Attaches simplesitemap behaviors to the entity form.
 */
(function($) {

  "use strict";

  Drupal.behaviors.simplesitemapFieldsetSummaries = {
    attach: function(context) {
      $(context).find('#edit-simplesitemap').drupalSetSummary(function (context) {
        var vals = [];
        if ($(context).find('#edit-simplesitemap-index-content').is(':checked')) {

          // Display summary of the settings in tabs.
          vals.push(Drupal.t('Included in sitemap'));
          vals.push(Drupal.t('Priority') + ' ' + $('#edit-simplesitemap-priority option:selected', context).text());
        }
        else {
          // Display summary of the settings in tabs.
          vals.push(Drupal.t('Excluded from sitemap'));
        }
        return vals.join('<br />');
      });
    }
  };
})(jQuery);
