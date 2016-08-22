<?php

/**
 * @file
 * Contains \Drupal\icon_select_field\Plugin\Field\FieldWidget\IconSelectWidget.
 */

namespace Drupal\icon_select_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\icon_select_field\Plugin\Field\FieldType\IconSelectType;

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
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $configService = \Drupal::service('config.storage');
    $custom_colors_config = $configService->read('icon_select_field.settings')['custom'];
    $select_options = [];

    foreach ($custom_colors_config as $color_config) {
      if (!$color_config) {
        continue;
      }

      $select_options[$color_config['key']] = $color_config['display_name'];
    }

    $element['value'] = [
      '#title' => $this->t('Icon'),
      '#type' => 'select',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#options' => ['' => t('')] + $select_options,
      '#icon_select_tag_list' => $this->convertConfig($select_options),
      '#theme' => 'icon_select_select',
      '#attributes' => [
        'class' => [
          'icon-select-field',
        ],
        'data-placeholder' => t('Select an option'),
        'data-allow-clear' => 'true'
      ],
    ];

    return $element;
  }


  private function convertConfig($custom_colors_config) {
    $array = [];

    foreach ($custom_colors_config as $key => $display_name) {
      $array[$key] = IconSelectType::getRenderedTag($key);
    }

    return $array;
  }
}
