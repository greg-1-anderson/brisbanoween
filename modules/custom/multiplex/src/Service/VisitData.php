<?php

namespace Drupal\multiplex\Service;

class VisitData {

  protected $who;

  protected $id;

  protected $target;

  public function __construct($who, $id, $target_nid) {
    $this->who = $who;
    $this->id = $id;
    $this->target = $target_nid;
  }

  /**
   * Database id for row in visitation table
   *
   * @return int
   */
  public function id() {
    return $this->id;
  }

  /**
   * Node ID of target for this visited location
   *
   * @return int $nid
   */
  public function target() {
    return $this->target;
  }

  /**
   * Determine whether or not this location has been visited
   *
   * @return bool
   */
  public function visited() {
    return !empty($this->target);
  }

  public function who() {
    return $this->who;
  }
}
