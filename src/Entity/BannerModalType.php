<?php

namespace Drupal\bannermodal\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Banner modal type entity.
 *
 * @ConfigEntityType(
 *   id = "banner_modal_type",
 *   label = @Translation("Banner modal type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bannermodal\BannerModalTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bannermodal\Form\BannerModalTypeForm",
 *       "edit" = "Drupal\bannermodal\Form\BannerModalTypeForm",
 *       "delete" = "Drupal\bannermodal\Form\BannerModalTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\bannermodal\BannerModalTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "banner_modal_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "banner_modal",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/banner_modal_type/{banner_modal_type}",
 *     "add-form" = "/admin/structure/banner_modal_type/add",
 *     "edit-form" = "/admin/structure/banner_modal_type/{banner_modal_type}/edit",
 *     "delete-form" = "/admin/structure/banner_modal_type/{banner_modal_type}/delete",
 *     "collection" = "/admin/structure/banner_modal_type"
 *   }
 * )
 */
class BannerModalType extends ConfigEntityBundleBase implements BannerModalTypeInterface {

  /**
   * The Banner modal type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Banner modal type label.
   *
   * @var string
   */
  protected $label;

}
