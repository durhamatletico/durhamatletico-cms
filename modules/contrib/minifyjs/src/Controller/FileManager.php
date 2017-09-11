<?php

namespace Drupal\minifyjs\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for minifyjs routes.
 */
class FileManager extends ControllerBase {

  /**
   * Minify a single file.
   *
   * @param stdClass $file
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the manage javascript page.
   */
  public function minify($file) {
    $result = minifyjs_minify_file($file, TRUE);

    if ($result === TRUE) {
      drupal_set_message(t('File was minified successfully.'));
    }
    else {
      drupal_set_message($result, 'error');
    }

    return $this->redirect('minifyjs.manage');
  }

  /**
   * Remove the minified version of a single file (restore it).
   *
   * @param stdClass $file
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the manage javascript page.
   */
  public function restore($file) {
    $result = minifyjs_remove_minified_file($file, TRUE);

    if ($result === TRUE) {
      drupal_set_message(t('File was restored successfully.'));
    }
    else {
      drupal_set_message($result, 'error');
    }

    return $this->redirect('minifyjs.manage');
  }

  /**
   * Scans the system for javascript.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the manage javascript page.
   */
  public function scan() {

    // Recursive scan of the entire doc root to find .js files. Include
    // minified files as well so they can be re-minified (comments removed).
    $directory = new \RecursiveDirectoryIterator(DRUPAL_ROOT);
    $iterator  = new \RecursiveIteratorIterator($directory);
    $regex     = new \RegexIterator($iterator, '/\.js$/i');

    // Process files.
    $new_files  = array();
    $old_files  = array();
    $existing   = minifyjs_load_all_files();
    $exclusions = \Drupal::config('minifyjs.config')->get('exclusion_list');

    foreach ($regex as $info) {
      $new_absolute = $info->getPathname();
      $new_relative = str_replace(DRUPAL_ROOT . '/', '', $new_absolute);

      // skip exclusions
      if (\Drupal::service('path.matcher')->matchPath($new_relative, $exclusions)) {
        continue;
      }

      // Loop existing and see if it already exists from previous scans.
      $exists = FALSE;
      foreach ($existing as $file) {
        if ($file->uri == $new_relative) {
          $exists = TRUE;
          $old_files[$new_relative] = TRUE;
          break;
        }
      }

      // File not found in the existing array, so it's new.
      if (!$exists) {
        $new_files[$new_absolute] = TRUE;
      }
    }

    // Build a list of files that currently exist in the minifyjs_file table but
    // no longer exist in the file system. These files should be removed.
    $remove_files = array();
    foreach ($existing as $file) {
      if (!isset($old_files[$file->uri])) {
        $this->remove_file($file->uri);
      }
    }

    // Add all new files to the database.
    foreach ($new_files as $file => $junk) {
      \Drupal::database()->insert('minifyjs_file')
        ->fields(
          array(
            'uri'      => str_replace(DRUPAL_ROOT . '/', '', $file),
            'size'     => filesize($file),
            'modified' => filemtime($file),
          )
        )
        ->execute();
    }

    // Clear the cache so all of these new files will be picked up.
    \Drupal::cache()->delete(MINIFYJS_CACHE_CID);

    return $this->redirect('minifyjs.manage');
  }

  /**
   * Helper function removes the file, the entry in the file_managed table and
   * the entry in the minifyjs_file.
   *
   * @param string $file_uri
   */
  private function remove_file($file_uri) {

    // Get the fid and minified uri of the file
    $query = \Drupal::database()->select('minifyjs_file', 'm')
      ->fields('m', array('fid', 'minified_uri'))
      ->condition('m.uri', $file_uri);

    // make sure that it exists
    if ($query->countQuery()->execute()->fetchField() > 0) {
      $file = $query->execute()->fetchObject();

      // Handle the minified file, if applicable.
      if (!empty($file->minified_uri)) {

        // Get the fid of the minified file.
        $query = \Drupal::database()->select('file_managed', 'f')
          ->fields('f', array('fid'))
          ->condition('f.uri', $file->minified_uri);
        if ($query->countQuery()->execute()->fetchField() > 0) {
          $minified_file = $query->execute()->fetchObject();

          // Remove the file from the file_managed table
          $minified_file = File::load($minified_file->fid);
          $minified_file->delete();
        }
      }

      // Remove the file from minifyjs_file table.
      \Drupal::database()->delete('minifyjs_file')
        ->condition('fid', $file->fid)
        ->execute();

      return TRUE;
    }
  }
}
