<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\manati_graphql\Wrappers\QueryConnection;
use Drupal\node\NodeInterface;
use GraphQL\Error\Error;

/**
 * @Schema(
 *   id = "manati",
 *   name = "Manati schema"
 * )
 */
class ManatiSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry();


    $this->addQueryFields($registry, $builder);
    $this->addLandingPageFields($registry, $builder);
    $this->addArticleFields($registry, $builder);
    $this->addResolveTypes($registry);

    // Re-usable connection type fields.
    $this->addConnectionFields('LandingPageConnection', $registry, $builder);

    return $registry;
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addQueryFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'landingPage',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['landing_page']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'article',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['article']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'landingPage',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['landing_page']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'drupalConfig',
      $builder->produce('query_drupal_config')
        ->map('config_file', $builder->fromArgument('configFile'))
        ->map('config_key', $builder->fromArgument('configKey'))
    );

    $registry->addFieldResolver('Query', 'block',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('block_content'))
        ->map('bundles', $builder->fromValue(['basic_block']))
        ->map('id', $builder->fromArgument('id'))
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addLandingPageFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('LandingPage', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('LandingPage', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('LandingPage', 'blocks',
      $builder->compose(
        $builder->produce('entity_reference')
          ->map('entity', $builder->fromParent())
          ->map('field', $builder->fromValue('field_blocks'))
      )
    );

    $registry->addFieldResolver('LandingPage', 'sections',
      $builder->compose(
        $builder->produce('query_landing_page_sections')
          ->map('entity', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('BasicBlock', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('BasicBlock', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('BasicBlock', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addArticleFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Article', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Article', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
      )
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   */
  protected function addResolveTypes(ResolverRegistry $registry) {
    // NodeInterface type resolver.
    $registry->addTypeResolver('NodeInterface', function ($value) {
      if ($value instanceof NodeInterface) {
        switch ($value->bundle()) {
          case 'article':
            return 'Article';

          case 'landing_page':
            return 'LandingPage';
        }
      }
      throw new Error('Could not resolve content type.');
    });
  }

  /**
   * @param string $type
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addConnectionFields($type, ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver($type, 'total',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->total();
      })
    );

    $registry->addFieldResolver($type, 'items',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->items();
      })
    );
  }

}
