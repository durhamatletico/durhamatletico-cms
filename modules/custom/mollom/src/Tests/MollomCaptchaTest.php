<?php
/**
 * @file contains Drupal\mollom\Tests\MollomCaptchaTest.
 */

namespace Drupal\mollom\Tests;
use Drupal\Component\Serialization\Json;
use Drupal\mollom\Entity\FormInterface;

/**
 * Test basic CAPTCHA functionality.
 *
 * @group mollom
 */
class MollomCaptchaTest extends MollomTestBase {

  public static $modules = ['dblog', 'mollom', 'node', 'comment', 'mollom_test_server', 'mollom_test'];
  public $disableDefaultSetup = TRUE;
  protected $useLocal = TRUE;

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->setKeys(TRUE);
    $this->assertValidKeys();

    $this->setProtection('mollom_test_form', FormInterface::MOLLOM_MODE_CAPTCHA);
  }

  /**
   * Tests #required validation of CAPTCHA form element.
   */
  function testCAPTCHARequired() {
    $this->drupalGet('mollom-test/form');
    // Verify that CAPTCHA cannot be left empty.
    $this->assertCaptchaField();
    return;
    $this->drupalPostForm(NULL, array(), 'Submit');
    $this->assertText(self::INCORRECT_MESSAGE);
    $this->assertNoText('Successful form submission.');

    // Verify it again on subsequent POST.
    $this->assertCaptchaField();
    $this->drupalPostForm(NULL, array(), 'Submit');
    $this->assertText(self::INCORRECT_MESSAGE);
    $this->assertNoText('Successful form submission.');

    // Verify that incorrect solution still leaves the field required.
    $edit = array(
      'title' => $this->randomString(),
    );
    $this->postIncorrectCaptcha(NULL, $edit, 'Submit', 'Successful form submission.');

    // Verify correct solution, but trigger other validation errors.
    $edit = array(
      'title' => '',
      'mollom[captcha]' => 'correct',
    );
    $this->drupalPostForm(NULL, $edit, 'Submit');
    $this->assertNoCaptchaField();
    $this->assertNoText('Successful form submission.');

    // Lastly, confirm we're able to submit.
    $edit = array(
      'title' => $this->randomString(),
    );
    $this->drupalPostForm(NULL, $edit, 'Submit');
    $this->assertNoText(self::INCORRECT_MESSAGE);
    $this->assertTestSubmitData();
  }

  /**
   * Tests incorrect solution of CAPTCHA form element.
   */
  /*
  function testCAPTCHAIncorrect() {
    $this->drupalGet('mollom-test/form');

    // Verify that incorrect solution still leaves the field required.
    $edit = array(
      'title' => $this->randomString(),
    );
    $this->postIncorrectCaptcha(NULL, $edit, 'Submit', 'Successful form submission.');

    // Lastly, verify correct solution.
    $edit = array(
      'mollom[captcha]' => 'correct',
    );
    $this->drupalPostForm(NULL, $edit, 'Submit');
    $this->assertTestSubmitData();
  }
  */

  /**
   * Tests correct solution of CAPTCHA.
   */
  /*
  function testCAPTCHACorrect() {
    $this->drupalGet('mollom-test/form');

    $edit = array(
      'mollom[captcha]' => 'correct',
    );
    $this->drupalPostForm(NULL, $edit, 'Submit');
    $this->assertNoCaptchaField();
    $this->assertNoText('Successful form submission.');

    $edit = array(
      'body' => $this->randomString(),
    );
    $this->drupalPostForm(NULL, $edit, 'Submit');
    $this->assertNoCaptchaField();
    $this->assertNoText('Successful form submission.');

    $edit = array(
      'title' => $this->randomString(),
    );
    $this->drupalPostForm(NULL, $edit, 'Submit');
    $this->assertTestSubmitData();
  }
  */

  /**
   * Tests correct solution of CAPTCHA in a single pass.
   */
  /*
  function testCAPTCHACorrectSinglePass() {
    $this->drupalGet('mollom-test/form');

    // Verify that CAPTCHA can be solved in one shot.
    $edit = array(
      'title' => $this->randomString(),
      'mollom[captcha]' => 'correct',
    );
    $this->drupalPostForm(NULL, $edit, 'Submit');
    $this->assertTestSubmitData();
  }
*/
  /**
   * Tests the CAPTCHA type switch callback.
   */
  /*
  function testCAPTCHASwitchCallback() {
    // Verify that the CAPTCHA can be switched on a CAPTCHA-only protected form.
    // (without having a contentId)
    $this->drupalGet('mollom-test/form');
    $form_build_id = $this->getFieldValueByName('form_build_id');

    // @see drupalPostForm(), drupalGet()
    $path = url('mollom/captcha/audio/' . $form_build_id, array('absolute' => TRUE));
    $out = $this->curlExec(array(
      CURLOPT_URL => $path,
      CURLOPT_POST => TRUE,
    ));
    // Ensure that any changes to variables in the other thread are picked up.
    $this->refreshVariables();
    $this->verbose('POST request to: ' . $path .
      '<hr />Ending URL: ' . $this->getUrl() .
      '<hr />' . $out);

    $this->assertResponse(200);
    $this->assertText('mollom-captcha-player.swf');
    $response = Json::decode($out);
    $this->assertTrue($response['captchaId']);
  }
  */

  /**
   * Tests the CAPTCHA audio enable/disable functionality.
   */
  /*
  function testCAPTCHAAudioEnable() {
    // Default should be enabled audio.
    $this->drupalGet('mollom-test/form');
    $this->assertLink(t('Switch to audio verification'));

    // Verify that CAPTCHA cannot be switched when audio is disabled.
    $config = \Drupal::configFactory()->getEditable('mollom.settings');
    $config->set('captcha.audio.enabled', FALSE)->save();
    $this->drupalGet('mollom-test/form');
    $this->assertNoLink(t('Switch to audio verification'));
  }
  */
}
