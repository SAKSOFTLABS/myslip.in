<?php
namespace App\Core;

/**
 * Lightweight View Rendering Engine with Master Layouts & Partials
 */
class View
{
    /**
     * Render a view within a master layout
     */
    public static function render(string $viewPath, array $data = [], string $layout = 'app'): void
    {
        // Extract data array into variables for the view
        extract($data);

        // Include global helpers and config
        $config = require __DIR__ . '/../Config/app.php';
        $flashes = Session::getFlashes();
        $isLoggedIn = Session::isAuthenticated();
        $companyId = Session::getCompanyId();
        $isSuperAdmin = Session::isSuperAdmin();
        $gstEnabled = Session::get('gst_enabled', true);

        // Path to specific view
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("View template [{$viewPath}] not found at {$viewFile}");
        }

        // Buffer the content of the view
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If no layout requested, output raw view content
        if ($layout === 'none' || $layout === '') {
            echo $content;
            return;
        }

        // Path to master layout
        $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            die("Layout [{$layout}] not found at {$layoutFile}");
        }

        // Render master layout with injected $content
        include $layoutFile;
    }

    /**
     * Include a reusable partial snippet
     */
    public static function partial(string $partialPath, array $data = []): void
    {
        extract($data);
        $config = require __DIR__ . '/../Config/app.php';
        $flashes = Session::getFlashes();
        $partialFile = __DIR__ . '/../Views/partials/' . str_replace('.', '/', $partialPath) . '.php';
        
        if (file_exists($partialFile)) {
            include $partialFile;
        }
    }

    /**
     * Render friendly HTTP error page
     */
    public static function renderError(int $code, string $message): void
    {
        $config = require __DIR__ . '/../Config/app.php';
        include __DIR__ . '/../Views/layouts/error.php';
    }
}
