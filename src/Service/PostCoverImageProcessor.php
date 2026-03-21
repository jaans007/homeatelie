<?php

namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

class PostCoverImageProcessor
{
    public function process(string $absolutePath): void
    {
        $targetWidth = 1600;
        $targetHeight = 900;

        $imagine = new Imagine();
        $image = $imagine->open($absolutePath);
        $size = $image->getSize();

        $width = $size->getWidth();
        $height = $size->getHeight();

        if ($width <= 0 || $height <= 0) {
            return;
        }

        $scale = max(
            $targetWidth / $width,
            $targetHeight / $height
        );

        $resizedWidth = (int) ceil($width * $scale);
        $resizedHeight = (int) ceil($height * $scale);

        $image->resize(new Box($resizedWidth, $resizedHeight));

        $cropX = max(0, (int) floor(($resizedWidth - $targetWidth) / 2));
        $cropY = max(0, (int) floor(($resizedHeight - $targetHeight) / 2));

        $image->crop(
            new Point($cropX, $cropY),
            new Box($targetWidth, $targetHeight)
        );

        $image->save($absolutePath, [
            'jpeg_quality' => 99,
        ]);
    }
}
