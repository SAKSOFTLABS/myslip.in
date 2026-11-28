<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Plan;

class LandingController extends Controller
{
    public function index(): void
    {
        $planModel = new Plan();
        $plans = $planModel->all(['status' => 'active'], 'price ASC');

        $this->view('landing.index', [
            'plans' => $plans
        ], 'landing');
    }

    /**
     * Direct download of Windows desktop application (.exe)
     */
    public function downloadApp(): void
    {
        $exePath = dirname(__DIR__, 2) . '/public/downloads/MySlip.exe';
        if (!file_exists($exePath)) {
            http_response_code(404);
            die("Desktop application file not found.");
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.microsoft.portable-executable');
        header('Content-Disposition: attachment; filename="MySlip.exe"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($exePath));
        readfile($exePath);
        exit;
    }
}
