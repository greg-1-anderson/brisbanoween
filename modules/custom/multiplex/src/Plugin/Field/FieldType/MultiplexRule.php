<?php

/**
 * @file
 * Contains \Drupal\multiplex\Plugin\Field\FieldType\MultiplexRule.
 */

namespace Drupal\multiplex\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'multiplex rule' field type.
 *
 * @FieldType (
 *   id = "multiplexrule",
 *   label = @Translation("Multiplex Rule"),
 *   description = @Translation("Stores redirection rules for multiplex module."),
 *   default_widget = "multiplexrule",
 *   default_formatter = "multiplexrule"
 * )
 */
class MultiplexRule extends FieldItemBase {
  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'rule_type' => array(
          'type' => 'varchar',
          'length' => 32,
        ),
        'parameter_node' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'target_node' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value1 = $this->get('rule_type')->getValue();
    $value2 = $this->get('parameter_node')->getValue();
    $value3 = $this->get('target_node')->getValue();
    return empty($value1) && empty($value2) && empty($value3);
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Add our properties.
    $properties['rule_type'] = DataDefinition::create('string')
      ->setLabel(t('Rule Type'))
      ->setDescription(t('The type of rule'));

    $properties['parameter_node'] = DataDefinition::create('integer')
      ->setLabel(t('Parameter'))
      ->setDescription(t('The parameter for the rule'));

    $properties['target_node'] = DataDefinition::create('integer')
      ->setLabel(t('Target'))
      ->setDescription(t('The target (or default target) for the rule'));

    $properties['average'] = DataDefinition::create('float')
      ->setLabel(t('Average'))
      ->setDescription(t('Unused computed field'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\multiplex\AverageRoll');

    return $properties;
  }
}
