<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Drupal\Component\Utility\Random;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

  /**
   * @Given I wait :arg1 seconds
   */
  public function iWaitSeconds($arg1)
  {
    sleep($arg1);
  }

  /**
   * @Given I fill in :arg1 with a random e-mail address
   */
  public function iFillInWithARandomEMailAddress($arg1)
  {
    $name = $this->getDriver()->getRandom()->name();
    $this->getSession()->getPage()->fillField($arg1, $name . '@localhost.com');
  }

  /**
   * @Given I fill in :arg1 with a random string
   */
  public function iFillInWithARandomString($arg1)
  {
    $name = $this->getDriver()->getRandom()->name();
    $this->getSession()->getPage()->fillField($arg1, $name);
  }

}
