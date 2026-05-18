<script setup>
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { galleryItems } from '../siteData';
</script>

<template>
    <section class="section">
        <div class="page-container public-page">
            <article class="page-intro">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Galeri</p>
                        <h1 class="section-title">Ruang dokumentasi kegiatan dan promosi visual daerah.</h1>
                    </div>
                </div>
            </article>

            <div v-if="galleryItems.length" class="gallery-grid md:grid-cols-2">
                <article v-for="item in galleryItems" :key="item.title" class="gallery-card">
                    <div class="gallery-card__media">Foto</div>
                    <div class="gallery-card__body">
                        <span class="chip chip-light">{{ item.label }}</span>
                        <h2 class="content-title mt-4">{{ item.title }}</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-600">{{ item.description }}</p>
                    </div>
                </article>
            </div>

            <PublicEmptyState
                v-else
                eyebrow="Galeri"
                title="Belum ada galeri yang dipublikasikan."
                description="Dokumentasi kegiatan dan visual resmi akan tampil setelah tersedia."
            />
        </div>
    </section>
</template>
