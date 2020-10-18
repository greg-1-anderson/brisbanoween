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
    // $content_types = config setting for allowed content types for place nodes

    $element['rule_type'] = array(
      '#type' => 'select',
      '#title' => t('Rule Type'),
      '#default_value' => isset($items[$delta]->rule_type) ? $items[$delta]->rule_type : '',
      '#options' => [
        '' => t("---"),
        'visited' => t("Visited"),
        'not-visited' => t("Not Visited"),
        'multiplex' => t("Multiplex Select"),
      ],
    );
    $element['parameter_node'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_settings' => ['target_bundles' => ['multiplex_dest']], // https://www.drupal.org/node/2418529
      '#title' => t('Parameter'),
      '#default_value' => isset($items[$delta]->parameter_node) ? \Drupal\node\Entity\Node::load($items[$delta]->parameter_node) : NULL,
    );
    $element['visited_node'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      // '#selection_settings' => ['target_bundles' => $content_types],
      '#title' => t('Visited Location'),
      '#default_value' => isset($items[$delta]->visited_node) ? \Drupal\node\Entity\Node::load($items[$delta]->visited_node) : NULL,
    );
    $element['target_node'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      // '#selection_settings' => ['target_bundles' => $content_types],
      '#title' => t('Target'),
      '#default_value' => isset($items[$delta]->target_node) ? \Drupal\node\Entity\Node::load($items[$delta]->target_node) : NULL,
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
