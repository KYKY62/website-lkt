<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { findStaticPageByPath } from '../siteData';

const route = useRoute();

const page = computed(() => findStaticPageByPath(route.path));
</script>

<template>
    <section class="section">
        <div class="page-container" style="max-width: 68rem;">
            <article v-if="page" class="rich-surface">
                <p class="eyebrow">Halaman</p>
                <h1 class="section-title mt-3">{{ page.title }}</h1>
                <p v-if="page.excerpt" class="section-copy mt-4">{{ page.excerpt }}</p>
                <div class="prose-block prose-block--rich mt-8" v-html="page.content_html"></div>
            </article>

            <article v-else class="feature-card">
                <p class="eyebrow">Konten</p>
                <h1 class="section-title mt-3">Halaman tidak ditemukan.</h1>
                <p class="section-copy mt-4">Konten yang Anda cari belum tersedia atau belum dipublikasikan.</p>
            </article>
        </div>
    </section>
</template>
