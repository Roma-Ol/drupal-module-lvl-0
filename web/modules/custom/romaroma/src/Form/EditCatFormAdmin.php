<?php

namespace Drupal\romaroma\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class EditCatFormAdmin extends FormBase {

  /**
   * ID of the item to delete.
   *
   * @var.
   */

  protected $id;

  /**
   * Just getting the form IDentifier.
   */
  public function getFormId() {
    return 'formCatsAdmin';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    $this->id = $id;
    $query = \Drupal::database();
    $data = $query->select('romaroma', 'r')
      ->fields('r', ['id', 'title', 'mail', 'image'])
      ->condition('r.id', $id, '=')
      ->execute()->fetchAll(\PDO::FETCH_OBJ);

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => t('Your kitty name:'),
      '#description' => $this->t('A-Z / min-2 / max-32'),
      '#placeholder' => 'Name',
      '#default_value' => $data[0]->title,
      '#required' => FALSE,
      '#ajax' => [
        'callback' => '::validateNameAjax',
        'event' => 'change',
      ],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => t('Your Email:'),
      '#description' => $this->t('allowed values: Aa-Zz / _ / -'),
      '#placeholder' => 'Email',
      '#default_value' => $data[0]->mail,
      '#ajax' => [
        'callback' => '::validateEmailAjax',
        'event' => 'keyup',
      ],
      '#suffix' => '<div class="email-validation-message"></div>',
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Select the new photo or reatach the old one'),
      '#description' => 'jpeg/jpg/png/<2MB',
      '#placeholder' => 'Image',
      '#required' => 'TRUE',
      '#default_value' => [$data[0]->image],
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
      '#upload_location' => 'public://romaroma/',
    ];

    $form['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
    ];

    // Add a submit button that handles the submission of the form.
    $form['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t('Update'),
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'event' => 'click',
      ],
    ];
    return $form;
  }

  /**
   * General form validation.
   *
   * @inheritdoc .
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $nameValue = $form_state->getValue('title');
    $emailValue = $form_state->getValue('email');
    if (!preg_match('/^[A-Za-z]*$/', $nameValue) || strlen($nameValue) <= 2 || strlen($nameValue) > 32 || $nameValue = "") {
      $form_state->setErrorByName('title', t('The name %name is not valid.', ['%name' => $nameValue]));
    }
    else {
      // Message cleaning after submitting.
      $this->messenger()->deleteAll();
    }
    if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z1-9-_]+[@]+[a-z]+[.]+[a-z]+$/', $emailValue)) {
      $form_state->setErrorByName('email', t('the email %email is not valid.', ['%email' => $emailValue]));
    }
    else {
      // Message cleaning after submitting.
      $this->messenger()->deleteAll();
    }
  }

  /**
   * Show dynamic validation 4 email.
   */
  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $emailValue = $form_state->getValue('email');
    if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z1-9-_]+[@]+[a-z]+[.]+[a-z]+$/', $emailValue)) {
      $response->addCommand(new HtmlCommand('#form-system-messages', '
          <div class="email-ajax-validation-alert">
              <p class="email-ajax-validation-alert-text">
                  Budy, ur Email isn`t ok
              </p>
          </div>'));
    }
    else {
      $response->addCommand(new HtmlCommand('#form-system-messages', ''));
    }
    return $response;
  }

  /**
   * Do something.
   *
   * { @inheritdoc }
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setUserInput([]);
    $image = $form_state->getValue('image');
    $file = File::load($image[0]);
    $file->setPermanent();
    $form_state->setRedirect('romaroma.formex');
    $file->save();
    $value = $this->getDestinationArray();
    $let = $value["destination"];

    $query = \Drupal::database()->update('romaroma')
      ->condition('id', $this->id)
      ->fields([
        'title' => $form_state->getValue('title'),
        'mail' => $form_state->getValue('email'),
        'image' => $form_state->getValue('image')[0],
      ])
      ->execute();

    \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
    $output['admin_filtered_string'] = [
      '#markup' => '<em>This is filtered using the admin tag list</em>',
    ];
  }

  /**
   * Making the redirect.
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand('/admin/structure/cats'));
    return $response;
  }

}
