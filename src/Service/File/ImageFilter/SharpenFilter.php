<?php

namespace App\Service\File\ImageFilter;

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class SharpenFilter implements LoaderInterface
{
    /**
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $image->effects()->sharpen();
        // $image = imagecreatefromjpeg('image.png');
        // imagefilter($image, IMG_FILTER_CONTRAST, isset($options['value']) ? $options['value'] : -100);
        // imagejpeg($image);

        return $image;
    }
}
