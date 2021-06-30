<?php
/**
 * @return
 * Contains \Drupal\romaroma\Controller\FirstPageController.
 */

namespace Drupal\romaroma\Controller;

//use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

//use Drupal\Core\Url;
/**
 * Provides route responses for the romaroma module.
 */

class FirstPageController extends ControllerBase
{

  //FORMBUILDER INTERFACE
    protected $formBuilder;

  //GETTING THE FORM
    public static function create(ContainerInterface $container)
    {
    $instance = parent::create($container);
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  public function build() {
    $form = $this->formBuilder->getForm('Drupal\romaroma\Form\FormCats');
    return $form;
  }

  protected function load() {
    $query = Database::getConnection()->select('romaroma', 'r');
    $query
      ->fields('r',["title", "mail", "image", "created"])
      ->orderBy('created', 'DESC');

    $entries = $query->execute()->fetchAll();

    return $entries;
  }

  //GENERATING THE FORM ON THE PAGE
  public function report() {
    $content = [];
    $content['r'] = $this->build();

    //  adding headers for the table
    $formTitle = [
      t('title'),
      t('email'),
      t('image'),
      t('created'),
    ];
    $form = $this->build();
    //  decoding the image - from obj to array
    $abra = $this->load();
    $rows = json_decode(json_encode($abra), TRUE);

    foreach ($rows as $key => $value) {
      $file = File::load($value['image']);  // loading the image on the page
      $uri = $file->getFileUri();
      //  adding the markup to the renderable element
      $image = [
        '#type' => 'image',
        '#theme' => 'image_style',
        '#style_name' => 'large',
        '#alt' => 'catimg',
        '#title' => 'catimage',
        '#uri' => $uri,
      ];
      //  rendering the image
      $renderer = \Drupal::service('renderer')->render($image);
      $rows[$key]['image'] = $renderer;

    }
    //  displaying the table with data
    $content['table'] = [
      '#type' => 'table',
      '#header' => $formTitle,
      '#rows' => $rows,
      '#empty' => t('There are no cats so far'),
    ];

    return [
      '#title' => 'some test text for new twig',
      '#form' => $form,
      '#content' => $content
    ];
  }
}
