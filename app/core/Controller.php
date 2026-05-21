<?php
/**
 * Base Controller - renders views and provides shared utilities
 */
abstract class Controller {
    /**
     * Render a view file with data
     *
     * @param string $view   Path relative to app/views/, e.g. 'auth/login'
     * @param array  $data   Variables to extract into the view
     * @param string $layout Layout file name inside app/views/layouts/
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void {
        // Extract data variables into scope
        extract($data);

        // Capture view content
        ob_start();
        $viewFile = APP_PATH . "/views/{$view}.php";
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View not found: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Render inside layout
        $layoutFile = APP_PATH . "/views/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Return a JSON response (for AJAX / API calls)
     */
    protected function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void {
        header("Location: " . BASE_URL . $url);
        exit;
    }

    /**
     * Abort with HTTP status
     */
    protected function abort(int $status = 404, string $message = 'Not Found'): void {
        http_response_code($status);
        echo $message;
        exit;
    }

    /**
     * Get POST parameter with optional default
     */
    protected function post(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET parameter with optional default
     */
    protected function get(string $key, mixed $default = null): mixed {
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
