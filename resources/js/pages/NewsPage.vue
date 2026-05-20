<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import PublicEmptyState from '../components/PublicEmptyState.vue';

const batchSize = 10;
const newsItems = ref([]);
const nextPage = ref(1);
const hasMoreNews = ref(true);
const isLoading = ref(false);
const loadError = ref('');

async function loadMoreNews() {
    if (isLoading.value || !hasMoreNews.value) {
        return;
    }

    isLoading.value = true;
    loadError.value = '';

    try {
        const response = await fetch(`/api/news?page=${nextPage.value}&per_page=${batchSize}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Gagal memuat berita.');
        }

        const payload = await response.json();
        const existingSlugs = new Set(newsItems.value.map((item) => item.slug));
        const freshItems = (payload.data ?? []).filter((item) => !existingSlugs.has(item.slug));

        newsItems.value = [...newsItems.value, ...freshItems];
        hasMoreNews.value = Boolean(payload.meta?.has_more);
        nextPage.value = payload.meta?.next_page ?? nextPage.value + 1;
    } catch (error) {
        loadError.value = error instanceof Error ? error.message : 'Gagal memuat berita.';
    } finally {
        isLoading.value = false;
    }
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
    loadMoreNews();
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
                    v-for="item in newsItems"
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

            <div v-if="isLoading" class="news-load-more">
                <span class="content-meta">Memuat berita...</span>
            </div>

            <div v-else-if="loadError" class="feature-card mt-6">
                <p class="content-summary">{{ loadError }}</p>
                <button type="button" class="button button--secondary mt-4" @click="loadMoreNews">
                    Coba lagi
                </button>
            </div>

            <div v-else-if="hasMoreNews" class="news-load-more">
                <button type="button" class="button button--secondary" @click="loadMoreNews">
                    Muat 10 berita lagi
                </button>
            </div>

            <PublicEmptyState
                v-else-if="!newsItems.length"
                eyebrow="Berita"
                title="Belum ada berita yang dipublikasikan."
                description="Silakan kembali lagi setelah artikel resmi tersedia."
            />
        </div>
    </section>
</template>
