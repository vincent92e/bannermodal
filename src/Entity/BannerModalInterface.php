<?php

namespace Drupal\bannermodal\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Banner modal entities.
 *
 * @ingroup bannermodal
 */
interface BannerModalInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Banner modal name.
   *
   * @return string
   *   Name of the Banner modal.
   */
  public function getName();

  /**
   * Sets the Banner modal name.
   *
   * @param string $name
   *   The Banner modal name.
   *
   * @return \Drupal\bannermodal\Entity\BannerModalInterface
   *   The called Banner modal entity.
   */
  public function setName($name);

  /**
   * Gets the Banner modal creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Banner modal.
   */
  public function getCreatedTime();

  /**
   * Sets the Banner modal creation timestamp.
   *
   * @param int $timestamp
   *   The Banner modal creation timestamp.
   *
   * @return \Drupal\bannermodal\Entity\BannerModalInterface
   *   The called Banner modal entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Banner modal revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Banner modal revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\bannermodal\Entity\BannerModalInterface
   *   The called Banner modal entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Banner modal revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Banner modal revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\bannermodal\Entity\BannerModalInterface
   *   The called Banner modal entity.
   */
  public function setRevisionUserId($uid);

}
