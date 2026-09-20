<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Inertia\Inertia;
use Inertia\Response;

class TenantAuditController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tenant/Audit/Index', [
            'logs' => AuditLog::query()->latest()->limit(100)->get(),
        ]);
    }
}