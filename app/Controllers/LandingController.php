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
}
