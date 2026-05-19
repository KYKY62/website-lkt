<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\DownloadDocument;
use App\Models\NewsArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LegacyRedirectController extends Controller
{
    public function news(int $legacyId): RedirectResponse
    {
        $article = NewsArticle::query()
            ->where('legacy_id', $legacyId)
            ->where('status', 'published')
            ->firstOrFail();

        return redirect('/berita/'.$article->slug, 301);
    }

    public function announcement(int $legacyId): RedirectResponse
    {
        $announcement = Announcement::query()
            ->where('legacy_id', $legacyId)
            ->where('status', Announcement::STATUS_PUBLISHED)
            ->firstOrFail();

        return redirect('/pengumuman/'.$announcement->slug, 301);
    }

    public function announcementFile(int $legacyId): StreamedResponse|RedirectResponse
    {
        $announcement = Announcement::query()
            ->where('legacy_id', $legacyId)
            ->where('status', Announcement::STATUS_PUBLISHED)
            ->firstOrFail();

        abort_unless($announcement->file_path, 404);

        return $this->download($announcement->file_path, $announcement->file_name ?: $announcement->slug);
    }

    public function downloadFile(int $legacyId): StreamedResponse|RedirectResponse
    {
        $download = DownloadDocument::query()
            ->where('legacy_id', $legacyId)
            ->where('status', DownloadDocument::STATUS_PUBLISHED)
            ->firstOrFail();

        abort_unless($download->file_path, 404);

        return $this->download($download->file_path, $download->file_name ?: $download->slug);
    }

    private function download(string $path, string $name): StreamedResponse|RedirectResponse
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return redirect($path, 301);
        }

        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, $name);
    }
}
