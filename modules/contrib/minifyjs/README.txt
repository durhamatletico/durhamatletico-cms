
Description
===============================================================================

Minify JS was developed to replace the implementation of the Minify module
(https://www.drupal.org/project/minify) which has a couple of problems:

  1. Problem: It is not storing the minified versions of it's files in the
     public file system properly. This in turn will not allow other modules,
     such as the S3 File System module (https://www.drupal.org/project/s3fs),
     to work with it.

     Solution: This module uses the public file system exclusively and any
     module that uses it's own Stream Wrapper for the public file system will
     work with this module out of the box.

  2. Problem: The minification process is using a remote call to Closure
     Compiler which has a few limitations, specifically a limitation on the
     number of files it can minify in an hour and another preventing it from
     minifying the JS to a single line.

     Solution: This module uses the JSqueeze PHP class for it's minification
     (https://github.com/tchwork/jsqueeze) which does not have these
     limitations.

  3. Problem: The module only detected javascript files that were loaded on a
     page, which means that all pages would need to be visited to get a
     complete list of javascript files.

     Solution: This module scans the file directory for .js files (excluding
     .min.js files).

Installation
===============================================================================

  1. Composer install - add

      "drupal/minifyjs": "1.0"

     to your composer.json file and run 'composer update'.

  2. Enable the Minify JS module.

  3. Go to the Performance page: Configuration > Performance.

  4. Click on the Manage Javascript Files tab.

  5. Bulk minify using the checkboxes or use the Operation links for individual
     minifications.

  6. Go to the Performance page: Configuration > Performance. Enable the
     minified files by using the checkbox called "Use Minified Javascript
     files."
