<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\LayoutBuilder\Components;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\layout_builder\SectionComponent;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "component_type",
 *   name = @Translation("Component Type"),
 *   description = @Translation("Returns the type of the component."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Component Type"),
 *   ),
 *   consumes = {
 *     "component" = @ContextDefinition("any",
 *       label = @Translation("Component")
 *     )
 *   }
 * )
 */
class ComponentType extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(SectionComponent $component) {
    return $component->getPlugin()->getConfiguration()['type'];
  }

}
