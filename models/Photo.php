<?php

use Kirby\Cms\Page;

class PhotoPage extends Page {
  public function image(?string $filename = null) {
    if (!$filename) {
      return $this->parent()->images()->template('photo')->findBy('name', $this->slug());
    }

    return parent::filename($filename);
  }
}
