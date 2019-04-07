<?php

namespace Pianissimo\Component\Allegro;

use Pianissimo\Component\Allegro\Exception\TemplateNotFoundException;

class Allegro
{
    /**
     * @throws TemplateNotFoundException
     */
    public function render(string $template): string
    {
        $path = getRootDirectory() . '/templates\\' . $template;

        if (file_exists($path) === false) {
            throw new TemplateNotFoundException(sprintf("Template '%s' not found", $template));
        }

        $content = file_get_contents($path);

        dd($content);
    }
}