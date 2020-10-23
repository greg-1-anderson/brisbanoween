<?php

namespace Drupal\multiplex\RuleEvaluator;

use Drupal\multiplex\Service\VisitationService;

// The multiplex service determines the target locations for redirects
class RuleEvaluator {

	static function create($rule, VisitationService $visitation_service, $who) {
    switch ($rule['rule_type']) {
      case 'visited':
        return new VisitedEvaluator($visitation_service, $who, $rule['visited_node'], $rule['target_node'], true);
      case 'not-visited':
        return new VisitedEvaluator($visitation_service, $who, $rule['visited_node'], $rule['target_node'], false);
      case 'multiplex':
        return new MultiplexEvaluator($visitation_service, $who, $rule['parameter_node'], $rule['target_node']);
    }
    throw new \Exception('Invalid rule type');
	}

}
