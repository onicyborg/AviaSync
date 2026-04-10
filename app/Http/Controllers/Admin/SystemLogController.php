<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    public function index(): View
    {
        $logs = SystemLog::query()
            ->with('user')
            ->latest()
            ->get();

        return view('admin.system_logs.index', compact('logs'));
    }
}
