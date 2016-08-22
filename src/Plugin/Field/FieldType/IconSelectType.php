<?php

/**
 * @file
 * Contains \Drupal\icon_select_field\Plugin\Field\FieldType\IconSelectType.
 */

namespace Drupal\icon_select_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'icon_select_type' field type.
 *
 * @FieldType(
 *   id = "icon_select_type",
 *   label = @Translation("Icon Select"),
 *   description = @Translation("Select a background for the current stripe"),
 *   default_widget = "icon_select_widget",
 *   default_formatter = "string"
 * )
 */
class IconSelectType extends FieldItemBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Text value'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = array(
      'columns' => array(
        'value' => array(
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    return parent::getConstraints();
  }

  /**
   * @return string
   */
  private function getClassString() {
    $config = \Drupal::config('icon_select_field.settings');
    $strings = [
      $config->get('class_prefix'),
      $this->get('value')->getValue(),
    ];

    array_filter($strings);

    return implode('', $strings);
  }

  public function getExtension() {
    $config = \Drupal::config('icon_select_field.settings');

    return $config->get('file_extension');
  }

  public static function getRenderedTag($value) {
    $config = \Drupal::config('icon_select_field.settings');
    $icon_path = $config->get('icon_folder_path') . $value . '.' . $config->get('file_extension');

    return "<img src=\"{$icon_path}\">";
  }

  /**
   * {@inheritdoc}
   */
  public function getString() {
    return $this->getClassString();
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();

    return $value === NULL || $value === '';
  }
}
