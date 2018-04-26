<?php

/**
 * @file
 * Contains \Drupal\icon_select_field\Plugin\Field\FieldWidget\IconSelectWidget.
 */

namespace Drupal\icon_select_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'icon_select_widget' widget.
 *
 * @FieldWidget(
 *   id = "icon_select_widget",
 *   label = @Translation("Icon Select Field Widget"),
 *   field_types = {
 *     "icon_select_type"
 *   }
 * )
 */
class IconSelectWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    // Override the settings with the Field Type settings.
    $this->settings = $this->getConditionalSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $options = [];

    if (isset($settings['icon_folder_path'])) {
      /** @var \Drupal\icon_select_field\IconsManagerInterface $icons_manager */
      $icons_manager = \Drupal::service('icons.manager');
      $options = $icons_manager->scanIconsList($settings['icon_folder_path'])
        ->match($settings['custom_options']);
    }

    $element['value'] = [
      '#title' => $this->t('Icon'),
      '#type' => 'select',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#options' => ['' => t('')] + $options,
      '#icon_select_tag_list' => $this->getTagList($options),
      '#theme' => 'icon_select_select',
      '#attributes' => [
        'class' => [
          'icon-select-field',
        ],
        'data-placeholder' => t('Select an option'),
        'data-allow-clear' => 'true',
      ],
    ];

    return $element;
  }

  /**
   * Generates the rendered tags list.
   *
   * @param $custom_icons_config
   *
   * @return array
   */
  private function getTagList(array $custom_icons_config) {
    $list = [];

    foreach ($custom_icons_config as $key => $display_name) {
      $list[$key] = $this->getRenderedTag($key);
    }

    return $list;
  }

  /**
   * Renders the tag value.
   *
   * @return string
   *   The rendered tag.
   */
  public function getRenderedTag($value) {
    $settings = $this->getSettings();
    $icon_path = $settings['icon_folder_path'] . $value . '.' . $settings['file_extension'];

    return "<img src=\"{$icon_path}\">";
  }

  /**
   * Gets the settings from the Field Type.
   *
   * @return array
   *   Settings array.
   */
  public function getConditionalSettings() {
    $field_item_list = $this->fieldDefinition->getItemDefinition()
      ->getTypedDataManager()
      ->createInstance($this->fieldDefinition->getDataType(), [
        'data_definition' => $this->fieldDefinition->getItemDefinition(),
        'name' => 0,
        'parent' => NULL,
      ]);
    // Get the settings from the IconSelectType class.
    return $field_item_list->getConditionalSettings();
  }

}
