<?php
use Kirby\Cms\App as Kirby;

load([
  'PhotosPage' => 'models/Photos.php',
  'PhotoPage' => 'models/Photo.php',
], __DIR__);

function processImages($file) {

  // Is it an image?
  if($file->isResizable()) {

    // Should we preserve EXIF data?
    $template = option('chancearthur.photoLibrary.template');
    if ($file->template() === $template) {

      // Copy EXIF data before it gets stripped
      try {
        $newFile = $file->update([
          // Not necessary to include "Apple" in the camera tag
          'camera'      => $file->exif()->camera()->make() === 'Apple' ? $file->exif()->camera()->model() : $file->exif()->camera(),
          'geo'         => $file->exif()->location()->lat() === null ?: $file->exif()->location()->toArray(),
          'date'        => date('Y-m-d H:i:s', $file->exif()->timestamp()),
          'exposure'    => $file->exif()->exposure(),
          'aperture'    => $file->exif()->aperture(),
          'iso'         => $file->exif()->iso(),
          'focallength' => $file->exif()->focalLength()
        ]);
      }
      catch (Exception $e) {
        throw new Exception($e->getMessage());
      }

      // Rename file based on timestamp
      try {
        $file = $file->changeName(date('ymd-His', $file->exif()->timestamp()));
      }
      catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
    }

    // Limit longest edge to this value
    $maxSize = option('chancearthur.photoLibrary.maxSize');
    $quality = option('chancearthur.photoLibrary.quality');

    try {
      kirby()->thumb($file->root(), $file->root(), [
        // Conditions are within array so EXIF still gets stripped
        'width'   => $file->width() > $maxSize ? $maxSize : false,
        'height'  => $file->height() > $maxSize ? $maxSize : false,
        'quality' => $quality
      ]);
    }
    catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }
}

Kirby::plugin('chancearthur/photoLibrary', [
  'hooks' => [
    'file.create:after'  => function ($file) {
      processImages($file);
    },
    'file.replace:after' => function ($newFile, $oldFile) {
      processImages($newFile);
    }
  ],
  'options' => [
    'template' => 'photo',
    'maxSize'  => 1024,
    'quality'  => 100,
  ],
  'pageModels' => [
    'photos' => 'PhotosPage',
    'photo'  => 'PhotoPage',
  ]
]);
