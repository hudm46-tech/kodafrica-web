<?php
declare(strict_types=1);
namespace App\Core;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        $file = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $template) . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            die("View not found: $template");
        }

        // Extract data as local variables
        extract($data, EXTR_SKIP);

        // Render into buffer
        ob_start();
        require $file;
        $content = ob_get_clean();

        // If template wants a layout, wrap it
        if (isset($layout)) {
            $layoutFile = dirname(__DIR__) . '/Views/layouts/' . $layout . '.php';
            if (is_file($layoutFile)) {
                require $layoutFile;
                return;
            }
        }

        echo $content;
    }
}
