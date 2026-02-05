<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditAction;

class AuditController extends Controller
{
    public function index()
    {
        $actions = AuditAction::with('actor')->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.audit.index', compact('actions'));
    }
}
