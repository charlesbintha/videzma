<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaConfig;
use App\Models\SlaRule;

class SlaController extends Controller
{
    public function index()
    {
        $configs = SlaConfig::orderByDesc('created_at')->paginate(10)->withQueryString();
        $rules = SlaRule::with('config')->orderByDesc('created_at')->paginate(10, ['*'], 'rules')->withQueryString();

        return view('admin.sla.index', compact('configs', 'rules'));
    }
}
