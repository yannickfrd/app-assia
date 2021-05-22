<?php

namespace AppBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class CustomResolver implements ResolverInterface
{
    /**
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    public function isStored($path, $filter)
    {
        /* @todo: implement */
    }

    /**
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function resolve($path, $filter)
    {
        /* @todo: implement */
    }

    /**
     * @param string $path
     * @param string $filter
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        /* @todo: implement */
    }

    /**
     * @param string[] $paths
     * @param string[] $filters
     */
    public function remove(array $paths, array $filters)
    {
        /* @todo: implement */
    }
}
