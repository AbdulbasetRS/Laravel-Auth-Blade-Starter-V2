<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Temporary Admin Layout test page — no Dashboard features yet
 * (per project rule: no Dashboard/Cards/Charts/CRUD until explicitly requested).
 */
class DashboardTestController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.test');
    }
}
