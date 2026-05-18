<script setup>
import { reactive, ref } from 'vue';
import { siteData } from '../siteData';

const form = reactive({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const loading = ref(false);
const feedback = ref('');
const errors = ref({});

async function submitForm() {
    loading.value = true;
    feedback.value = '';
    errors.value = {};

    try {
        const response = await fetch('/api/contact-messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.Laravel?.csrfToken ?? '',
                Accept: 'application/json',
            },
            body: JSON.stringify(form),
        });

        const payload = await response.json();

        if (!response.ok) {
            errors.value = payload.errors ?? {};
            feedback.value = payload.message ?? 'Terjadi kesalahan saat mengirim pesan.';
            return;
        }

        feedback.value = payload.message;
        Object.assign(form, {
            name: '',
            email: '',
            phone: '',
            subject: '',
            message: '',
        });
    } catch (error) {
        feedback.value = 'Server belum dapat dihubungi. Silakan coba lagi.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="section">
        <div class="page-container public-page">
            <article class="page-intro">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Kontak</p>
                        <h1 class="section-title">Hubungi Pemerintah Kabupaten Langkat melalui formulir resmi.</h1>
                    </div>
                    <p class="section-copy">Gunakan formulir ini untuk menyampaikan pertanyaan umum, permintaan informasi, atau kebutuhan komunikasi resmi.</p>
                </div>
            </article>

            <div class="grid gap-6 lg:grid-cols-[0.82fr_1.18fr]">
                <article class="contact-aside">
                    <p class="eyebrow">Sekretariat</p>
                    <h2 class="content-title content-title--detail content-title--light mt-4">{{ siteData.identity.name }}</h2>
                    <p class="contact-aside__copy mt-3">Kanal ini dapat digunakan untuk pertanyaan umum, permintaan informasi, atau arahan menuju layanan perangkat daerah terkait.</p>
                    <div class="contact-meta mt-6">
                        <p>{{ siteData.identity.address }}</p>
                        <p>{{ siteData.identity.phone }}</p>
                        <p>{{ siteData.identity.email }}</p>
                        <p>{{ siteData.identity.office_hours }}</p>
                    </div>
                </article>

                <form class="feature-card space-y-5" @submit.prevent="submitForm">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="space-y-2">
                            <span class="form-label">Nama</span>
                            <input v-model="form.name" type="text" class="form-input" />
                            <small v-if="errors.name" class="form-error">{{ errors.name[0] }}</small>
                        </label>
                        <label class="space-y-2">
                            <span class="form-label">Email</span>
                            <input v-model="form.email" type="email" class="form-input" />
                            <small v-if="errors.email" class="form-error">{{ errors.email[0] }}</small>
                        </label>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="space-y-2">
                            <span class="form-label">Telepon</span>
                            <input v-model="form.phone" type="text" class="form-input" />
                        </label>
                        <label class="space-y-2">
                            <span class="form-label">Subjek</span>
                            <input v-model="form.subject" type="text" class="form-input" />
                            <small v-if="errors.subject" class="form-error">{{ errors.subject[0] }}</small>
                        </label>
                    </div>

                    <label class="space-y-2">
                        <span class="form-label">Pesan</span>
                        <textarea v-model="form.message" rows="6" class="form-input"></textarea>
                        <small v-if="errors.message" class="form-error">{{ errors.message[0] }}</small>
                    </label>

                    <div class="flex flex-wrap items-center gap-4">
                        <button type="submit" class="button-primary" :disabled="loading">
                            {{ loading ? 'Mengirim...' : 'Kirim Pesan' }}
                        </button>
                        <p v-if="feedback" class="text-sm text-slate-600">{{ feedback }}</p>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
