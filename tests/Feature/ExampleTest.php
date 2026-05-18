<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_public_homepage_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Pemerintah Kabupaten Langkat');
    }

    public function test_contact_message_can_be_submitted(): void
    {
        Storage::fake('local');

        $response = $this->postJson('/api/contact-messages', [
            'name' => 'Pengunjung Portal',
            'email' => 'pengunjung@langkatkab.go.id',
            'phone' => '08123456789',
            'subject' => 'Uji formulir kontak',
            'message' => 'Pesan ini dikirim oleh automated test.',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Pesan Anda sudah kami terima dan akan segera ditindaklanjuti.',
            ]);

        Storage::disk('local')->assertExists('contact-messages.jsonl');
    }
}
