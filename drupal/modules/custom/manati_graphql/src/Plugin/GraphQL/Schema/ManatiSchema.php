<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\Schema;

use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\manati_graphql\Wrappers\QueryConnection;
use Drupal\block_content\BlockContentInterface;
use Drupal\media\MediaInterface;
use Drupal\Core\Url;
use GraphQL\Error\Error;

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
    $this->addNodeInterfaceTypeResolver($registry, $builder);
    $this->addLandingPageFields($registry, $builder);
    $this->addArticleFields($registry, $builder);
    $this->addSectionFields($registry, $builder);
    $this->addComponentFields($registry, $builder);
    $this->addLayoutBuilderBlockTypeResolver($registry);
    $this->addBasicBlockFields($registry, $builder);
    $this->addCardFields($registry, $builder);
    $this->addMediaInterfaceTypeResolver($registry, $builder);
    $this->addMediaImageFields($registry, $builder);
    $this->addMediaFileFields($registry, $builder);
    $this->addMediaRemoteVideoFields($registry, $builder);
    $this->addParagraphInterfaceTypeResolver($registry, $builder);
    $this->addParagraphCtaFields($registry, $builder);
    $this->addParagraphFileFields($registry, $builder);
    $this->addParagraphLinkFields($registry, $builder);

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

    $registry->addFieldResolver('Query', 'route', $builder->compose(
      $builder->produce('route_load')
        ->map('path', $builder->fromArgument('path')),
      $builder->produce('route_entity')
        ->map('url', $builder->fromParent())
    ));

    $registry->addFieldResolver('Query', 'mediaImage',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('media'))
        ->map('bundles', $builder->fromValue(['image']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'mediaFile',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('media'))
        ->map('bundles', $builder->fromValue(['file']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'mediaRemoteVideo',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('media'))
        ->map('bundles', $builder->fromValue(['remote_video']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'paragraphLink',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('paragraph'))
        ->map('bundles', $builder->fromValue(['link']))
        ->map('id', $builder->fromArgument('id'))
    );
  }

  /**
   * Undocumented function.
   */
  protected function addNodeInterfaceTypeResolver(ResolverRegistry $registry, ResolverBuilder $builder) {
    // Tell GraphQL how to resolve types of a common interface.
    $registry->addTypeResolver('NodeInterface', function ($value) {
      if ($value instanceof NodeInterface) {
        switch ($value->bundle()) {
          case 'landing_page': return 'LandingPage';
          case 'article': return 'Article';
        }
      }
      throw new Error('Could not resolve content type.');
    });
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

    $registry->addFieldResolver('LandingPage', 'url',
      $builder->compose(
        $builder->produce('entity_url')
          ->map('entity', $builder->fromParent()),
        $builder->callback(function ($url) {
          return $url->toString();
        })
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

    $registry->addFieldResolver('Article', 'media',
      $builder->produce('entity_reference')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('field_media_unlimited'))
    );

    $registry->addFieldResolver('Article', 'url',
      $builder->compose(
        $builder->produce('entity_url')
          ->map('entity', $builder->fromParent()),
        $builder->callback(function ($url) {
          return $url->toString();
        })
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

    $registry->addFieldResolver('Card', 'field_image',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_image.entity'))
    );

    $registry->addFieldResolver('Card', 'body',
      $builder->produce('block_formatted_text')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('body'))
    );

    $registry->addFieldResolver('Card', 'field_link',
      $builder->produce('entity_reference_revisions')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('field_link'))
    );

    $registry->addFieldResolver('Card', 'field_cta',
      $builder->produce('entity_reference_revisions')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('field_cta'))
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

  /**
   * Undocumented function.
   */
  protected function addMediaInterfaceTypeResolver(ResolverRegistry $registry, ResolverBuilder $builder) {
    // Tell GraphQL how to resolve the MediaInterface interface.
    $registry->addTypeResolver('MediaInterface', function ($entity) {
      if ($entity instanceof MediaInterface) {
        switch ($entity->bundle()) {
          case 'image':
            return 'MediaImage';
          case 'file':
            return 'MediaFile';
          case 'remote_video':
            return 'MediaRemoteVideo';
        }

      }
      throw new Error('Could not resolve media type.');
    });
  }

  /**
   * Undocumented function.
   */
  protected function addMediaImageFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('MediaImage', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('MediaImage', 'label',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('MediaImage', 'alt',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:media'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_media_image.alt'))
    );

    $registry->addFieldResolver('MediaImage', 'width',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:media'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_media_image.width'))
    );

    $registry->addFieldResolver('MediaImage', 'height',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:media'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_media_image.height'))
    );

    $registry->addFieldResolver('MediaImage', 'imageURL',
      $builder->compose(
        $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:media'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_media_image.entity')),
        $builder->produce('image_url')
          ->map('entity', $builder->fromParent())
      )
    );
  }

  /**
   * Undocumented function.
   */
  protected function addMediaFileFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('MediaFile', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('MediaFile', 'label',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('MediaFile', 'fileURL',
      $builder->compose(
        $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:media'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_media_file.entity')),
        $builder->produce('image_url')
          ->map('entity', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('MediaFile', 'description',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:media'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_media_file.description'))
    );
  }

  /**
   * Undocumented function.
   */
  protected function addMediaRemoteVideoFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('MediaRemoteVideo', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('MediaRemoteVideo', 'label',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('MediaRemoteVideo', 'videoURL',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:media'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_media_oembed_video.value'))
    );
  }

  /**
   * Undocumented function.
   */
  protected function addParagraphInterfaceTypeResolver(ResolverRegistry $registry, ResolverBuilder $builder) {
    // Tell GraphQL how to resolve the ParagraphInterface interface.
    $registry->addTypeResolver('ParagraphInterface', function ($entity) {
      if ($entity instanceof ParagraphInterface) {
        switch ($entity->bundle()) {
          case 'cta':
            return 'ParagraphCta';
          case 'file':
            return 'ParagraphFile';
          case 'link':
            return 'ParagraphLink';
        }

      }
      throw new Error('Could not resolve paragraph type.');
    });
  }

  /**
   * Undocumented function.
   */
  protected function addParagraphLinkFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('ParagraphLink', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('ParagraphLink', 'linkURL',
      $builder->compose(
        $builder->produce('property_path')
          ->map('path', $builder->fromValue('field_link.uri'))
          ->map('value', $builder->fromParent())
          ->map('type', $builder->fromValue('entity:paragraph')),
        $builder->callback(function($uri) {
          return Url::fromUri($uri)->toString();
        })
      )
    );

    $registry->addFieldResolver('ParagraphLink', 'linkTitle',
      $builder->produce('property_path')
        ->map('path', $builder->fromValue('field_link.title'))
        ->map('value', $builder->fromParent())
        ->map('type', $builder->fromValue('entity:paragraph'))
    );
  }

  /**
   * Undocumented function.
   */
  protected function addParagraphCtaFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('ParagraphCta', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );


    $registry->addFieldResolver('ParagraphCta', 'file_link',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:paragraph'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_file_link.entity'))
    );
  }

  /**
   * Undocumented function.
   */
  protected function addParagraphFileFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('ParagraphFile', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('ParagraphFile', 'file',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:paragraph'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_file.entity'))
  );
  }
}
