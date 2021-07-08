<?php

namespace Drupal\romaroma\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class DeleteCatForm extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @let
   */
  protected $id;

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    $this->id = $id;
    $form['delete'] = [
      '#type' => 'submit',
      '#value' => t('Delete this kitty'),
      '#ajax' => [
        'callback' => '::action',
        'event' => 'click',
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Do something.
   *
   * { @inheritdoc }
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = \Drupal::database()->delete('romaroma');
    $query->condition('id', $this->id);
    $query->execute();
  }

  public function action(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $currentURL = Url::fromRoute('romaroma.cats');
    $response->addCommand(new RedirectCommand($currentURL->toString()));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "deleteCarForm";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('romaroma.formex');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to delete %id?', ['%id' => $this->id]);
  }

}
