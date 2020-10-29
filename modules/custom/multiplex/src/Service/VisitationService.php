<?php

namespace Drupal\multiplex\Service;

use Drupal\Core\Database\Connection;

class VisitationService {

  /** \Drupal\Core\Database\Connection */
  protected $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Record a map marker for the QR code that was just scanned
   *
   * @param string $who
   *   Representation of visiting user
   * @param Node $qr_node
   *   QR code scanned
   * @param bool $visited
   *   'true' if code was visited, 'false' if adding this as a hint.
   * @return bool
   *   'true' if this is a new hint, 'false' if the location was already on the map.
   */
  public function recordMapMarker($who, $qr_node, $visited = true) {
    // No user info, no tracking.
    if (empty($who)) {
      return false;
    }

    // Look up the latitude and longitude of the QR code, if available
    $geo = $qr_node->get('field_geolocation')->getValue();
    if (empty($geo[0])) {
      return false;
    }

    $lat = floatval($geo[0]['lat']);
    $lng = floatval($geo[0]['lng']);

/*
    // TODO: There's also no point in saving a marker with no location (except testing)
    if (!$lat || !$lng) {
      return false;
    }
*/

    $now = \Drupal::time()->getRequestTime();

    // If there is already a record for this user and lat / lng,
    // then update its 'visited' time
    $result = $this->connection->query("SELECT id FROM {multiplex_map_markers} WHERE nid = :nid AND who = :who", [
      ':nid' => $qr_node->id(),
      ':who' => $who,
    ]);

    if ($result) {
      // There should only be one
      while ($row = $result->fetchAssoc()) {
        $key_to_update = $visited ? 'visited' : 'hinted';
        $num_updated = $this->connection->update('multiplex_map_markers')
          ->fields([
            $key_to_update => $now,
          ])
          ->condition('id', $row['id'], '=')
          ->execute();
        return false;
      }
    }

    // If the record does not alread exist, then create a new one.
    $last_insert_id = $this->connection->insert('multiplex_map_markers')
      ->fields([
        'nid' => $qr_node->id(),
        'code' => $qr_node->Url(),
        'uid' => \Drupal::currentUser()->id(),
        'who' => $who,
        'created' => $now,
        'visited' => $visited ? $now : 0,
        'hinted' => $visited ? 0 : $now,
        'lat' => $lat,
        'lng' => $lng,
      ])
      ->execute();

    return true;
  }

  /**
   * Record a record of the specified path being visited.
   *
   * @param string $who
   *   Representation of visiting user
   * @param Node $node
   *   Node visited
   * @return VisitData
   *   Visitation data including record id and cached target path
   */
  public function recordVisit($who, $node) {
    // No user info, no tracking.
    if (empty($who)) {
      return new VisitData($who, 0, 0);
    }

    $now = \Drupal::time()->getRequestTime();

    // If there is already a record for this user and visited location,
    // then update its 'visited' time
    $visit_data = $this->findVisitedRecord($who, $node);
    if ($visit_data->id()) {
      $num_updated = $this->connection->update('multiplex_visitors')
        ->fields([
          'visited' => $now,
        ])
        ->condition('id', $visit_data->id(), '=')
        ->execute();

      return $visit_data;
    }

    // If the record does not already exist, then create a new one.
    $last_insert_id = $this->connection->insert('multiplex_visitors')
      ->fields([
        'path_nid' => $node->id(),
        'target_nid' => 0,
        'uid' => \Drupal::currentUser()->id(),
        'who' => $who,
        'created' => $now,
        'visited' => $now,
      ])
      ->execute();

    return new VisitData($who, $last_insert_id, 0);
  }

  /**
   * Return visitation data for the specified node.
   */
  protected function findVisitedRecord($who, $node) {
    $result = $this->connection->query("SELECT id,target_nid FROM {multiplex_visitors} WHERE path_nid = :nid AND who = :who", [
      ':nid' => $node->id(),
      ':who' => $who,
    ]);

    if ($result) {
      // There should only be one
      while ($row = $result->fetchAssoc()) {
        return new VisitData($who, $row['id'], $row['target_nid']);
      }
    }
    return new VisitData($who, 0, 0);
  }

  /**
   * Record the redirect target in the visitation record so that future visits
   * to the same path will result in the same redirection for the same user(s).
   *
   * Note that during the normal flow, this method will only be called when
   * the target entry for the specified visitation record is empty. However,
   * this method is also used by 'recordRecent' to rewrite the target for
   * the recent pointer on every scan.
   *
   * @param VisitData $visit_data
   * @param Node $target
   */
  public function recordTarget(VisitData $visit_data, $target) {
    // If there's no user, then there's no reason to record the target.
    if (empty($visit_data->who())) {
      return;
    }
    $this->connection->update('multiplex_visitors')
      ->fields([
        'target_nid' => $target->id(),
      ])
      ->condition('id', $visit_data->id(), '=')
      ->execute();

    // Also record that the target was visited
    $this->recordVisit($visit_data->who(), $target);
  }

  /**
   * Update the visitation data for the path '/to/recent' to
   * point to the most recently scanned node
   *
   * @param string $who
   *   Representation of visiting user
   * @param Node $recent_node
   *   Most recently scanned node
   * @return bool
   *   'true' if this is the very first scan the user has done
   */
  public function recordRecent($who, $recent_node) {
    // No user info, no tracking.
    $pointer_to_recent = $this->getPointerToRecent();
    if (empty($who) || !$pointer_to_recent) {
      return false;
    }
    $visit_data = $this->recordVisit($who, $pointer_to_recent);
    $first_time_scan = !$visit_data->visited();
    $this->recordTarget($visit_data, $recent_node);

    return $first_time_scan;
  }

  /**
   * Return the most recent scanned node (QR code node)
   *
   * @param string $who
   * @return Node|null
   */
  public function mostRecent($who) {
    $recent_pointer = $this->getPointerToRecent();
    $visit_data = $this->findVisitedRecord($who, $recent_pointer);
    if (!$visit_data->visited()) {
      return null;
    }
    return \Drupal\node\Entity\Node::load($visit_data->target());
  }

  /**
   * Look up the node used to store the pointer to the most recent scan.
   */
  protected function getPointerToRecent() {
    try {
      // TODO: Maybe we should store the nid of the recent node pointer
      // in settings or something. For now we assume the well-known
      // path "/recent".
      $params = \Drupal\Core\Url::fromUserInput('/recent')->getRouteParameters();
      if (isset($params['node'])) {
        return \Drupal\node\Entity\Node::load($params['node']);
      }

    } catch(\Exception $e) {}

    return null;
  }

  /**
   * Determine which of the provided targets have been visited by the specified user.
   *
   * @param string $who
   *   Representation of visiting user
   * @param array $target_nids
   * @return array
   */
  public function findVisitedTargets($who, array $target_nids) {
    return $this->findVisited($who, $target_nids, 'target_nid');
  }

  public function findVisitedPaths($who, array $path_nids) {
    return $this->findVisited($who, $path_nids, 'path_nid');
  }

  protected function findVisited($who, array $nids, $field) {
    if (empty($nids) || empty($who)) {
      return [];
    }
    $result = $this->connection->query("SELECT $field FROM {multiplex_visitors} WHERE who = :who AND $field IN (:args[])", [':who' => $who, ':args[]' => $nids]);

    $visited = [];
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $visited[] = $row[$field];
      }
    }
    return $visited;
  }

  public function getVisitedLocationData($who) {
    // Admins always get to play
    $user = \Drupal::currentUser();
    $is_admin = $user->hasPermission('access administration menu');

    $result = $this->connection->query("SELECT id, code, lat, lng, visited, hinted FROM {multiplex_map_markers} WHERE who = :who", [':who' => $who]);

    $visited = [];
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        if (!empty($row['lat'])) {
          // Admins get to click on unvisited locations
          $is_visited = $is_admin || ($row['visited'] > 0);
          $visited[] = [
            'id' => $row['id'],
            'code' => trim($row['code'], '/'),
            'position' => [$row['lat'], $row['lng']],
            'legendId' => $is_visited ? 'visited' : 'unvisited',
            'visited' => $is_visited,
            'time' => $row['hinted'] > $row['visited'] ? $row['hinted'] * 1000 : 0,
          ];
        }
      }
    }
    return $visited;
  }
}
