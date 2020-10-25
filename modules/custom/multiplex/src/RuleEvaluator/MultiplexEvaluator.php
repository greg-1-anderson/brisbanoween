<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
class MultiplexEvaluator extends EvaluatorBase {

  protected $multiplex_data_node;

  public function __construct(VisitationService $visitation_service, $who, $multiplex_data_node) {
    parent::__construct($visitation_service, $who);
    $this->multiplex_data_node = $multiplex_data_node;
  }

  public function evaluate() {
    // Sanity check. In the future perhaps we limit our content type to
    // multiplex_dest so that we can be assured that the fields we need exist.
    if (!$this->multiplex_data_node->hasField('field_multiplex_dest_targets')) {
      return null;
    }

    $targets = $this->loadTargetNodes($this->multiplex_data_node, 'field_multiplex_dest_targets');

    if (!empty($targets)) {
      $target_nids = array_map(
        function ($node) {
          return $node->id();
        },
        $targets
      );

      // Remove from consideration any target that already appears as a
      // recorded visited location for the specified user.
      $visited = $this->visitationService->findVisitedTargets($this->who, $target_nids);
      $targets = array_filter(
        $targets,
        function ($node) use($visited) {
          return !in_array($node->Url(), $visited);
        }
      );

      if ($this->isRandom($this->multiplex_data_node)) {
        shuffle($targets);
      }
    }

    if (empty($targets)) {
      $targets = $this->fallbackLocation($this->multiplex_data_node);
    }

    return array_pop($targets);
  }

  protected function loadTargetNodes($multiplex_data_node, $field) {
    if (empty($this->who)) {
      return [];
    }
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
