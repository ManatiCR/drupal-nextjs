<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\manati_graphql\Wrappers\QueryConnection;

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
    $this->addLandingPageFields($registry, $builder);
    $this->addSectionFields($registry, $builder);
    $this->addComponentFields($registry, $builder);
    $this->addBlockComponentFields($registry, $builder);

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

    $registry->addFieldResolver('Query', 'drupalConfig',
      $builder->produce('query_drupal_config')
        ->map('config_file', $builder->fromArgument('configFile'))
        ->map('config_key', $builder->fromArgument('configKey'))
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

    $registry->addFieldResolver('Component', 'id',
      $builder->produce('component_id')
        ->map('component', $builder->fromParent())
    );

    $registry->addFieldResolver('Component', 'fields',
      $builder->compose(
        $builder->produce('component_id')
          ->map('component', $builder->fromParent()),
        $builder->produce('entity_load')
          ->map('type', $builder->fromValue('block_content'))
          ->map('id', $builder->fromParent())
      )
    );
  }

  /**
   * Undocumented function.
   */
  protected function addBlockComponentFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('ComponentFields', 'label',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('ComponentFields', 'field_title',
      $builder->produce('entity_id')
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
