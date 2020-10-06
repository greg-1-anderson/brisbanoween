<?php

namespace Drupal\multiplex\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'visited_rule' field type.
 *
 * @FieldType(
 *   id = "visited_rule",
 *   label = @Translation("Visited Rule"),
 *   description = @Translation("This field type stores a rule that redirects to the locaton specified in the target value if the user has been to the location specified by the visited location."),
 *   category = @Translation("General"),
 *   default_widget = "visited_rule_widget",
 *   default_formatter = "visited_rule_formatter"
 * )
 */
class VisitedRuleItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $visited = $this->get('visited')->getValue();
    $target = $this->get('target')->getValue();
    return empty($visited) && empty($target);
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    // @DCG
    // See /core/lib/Drupal/Core/TypedData/Plugin/DataType directory for
    // available data types.
    $properties['visited'] = DataDefinition::create('string')
      ->setLabel(t('Visited'))
      ->setRequired(TRUE);

    $properties['target'] = DataDefinition::create('string')
      ->setLabel(t('Target'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();

    // TODO: Do we need a maximum length for 'visited'?
    $options['visited']['Length']['max'] = 20;

    // @DCG
    // See /core/lib/Drupal/Core/Validation/Plugin/Validation/Constraint
    // directory for available constraints.
    $constraints[] = $constraint_manager->create('ComplexData', $options);
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $columns = [
      'visited' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'description' => 'URI to test when evaluating rule.',
        'length' => 255,
      ],
      'target' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'description' => 'URL to redirect to when user has visited the test URI.',
        'length' => 255,
      ],
    ];

    $schema = [
      'columns' => $columns,
      'indexes' => [],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['visited'] = $random->word(mt_rand(1, 50));
    $values['target'] = $random->word(mt_rand(1, 50));
    return $values;
  }

}
