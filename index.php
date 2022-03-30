<?php
function processImages($file) {

  // Is it an image?
  if($file->isResizable()) {

    // Should we preserve EXIF data?
    /*
      NEEDS WORK:
      I would prefer to have this check for EXIF data, rather than a template.
      I couldn't get it to function properly.
      Please open a pull request if you can help. Thanks!
    */
    $template = option('chancearthur.exifAndResize.template');
    if ($file->template() === $template) {

      // Copy EXIF data before it gets stripped
      try {
        $newFile = $file->update([
          // Not necessary to include "Apple" in the camera tag
          'camera'      => $file->exif()->camera()->make() === 'Apple' ? $file->exif()->camera()->model() : $file->exif()->camera(),
          /*
            NEEDS WORK:
            Geo currently writes 'null' to the content file if location is null.
            Would prefer that it write nothing at all.
            Please open a pull request if you can help. Thanks!
          */
          'geo'         => $file->exif()->location()->lat() === null ?: $file->exif()->location()->toArray(),
          'timestamp'   => $file->exif()->timestamp(),
          'exposure'    => $file->exif()->exposure(),
          'aperture'    => $file->exif()->aperture(),
          'iso'         => $file->exif()->iso(),
          'focallength' => $file->exif()->focalLength()
        ]);
      }
      catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
    }

    // Limit longest edge to this value
    $maxSize = option('chancearthur.exifAndResize.maxSize');
    $quality = option('chancearthur.exifAndResize.quality');

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

Kirby::plugin('chancearthur/exifAndResize', [
  'options' => [
    'template' => 'photo',
    'maxSize'  => 1024,
    'quality'  => 100,
  ],
  'hooks' => [
    'file.create:after'  => function ($file) {
      processImages($file);
    },
    'file.replace:after' => function ($newFile, $oldFile) {
      processImages($newFile);
    }
  ]
]);
