<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\LayoutBuilder\Blocks\Fields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "block_formatted_text",
 *   name = @Translation("Block forrmated text field"),
 *   description = @Translation("Returns the block forrmated text field."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("forrmated text")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "field" = @ContextDefinition("string",
 *       label = @Translation("name")
 *     )
 *   }
 * )
 */
class FormattedText extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(EntityInterface $entity, string $field) {
    if (isset($entity->$field)) {
      return $entity->$field->processed;
    }
    return NULL;
  }

}
