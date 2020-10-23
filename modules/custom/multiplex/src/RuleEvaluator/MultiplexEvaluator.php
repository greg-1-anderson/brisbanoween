<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
class MultiplexEvaluator extends EvaluatorBase {

  protected $parameter_nid;

  public function __construct(VisitationService $visitation_service, $who, $parameter_nid, $if_visited) {
    parent::__construct($visitation_service, $who);
    $this->parameter_nid = $parameter_nid;
    $this->ifVisited = $if_visited;
  }

  public function evaluate($parameter_nid) {
    $multiplex_data_node = \Drupal\node\Entity\Node::load($this->parameter_nid);
    if (!$multiplex_data_node) {
      return null;
    }

    // Sanity check. In the future perhaps we limit our content type to
    // multiplex_dest so that we can be assured that the fields we need exist.
    if (!$multiplex_data_node->hasField('field_multiplex_dest_targets')) {
      return null;
    }

    $targets = $this->loadTargetNodes($multiplex_data_node, 'field_multiplex_dest_targets');

    if (!empty($targets)) {
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
    }

    if (empty($targets)) {
      $targets = $this->fallbackLocation($multiplex_data_node);
    }

    return array_pop($targets);
  }

  protected function loadTargetNodes($multiplex_data_node, $field) {
    $field_data = $multiplex_data_node->get($field)->getValue();
    if (empty($field_data)) {
      return [];
    }

    $targets = array_filter(
      array_map(
        function ($item) {
          return \Drupal\node\Entity\Node::load($item['target_id']);
        },
        $field_data
      )
    );

    return $targets;
  }

  protected function fallbackLocation($multiplex_data_node) {
    return $this->loadTargetNodes($multiplex_data_node, 'field_multiplex_dest_fallback');
  }

  protected function isRandom($multiplex_data_node) {
    if (!$multiplex_data_node->hasField('field_multiplex_dest_random')) {
      return false;
    }
    $field_data = $multiplex_data_node->get('field_multiplex_dest_random')->getValue();
    return $field_data[0]['value'];
  }

}
