<?php

namespace Drupal\romaroma\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use function Drupal\Tests\Core\Render\callback;

class FormCats extends FormBase {

  public function getFormId() {
    return 'formCats';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#markup'] = '<p class="heading-text">Hello! You can add here a photo of your cat.</p>';

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => 'Your cat’s name:',
      '#description' => $this->t('A-Z / min-2 / max-32'),
      '#placeholder' => 'Name',
      '#required' => FALSE,
      '#ajax' => [
        'callback' => '::validateNameAjax',
         'event' => 'change',
      ]
    ];

    $form['email'] = [
      '#title' => 'Your Email:',
      '#type' => 'email',
      '#description' => $this->t('allowed values: Aa-Zz / _ / -'),
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

    //WORKING VARIANT general form validation
    public function validateForm(array &$form, FormStateInterface $form_state) {
      $nameValue = $form_state->getValue('title');
      $emailValue = $form_state->getValue('email');
      if (!preg_match('/^[A-Za-z]*$/', $nameValue) || strlen($nameValue)<=2 || strlen($nameValue)>32 || $nameValue = "") {
        $form_state->setErrorByName ('title', t('The name %name is not valid.', array('%name' => $nameValue)));
      } else {
        // CLEARS THE MESSAGES BEFORE SUBMITTING
        $this->messenger()->deleteAll();
      }
      if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z1-9-_]+[@]+[a-z]+[.]+[a-z]+$/', $emailValue)) {
        $form_state->setErrorByName('email', t('the email %email is not valid.', array('%email' => $emailValue)));
      } else {
        // CLEARS THE MESSAGES BEFORE SUBMITTING
        $this->messenger()->deleteAll();
      }
    }

    // EMAIL VALIDATION - AJAX dynamic alerts
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
      } else {
        $response->addCommand(new HtmlCommand('#form-system-messages', ''));
      }
      return $response;
    }

    // SUBMIT BUTTON CALLBACK - AJAX
    public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
      $response = new AjaxResponse();
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
      $response->addCommand(new HtmlCommand('#form-system-messages', $messages));
      return $response;
    }
    // SUBMIT BUTTON
    public function submitForm(array &$form, FormStateInterface $form_state) {
      \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
    }
}
