<?php

namespace App\Support;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $viewFile = app_path('resources/views/'.str_replace('.', '/', $template).'.php');
        if (! is_file($viewFile)) {
            http_response_code(500);
            return 'View not found: '.e($template);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        if (($layout ?? true) === false) {
            return $content;
        }
        ob_start();
        require app_path('resources/views/layouts/app.php');
        return ob_get_clean();
    }
}
