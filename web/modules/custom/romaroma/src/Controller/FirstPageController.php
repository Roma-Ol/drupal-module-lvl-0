<?php
/**
* @return
* Contains \Drupal\romaroma\Controller\FirstPageController.
*/

namespace Drupal\romaroma\Controller;
/**
* Provides route responses for the romaroma module.
*/
class FirstPageController {

/**
* Returns a simple page.
*
* @return array
*   A simple renderable array.
*/
public function content() {
$element = array(
'#markup' => '<p class="heading-text">Hello! You can add here a photo of your cat.</p>',
);
return $element;
}

}
