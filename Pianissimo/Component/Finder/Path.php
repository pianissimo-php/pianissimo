<?php

namespace Pianissimo\Component\Finder;

/**
 * This is a utility tool to help you with navigating through paths.
 */
class Path
{
    /** @var string */
    private $dirname;

    /** @var string */
    private $basename;

    /** @var string|null */
    private $filename;

    /** @var string|null */
    private $extension;

    public function __construct(?string $path = null)
    {
        $this->init($path);
    }

    private function init(?string $path = null): void
    {
        if ($path === null) {
            $path = __DIR__;
        }

        if (is_dir($path)) {
            $this->initDir($path);
        }

        if (is_file($path)) {
            $this->initFile($path);
        }
    }

    private function initDir(string $path): void
    {
        $pathInfo = pathinfo($path);
        $this->dirname = $pathInfo['dirname'] . '/' . $pathInfo['basename'];
    }

    private function initFile(string $path): void
    {
        $this->dirname = pathinfo($path, PATHINFO_DIRNAME);
        $this->basename = pathinfo($path, PATHINFO_BASENAME);
        $this->filename = pathinfo($path, PATHINFO_FILENAME);
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
    }


    public function back(int $times = 1): self
    {
        for ($i = 1; $i <= $times; $i++) {
            $this->dirname = dirname($this->dirname);
        }

        return $this;
    }

    public function dir(string $dir): self
    {
        $this->dirname = $this->dirname . '/' . trim($dir, '/');

        return $this;
    }

    public function file(string $file): self
    {
        $file = trim($file, '/');

        $this->basename = $file;
        $this->filename = pathinfo($file, PATHINFO_FILENAME);
        $this->extension = pathinfo($file, PATHINFO_EXTENSION);

        return $this;
    }

    public function path(): string
    {
        return $this->dirname . ($this->filename ? '/' . $this->basename : '');
    }

    public function isDir(): bool
    {
        return is_dir($this->path());
    }

    public function isFile(): bool
    {
        return is_file($this->path());
    }

    public function basename(): string
    {
        return $this->basename;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    /**
     * Returns a new instance of this Path object, starting with the given directory
     */
    public static function Start(string $dir): self
    {
        return new Path($dir);
    }

    /**
     * Returns a new instance of this Path object, starting at the root folder.
     */
    public static function Root(): self
    {
        return (new Path(__DIR__))->back(3);
    }

    /**
     * Returns a new instance of this Path object, starting at the project folder (src/).
     */
    public static function Project(): self
    {
        return self::Root()->dir('src');
    }

    /**
     * Returns the absolute root path
     */
    public static function RootPath(): string
    {
        return self::Root()->path();
    }

    /**
     * Returns the absolute project (src/) path
     */
    public static function ProjectPath(): string
    {
        return self::Root()->dir('src')->path();
    }
}