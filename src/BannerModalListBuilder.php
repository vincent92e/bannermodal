<?php

namespace Drupal\bannermodal;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Banner modal entities.
 *
 * @ingroup bannermodal
 */
class BannerModalListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Banner modal ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\bannermodal\Entity\BannerModal $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.banner_modal.edit_form',
      ['banner_modal' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
