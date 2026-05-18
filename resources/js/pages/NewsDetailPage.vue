<script setup>
import { computed, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { findNewsBySlug } from '../siteData';

const route = useRoute();
const article = computed(() => findNewsBySlug(route.params.slug));
const activeImage = ref(null);

function openImage(imageUrl) {
    activeImage.value = imageUrl;
}

function closeImage() {
    activeImage.value = null;
}
</script>

<template>
    <section class="section">
        <div class="page-container" style="max-width: 64rem;">
            <RouterLink to="/berita" class="button-secondary">Kembali ke berita</RouterLink>

            <article v-if="article" class="rich-surface mt-8">
                <div class="detail-shell">
                    <div>
                        <p class="content-meta">{{ article.category }} | {{ article.date }}</p>
                        <h1 class="content-title content-title--detail">{{ article.title }}</h1>
                        <p class="content-summary">{{ article.summary }}</p>
                        <p v-if="article.editor_name" class="mt-4 text-sm font-semibold text-slate-500">Dipublikasikan oleh {{ article.editor_name }}</p>
                    </div>

                    <div
                        class="detail-hero"
                        :style="article.cover_image_url ? { backgroundImage: `linear-gradient(rgba(15, 23, 42, 0.45), rgba(13, 92, 171, 0.32)), url('${article.cover_image_url}')` } : { backgroundImage: 'linear-gradient(135deg, #0a4b8c, #083767 58%, #e4bf47)' }"
                    ></div>

                    <div class="prose-block prose-block--rich" v-html="article.content_html"></div>
                </div>

                <div v-if="article.gallery_images?.length" class="mt-10">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="section-title section-title--small" style="max-width: none;">Galeri Berita</h2>
                        <p class="text-sm text-slate-500">Klik thumbnail untuk melihat gambar penuh.</p>
                    </div>

                    <div class="news-gallery mt-5">
                        <button
                            v-for="imageUrl in article.gallery_images"
                            :key="imageUrl"
                            type="button"
                            class="news-gallery__thumb"
                            @click="openImage(imageUrl)"
                        >
                            <img :src="imageUrl" alt="Galeri berita" class="news-gallery__image" />
                        </button>
                    </div>
                </div>
            </article>

            <article v-else class="feature-card mt-8">
                <h1 class="section-title">Berita tidak ditemukan.</h1>
                <p class="mt-4 text-sm leading-7 text-slate-600">Artikel belum tersedia atau belum dipublikasikan.</p>
            </article>
        </div>

        <div v-if="activeImage" class="image-lightbox" @click.self="closeImage">
            <button type="button" class="image-lightbox__close" @click="closeImage">Tutup</button>
            <img :src="activeImage" alt="Preview galeri berita" class="image-lightbox__image" />
        </div>
    </section>
</template>
