<?php

namespace Drupal\manati_graphql\Plugin\GraphQL\DataProducer\SearchApi;

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
 *     "range" = @ContextDefinition("any",
 *       label = @Translation("Range"),
 *       required = FALSE
 *     ),
 *     "sort" = @ContextDefinition("any",
 *       label = @Translation("Sort"),
 *       multiple = TRUE,
 *       required = FALSE
 *     ),
 *     "fulltext" = @ContextDefinition("any",
 *       label = @Translation("Full Text"),
 *       required = FALSE
 *     ),
 *     "conditions" = @ContextDefinition("any",
 *       label = @Translation("Conditions"),
 *       multiple = TRUE,
 *       required = FALSE
 *     ),
 *     "condition_group" = @ContextDefinition("any",
 *       label = @Translation("Condition Group"),
 *       required = FALSE
 *     ),
 *     "languages" = @ContextDefinition("any",
 *       label = @Translation("Langauge"),
 *       multiple = TRUE,
 *       required = FALSE
 *     ),
 *     "solr_params" = @ContextDefinition("any",
 *       label = @Translation("Solr Parameters"),
 *       multiple = TRUE,
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
   * @var \Drupal\search_api\Query\QueryInterface
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
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
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
  public function resolve(string $index_id, $range, $sort, $fulltext, $conditions, $condition_group, $languages, $solr_params) {
    // Load up the index passed in argument.
    $this->index = $this->entityTypeManager->getStorage('search_api_index')->load($index_id);

    // Prepare the query with our arguments.
    $this->prepareSearchQuery($range, $sort, $fulltext, $conditions, $condition_group, $languages, $solr_params);

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
  private function prepareSearchQuery($range, $sort, $fulltext, $conditions, $condition_group, $languages, $solr_params) {

    // Prepare a query for the respective Search API index.
    $this->query = $this->index->query();

    // Adding query conditions if they exist.
    if ($conditions) {
      $this->addConditions($conditions);
    }
    // Adding query group conditions if they exist.
    if ($condition_group) {
      $this->addConditionGroup($condition_group);
    }
    // Adding Solr specific parameters if they exist.
    if ($solr_params) {
      $this->addSolrParams($solr_params);
    }
    // Restrict the search to specific languages.
    if ($languages) {
      $this->query->setLanguages($languages);
    }
    // Set fulltext search parameters in the query.
    if ($fulltext) {
      $this->setFulltextFields($fulltext);
    }
    // Adding range parameters to the query (e.g for pagination).
    if ($range) {
      $this->query->range($range['offset'], $range['limit']);
    }
    // Adding sort parameters to the query.
    if ($sort) {
      foreach ($sort as $sort_item) {
        $this->query->sort($sort_item['field'], $sort_item['value']);
      }
    }
  }

  /**
   * Adds conditions to the Search API query.
   *
   * @conditions
   *  The conditions to be added.
   */
  private function addConditions($conditions) {

    // Loop through conditions to add them into the query.
    foreach ($conditions as $condition) {
      if (empty($condition['operator'])) {
        $condition['operator'] = '=';
      }
      if ($condition['value'] == 'NULL') {
        $condition['value'] = NULL;
      }
      // Set the condition in the query.
      $this->query->addCondition($condition['name'], $condition['value'], $condition['operator']);
    }
  }

  /**
   * Adds a condition group to the Search API query.
   *
   * @condition_group
   *  The conditions to be added.
   */
  private function addConditionGroup($condition_group) {

    // Loop through the groups in the args.
    foreach ($condition_group['groups'] as $group) {

      // Set default conjunction and tags.
      $group_conjunction = 'AND';
      $group_tags = [];

      // Set conjunction from args.
      if (isset($group['conjunction'])) {

        $group_conjunction = $group['conjunction'];
      }
      if (isset($group['tags'])) {
        $group_tags = $group['tags'];
      }

      // Create a single condition group.
      $condition_group = $this->query->createConditionGroup($group_conjunction, $group_tags);

      // Loop through all conditions and add them to the Group.
      foreach ($group['conditions'] as $condition) {

        $condition_group->addCondition($condition['name'], $condition['value'], $condition['operator']);
      }

      // Merge the single groups to the condition group.
      $this->query->addConditionGroup($condition_group);
    }
  }

  /**
   * Adds direct Solr parameters to the Search API query.
   *
   * @params
   *  The conditions to be added.
   */
  private function addSolrParams($params) {

    // Loop through conditions to add them into the query.
    foreach ($params as $param) {
      // Set the condition in the query.
      $this->query->setOption('solr_param_' . $param['parameter'], $param['value']);
    }
  }

  /**
   * Sets fulltext fields in the Search API query.
   *
   * @fulltext
   *  Parameters containing fulltext keywords to be used as well as optional
   *  fields.
   */
  private function setFulltextFields($fulltext) {

    // Check if keys is an array and if so set a conjunction.
    if (is_array($fulltext['keys'])) {
      // If no conjunction was specified use OR as default.
      if (!empty($fulltext['conjunction'])) {
        $fulltext['keys']['#conjunction'] = $fulltext['conjunction'];
      }
      else {
        $fulltext['keys']['#conjunction'] = 'OR';
      }
    }

    // Set the keys in the query.
    $this->query->keys($fulltext['keys']);

    // Set the optional fulltext fields if specified.
    if (!empty($fulltext['fields'])) {
      $this->query->setFulltextFields($fulltext['fields']);
    }
  }

}
