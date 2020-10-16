<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
class MultiplexEvaluator extends EvaluatorBase {

  protected $randomize;

  public function __construct(VisitationService $visitation_service, $who, $randomize) {
    parent::__construct($visitation_service, $who);
    $this->randomize = $randomize;
  }

  public function evaluate($parameter_nid, $target_nid) {
    $multiplex_data_node = \Drupal\node\Entity\Node::load($parameter_nid);
    if (!$multiplex_data_node) {
      return null;
    }

    // TODO: Look for any entity reference field
    if (!$multiplex_data_node->hasField('field_targets')) {
      return null;
    }

    $field_data = $multiplex_data_node->get('field_targets')->getValue();
    if (empty($field_data)) {
      return null;
    }

    $targets = array_filter(
      array_map(
        function ($item) {
          return $this->getMultiplexTargetPath($item['target_id']);
        },
        $field_data
      )
    );

    // Remove from consideration any target that already appears as a
    // recorded visited location for the specified user.
    $visited = $this->visitationService->findVisitedTargets($this->who, $targets);
    $targets = array_diff($targets, $visited);

    if ($this->randomize) {
      shuffle($targets);
    }

    return array_pop($targets);
  }

  protected function getMultiplexTargetPath($value) {
    // If it's not a nid, assume it's a path
    if (!is_numeric($value)) {
      return $value;
    }
    $target_node = \Drupal\node\Entity\Node::load($value);
    if (!$target_node) {
      return null;
    }

    return $target_node->Url();
  }
}
