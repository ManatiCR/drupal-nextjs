<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\LayoutBuilder\Components;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\layout_builder\SectionComponent;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "component_uuid",
 *   name = @Translation("Component Uuid"),
 *   description = @Translation("Returns the Uuid of the component."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Component Uuid"),
 *   ),
 *   consumes = {
 *     "component" = @ContextDefinition("any",
 *       label = @Translation("Component")
 *     )
 *   }
 * )
 */
class ComponentUuid extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(SectionComponent $component) {
    return $component->getPlugin()->getConfiguration()['uuid'];
  }

}
