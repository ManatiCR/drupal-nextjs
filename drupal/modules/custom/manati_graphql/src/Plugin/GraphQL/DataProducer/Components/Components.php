<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\Components;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\layout_builder\Section;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "section_components",
 *   name = @Translation("Section Components"),
 *   description = @Translation("Returns the components of the section."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Component"),
 *   ),
 *   consumes = {
 *     "section" = @ContextDefinition("any",
 *       label = @Translation("Section")
 *     )
 *   }
 * )
 */
class Components extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(Section $section) {
    $components = $section->getComponents();
    foreach ($components as $component) {
      yield $component;
    }
  }

}
