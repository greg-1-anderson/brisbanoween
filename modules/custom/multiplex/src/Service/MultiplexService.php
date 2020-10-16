<?php

namespace Drupal\multiplex\Service;

use Drupal\multiplex\RuleEvaluator\RuleEvaluator;

// The multiplex service determines the target locations for redirects
class MultiplexService {

  /** \Drupal\multiplex\Service\VisitationService */
  protected $visitationService;

  /** \Drupal\Core\Config\ConfigFactoryInterface */
  protected $config;

  public function __construct(VisitationService $visitationService, \Drupal\Core\Config\ConfigFactoryInterface $config) {
    $this->visitationService = $visitationService;
    $this->config = $config;
  }

  public function findMultiplexLocation($who, $path) {
    // We can perform no service unless we have a visitor identifier.
    if (empty($who)) {
      $unidentified_user_path = $this->config->get('multiplex.settings')->get('unidentified_user_path');
      if (!empty($unidentified_user_path)) {
        return $unidentified_user_path;
      }
      return $path;
    }

    // Record that "$path" was visited, regardless of where it resolves to.
    $visit_data = $this->visitationService->recordVisit($who, $path);

    $node = $this->getNodeFromPath($path);
    if (!$node) {
      return $path;
    }

    $multiplex_result = $this->resolveMultiplexRules($visit_data, $node);
    if ($multiplex_result) {
      $this->visitationService->recordTarget($visit_data, $multiplex_result);
      return $multiplex_result;
    }

    return $path;
  }

  protected function getNodeFromPath($path) {
    if (empty($path)) {
      return null;
    }
    $params = \Drupal\Core\Url::fromUserInput($path)->getRouteParameters();
    if (!isset($params['node'])) {
      return null;
    }

    return \Drupal\node\Entity\Node::load($params['node']);
  }

  protected function resolveMultiplexRules(VisitData $visit_data, $node) {

    // If the path has been visited before, and if a specific target
    // was recorded, then be consistent and return the same target every time.
    if ($visit_data->visited()) {
      return $visit_data->target();
    }

    // TODO: Find the first multiplex rule field of any name.
    // For now, assume it is named "field_rules".
    if (!$node->hasField('field_rules')) {
      return null;
    }
    $rule_data = $node->get('field_rules')->getValue();
    if (empty($rule_data)) {
      return null;
    }

    foreach ($rule_data as $rule) {
      $evaluator = RuleEvaluator::create($rule['rule_type'], $this->visitationService, $visit_data->who());
      $result = $evaluator->evaluate($rule['parameter_node'], $rule['target_node']);
      if ($result) {
        return $result;
      }
    }

    return null;
  }

}
