<?php

namespace Drupal\multiplex\Service;

use Drupal\multiplex\RuleEvaluator\RuleEvaluator;
use Drupal\multiplex\RuleEvaluator\MultiplexEvaluator;

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

  public function findMultiplexLocation($who, $qr_node, $multiplex_data_node) {
    $evaluators = [
        new MultiplexEvaluator($this->visitationService, $who, $multiplex_data_node),
    ];
    return $this->evaluateMultiplexLocation($who, $qr_node, $evaluators);
  }

  public function findMultiplexLocationFromRules($who, $node) {
    $evaluators = $this->getEvaluatorsForRulesField($who, $node);
    return $this->evaluateMultiplexLocation($who, $node, $evaluators);
  }

  protected function evaluateMultiplexLocation($who, $node, $evaluators) {
    $path = $node->Url();

    // Record that "$path" was visited, regardless of where it resolves to.
    $visit_data = $this->visitationService->recordVisit($who, $node);

    $multiplex_result_node = $this->resolveMultiplexRules($visit_data, $evaluators);
    if ($multiplex_result_node) {
      $this->visitationService->recordTarget($visit_data, $multiplex_result_node);
      return $multiplex_result_node;
    }

    return $node;
  }

  protected function resolveMultiplexRules(VisitData $visit_data, $evaluators) {
    // If the path has been visited before, and if a specific target
    // was recorded, then be consistent and return the same target every time.
    if ($visit_data->visited()) {
      return \Drupal\node\Entity\Node::load($visit_data->target());
    }

    foreach ($evaluators as $evaluator) {
      $target_node = $evaluator->evaluate();
      if ($target_node) {
        return $target_node;
      }
    }

    return null;
  }

  protected function getEvaluatorsForRulesField($who, $node) {
    // TODO: Find the first multiplex rule field of any name.
    // For now, assume it is named "field_rules".
    if (!$node->hasField('field_rules')) {
      return [];
    }
    $rule_data = $node->get('field_rules')->getValue();
    if (empty($rule_data)) {
      return [];
    }

    $evaluators = [];
    foreach ($rule_data as $rule) {
      $evaluator = RuleEvaluator::create($rule, $this->visitationService, $who);
      $evaluators[] = $evaluator;
    }
    return $evaluators;
  }
}
