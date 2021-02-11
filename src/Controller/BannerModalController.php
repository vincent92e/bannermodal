<?php

namespace Drupal\bannermodal\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\bannermodal\Entity\BannerModalInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BannerModalController.
 *
 *  Returns responses for Banner modal routes.
 */
class BannerModalController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Banner modal revision.
   *
   * @param int $banner_modal_revision
   *   The Banner modal revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($banner_modal_revision) {
    $banner_modal = $this->entityTypeManager()->getStorage('banner_modal')
      ->loadRevision($banner_modal_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('banner_modal');

    return $view_builder->view($banner_modal);
  }

  /**
   * Page title callback for a Banner modal revision.
   *
   * @param int $banner_modal_revision
   *   The Banner modal revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($banner_modal_revision) {
    $banner_modal = $this->entityTypeManager()->getStorage('banner_modal')
      ->loadRevision($banner_modal_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $banner_modal->label(),
      '%date' => $this->dateFormatter->format($banner_modal->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Banner modal.
   *
   * @param \Drupal\bannermodal\Entity\BannerModalInterface $banner_modal
   *   A Banner modal object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BannerModalInterface $banner_modal) {
    $account = $this->currentUser();
    $banner_modal_storage = $this->entityTypeManager()->getStorage('banner_modal');

    $langcode = $banner_modal->language()->getId();
    $langname = $banner_modal->language()->getName();
    $languages = $banner_modal->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $banner_modal->label()]) : $this->t('Revisions for %title', ['%title' => $banner_modal->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all banner modal revisions") || $account->hasPermission('administer banner modal entities')));
    $delete_permission = (($account->hasPermission("delete all banner modal revisions") || $account->hasPermission('administer banner modal entities')));

    $rows = [];

    $vids = $banner_modal_storage->revisionIds($banner_modal);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\bannermodal\BannerModalInterface $revision */
      $revision = $banner_modal_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $banner_modal->getRevisionId()) {
          $link = $this->l($date, new Url('entity.banner_modal.revision', [
            'banner_modal' => $banner_modal->id(),
            'banner_modal_revision' => $vid,
          ]));
        }
        else {
          $link = $banner_modal->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.banner_modal.translation_revert', [
                'banner_modal' => $banner_modal->id(),
                'banner_modal_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.banner_modal.revision_revert', [
                'banner_modal' => $banner_modal->id(),
                'banner_modal_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.banner_modal.revision_delete', [
                'banner_modal' => $banner_modal->id(),
                'banner_modal_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['banner_modal_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
