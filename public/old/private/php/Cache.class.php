<?php

namespace ZeroCz\Admin;

use Wruczek\PhpFileCache\PhpFileCache;

class Cache {

    use Singleton;

    private $cache;

    private function __construct() {
        $this->cache = new PhpFileCache(__DIR__ . "/../cache/");
        $this->cache->setDevMode(true);
    }

    public function cache() {
        return $this->cache;
    }

    public function eraseKeyArray(array $array) {
        foreach ($array as $key) {
            $this->cache->eraseKey($key);
        }
    }
}
