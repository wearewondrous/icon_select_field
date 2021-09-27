<?php

namespace Drupal\icon_select_field;

use Drupal\Core\Config\CachedStorage;
use Symfony\Component\Finder\Finder;

/**
 * Class IconsManager.
 */
class IconsManager implements IconsManagerInterface {

  /**
   * Path of the icons.
   */
  public $path = [];

  /**
   * List of available icons.
   */
  public $availableIcons = [];

  /**
   * Drupal\Core\Config\CachedStorage definition.
   *
   * @var \Drupal\Core\Config\CachedStorage
   */
  protected $configStorage;

  /**
   * Constructs a new IconsManager object.
   */
  public function __construct(CachedStorage $config_storage) {
    $this->configStorage = $config_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function scanIconsList($relative_path) {
    $this->availableIcons = [];
    $this->path = $relative_path;
    $full_path = \Drupal::root() . $relative_path;

    // Check if the directory exist.
    if (is_dir($full_path)) {
      $finder = new Finder();
      $finder
        ->files()
        ->in($full_path);
      foreach ($finder as $file) {
        $filePath = $file->getPath();
        $splits = explode($full_path, $filePath);
        $subFolder = count($splits) > 1 ? $splits[1]: '';
        $key = $file->getBasename('.' . $file->getExtension());
        $key = $subFolder ? $subFolder . '/' . $key : $key;
        $this->availableIcons[$key] = $key;
      }
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function match($allowed_icons) {
    return ($allowed_icons) ? array_intersect_key($allowed_icons, $this->availableIcons) : $this->availableIcons;
  }

}
