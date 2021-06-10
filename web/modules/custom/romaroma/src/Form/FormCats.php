<?php

namespace Drupal\romaroma\Form;
use Drupal\Core\Form\FormBase;                   // Базовый класс Form API
use Drupal\Core\Form\FormStateInterface;              // Класс отвечает за обработку данных

/**
 * Наследуемся от базового класса Form API
 * @see \Drupal\Core\Form\FormBase
 */
class FormCats extends FormBase {

  // метод, который отвечает за саму форму - кнопки, поля

  public function content() {
    $element = array(
      '#markup' => '<p class="heading-text">Hello! You can add a photo of your cat here.</p>',
    );
    return $element;
  }

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

  // метод, который будет возвращать название формы
  public function getFormId() {
    return 'ex_form_exform_form';
  }

  // действия по сабмиту
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    \Drupal::messenger()->addMessage(t('Welcome to the club, %title', ['%title' => $title]));
  }

}
