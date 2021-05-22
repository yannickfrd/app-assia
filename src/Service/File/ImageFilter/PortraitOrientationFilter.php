<?php

namespace App\Service\File\ImageFilter;

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class PortraitRotationFilter implements LoaderInterface
{
    /**
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $size = $image->getSize();
        $height = $size->getHeight();
        $width = $size->getWidth();

        if ($height < $width) {
            $image->rotate(90);
        }

        return $image;
    }
}
