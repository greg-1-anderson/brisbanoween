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
   * Record a record of the specified path being visited.
   *
   * @param string $who
   *   Representation of visiting user
   * @param string $path
   *   Path visited
   * @return VisitData
   *   Visitation data including record id and cached target path
   */
  public function recordVisit($who, $path) {

    // No user info, no tracking.
    if (empty($who)) {
      return;
    }

    $now = \Drupal::time()->getRequestTime();

    // If there is already a record for this user and target location,
    // then update its 'visited' time
    $result = $this->connection->query("SELECT id,target FROM {multiplex_visitors} WHERE path = :path AND who = :who", [
      ':path' => $path,
      ':who' => $who,
    ]);

    if ($result) {
      // There should only be one
      while ($row = $result->fetchAssoc()) {
        $num_updated = $this->connection->update('multiplex_visitors')
          ->fields([
            'visited' => $now,
          ])
          ->condition('id', $row['id'], '=')
          ->execute();
        return new VisitData($row['id'], $row['target']);
      }
    }

    // If the record does not alread exist, then create a new one.
    $last_insert_id = $this->connection->insert('multiplex_visitors')
      ->fields([
        'path' => $path,
        'target' => '',
        'uid' => 0,
        'who' => $who,
        'created' => $now,
        'visited' => $now,
      ])
      ->execute();

    return new VisitData($last_insert_id, '');
  }

  /**
   * Record the redirect target in the visitation record so that future visits
   * to the same path will result in the same redirection for the same user(s).
   *
   * @param VisitData $visit_data
   * @param string $target
   */
  public function recordTarget(VisitData $visit_data, $target) {
    $this->connection->update('multiplex_visitors')
      ->fields([
        'target' => $target,
      ])
      ->condition('id', $visit_data->id(), '=')
      ->execute();
  }

  /**
   * Determine which of the provided targets have been visited by the specified user.
   *
   * @param string $who
   *   Representation of visiting user
   * @param array $targets
   * @return array
   */
  public function findVisitedTargets($who, array $targets) {
    $result = $this->connection->query("SELECT target FROM {multiplex_visitors} WHERE who = :who AND target IN (:targets[])", [':who' => $who, ':targets[]' => $targets]);

    $visited = [];
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $visited[] = $row['target'];
      }
    }
    return $visited;
  }
}
