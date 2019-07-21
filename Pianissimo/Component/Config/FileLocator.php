<?php

namespace Pianissimo\Component\Config;

class FileLocator
{
    /**
     * @var array
     */
    private $paths;

    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function locate(string $file, ?string $currentDirectory = null, bool $first = false)
    {
        $results = [];

        foreach ($this->paths as $path) {
            $currentFile = $path . DIRECTORY_SEPARATOR . $file;

            if (file_exists($currentFile) === false) {
                continue;
            }

            if ($first === true) {
                return $currentFile;
            }

            $results[] = $currentFile;
        }

        return $results;
    }
}
