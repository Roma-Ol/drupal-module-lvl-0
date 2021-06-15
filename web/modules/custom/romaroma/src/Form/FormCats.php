<?php

namespace Drupal\romaroma\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Form;
use Symfony\Component\HttpFoundation\JsonResponse;

// Класс отвечает за обработку данных

/**
 * Наследуемся от базового класса Form API
 * @see \Drupal\Core\Form\FormBase
 */
class FormCats extends FormBase {

  // method that returns the form`s name
  public function getFormId() {
    return 'formCats';
  }

  // method that creates a form
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#markup'] = '<p class="heading-text">Hello! You can add here a photo of your cat.</p>';

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => 'Your cat’s name:',
      '#description' => $this->t('max - 2 / min - 32'),
      '#placeholder' => 'Name',
      //      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::validateNameAjax',
        //        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying email..'),
        ],
      ]
    ];

    $form['email'] = [
      '#title' => 'Your Email:',
      '#type' => 'email',
      '#description' => $this->t('allowed values: Aa-Zz / _ / -'),
      '#required' => TRUE,
      '#placeholder' => 'Email',
      '#ajax' => [
        'callback' => '::validateEmailAjax',
        'event' => 'keyup',
      ],
      '#suffix' => '<div class="email-validation-message"></div>',
    ];

    $form['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
    ];

    // Add a submit button that handles the submission of the form.
    $form['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t('Add cat'),
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'event' => 'click'
      ],
    ];
    return $form;
  }

  // EMAIL VALIDATION with dynamic text appearing
  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $value = $form_state->getValue('email');
    if (filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/[#$%^&*()+=!\[\]\';,\/{}|":<>?~\\\\]/', $value)) {
      $response->addCommand(new HtmlCommand('.email-validation-message', '<p class="email-input-message"></p>'));
    } else {
      $response->addCommand(new HtmlCommand('.email-validation-message', '<p class="email-input-message">sry, but we can`t find this email</p>'));
    }
    return $response;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('title');
    if (!preg_match('/^[A-Za-z]*$/', $value) || strlen($value)<2) {
      $form_state->setErrorByName ('title', t('The name %name is not valid.', array('%name' => $value)));
    }
  }

  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();
    $message = [
      '#theme' => 'status_messages',
      '#message_list' => $this->messenger()->all(),
      '#status_headings' => [
        'status' => t('Status message'),
        'error' => t('Error message'),
        'warning' => t('Warning message'),
      ],
    ];
    $messages = \Drupal::service('renderer')->render($message);
    $ajax_response->addCommand(new HtmlCommand('#form-system-messages', $messages));
    return $ajax_response;
  }

  /**
   *  (@inheritdoc).
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
  }
}
