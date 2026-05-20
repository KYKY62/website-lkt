<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { newsItems } from '../siteData';

const batchSize = 10;
const visibleNewsCount = ref(batchSize);

const visibleNewsItems = computed(() => newsItems.slice(0, visibleNewsCount.value));
const hasMoreNews = computed(() => visibleNewsCount.value < newsItems.length);

function loadMoreNews() {
    visibleNewsCount.value = Math.min(visibleNewsCount.value + batchSize, newsItems.length);
}

function handleScroll() {
    if (!hasMoreNews.value) {
        return;
    }

    const scrollBottom = document.documentElement.scrollHeight - (window.scrollY + window.innerHeight);

    if (scrollBottom <= 520) {
        loadMoreNews();
    }
}

function newsInitials(item) {
    return String(item.title ?? 'LK')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase();
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
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

            <div v-if="newsItems.length" class="news-directory">
                <RouterLink
                    v-for="item in visibleNewsItems"
                    :key="item.slug"
                    :to="`/berita/${item.slug}`"
                    class="feature-card feature-card--hover news-directory-card"
                >
                    <div class="news-directory-card__media">
                        <img
                            v-if="item.cover_image_url"
                            :src="item.cover_image_url"
                            :alt="item.title"
                            loading="lazy"
                        >
                        <div v-else class="news-thumb__placeholder">{{ newsInitials(item) }}</div>
                    </div>
                    <div class="min-w-0">
                        <p class="content-meta">{{ item.category }} | {{ item.date }}</p>
                        <h2 class="content-title">{{ item.title }}</h2>
                        <p class="content-summary">{{ item.summary }}</p>
                    </div>
                </RouterLink>
            </div>

            <div v-if="hasMoreNews" class="news-load-more">
                <button type="button" class="button button--secondary" @click="loadMoreNews">
                    Muat 10 berita lagi
                </button>
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
