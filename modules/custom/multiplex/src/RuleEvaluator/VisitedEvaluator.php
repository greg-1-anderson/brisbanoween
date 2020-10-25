<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
class VisitedEvaluator extends EvaluatorBase {

  protected $ifVisited;
  protected $visited_nid;
  protected $target_nid;

  public function __construct(VisitationService $visitation_service, $who, $visited_nid, $target_nid, $if_visited) {
    parent::__construct($visitation_service, $who);
    $this->visited_nid = $visited_nid;
    $this->target_nid = $target_nid;
    $this->ifVisited = $if_visited;
  }

  public function evaluate() {
    $visitation_test_node = \Drupal\node\Entity\Node::load($this->visited_nid);
    if (!$visitation_test_node) {
      return null;
    }

    $visitation_nid = $visitation_test_node->id();
    $visited = $this->visitationService->findVisitedPaths($this->who, [$visitation_nid]);

    $was_visited = !empty($visited);

    if ($was_visited != $this->ifVisited) {
      return null;
    }

    $target_node = \Drupal\node\Entity\Node::load($this->target_nid);
    if (!$target_node) {
      return null;
    }

    return $target_node;
  }
}
