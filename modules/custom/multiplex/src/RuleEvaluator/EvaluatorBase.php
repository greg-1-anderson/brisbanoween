<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
abstract class EvaluatorBase {

  /** \Drupal\multiplex\Service\VisitationService */
  protected $visitationService;

  protected $who;

  public function __construct(VisitationService $visitation_service, $who) {
    $this->visitationService = $visitation_service;
    $this->who = $who;
  }

  public abstract function evaluate($parameter_nid, $target_nid);

}
