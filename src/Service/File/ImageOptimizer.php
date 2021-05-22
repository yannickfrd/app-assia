<?php

namespace App\Service\File;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class to optimize an image files with Imagine.
 */
class ImageOptimizer
{
    /**
     * @var DataManager
     */
    private $dataManager;
    private $imagineCacheManager;
    private $filterManager;

    public function __construct(DataManager $dataManager, CacheManager $imagineCacheManager, FilterManager $filterManager)
    {
        $this->dataManager = $dataManager;
        $this->imagineCacheManager = $imagineCacheManager;
        $this->filterManager = $filterManager;
    }

    /**
     * Optimise l'image.
     *
     * @return string|false
     */
    public function optimize(string $file, array $filters = [])
    {
        foreach ($filters as $filter) {
            try {
                if (!$this->imagineCacheManager->isStored($file, $filter)) {
                    $filteredImage = $this->filterManager->applyFilter($this->dataManager->find($filter, $file), $filter);
                    $this->imagineCacheManager->store($filteredImage, $file, $filter);
                }
                $resolvedUrl = $this->imagineCacheManager->getBrowserPath($file, $filter);

                return $resolvedUrl;
                // $resolvedUrl = $this->imagineCacheManager->getBrowserPath($file, $filter, [], null, UrlGeneratorInterface::RELATIVE_PATH);
                // dump(parse_url($resolvedUrl, PHP_URL_PATH));
            } catch (\Exception $e) {
                throw $e;

                return false;
            }
        }
    }
}
