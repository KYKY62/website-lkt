<script setup>
import { RouterLink } from 'vue-router';
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { newsItems } from '../siteData';
</script>

<template>
    <section class="section">
        <div class="page-container public-page">
            <article class="page-intro">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Berita</p>
                        <h1 class="section-title">Daftar berita resmi pemerintah daerah.</h1>
                    </div>
                    <p class="section-copy">Artikel yang telah dipublikasikan oleh editor akan tampil pada halaman ini.</p>
                </div>
            </article>

            <div v-if="newsItems.length" class="grid gap-5 lg:grid-cols-2">
                <RouterLink
                    v-for="item in newsItems"
                    :key="item.slug"
                    :to="`/berita/${item.slug}`"
                    class="feature-card feature-card--hover block"
                >
                    <p class="content-meta">{{ item.category }} | {{ item.date }}</p>
                    <h2 class="content-title">{{ item.title }}</h2>
                    <p class="content-summary">{{ item.summary }}</p>
                </RouterLink>
            </div>

            <PublicEmptyState
                v-else
                eyebrow="Berita"
                title="Belum ada berita yang dipublikasikan."
                description="Silakan kembali lagi setelah artikel resmi tersedia."
            />
        </div>
    </section>
</template>
