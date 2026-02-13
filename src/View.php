<?php

declare(strict_types=1);

namespace Logush;

final class View
{
    public function __construct(private readonly string $viewsDir)
    {
    }

    public function render(string $template, array $vars = []): string
    {
        $file = $this->viewsDir . '/' . ltrim($template, '/');
        if (!is_file($file)) {
            throw new \RuntimeException('Template not found: ' . $template);
        }

        extract($vars, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
