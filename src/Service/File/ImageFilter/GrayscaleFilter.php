<?php

namespace App\Service\File\ImageFilter;

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class GrayscaleFilter implements LoaderInterface
{
    /**
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $image->effects()->grayscale();

        return $image;
    }
}
