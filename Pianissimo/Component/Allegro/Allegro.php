<?php

namespace Pianissimo\Component\Allegro;

use Pianissimo\Component\Allegro\Exception\TemplateNotFoundException;
use Pianissimo\Component\Finder\Path;

class Allegro
{
    /**
     * @throws TemplateNotFoundException
     */
    public function render(string $template, array $data = []): string
    {
        if (file_exists($template) === true) {
            $path = $template;
        } else {
            $path = Path::Root()->dir('templates')->file($template)->path();
        }

        if (file_exists($path) === false) {
            throw new TemplateNotFoundException(sprintf("Template '%s' not found", $template));
        }

        $content = file_get_contents($path);

        return $this->temporarySolution($content, $data);
    }

    /**
     * TODO ombouwen
     */
    private function temporarySolution(string $content, array $data): string
    {
        foreach ($data as $key => $value)
        {
            $tag = '{{ ' . $key . ' }}';
            $content = str_replace($tag, $value, $content);
        }

        return $content;
    }
}