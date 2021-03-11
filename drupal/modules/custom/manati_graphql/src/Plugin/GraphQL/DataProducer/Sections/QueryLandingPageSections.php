<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\Sections;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * @DataProducer(
 *   id = "query_landing_page_sections",
 *   name = @Translation("Entity identifier"),
 *   description = @Translation("Returns the entity identifier."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Identifier")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class QueryLandingPageSections extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Articles constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function resolve(EntityInterface $entity) {
    $result = [];
    foreach ($entity->layout_builder__layout as $layout) {
      $section = $layout->section;
      $section_components = [];
      foreach ($section->getComponents() as $component_data) {
        $component = $component_data->toArray();
        $section_components[] = [
          'id' => $component['configuration']['block_revision_id'],
          'type' => $component['configuration']['type'],
          'region' => $component['region'],
          'fields' => $this->getBlockFields($component['configuration']['block_revision_id']),
        ];
      }

      $result[] = [
        'layoutId' => $section->getLayoutId(),
        'layoutSettings' => $section->getLayoutSettings(),
        'components' => $section_components,
      ];
    }
    return $result;
  }

  /**
   * Undocumented function
   *
   * @param int $id
   * @return array
   */
  protected function getBlockFields($id) {
    $entity_fields = [];
    if ($entity = $this->entityTypeManager->getStorage('block_content')->loadRevision($id)) {

    }

    return $entity_fields;
  }

}
