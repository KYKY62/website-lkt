<script setup>
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { findAnnouncementBySlug } from '../siteData';

const route = useRoute();
const announcement = computed(() => findAnnouncementBySlug(route.params.slug));
</script>

<template>
    <section class="section">
        <div class="page-container" style="max-width: 64rem;">
            <RouterLink to="/pengumuman" class="button-secondary">Kembali ke pengumuman</RouterLink>

            <article v-if="announcement" class="rich-surface mt-8">
                <div class="detail-shell">
                    <div>
                        <p class="content-meta">{{ announcement.category }} | {{ announcement.date }}</p>
                        <h1 class="content-title content-title--detail">{{ announcement.title }}</h1>
                        <p v-if="announcement.editor_name" class="mt-4 text-sm font-semibold text-slate-500">
                            Dipublikasikan oleh {{ announcement.editor_name }}
                        </p>
                    </div>

                    <div v-if="announcement.content_html" class="prose-block prose-block--rich" v-html="announcement.content_html"></div>

                    <div v-if="announcement.file_url" class="surface-card surface-card--subtle mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="content-meta">Lampiran</p>
                            <h2 class="content-title">{{ announcement.file_name || 'File pengumuman' }}</h2>
                        </div>
                        <a :href="announcement.file_url" class="button button--primary">Unduh Lampiran</a>
                    </div>
                </div>
            </article>

            <PublicEmptyState
                v-else
                class="mt-8"
                eyebrow="Pengumuman"
                title="Pengumuman tidak ditemukan."
                description="Konten yang Anda cari belum tersedia atau belum dipublikasikan."
            />
        </div>
    </section>
</template>
