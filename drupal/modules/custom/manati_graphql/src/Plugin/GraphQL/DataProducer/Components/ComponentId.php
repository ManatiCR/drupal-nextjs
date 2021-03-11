<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\Components;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\layout_builder\SectionComponent;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "component_id",
 *   name = @Translation("Component ID"),
 *   description = @Translation("Returns the ID of the component."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Component ID"),
 *   ),
 *   consumes = {
 *     "component" = @ContextDefinition("any",
 *       label = @Translation("Component")
 *     )
 *   }
 * )
 */
class ComponentId extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(SectionComponent $component) {
    return $component->getPlugin()->getConfiguration()['block_revision_id'];
  }

}
