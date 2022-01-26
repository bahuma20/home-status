<?php

namespace App\Service;

use PhpParser\Node\Scalar\MagicConst\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpKernel\KernelInterface;

class KeyValueStore
{
    protected Filesystem $filesystem;
    protected string $storePath;
    protected object $store;

    public function __construct(KernelInterface $appKernel)
    {
        $this->filesystem = new Filesystem();
        $storeDirectory = Path::canonicalize($appKernel->getProjectDir() . '/var/key_value_store');
        $this->filesystem->mkdir($storeDirectory, 0700);
        $this->storePath = $storeDirectory . '/store.json';

        if ($this->filesystem->exists($this->storePath)) {
            $this->store = json_decode(file_get_contents($this->storePath));
        } else {
            $this->store = new \stdClass();
        }
    }

    public function set(string $key, $value)
    {
        $this->store->$key = serialize($value);
        file_put_contents($this->storePath, json_encode($this->store));
    }

    public function get(string $key)
    {
        if (!property_exists($this->store, $key)) {
            return null;
        }

        return unserialize($this->store->$key);
    }
}
