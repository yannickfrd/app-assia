<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheService extends FilesystemAdapter
{
    /**
     * Cherche un item en cache.
     */
    public function find(string $key)
    {
        $item = $this->getItem($key);

        return $item->isHit() ? $item->get() : null;
    }

    /**
     * Met un item en cache.
     *
     * @param object|array|int       $value
     * @param int|\DateInterval|null $time
     */
    public function cache(string $key, $value, $time = null)
    {
        $item = $this->getItem($key);

        $item->set($value);
        $item->expiresAfter($time);
        $this->save($item);

        return $item->get();
    }

    /**
     * Vide l'item du suivi en cache.
     *
     * @param string|array $keys
     */
    public function discache($keys): bool
    {
        return is_array($keys) ? $this->deleteItems($keys) : $this->deleteItem($keys);
    }
}
