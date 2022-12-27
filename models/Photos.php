<?php

use Kirby\Cms\Page;
use Kirby\Cms\Pages;

class PhotosPage extends Page {
  public function children() {
    $images = [];

    foreach ($this->images()->template('photo') as $image) {

      if ($image->alt()->isNotEmpty()) {
        $title = $image->alt();
      } elseif ($image->location()->isNotEmpty()) {
        $title = $image->location();
      } elseif ($image->date()->isNotEmpty()) {
        $title = $image->date()->toDate('M j, Y');
      } else {
        $title = "Photo";
      }

      $images[] = [
        'slug'     => $image->name(),
        'template' => 'photo',
        'model'    => 'photo',
        'content'  => [
          'title' => $title,
          'date'  => $image->date(),
          $image->content()->toArray()
        ]
      ];
    }

    return Pages::factory($images, $this);
  }
}
