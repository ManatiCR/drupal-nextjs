<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets entity definition for a given entity type.
 *
 * @DataProducer(
 *   id = "query_drupal_config",
 *   name = @Translation("Load drupal config"),
 *   description = @Translation("Loads a list of drupal config."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Drupal config")
 *   ),
 * consumes = {
 *     "config_file" = @ContextDefinition("string",
 *       label = @Translation("Config File"),
 *     ),
 *     "config_key" = @ContextDefinition("string",
 *       label = @Translation("Config Key"),
 *     )
 *   }
 * )
 */
class QueryDrupalConfig extends DataProducerPluginBase implements ContainerFactoryPluginInterface {


  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new QueryDrupalConfig instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * Undocumented function.
   */
  public function resolve($config_file, $config_key) {
    $config = $this->configFactory->get($config_file)->get($config_key);
    $result = [];
    if (empty($config)) {
      return $result;
    }
    if (is_array($config)) {
      foreach ($config as $key => $value) {
        $result[] = [
          'name' => $key,
          'value' => $value,
        ];
      }
    }
    else {
      $result[] = [
        'name' => $config_key,
        'value' => $config,
      ];
    }

    return $result;
  }

}
