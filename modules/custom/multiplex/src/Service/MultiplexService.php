<?php

namespace Drupal\multiplex\Service;

use Drupal\Core\Database\Connection;

// The multiplex service determines the target locations for redirects
class MultiplexService {

  /** \Drupal\multiplex\Service\VisitationService */
  protected $visitationService;

  public function __construct(VisitationService $visitationService) {
    $this->visitationService = $visitationService;
  }

  public function findMultiplexLocation($who, $path) {
    // Record that "$target" was visited.
    $visit_data = $this->visitationService->recordVisit($who, $path);

    // If the path has been visited before, and if a specific target
    // was recorded, then be consistent and return the same target every time.
    if ($visit_data->visited()) {
      return $visit_data->target();
    }

    $node = $this->getNodeFromPath($path);
    if (!$node) {
      return $path;
    }

    $random_multiplex_result = $this->randomMultiplex($who, $node);
    if ($random_multiplex_result) {
      $this->visitationService->recordTarget($visit_data, $random_multiplex_result);
      return $random_multiplex_result;
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

  protected function randomMultiplex($who, $node) {
    if (!$node->hasField('field_random_multiplex')) {
      return false;
    }
    $field_data = $node->get('field_random_multiplex')->getValue();
    if (empty($field_data)) {
      return false;
    }
    $random_selections_path = $field_data[0]['value'];
    $random_selection_node = $this->getNodeFromPath($random_selections_path);
    if (!$random_selection_node) {
      return false;
    }

    $field_data = $random_selection_node->get('field_multiplex_targets')->getValue();

    $targets = array_map(
      function ($item) {
        return $item['value'];
      }, $field_data);

    // Remove from consideration any target that already appears as a
    // recorded visited location for the specified user.
    $visited = $this->visitationService->findVisitedTargets($who, $targets);
    $targets = array_diff($targets, $visited);

    shuffle($targets);

    return array_pop($targets);
  }

}
