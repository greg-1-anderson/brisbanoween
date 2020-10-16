<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
class VisitedEvaluator extends EvaluatorBase {

  protected $ifVisited;

  public function __construct(VisitationService $visitation_service, $who, $if_visited) {
    parent::__construct($visitation_service, $who);
    $this->ifVisited = $if_visited;
  }

  public function evaluate($parameter_nid, $target_nid) {
    $visitation_test_node = \Drupal\node\Entity\Node::load($parameter_nid);
    if (!$visitation_test_node) {
      return null;
    }

    $visitation_path = $visitation_test_node->Url();

    $visited = $this->visitationService->findVisitedPaths($this->who, [$visitation_path]);

    $was_visited = !empty($visited);

    if ($was_visited != $this->ifVisited) {
      return null;
    }

    $target_node = \Drupal\node\Entity\Node::load($target_nid);
    if (!$target_node) {
      return null;
    }

    return $target_node->Url();
  }
}
