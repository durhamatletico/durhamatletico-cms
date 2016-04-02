<?php

/**
 * @file
 * Contains \Drupal\stripe_checkout\StripeCallback.
 */

namespace Drupal\stripe_checkout;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Error\Card;

require_once __DIR__ . '/../vendor/autoload.php';

class StripeCallback extends ControllerBase {

  public function charge($id, $currency) {

    // Load the API key
    $config = \Drupal::config('stripe_checkout.settings');
    $skey = $config->get('stripe_checkout_key_secret');
    Stripe::setApiKey($skey);
    $default_currency = $config->get('stripe_checkout_currency');
    // Receive the token from Stripe.
    $token = \Drupal::request()->request->get('stripeToken');

    // Load the node to get the correct values.
    $node = Node::load($id);

    // Find the field that was used for creating the charge.
    $view_mode = \Drupal::config('core.entity_view_display.node.' . $node->getType() . '.default');
    $fields = $view_mode->get('content');
    foreach ($fields as $field => $values) {
      if (isset($values['type']) && $values['type'] == 'stripe_checkout') {
        $fc = $values['settings']['stripe_checkout_currency'];
        if ($fc == NULL) {
          $fc = $default_currency;
        }
        if (strtolower($fc) == strtolower($currency)) {
          $clicked = $field;
        }
      }
    }

    // Process the charge.
    try {
      $charge = Charge::create(array(
        "amount" => $node->$clicked->value,
        "currency" => $currency,
        "source" => $token,
        "description" => $node->title->value,
        "metadata" => array("registration_nid" => $id),
      ));
      drupal_set_message(t("Thank you. Payment has been processed."));
      // Update the node to mark as paid.
      $node->$clicked->value = 0;
      $node->revision = new \stdClass();
      $node->revision->value = TRUE;
      $node->revision_log->value = "Payment: " . $charge->id;
      $node->save();
    }
    catch (Card $e) {
      \Drupal::logger('stripe_checkout')->error('Payment failed registration node ' . (int) $id);
      drupal_set_message(t("Payment failed."), 'error');
    }

    // Go back to the node.
    // TODO: Redirect to user page.
    return $this->redirect('entity.node.canonical', array('node' => $id));
  }
}
