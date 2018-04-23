<?php

/**
 * @file
 * Contains \Drupal\icon_select_field\Plugin\Field\FieldType\IconSelectType.
 */

namespace Drupal\icon_select_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

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

  private $config = NULL;

  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
    $this->config = \Drupal::config('icon_select_field.settings');
  }

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
    $schema = [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
        ],
      ],
    ];

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
    $strings = [
      $this->config->get('class_prefix'),
      $this->get('value')->getValue(),
    ];

    array_filter($strings);

    return implode('', $strings);
  }

  public function getExtension() {
    return $this->config->get('file_extension');
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

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = parent::defaultFieldSettings();

    $settings['use_global_settings'] = 1;
    $settings['icons_settings'] = [
      'class_prefix' => '',
      'file_extension' => NULL,
      'icon_folder_path' => '',
      'custom' => '',
    ];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form_state_use_global_settings = $form_state->getValue([
      'settings',
      'use_global_settings',
    ]);
    $use_global_settings = isset($form_state_use_global_settings) ? $form_state_use_global_settings : $this->getSetting('use_global_settings');
    $settings_page_url = Url::fromRoute('icon_select_field.settings', [], [['attributes' => ['target' => '_blank']]]);
    $link = Link::fromTextAndUrl($this->t('Global Settings'), $settings_page_url)
      ->toString();
    $wrapper = 'settings_fields';

    $form['use_global_settings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use global icons settings'),
      '#default_value' => $use_global_settings,
      '#weight' => 1,
      '#description' => $this->t('See %link page.', ['%link' => $link]),
      '#ajax' => [
        'wrapper' => $wrapper,
        'callback' => [get_class($this), 'ajaxUpdateFields'],
      ],
    ];

    $form['icons_settings'] = [
      '#type' => 'fieldset',
      '#title' => t('Icons settings'),
      '#description' => '<p>' . t('List of icons available in the dropdown.') . '</p>',
      '#prefix' => '<div id="' . $wrapper . '">',
      '#suffix' => '</div>',
      '#weight' => 2,
    ];

    $form['icons_settings']['class_prefix'] = [
      '#type' => 'textfield',
      '#title' => t('Class Prefix'),
      '#size' => 34,
      '#maxlength' => 34,
      '#default_value' => $this->getSetting('class_prefix'),
      '#description' => t("The prefix for the icon will be prepended to generate the class."),
      '#disabled' => $use_global_settings,
    ];

    $form['icons_settings']['file_extension'] = [
      '#type' => 'select',
      '#title' => t('Icon File Extension'),
      '#reqired' => TRUE,
      '#default_value' => $this->getSetting('file_extension'),
      '#options' => array_combine($this->config->get('allowed_file_extensions'), $this->config->get('allowed_file_extensions')),
      '#disabled' => $use_global_settings,
    ];

    $form['icons_settings']['icon_folder_path'] = [
      '#type' => 'textfield',
      '#title' => t('Folder Path to Icons'),
      '#size' => 128,
      '#maxlength' => 128,
      '#reqired' => TRUE,
      '#default_value' => $this->getSetting('icon_folder_path'),
      '#description' => t("Provide a folder path relative to the docroot, starting with a slash ('/')."),
      '#disabled' => $use_global_settings,
    ];

    $form['icons_settings']['custom'] = [
      '#type' => 'textarea',
      '#title' => t('Allowed icons'),
      '#cols' => 60,
      '#rows' => 8,
      '#reqired' => TRUE,
      '#resizable' => 'vertical',
      '#default_value' => $this->getSetting('custom'),
      '#description' => t("A list of Icons that will be provided in the \"Icon Select\" dropdown. Enter one or more icons on each line in the format: <code>icon-file-name|Label</code>. Example: <code>arrow-right|Arrow right</code>.<br>These icons should be available in your theme's Icon folder."),
      '#disabled' => $use_global_settings,
    ];

    return $form;
  }

  /**
   * Implements form element ajax callback.
   */
  public function ajaxUpdateFields(array $element, FormStateInterface $form_state) {
    return $element['settings']['icons_settings'];
  }

  /**
   * Converts array to text with multiple lines.
   *
   * @param $options
   *   The options array.
   *
   * @return string
   *   The text value.
   */
  public function optionsToText($options) {
    $value = '';

    foreach ($options as $item) {
      $value .= isset($item['key']) ? $item['key'] . '|' . $item['display_name'] : '';
      $value .= "\r\n";
    }

    return $value;
  }

  /**
   * Converts text to options.
   *
   * @param $text
   *   The text to convert.
   * @param string $line_separator
   *   Custom line separator.
   * @param string $column_separator
   *   Custom key value separator.
   *
   * @return array
   */
  public function textToOptions($text, $line_separator = "\r\n", $column_separator = '|') {
    $options = [];
    if ($lines = explode($line_separator, $text)) {
      foreach ($lines as $line) {
        if (!empty(trim($line))) {
          list($key, $label) = array_pad(explode($column_separator, $line), 2, 'N/A');
          $options[$key] = $label;
        }
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSetting($setting_name) {
    $settings = $this->getSettings();
    return $settings[$setting_name];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSettings() {
    // Avoid to build the same settings array multiple times.
    $settings = &drupal_static(__FUNCTION__);

    if (!isset($settings)) {
      $settings = $this->getFieldDefinition()->getSettings();
      $settings = array_merge($settings, $settings['icons_settings']);

      if ($settings['use_global_settings']) {
        // Override the field settings with the global settings.
        foreach ($settings as $name => $value) {
          $config_value = $this->config->get($name);
          if ($config_value != NULL) {
            if ($name == 'custom') {
              $config_value = $this->optionsToText($config_value);
            }
            $settings[$name] = $config_value;
          }
        }
      }
      // Prepare a helper array to get the custom list as an array.
      $settings['custom_options'] = !empty($settings['custom']) ? $this->textToOptions($settings['custom']) : [];
    }

    return $settings;
  }

  /**
   * Implement a public settings callback.
   *
   * @return array
   *   The field settings array;
   */
  public function getConditionalSettings() {
    return $this->getSettings();
  }

}
