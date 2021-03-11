<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\Sections;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\layout_builder\Section;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "section_id",
 *   name = @Translation("Layout Section Id"),
 *   description = @Translation("Returns the ID of the layout section."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Layut Identifier"),
 *   ),
 *   consumes = {
 *     "section" = @ContextDefinition("any",
 *       label = @Translation("Section")
 *     )
 *   }
 * )
 */
class SectionId extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(Section $section) {
    return $section->getLayoutId();
  }

}
