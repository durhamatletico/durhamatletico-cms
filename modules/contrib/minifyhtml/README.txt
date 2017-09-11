
Description
===============================================================================

Minify Source HTML was developed to replace the implementation of the Minify
module (https://www.drupal.org/project/minify) which would only minify the html
in the content area of the page, not the html of the entire page. This module
hooks in at the very end of the page render process and minifies everything.

Installation
===============================================================================

  1. Add the following entry to your composer.json file, under "require":

      "drupal/minifyhtml": "1.0"

  2. run 'composer update'

  3. Enable the Minify HTML module.

  4. Go to the Performance page: Configuration > Performance. Check the
     Minified Source HTML checkbox and Save configuration.
