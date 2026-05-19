<script setup>
import { RouterLink } from 'vue-router';
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { announcementItems } from '../siteData';
</script>

<template>
    <section class="section">
        <div class="page-container public-page">
            <article class="page-intro">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Pengumuman</p>
                        <h1 class="section-title">Kanal resmi untuk surat edaran, agenda, dan informasi penting.</h1>
                    </div>
                </div>
            </article>

            <div v-if="announcementItems.length" class="grid gap-5">
                <article v-for="item in announcementItems" :key="item.slug" class="feature-card flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="content-meta">{{ item.category }} | {{ item.date }}</p>
                        <h2 class="content-title">{{ item.title }}</h2>
                    </div>
                    <div class="button-row">
                        <RouterLink :to="`/pengumuman/${item.slug}`" class="button button--primary">Detail</RouterLink>
                        <a v-if="item.file_url" :href="item.file_url" class="button button--secondary">Unduh</a>
                    </div>
                </article>
            </div>

            <PublicEmptyState
                v-else
                eyebrow="Pengumuman"
                title="Belum ada pengumuman aktif."
                description="Surat edaran, agenda, dan informasi penting akan tampil setelah dipublikasikan."
            />
        </div>
    </section>
</template>
