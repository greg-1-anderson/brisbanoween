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
        'number' => array(
          'type' => 'varchar',
          'length' => 32,
        ),
        'sides' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'modifier' => array(
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
    $value1 = $this->get('number')->getValue();
    $value2 = $this->get('sides')->getValue();
    $value3 = $this->get('modifier')->getValue();
    return empty($value1) && empty($value2) && empty($value3);
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Add our properties.
    $properties['number'] = DataDefinition::create('string')
      ->setLabel(t('Rule Type'))
      ->setDescription(t('The type of rule'));

    $properties['sides'] = DataDefinition::create('integer')
      ->setLabel(t('Sides'))
      ->setDescription(t('The number of sides on each die'));

    $properties['modifier'] = DataDefinition::create('integer')
      ->setLabel(t('Modifier'))
      ->setDescription(t('The modifier to be applied after the roll'));

    $properties['average'] = DataDefinition::create('float')
      ->setLabel(t('Average'))
      ->setDescription(t('Unused computed field'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\multiplex\AverageRoll');

    return $properties;
  }
}
