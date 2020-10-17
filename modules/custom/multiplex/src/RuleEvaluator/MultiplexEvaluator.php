<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
class MultiplexEvaluator extends EvaluatorBase {

  public function __construct(VisitationService $visitation_service, $who) {
    parent::__construct($visitation_service, $who);
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

    if ($this->isRandom($multiplex_data_node)) {
      shuffle($targets);
    }

    return array_pop($targets);
  }

  protected function isRandom($multiplex_data_node) {
    if (!$multiplex_data_node->hasField('field_random')) {
      return false;
    }
    $field_data = $multiplex_data_node->get('field_random')->getValue();
    return $field_data[0]['value'];
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
