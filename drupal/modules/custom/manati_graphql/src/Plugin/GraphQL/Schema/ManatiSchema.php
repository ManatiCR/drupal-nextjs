<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\manati_graphql\Wrappers\QueryConnection;
use Drupal\block_content\BlockContentInterface;
use Drupal\media\MediaInterface;
use GraphQL\Error\Error;
use Drupal\node\NodeInterface;

/**
 * Undocumented function.
 *
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
    // $this->addSearchFields($registry, $builder);
    $this->addLandingPageFields($registry, $builder);
    $this->addTermFields($registry, $builder);
    $this->addArticleFields($registry, $builder);
    $this->addSectionFields($registry, $builder);
    $this->addComponentFields($registry, $builder);
    $this->addLayoutBuilderBlockTypeResolver($registry);
    $this->addMediaBlockTypeResolver($registry);
    $this->addNodeInterfaceTypeResolver($registry);
    $this->addBasicBlockFields($registry, $builder);
    $this->addCardFields($registry, $builder);
    $this->addMediaBlockImageFields($registry, $builder);
    $this->addFileFields($registry, $builder);
    // $this->addParagraphFields($registry, $builder);
    // $this->addMediaFileFields($registry, $builder);
    // Re-usable connection type fields.
    $this->addConnectionFields('LandingPageConnection', $registry, $builder);

    return $registry;
  }

  /**
   * Undocumented function.
   */
  protected function addQueryFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'landingPage',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['landing_page']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'landingPages',
      $builder->produce('query_landing_pages')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );

    $registry->addFieldResolver('Query', 'terms',
      $builder->produce('taxonomy_load_tree')
        ->map('vid', $builder->fromArgument('vocabulary'))
        ->map('parent', $builder->fromValue(0))
        ->map('max_depth', $builder->fromValue(1))
    );


    $registry->addFieldResolver('Query', 'drupalConfig',
      $builder->produce('query_drupal_config')
        ->map('config_file', $builder->fromArgument('configFile'))
        ->map('config_key', $builder->fromArgument('configKey'))
    );

    $registry->addFieldResolver('Query', 'search',
      $builder->produce('query_search_api_search')
        ->map('index_id', $builder->fromValue('main_index'))
        ->map('range', $builder->fromArgument('range'))
        ->map('sort', $builder->fromArgument('sort'))
        ->map('fulltext', $builder->fromArgument('fulltext'))
        ->map('conditions', $builder->fromArgument('conditions'))
        ->map('languages', $builder->fromArgument('languages'))
        ->map('solr_params', $builder->fromArgument('solrParams'))
        ->map('condition_group', $builder->fromArgument('conditionGroup'))
    );

  }

  /**
   * Undocumented function.
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

    $registry->addFieldResolver('LandingPage', 'type',
      $builder->compose(
        $builder->produce('entity_bundle')
          ->map('entity', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('LandingPage', 'sections',
      $builder->compose(
        $builder->produce('layout_sections')
          ->map('entity', $builder->fromParent())
      )
    );

  }

  /**
   * Undocumented function.
   */
  protected function addTermFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Term', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Term', 'name',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Term', 'children',
      $builder->compose(
        $builder->produce('taxonomy_load_tree')
          ->map('vid', $builder->produce('entity_bundle')
            ->map('entity', $builder->fromParent()))
          ->map('parent', $builder->produce('entity_id')
            ->map('entity', $builder->fromParent()))
          ->map('max_depth', $builder->fromValue(1))
      )
    );

  }

  /**
   * Undocumented function.
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

    $registry->addFieldResolver('Article', 'type',
      $builder->compose(
        $builder->produce('entity_bundle')
          ->map('entity', $builder->fromParent())
      )
    );

  }

  /**
   * Undocumented function.
   */
  protected function addSectionFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Section', 'section_id',
      $builder->produce('section_id')
        ->map('section', $builder->fromParent())
    );

    $registry->addFieldResolver('Section', 'variant',
      $builder->produce('section_field')
        ->map('section', $builder->fromParent())
        ->map('field', $builder->fromValue('variant'))
    );

    $registry->addFieldResolver('Section', 'background',
      $builder->produce('section_field')
        ->map('section', $builder->fromParent())
        ->map('field', $builder->fromValue('background'))
    );

    $registry->addFieldResolver('Section', 'column_proportions',
      $builder->produce('section_field')
        ->map('section', $builder->fromParent())
        ->map('field', $builder->fromValue('column_proportions'))
    );

    $registry->addFieldResolver('Section', 'spacing_top',
      $builder->produce('section_field')
        ->map('section', $builder->fromParent())
        ->map('field', $builder->fromValue('spacing_top'))
    );

    $registry->addFieldResolver('Section', 'spacing_bottom',
      $builder->produce('section_field')
        ->map('section', $builder->fromParent())
        ->map('field', $builder->fromValue('spacing_bottom'))
    );

    $registry->addFieldResolver('Section', 'spacing_columns',
      $builder->produce('section_field')
        ->map('section', $builder->fromParent())
        ->map('field', $builder->fromValue('spacing_columns'))
    );

    $registry->addFieldResolver('Section', 'equal_height',
      $builder->produce('section_field')
        ->map('section', $builder->fromParent())
        ->map('field', $builder->fromValue('equal_height'))
    );

    $registry->addFieldResolver('Section', 'side_spacing',
      $builder->produce('section_field')
        ->map('section', $builder->fromParent())
        ->map('field', $builder->fromValue('side_spacing'))
    );

    $registry->addFieldResolver('Section', 'components',
      $builder->produce('section_components')
        ->map('section', $builder->fromParent())
    );
  }

  /**
   * Undocumented function.
   */
  protected function addComponentFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Component', 'region',
      $builder->produce('component_region')
        ->map('component', $builder->fromParent())
    );

    $registry->addFieldResolver('Component', 'type',
      $builder->produce('component_type')
        ->map('component', $builder->fromParent())
    );

    $registry->addFieldResolver('Component', 'uuid',
      $builder->produce('component_uuid')
        ->map('component', $builder->fromParent())
    );

    $registry->addFieldResolver('Component', 'block',
    $builder->produce('entity_load_by_uuid')
      ->map('type', $builder->fromValue('block_content'))
      ->map('bundle', $builder->produce('component_type')
        ->map('component', $builder->fromParent()))
      ->map('uuid', $builder->produce('component_uuid')
        ->map('component', $builder->fromParent()))

    );
  }

  /**
   * Undocumented function.
   */
  protected function addNodeInterfaceTypeResolver(ResolverRegistry $registry) {
    // Tell GraphQL how to resolve the NodeInterface interface.
    $registry->addTypeResolver('NodeInterface', function ($entity) {
      if ($entity instanceof NodeInterface) {
        switch ($entity->bundle()) {
          case 'landing_page':
            return 'LandingPage';

          case 'article':
            return 'Article';
        }

      }
      throw new Error('Could not resolve content type.');
    });
  }

  /**
   * Undocumented function.
   */
  protected function addLayoutBuilderBlockTypeResolver(ResolverRegistry $registry) {
    // Tell GraphQL how to resolve the LayoutBuilderBlock interface.
    $registry->addTypeResolver('LayoutBuilderBlock', function ($entity) {
      if ($entity instanceof BlockContentInterface) {
        switch ($entity->bundle()) {
          case 'basic_block':
            return 'BasicBlock';

          case 'card':
            return 'Card';
        }

      }
      throw new Error('Could not resolve content type.');
    });
  }

  /**
   * Undocumented function.
   */
  protected function addMediaBlockTypeResolver(ResolverRegistry $registry) {
    // Tell GraphQL how to resolve the MediaBlock interface.
    $registry->addTypeResolver('MediaBlock', function ($entity) {
      if ($entity instanceof MediaInterface) {
        switch ($entity->bundle()) {
          case 'image':
            return 'MediaBlockImage';

          case 'file':
            return 'MediaBlockFile';
        }

      }
      throw new Error('Could not resolve content type.');
    });
  }

  /**
   * Undocumented function.
   */
  protected function addBasicBlockFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('BasicBlock', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('BasicBlock', 'label',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('BasicBlock', 'field_title',
      $builder->produce('block_plain_text')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('field_title'))
    );

    $registry->addFieldResolver('BasicBlock', 'body',
      $builder->produce('block_formatted_text')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('body'))
    );
  }

  /**
   * Undocumented function.
   */
  protected function addCardFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Card', 'label',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Card', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Card', 'body',
      $builder->produce('block_formatted_text')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('body'))
    );

    $registry->addFieldResolver('Card', 'field_image',
      $builder->produce('block_entity_reference')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('field_image'))
    );
  }

  /**
   * Undocumented function.
   */
  protected function addMediaBlockImageFields(ResolverRegistry $registry, ResolverBuilder $builder) {

    $registry->addFieldResolver('MediaBlockImage', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('MediaBlockImage', 'file',
      $builder->produce('entity_reference')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('field_media_image'))
    );

  }

  /**
   * Undocumented function.
   */
  protected function addFileFields(ResolverRegistry $registry, ResolverBuilder $builder) {

    $registry->addFieldResolver('File', 'url',
      $builder->produce('image_url')
        ->map('entity', $builder->fromParent())
    );

  }

  /**
   * Undocumented function.
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
