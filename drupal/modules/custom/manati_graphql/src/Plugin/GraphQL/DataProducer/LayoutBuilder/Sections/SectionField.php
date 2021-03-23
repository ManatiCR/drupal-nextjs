<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\LayoutBuilder\Sections;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\layout_builder\Section;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "section_field",
 *   name = @Translation("Layout Section Id"),
 *   description = @Translation("Returns the ID of the layout section."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Layut Identifier"),
 *   ),
 *   consumes = {
 *     "section" = @ContextDefinition("any",
 *       label = @Translation("Section")
 *     ),
 *     "field" = @ContextDefinition("string",
 *       label = @Translation("Section field")
 *     )
 *   }
 * )
 */
class SectionField extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(Section $section, string $field) {
    if (isset($section->getLayoutSettings()[$field])) {
      return $section->getLayoutSettings()[$field];
    }
    else {
      return NULL;
    }
  }

}
