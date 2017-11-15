<?php

namespace Drupal\durhamatletico_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\durhamatletico_core\GoldenBoot as GoldenBootService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'GoldenBoot' block.
 *
 * @Block(
 *  id = "golden_boot",
 *  admin_label = @Translation("Golden boot"),
 * )
 */
class GoldenBoot extends BlockBase implements ContainerFactoryPluginInterface, BlockPluginInterface {

  /**
   * GoldenBoot service.
   *
   * @var \Drupal\durhamatletico_core\GoldenBootService
   */
  private $goldenBootService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GoldenBootService $goldenBootService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->goldenBootService = $goldenBootService;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['competition_reference'] = [
      '#type' => 'number',
      '#title' => $this->t('Division to display Golden Boot stats for'),
      '#default_value' => isset($config['competition_reference']) ? $config['competition_reference'] : NULL,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['competition_reference'] = $values['competition_reference'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->goldenBootService->getGoldenBootForDivision((int) $this->configuration['competition_reference']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('durhamatletico_core.goldenboot')
    );
  }

}
