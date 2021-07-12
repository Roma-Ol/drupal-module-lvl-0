<?php

namespace Drupal\romaroma\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

// Use Drupal;    .
// Use Drupal\Core\Url;   .

/**
 * Provides route responses for the romaroma module.
 */
class FirstPageController extends ControllerBase {

  /**
   * Formbuilder interface.
   *
   * @var
   */
  protected $formBuilder;

  /**
   * Getting the form.
   *
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    $instance              = parent::create($container);
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  /**
   * Do a specific functional.
   *
   * @inheritDoc
   */
  public function build() {
    $form = $this->formBuilder->getForm('Drupal\romaroma\Form\FormCats');
    return $form;
  }

  /**
   * Do a specific functional.
   *
   * @inheritDoc
   */
  public function delete() {
    $formdelete = $this->formBuilder->getForm('\Drupal\romaroma\Form\CatDeleteForm');
    return $formdelete;
  }

  protected function load() {
    $query = Database::getConnection()->select('romaroma', 'r');
    $query
      ->fields('r', ["title", "mail", "image", "created", "id"])
      ->orderBy('created', 'DESC');

    $entries = $query->execute()->fetchAll();

    return $entries;
  }

  /**
   * Generating the form on the page.
   */
  public function report() {
    $formTitle = [
      t('title'),
      t('email'),
      t('image'),
      t('created'),
    ];
    $form = $this->build();
    // Decoding the image - from obj to array.
    $abra = $this->load();
    $rows = json_decode(json_encode($abra), TRUE);

    foreach ($rows as $key => $value) {
      // Loading the image on the page.
      $file = File::load($value['image']);
      $uri  = $file->getFileUri();
      // Adding the markup to the renderable element.
      $image = [
        '#type'       => 'image',
        '#theme'      => 'image_style',
        '#style_name' => 'large',
        '#alt'        => 'catimg',
        '#title'      => 'catimage',
        '#uri'        => $uri,
      ];
      $value['images'] = file_url_transform_relative(file_create_url($file->getFileUri()));
      // Rendering the image.
      $renderer = \Drupal::service('renderer')->render($image);
      $rows[$key]['image'] = $renderer;
      $rows[$key]['image_url'] = $value['images'];

    }
    // Displaying the table with data.
    $content['table'] = [
      '#type'   => 'table',
      '#header' => $formTitle,
      '#rows'   => $rows,
      '#empty'  => t('There are no cats so far'),
    ];

    $value = $this->getDestinationArray();
    $let = $value["destination"];

    return [
      '#theme' => 'cat_list',
      '#form'    => $form,
      '#content' => $rows,
      '#getDest' => $let,
    ];
  }

}
