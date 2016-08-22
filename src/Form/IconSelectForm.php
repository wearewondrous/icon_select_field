<?php

/**
 * @file
 * Contains \Drupal\icon_select_field\Form\IconSelectForm.
 */

namespace Drupal\icon_select_field\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class IconSelectForm.
 *
 * @package Drupal\icon_select_field\Form
 */
class IconSelectForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'icon_selects_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'icon_select_field.settings_class_prefix',
      'icon_select_field.settings_custom',
      'icon_select_field.settings_file_extension',
      'icon_select_field.settings_icon_folder_path',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('icon_select_field.settings');
    $setting_string = '';

    foreach ($config->get('custom') as $custom_icon) {
      $setting_string .= isset($custom_icon['key']) ? $custom_icon['key'] . '|' . $custom_icon['display_name'] : '';
      $setting_string .= "\r\n";
    }

    $form['description'] = [
      '#markup' => '<p>' . t('List of icons available in the dropdown.') . '</p>',
    ];

    $form['custom_class_prefix'] = [
      '#type' => 'textfield',
      '#title' => t('Class Prefix'),
      '#size' => 34,
      '#maxlength' => 34,
      '#default_value' => $config->get('class_prefix'),
      '#description' => t("The prefix for the icon will be prepended to generate the class."),
    ];

    $form['custom_file_extension'] = [
      '#type' => 'select',
      '#title' => t('Icon File Extension'),
      '#reqired' => true,
      '#default_value' => $config->get('file_extension'),
      '#options' => array_combine($config->get('allowed_file_extensions'), $config->get('allowed_file_extensions')),
    ];

    $form['custom_icon_folder_path'] = [
      '#type' => 'textfield',
      '#title' => t('Folder Path to Icons'),
      '#size' => 128,
      '#maxlength' => 128,
      '#reqired' => true,
      '#default_value' => $config->get('icon_folder_path'),
      '#description' => t("Provide a folder path relative to the docroot, starting with a slash ('/')."),
    ];

    $form['custom_icons'] = array(
      '#type' => 'textarea',
      '#title' => t('Color Codes'),
      '#cols' => 60,
      '#rows' => 8,
      '#reqired' => true,
      '#resizable' => 'vertical',
      '#default_value' => $setting_string,
      '#description' => t("A list of classes that will be provided in the \"Icon Select\" dropdown. Enter one or more classes on each line in the format: <code>class|Label</code>. Example: <code>big-smile|Big Smile</code>.<br>These styles should be available in your theme's CSS file."),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $custom_icon_config = $this->getCustomIcons($form_state->getValue('custom_icons'));
    $custom_icon_folder_path = $form_state->getValue('custom_icon_folder_path');

    foreach($custom_icon_config as $icon_config) {
      if (empty($icon_config['key']) || empty($icon_config['display_name'])) {
        $form_state->setErrorByName('', t('<code>key</code> and <code>display_name</code>can not be empty.'));
      }
      if (!empty($custom_icon_folder_path)) {
        if ($custom_icon_folder_path[0] !== '/' || substr($custom_icon_folder_path, -1 ) !== '/') {
          $form_state->setErrorByName('', t('Folder Path to Icons has to start and end with a slash (\'/\').'));
        } elseif (!glob(DRUPAL_ROOT . $custom_icon_folder_path . '*.' . $form_state->getValue('custom_file_extension'))) {
          $form_state->setErrorByName('', t('Folder Path to Icons not a folder.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $custom_icon_config = $this->getCustomIcons($form_state->getValue('custom_icons'));

    \Drupal::configFactory()->getEditable('icon_select_field.settings')
      ->set('class_prefix', $form_state->getValue('custom_class_prefix'))
      ->set('custom', $custom_icon_config)
      ->set('file_extension', $form_state->getValue('custom_file_extension'))
      ->set('icon_folder_path', $form_state->getValue('custom_icon_folder_path'))
      ->save();
  }

  private function getCustomIcons($custom_icons_string) {
    $custom_icons_string_lines = array_filter(explode("\n", str_replace("\r\n", "\n", $custom_icons_string)), 'trim');
    $custom_icon_config = [];

    foreach ($custom_icons_string_lines as $index => $line) {
      $line_settings = explode('|', $line, 2);

      if (isset($line_settings[0])) {
        $custom_icon_config[$index]['key'] = $line_settings[0];
      }

      if (isset($line_settings[1])) {
        $custom_icon_config[$index]['display_name'] = $line_settings[1];
      }
    }

    return $custom_icon_config;
  }
}
