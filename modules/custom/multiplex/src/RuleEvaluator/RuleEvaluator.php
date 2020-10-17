<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
class RuleEvaluator {

	static function create($rule_type, VisitationService $visitation_service, $who) {
    switch ($rule_type) {
      case 'visited':
        return new VisitedEvaluator($visitation_service, $who, true);
      case 'not-visited':
        return new VisitedEvaluator($visitation_service, $who, false);
      case 'multiplex':
        return new MultiplexEvaluator($visitation_service, $who);
    }
    throw new \Exception('Invalid rule type');
	}

}
