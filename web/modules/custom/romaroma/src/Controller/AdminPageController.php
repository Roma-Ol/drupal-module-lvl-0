<?php

namespace Drupal\romaroma\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

class AdminPageController extends ControllerBase {

  public function getCatList() {
    $query = \Drupal::database();
    $result = $query->select('romaroma', 'r')
      ->fields('r', ['id', 'title', 'mail', 'Image', 'created'])
      ->orderBy('created', 'DESC')
      ->execute()->fetchAll(\PDO::FETCH_OBJ);
    $data = [];

    foreach ($result as $row) {
      $data[] = [
        'image' => [
          'data' => [
            '#theme'      => 'image',
            '#alt'        => 'catImg',
            '#uri'        => File::load($row->Image)->getFileUri(),
            '#width'      => 100,
          ],
        ],
        'title' => $row->title,
        'mail' => $row->mail,
        'created' => $row->created,
        'edit' => t("<a href='editKitty/$row->id' class='db-table-button 
        db-table-button-edit use-ajax' data-dialog-type='modal'>Edit</a>"),
        'delete' => t("<a href='delete-cat/$row->id' class='db-table-button
        db-table-button-edit use-ajax' data-dialog-type='modal'>Delete</a>"),
      ];
    }
//
//    $arrayX = array_values($data);

    $header = [
      t('image'), t('Name'), t('Email'), t('Created'),
      t('Edit'), t('Delete'),
    ];

    $build['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#rows' => $data,
    ];

    return [
      $build,
      '#theme' => 'admin_romaroma_theme',
      '#title' => 'list of the cats',
    ];
  }

}
