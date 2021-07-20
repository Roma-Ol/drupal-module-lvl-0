<?php

namespace Drupal\romaroma\Controller;

use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AdminPageController extends FormBase {

  protected $id;

  public function getFormId() {
    return 'formCatsAdmin';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $query = \Drupal::database();
    $result = $query->select('romaroma', 'r')
      ->fields('r', ['id', 'title', 'mail', 'Image', 'created'])
      ->orderBy('created', 'DESC')
      ->execute()->fetchAll(\PDO::FETCH_OBJ);
    $data = [];

    foreach ($result as $row) {
      $data[$row->id] = [
        [
          'data' => [
            '#theme'      => 'image',
            '#alt'        => 'catImg',
            '#uri'        => File::load($row->Image)->getFileUri(),
            '#width'      => 100,
          ],
        ],
        $row->id,
        $row->title,
        $row->mail,
        $row->created,
        t("<a href='editKitty/$row->id' class='db-table-button 
        db-table-button-edit use-ajax' data-dialog-type='modal'>Edit</a>"),
        t("<a href='delete-cat/$row->id' class='db-table-button
        db-table-button-edit use-ajax' data-dialog-type='modal'>Delete</a>"),
      ];
    }

    $header = [
      t('image'), t('id'), t('Name'), t('Email'),
      t('Created'), t('Edit'), t('Delete'),
    ];

    $build['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $data,
    ];

    $build['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t('Delete'),
    ];

    return [
      $build,
      '#title' => 'List of the cats',
    ];
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('table');
    $delete = array_filter($values);
    $query = \Drupal::database()->delete('romaroma')
      ->condition('id', $delete, 'IN')
      ->execute();
    $this->messenger()->addStatus($this->t("Succesfully deleted"));
  }

}
