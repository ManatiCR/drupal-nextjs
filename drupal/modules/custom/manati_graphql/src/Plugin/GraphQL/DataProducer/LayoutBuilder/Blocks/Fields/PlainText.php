<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\LayoutBuilder\Blocks\Fields;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "block_plain_text",
 *   name = @Translation("Block plain text field"),
 *   description = @Translation("Returns the block plain text field."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Plain text")
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
class PlainText extends DataProducerPluginBase {

  /**
   * Undocumented function.
   */
  public function resolve(EntityInterface $entity, string $field) {
    if (isset($entity->$field)) {
      return $entity->$field->value;
    }
    return NULL;
  }

}
