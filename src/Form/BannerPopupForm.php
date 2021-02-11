<?php

namespace Drupal\bannermodal\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BannerPopupForm.
 */
class BannerPopupForm extends FormBase {

  /**
   * Drupal\Core\Path\PathMatcherInterface definition.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Path\AliasManagerInterface definition.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $pathAliasManager;

  public function __construct(PathMatcherInterface $path_matcher, RequestStack $request_stack, EntityTypeManagerInterface $entity_manager, AliasManagerInterface $alias_manager) {
    $this->pathMatcher = $path_matcher;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_manager;
    $this->pathAliasManager = $alias_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.matcher'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('path_alias.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'banner_popup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $modalStorage = $this->entityTypeManager->getStorage('banner_modal');
    $form_route = '/bannermodal/form/banner_modal';
    $current_path = \Drupal::service('path.current')->getPath();
    $current_path = $this->pathAliasManager->getAliasByPath($current_path);
    $bannerIds = $this->getBannerIds($current_path);
    $modal = $modalStorage->load(intval($bannerIds[key($bannerIds)]));

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => ($current_path != $form_route)? $modal->name->value : $this->t('Default title'),
    ];

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => ($current_path != $form_route)? $modal->body->value : $this->t('Default body text'),
    ];

    $form['donation_amounts'] = array(
      '#type' => 'radios',
      '#title' => $this
        ->t('Donation amounts'),
      '#default_value' => 1,
      '#options' => array(
        0 => ($current_path != $form_route)? $modal->amount[0]->value : $this->t('£30'),
        1 => ($current_path != $form_route)? $modal->amount[1]->value : $this->t('£35'),
        2 => ($current_path != $form_route)? $modal->amount[2]->value : $this->t('£40'),
      ),
    );

    $form['button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button text'),
      '#default_value' => ($current_path != $form_route)? $modal->button_text->value : $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
  }

  /**
   * Get ids modal.
   *
   * @param string $currentPath
   *   Current path.
   *
   * @return mixed
   *   Return ids list.
   */
  protected function getBannerIds(string $currentPath) {
    $query = $this->entityTypeManager->getStorage('banner_modal')->getQuery();

    $current_path = $this->pathAliasManager->getPathByAlias($currentPath);

    $groupCondition = $query->orConditionGroup()
      // Get all items with wildcard.
      ->condition('pages', '%*%', 'like')
      // Get all with current path.
      ->condition('pages', '%' . $current_path . '%', 'like')
      // Get all with NULL (all pages).
      ->condition('pages', NULL, 'IS');

    $query->condition($groupCondition);

    return $query->execute();
  }

}
