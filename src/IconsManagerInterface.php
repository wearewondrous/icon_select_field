<?php

namespace Drupal\icon_select_field;

/**
 * Interface IconsManagerInterface.
 */
interface IconsManagerInterface {

  /**
   * Scans for the available icons.
   *
   * @param $source_directory
   *   The icons directory.
   */
  public function scanIconsList($relative_path);

  /**
   * Return the available matched icons.
   *
   * @param $allowedIcons
   *   The allowed icons list.
   *
   * @return array
   *   The allowed matched icons list.
   */
  public function match($allowed_icons);

}
