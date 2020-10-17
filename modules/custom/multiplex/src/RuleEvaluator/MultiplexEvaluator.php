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
          return \Drupal\node\Entity\Node::load($item['target_id']);
        },
        $field_data
      )
    );

    $target_paths = array_map(
      function ($node) {
        return $node->Url();
      },
      $targets
    );

    // Remove from consideration any target that already appears as a
    // recorded visited location for the specified user.
    $visited = $this->visitationService->findVisitedTargets($this->who, $target_paths);
    $targets = array_filter(
      $targets,
      function ($node) use($visited) {
        return !in_array($node->Url(), $visited);
      }
    );

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

}
