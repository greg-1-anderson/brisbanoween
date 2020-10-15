<?php
/**
 * @file
 * Contains \Drupal\multiplex\Plugin\Field\FieldWidget\MultiplexRuleWidget.
 */

namespace Drupal\multiplex\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'multiplex rule' widget.
 *
 * @FieldWidget (
 *   id = "multiplexrule",
 *   label = @Translation("Multiplex Rule widget"),
 *   field_types = {
 *     "multiplexrule"
 *   }
 * )
 */
class MultiplexRuleWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $element['rule_type'] = array(
      '#type' => 'select',
      '#title' => t('Rule Type'),
      '#default_value' => isset($items[$delta]->rule_type) ? $items[$delta]->rule_type : '',
      '#options' => [
        '' => t("---"),
        'visited' => t("Visited"),
        'not-visited' => t("Not Visited"),
        'random' => t("Random Multiplex"),
        'ordered' => t("Ordered Multiplex"),
      ],
    );
    $element['parameter_node'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => t('Parameter'),
      '#default_value' => NULL,
    );
    $element['target_node'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => t('Target'),
      '#default_value' => NULL,
    );

    // If cardinality is 1, ensure a label is output for the field by wrapping
    // it in a details element.
    if ($this->fieldDefinition->getFieldStorageDefinition()->getCardinality() == 1) {
      $element += array(
        '#type' => 'fieldset',
        '#attributes' => array('class' => array('container-inline')),
      );
    }

    return $element;
  }
}
