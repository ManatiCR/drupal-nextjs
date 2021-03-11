<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\Sections;

use Drupal\node\NodeInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "layout_sections",
 *   name = @Translation("Layout Sections"),
 *   description = @Translation("Returns the layout sections of the entity."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Section"),
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class Sections extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(NodeInterface $entity) {
    if ($entity->hasField('layout_builder__layout')) {
      $layout = $entity->get('layout_builder__layout');
      $sections = $layout->getIterator();

      foreach ($sections as $item) {
        $section = $item->section;
        yield $section;
      }
    }
    else {
      return NULL;
    }

  }

}
