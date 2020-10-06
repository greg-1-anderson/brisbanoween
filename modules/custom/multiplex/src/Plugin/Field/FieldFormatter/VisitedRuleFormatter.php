<?php

namespace Drupal\multiplex\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'Visited Rule' formatter.
 *
 * @FieldFormatter(
 *   id = "visited_rule_formatter",
 *   label = @Translation("Visited Rule"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class VisitedRuleFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#markup' => $item->value,
      ];
    }

    return $element;
  }

}
