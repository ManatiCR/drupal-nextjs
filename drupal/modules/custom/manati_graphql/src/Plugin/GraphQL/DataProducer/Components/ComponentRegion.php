<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\Components;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\layout_builder\SectionComponent;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "component_region",
 *   name = @Translation("Component Region"),
 *   description = @Translation("Returns the region of the component."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Component Region"),
 *   ),
 *   consumes = {
 *     "component" = @ContextDefinition("any",
 *       label = @Translation("Component")
 *     )
 *   }
 * )
 */
class ComponentRegion extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(SectionComponent $component) {
    return $component->getRegion();
  }

}
