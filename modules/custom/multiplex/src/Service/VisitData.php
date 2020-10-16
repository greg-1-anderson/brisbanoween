<?php

namespace Drupal\multiplex\Service;

class VisitData {

  protected $who;

  protected $id;

  protected $target;

  public function __construct($who, $id, $target) {
    $this->who = $who;
    $this->id = $id;
    $this->target = $target;
  }

  public function id() {
    return $this->id;
  }

  public function target() {
    return $this->target;
  }

  public function visited() {
    return !empty($this->target);
  }

  public function who() {
    return $this->who;
  }
}
