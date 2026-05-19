<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\DownloadDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicContentFileController extends Controller
{
    public function announcementFile(Announcement $announcement): StreamedResponse|RedirectResponse
    {
        abort_unless($announcement->status === Announcement::STATUS_PUBLISHED && $announcement->file_path, 404);

        return $this->download($announcement->file_path, $announcement->file_name ?: $announcement->slug);
    }

    public function downloadFile(DownloadDocument $download): StreamedResponse|RedirectResponse
    {
        abort_unless($download->status === DownloadDocument::STATUS_PUBLISHED && $download->file_path, 404);

        return $this->download($download->file_path, $download->file_name ?: $download->slug);
    }

    private function download(string $path, string $name): StreamedResponse|RedirectResponse
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return redirect($path);
        }

        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, $name);
    }
}
