<?php

namespace Drupal\multiplex\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'visited_rule' field widget.
 *
 * @FieldWidget(
 *   id = "visited_rule_widget",
 *   label = @Translation("Visited Rule"),
 *   field_types = {"visited_rule"},
 * )
 */
class VisitedRuleWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['visited'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->visited) ? $items[$delta]->visited : NULL,
    ];
    $element['target'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->target) ? $items[$delta]->target : NULL,
    ];

    return $element;
  }

}
