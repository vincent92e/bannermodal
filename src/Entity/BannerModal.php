<?php

namespace Drupal\bannermodal\Entity;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Banner modal entity.
 *
 * @ingroup bannermodal
 *
 * @ContentEntityType(
 *   id = "banner_modal",
 *   label = @Translation("Banner modal"),
 *   bundle_label = @Translation("Banner modal type"),
 *   handlers = {
 *     "storage" = "Drupal\bannermodal\BannerModalStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\bannermodal\BannerModalListBuilder",
 *     "views_data" = "Drupal\bannermodal\Entity\BannerModalViewsData",
 *     "translation" = "Drupal\bannermodal\BannerModalTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\bannermodal\Form\BannerModalForm",
 *       "add" = "Drupal\bannermodal\Form\BannerModalForm",
 *       "edit" = "Drupal\bannermodal\Form\BannerModalForm",
 *       "delete" = "Drupal\bannermodal\Form\BannerModalDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\bannermodal\BannerModalHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\bannermodal\BannerModalAccessControlHandler",
 *   },
 *   base_table = "banner_modal",
 *   data_table = "banner_modal_field_data",
 *   revision_table = "banner_modal_revision",
 *   revision_data_table = "banner_modal_field_revision",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer banner modal entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/banner_modal/{banner_modal}",
 *     "add-page" = "/admin/structure/banner_modal/add",
 *     "add-form" = "/admin/structure/banner_modal/add/{banner_modal_type}",
 *     "edit-form" = "/admin/structure/banner_modal/{banner_modal}/edit",
 *     "delete-form" = "/admin/structure/banner_modal/{banner_modal}/delete",
 *     "version-history" = "/admin/structure/banner_modal/{banner_modal}/revisions",
 *     "revision" = "/admin/structure/banner_modal/{banner_modal}/revisions/{banner_modal_revision}/view",
 *     "revision_revert" = "/admin/structure/banner_modal/{banner_modal}/revisions/{banner_modal_revision}/revert",
 *     "revision_delete" = "/admin/structure/banner_modal/{banner_modal}/revisions/{banner_modal_revision}/delete",
 *     "translation_revert" = "/admin/structure/banner_modal/{banner_modal}/revisions/{banner_modal_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/banner_modal",
 *   },
 *   bundle_entity_type = "banner_modal_type",
 *   field_ui_base_route = "entity.banner_modal_type.edit_form"
 * )
 */
class BannerModal extends EditorialContentEntityBase implements BannerModalInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $pages = $this->get('pages')->value;
    $pages = explode(PHP_EOL, $pages);

    foreach ($pages as $page) {
      $path = $page;
      if ($path != '<front>') {
        $path = Xss::filter($path);
      }
      $path = trim($path);
      $aliasPath[] = \Drupal::service('path_alias.manager')->getPathByAlias($path);
    }
    $pages = implode(PHP_EOL, $aliasPath);
    // Set original path from alias in database in pages field.
    $this->set('pages', $pages);
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Banner modal entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The Title of the Banner modal entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Body'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'text_default',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -9,
        'settings' => [
          'rows' => 11,
        ],
      ])

      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['amount'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Amount'))
      ->setDescription(t('The title of the Banner content entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
      ->setCardinality(3);

    $fields['button_text'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Button Text'))
      ->setDescription(t('Submit button text.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $pages_description = t("One per line. The '*' character is a wildcard. An example path is /admin/* for every admin pages. Leave in blank to show in all pages. @front_key@ is used to front page", ['@front_key@' => '<front>']);

    $fields['pages'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Pages'))
      ->setDescription($pages_description)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 4,
        ],

      ])

      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Banner modal is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
