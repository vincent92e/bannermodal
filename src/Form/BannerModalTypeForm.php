<?php

namespace Drupal\bannermodal\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BannerModalTypeForm.
 */
class BannerModalTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $banner_modal_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $banner_modal_type->label(),
      '#description' => $this->t("Label for the Banner modal type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $banner_modal_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\bannermodal\Entity\BannerModalType::load',
      ],
      '#disabled' => !$banner_modal_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $banner_modal_type = $this->entity;
    $status = $banner_modal_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Banner modal type.', [
          '%label' => $banner_modal_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Banner modal type.', [
          '%label' => $banner_modal_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($banner_modal_type->toUrl('collection'));
  }

}
