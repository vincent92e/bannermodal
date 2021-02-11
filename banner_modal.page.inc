<?php

/**
 * @file
 * Contains banner_modal.page.inc.
 *
 * Page callback for Banner modal entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Banner modal templates.
 *
 * Default template: banner_modal.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_banner_modal(array &$variables) {
  // Fetch BannerModal Entity Object.
  $banner_modal = $variables['elements']['#banner_modal'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
