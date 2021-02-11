<?php

namespace Drupal\bannermodal\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Banner modal revision.
 *
 * @ingroup bannermodal
 */
class BannerModalRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Banner modal revision.
   *
   * @var \Drupal\bannermodal\Entity\BannerModalInterface
   */
  protected $revision;

  /**
   * The Banner modal storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $bannerModalStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->bannerModalStorage = $container->get('entity_type.manager')->getStorage('banner_modal');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'banner_modal_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.banner_modal.version_history', ['banner_modal' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $banner_modal_revision = NULL) {
    $this->revision = $this->BannerModalStorage->loadRevision($banner_modal_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->BannerModalStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Banner modal: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Banner modal %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.banner_modal.canonical',
       ['banner_modal' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {banner_modal_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.banner_modal.version_history',
         ['banner_modal' => $this->revision->id()]
      );
    }
  }

}
