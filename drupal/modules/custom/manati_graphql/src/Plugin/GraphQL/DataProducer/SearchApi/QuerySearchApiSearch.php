<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\SearchApi;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\search_api\Item\ItemInterface;

/**
 * Undocumented function.
 *
 * @DataProducer(
 *   id = "query_search_api_search",
 *   name = @Translation("Load landing pages"),
 *   description = @Translation("Loads a list of landing pages."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Landing pages connection")
 *   ),
 *   consumes = {
 *     "index_id" = @ContextDefinition("string",
 *       label = @Translation("String"),
 *     ),
 *     "offset" = @ContextDefinition("integer",
 *       label = @Translation("Offset"),
 *       required = FALSE
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class QuerySearchApiSearch extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  const MAX_LIMIT = 100;

  /**
   * Undocumented function.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The query object.
   *
   * @var \Drupal\views\Plugin\views\query\QueryPluginBase
   */
  private $query;

  /**
   * The search index.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  private $index;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * Articles constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Undocumented function.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannelFactoryInterface $logger
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
  }

  /**
   * Undocumented function.
   */
  public function resolve($index_id, $offset, $limit, RefinableCacheableDependencyInterface $metadata) {
    // Load up the index passed in argument.
    $this->index = $this->entityTypeManager->getStorage('search_api_index')->load($index_id);

    // Prepare a query for the respective Search API index.
    $this->query = $this->index->query();

    // Execute search.
    try {
      $results = $this->query->execute();
    }
    // Handle error, check exception type -> SearchApiException ?
    catch (\Exception $exception) {
      $this->logger->get('graphql')->error($exception->getMessage());
    }

    // Load all entities at once, for better performance.
    $results->preLoadResultItems();
    $result_entities = array_map(static function (ItemInterface $item) {
      return $item->getOriginalObject()->getValue();
    }, $results->getResultItems());

    // Initialise response array.
    $search_response = [];

    // Get search response from results.
    $search_response['items'] = $result_entities;

    // Add the result count to the response.
    $search_response['total'] = $results->getResultCount();

    yield $search_response;
  }

  /**
   * Prepares the Search API query by adding all possible options.
   *
   * Options include conditions, language, fulltext, range, sort and facets.
   *
   * @args
   *  The arguments containing all the parameters to be loaded to the query.
   */
  private function prepareSearchQuery($args) {

    // Prepare a query for the respective Search API index.
    $this->query = $this->index->query();

    // Adding query conditions if they exist.
    if ($args['conditions']) {
      $this->addConditions($args['conditions']);
    }
    // Adding query group conditions if they exist.
    if ($args['condition_group']) {
      $this->addConditionGroup($args['condition_group']);
    }
    // Adding Solr specific parameters if they exist.
    if ($args['solr_params']) {
      $this->addSolrParams($args['solr_params']);
    }
    // Restrict the search to specific languages.
    if ($args['language']) {
      $this->query->setLanguages($args['language']);
    }
    // Set fulltext search parameters in the query.
    if ($args['fulltext']) {
      $this->setFulltextFields($args['fulltext']);
    }
    // Adding range parameters to the query (e.g for pagination).
    if ($args['range']) {
      $this->query->range($args['range']['offset'], $args['range']['limit']);
    }
    // Adding sort parameters to the query.
    if ($args['sort']) {
      foreach ($args['sort'] as $sort) {
        $this->query->sort($sort['field'], $sort['value']);
      }
    }
    // Adding facets to the query.
    if ($args['facets']) {
      $this->setFacets($args['facets']);
    }
    // Adding more like this parameters to the query.
    if ($args['more_like_this']) {
      $this->setMlt($args['more_like_this']);
    }
  }

}
