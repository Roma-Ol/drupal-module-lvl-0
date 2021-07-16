<?php

namespace Drupal\romaroma\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

class AdminPageController extends ControllerBase {

  public function getCatList() {
    $query = \Drupal::database();
    $result = $query->select('romaroma', 'r')
          ->fields('r',['id', 'title', 'mail', 'Image', 'created'])
          ->execute()->fetchAll(\PDO::FETCH_OBJ);
    $data = [];
    foreach ($result as $row) {
      $data[] = [
        'id' => $row->id,
        'title' => $row->title,
        'mail' => $row->mail,
        'created' => $row->created,
        'edit'=> t('<a href="edit-cat/$row->id">Edit<a/>'),
        'delete' => t('<a href="delete-cat/$row->id">Delete<a/>'),
      ];
    }
    $header = [ t('Name'), t('Email'), t('Created'),
                                  t('Edit'),t('Delete'),];
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $data,
    ];

    return [
      $build,
      '#title' => 'list of the cats',
    ];
  }
}
