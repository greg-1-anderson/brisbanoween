<?php

namespace Drupal\multiplex\Service;

class VisitData {

  protected $id;

  protected $target;

  public function __construct($id, $target) {
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
}
