<?php

namespace Drupal\bannermodal;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\bannermodal\Entity\BannerModalInterface;

/**
 * Defines the storage handler class for Banner modal entities.
 *
 * This extends the base storage class, adding required special handling for
 * Banner modal entities.
 *
 * @ingroup bannermodal
 */
interface BannerModalStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Banner modal revision IDs for a specific Banner modal.
   *
   * @param \Drupal\bannermodal\Entity\BannerModalInterface $entity
   *   The Banner modal entity.
   *
   * @return int[]
   *   Banner modal revision IDs (in ascending order).
   */
  public function revisionIds(BannerModalInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Banner modal author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Banner modal revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\bannermodal\Entity\BannerModalInterface $entity
   *   The Banner modal entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BannerModalInterface $entity);

  /**
   * Unsets the language for all Banner modal with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
