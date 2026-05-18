<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContactMessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Storage::disk('local')->append(
            'contact-messages.jsonl',
            json_encode([
                ...$validated,
                'submitted_at' => now()->toIso8601String(),
                'ip_address' => $request->ip(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return response()->json([
            'message' => 'Pesan Anda sudah kami terima dan akan segera ditindaklanjuti.',
        ]);
    }
}
