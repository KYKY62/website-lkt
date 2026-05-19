<?php

namespace App\Http\Controllers;

use App\Models\DepartmentNewsSetting;
use App\Services\DepartmentNewsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminDepartmentNewsController extends Controller
{
    public function edit(): View
    {
        return view('admin.department-news.edit', [
            'setting' => DepartmentNewsSetting::current(),
            'apiUrl' => config('department_news.api_url'),
        ]);
    }

    public function update(Request $request, DepartmentNewsService $newsService): RedirectResponse
    {
        $validated = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:300'],
            'item_limit' => ['required', 'integer', 'min:1', 'max:20'],
            'cache_ttl_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);

        $setting = DepartmentNewsSetting::current();
        $setting->update([
            'is_enabled' => (bool) ($validated['is_enabled'] ?? false),
            'title' => trim($validated['title']),
            'description' => filled($validated['description'] ?? null) ? trim((string) $validated['description']) : null,
            'item_limit' => (int) $validated['item_limit'],
            'cache_ttl_minutes' => (int) $validated['cache_ttl_minutes'],
        ]);

        if ($setting->is_enabled) {
            $newsService->refresh($setting);
        } else {
            $newsService->clearAll();
        }

        return redirect()
            ->route('admin.department-news.edit')
            ->with('status', 'Pengaturan Kabar Perangkat Daerah berhasil disimpan.');
    }

    public function refresh(DepartmentNewsService $newsService): RedirectResponse
    {
        $items = $newsService->refresh(DepartmentNewsSetting::current());

        return redirect()
            ->route('admin.department-news.edit')
            ->with('status', $items === []
                ? 'Cache sudah direfresh, tetapi data API belum tersedia.'
                : 'Cache Kabar Perangkat Daerah berhasil direfresh.');
    }

    public function clear(DepartmentNewsService $newsService): RedirectResponse
    {
        $newsService->clearAll();

        return redirect()
            ->route('admin.department-news.edit')
            ->with('status', 'Cache Kabar Perangkat Daerah berhasil dikosongkan.');
    }
}
