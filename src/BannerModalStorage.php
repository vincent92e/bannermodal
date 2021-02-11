<?php

namespace Drupal\bannermodal;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class BannerModalStorage extends SqlContentEntityStorage implements BannerModalStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BannerModalInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {banner_modal_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {banner_modal_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BannerModalInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {banner_modal_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('banner_modal_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
