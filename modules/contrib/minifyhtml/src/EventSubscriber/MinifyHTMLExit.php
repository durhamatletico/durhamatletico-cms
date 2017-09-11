<?php

namespace Drupal\minifyhtml\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Minifies the HTML of the response.
 *
 * @see \Symfony\Component\EventDispatcher\EventSubscriberInterface
 */
class MinifyHTMLExit implements EventSubscriberInterface {

  /**
   * The content that this class minifies.
   *
   * @var string
   */
  private $content;

  /**
   * A list of placeholders for HTML elements that cannot or should not be
   * minified.
   *
   * @var array
   */
  private $placeholders = [];

  /**
   * The placeholder token.
   *
   * @var string
   */
  private $token;

  /**
   * Constructs a MinifyHTMLExit object.
   */
  public function __construct() {
    $this->token = 'MINIFYHTML_' . md5(\Drupal::time()->getRequestTime());
  }

  /**
   * Minifies the HTML.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   */
  public function response(FilterResponseEvent $event) {
    if (\Drupal::config('system.performance')->get('minifyhtml.minify_html')) {
      $response = $event->getResponse();

      // Make sure that the following render classes are the only ones that
      // are minified.
      $allowed_response_classes = [
        'Drupal\big_pipe\Render\BigPipeResponse',
        'Drupal\Core\Render\HtmlResponse'
      ];
      if (in_array(get_class($response), $allowed_response_classes)) {
        $this->content = $response->getContent();
        $this->minify();
        $response->setContent($this->content);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[KernelEvents::RESPONSE][] = ['response', -10000];

    return $events;
  }

  /**
   * Helper function to minify HTML.
   */
  private function minify() {

    // Replace <textarea>, <pre>, <iframe>, <script>, and <style> with a
    // placeholder.
    $this->content = preg_replace_callback('/\\s*<textarea(\\b[^>]*?>[\\s\\S]*?<\\/textarea>)\\s*/i', [$this, 'minifyhtml_placeholder_callback'],        $this->content);
    $this->content = preg_replace_callback('/\\s*<pre(\\b[^>]*?>[\\s\\S]*?<\\/pre>)\\s*/i',           [$this, 'minifyhtml_placeholder_callback'],        $this->content);
    $this->content = preg_replace_callback('/\\s*<iframe(\\b[^>]*?>[\\s\\S]*?<\\/iframe>)\\s*/i',     [$this, 'minifyhtml_placeholder_callback_iframe'], $this->content);
    $this->content = preg_replace_callback('/\\s*<script(\\b[^>]*?>[\\s\\S]*?<\\/script>)\\s*/i',     [$this, 'minifyhtml_placeholder_callback_script'], $this->content);
    $this->content = preg_replace_callback('/\\s*<style(\\b[^>]*?>[\\s\\S]*?<\\/style>)\\s*/i',       [$this, 'minifyhtml_placeholder_callback_style'],  $this->content);

    // Remove HTML comments.
    $this->content = preg_replace_callback('/<!--([\\s\\S]*?)-->/',                                   [$this, 'minifyhtml_remove_html_comment'],         $this->content);

    // Minify the page.
    $this->minify_html();

    // Restore all values that are currently represented by a placeholder.
    if (!empty($this->placeholders)) {
      $this->content = str_replace(array_keys($this->placeholders), array_values($this->placeholders), $this->content);
    }
  }

  /**
   * Helper function to add place holder for <textarea> and <pre> tag.
   *
   * @param array $matches
   *
   * @return string
   */
  private function minifyhtml_placeholder_callback($matches) {
    return $this->minify_placeholder_replace(trim($matches[0]));
  }

  /**
   * Helper function to add place holder for <iframe> tag.
   *
   * @param array $matches
   *
   * @return string
   */
  private function minifyhtml_placeholder_callback_iframe($matches) {
    $iframe = preg_replace('/^\\s+|\\s+$/m', '', $matches[0]);

    return $this->minify_placeholder_replace(trim($iframe));
  }

  /**
   * Helper function to add place holder for <script> tag.
   *
   * @param array $matches
   *
   * @return string
   */
  private function minifyhtml_placeholder_callback_script($matches) {
    $search = [
      '!/\*.*?\*/!s',     // remove multi-line comment
      '/^\\s+|\\s+$/m',   // trim each line
      '/\n(\s*\n)+/',     // remove multiple empty line
    ];
    $replace = ['', "\n", "\n"];
    $script = preg_replace($search, $replace, $matches[0]);

    return $this->minify_placeholder_replace(trim($script));
  }

  /**
   * Helper function to add place holder for <style> tag.
   *
   * @param array $matches
   *
   * @return string
   */
  private function minifyhtml_placeholder_callback_style($matches) {
    $search = [
      '!/\*.*?\*/!s',   // remove multiline comment
      '/^\\s+|\\s+$/m'  // trim each line
    ];
    $replace = [''];
    $style = preg_replace($search, $replace, $matches[0]);

    return $this->minify_placeholder_replace(trim($style));
  }

  /**
   * Helper function to add tag key and value for further replacement.
   *
   * @param string $content
   *
   * @return string
   */
  private function minify_placeholder_replace($content) {
    $placeholder = '%' . $this->token . count($this->placeholders) . '%';
    $this->placeholders[$placeholder] = $content;

    return $placeholder;
  }

  /**
   * Helper function to remove HTML comments (not containing IE conditional
   * comments).
   *
   * @param string $string
   *
   * @return string
   */
  private function minifyhtml_remove_html_comment($string) {
    return (0 === strpos($string[1], '[') || FALSE !== strpos($string[1], '<![')) ? $string[0] : '';
  }

  /**
   * Helper function to minify the HTML.
   */
  private function minify_html() {
    $search = [
      '/\>[^\S ]+/s',                 // remove whitespaces after tags, except space
      '/[^\S ]+\</s',                 // remove whitespaces before tags, except space
      '/(\s)+/s',                     // shorten multiple whitespace sequences
      '/\\s+(<\\/?(?:area|base(?:font)?|blockquote|body'
          .'|caption|center|col(?:group)?|dd|dir|div|dl|dt|fieldset|form'
          .'|frame(?:set)?|h[1-6]|head|hr|html|legend|li|link|map|menu|meta'
          .'|ol|opt(?:group|ion)|p|param|t(?:able|body|head|d|h||r|foot|itle)'
          .'|ul)\\b[^>]*>)/i',        // remove whitespaces around block/undisplayed elements
      '/^\\s+|\\s+$/m',               // trim each line
    ];

    $replace = [
      '>',        // remove whitespaces after tags, except space
      '<',        // remove whitespaces before tags, except space
      '\\1',      // shorten multiple whitespace sequences
      '$1',       // remove whitespaces around block/undisplayed elements
      '',         // trim each line
    ];

    $this->content = preg_replace($search, $replace, $this->content);
  }
}