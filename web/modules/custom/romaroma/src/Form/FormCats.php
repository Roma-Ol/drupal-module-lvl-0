<?php

namespace Drupal\romaroma\Form;

use Drupal\Core\Form\FormBase;                         // Базовый класс Form API
use Drupal\Core\Form\FormStateInterface;              // Класс отвечает за обработку данных

/**
 * Наследуемся от базового класса Form API
 * @see \Drupal\Core\Form\FormBase
 */
class FormCats extends FormBase {

  // method that creates a form
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#markup'] = '<p class="heading-text">Hello! You can add here a photo of your cat.</p>';

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('What is the name of ur cat?'),
      '#description' => $this->t('max - 2 / min - 32'),
      '#required' => TRUE,
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add cat'),
    ];

    return $form;
  }

  // method that returns the form`s name
  public function getFormId() {
    return 'romaroma';
  }

  // validation
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('title');
    if (preg_match('/^[0-9]*$/', $value) || strlen($value)<=2 || strlen($value)>=32) {
      $form_state->setErrorByName ('title', t('%title `s name isn`t correct, sry.', array('%title' => $value)));
    }
  }

  // after submit action
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    \Drupal::messenger()->addMessage(t('Welcome to the club, %title', ['%title' => $title]));
  }

}
