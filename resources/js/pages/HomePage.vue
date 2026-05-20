<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { announcementItems, departmentNews, downloadItems, heroWidgets, newsItems, serviceApps, siteData } from '../siteData';

const router = useRouter();
const currentSlide = ref(0);
const keyword = ref('');
const failedDepartmentImages = ref(new Set());

const slides = computed(() => siteData.hero?.slides ?? []);
const heroHeadline = computed(() => slides.value[currentSlide.value]?.title ?? siteData.hero.description);
const heroTagline = computed(() => slides.value[currentSlide.value]?.tagline ?? siteData.hero.description);

const searchableItems = computed(() => [
    ...newsItems.map((item) => ({
        title: item.title,
        type: 'Berita',
        path: `/berita/${item.slug}`,
    })),
    ...(siteData.navigation ?? []).map((item) => ({
        title: item.label,
        type: 'Menu',
        path: item.path,
    })),
    ...(siteData.services ?? []).map((item) => ({
        title: item.title,
        type: 'Layanan',
        path: item.link_url?.startsWith('/') ? item.link_url : '/layanan',
    })),
    ...downloadItems.map((item) => ({
        title: item.title,
        type: 'Download',
        path: '/download',
    })),
    ...announcementItems.map((item) => ({
        title: item.title,
        type: 'Pengumuman',
        path: '/pengumuman',
    })),
]);

const filteredResults = computed(() => {
    const term = keyword.value.trim().toLowerCase();

    if (!term) {
        return [];
    }

    return searchableItems.value
        .filter((item) => item.title.toLowerCase().includes(term))
        .slice(0, 5);
});

let sliderInterval = null;

function nextSlide() {
    if (slides.value.length === 0) {
        return;
    }

    currentSlide.value = (currentSlide.value + 1) % slides.value.length;
}

function submitSearch() {
    if (filteredResults.value.length > 0) {
        router.push(filteredResults.value[0].path);
        return;
    }

    if (keyword.value.trim()) {
        router.push('/berita');
    }
}

function isInternalLink(widget) {
    return widget.link_url?.startsWith('/') && widget.link_target !== '_blank';
}

function serviceInitials(service) {
    return service.title
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase();
}

function isInternalService(service) {
    return service.link_url?.startsWith('/') && service.link_target !== '_blank';
}

function departmentItemKey(item) {
    return `${item.source}-${item.slug}`;
}

function hasDepartmentImage(item) {
    return item.image_url && !failedDepartmentImages.value.has(departmentItemKey(item));
}

function markDepartmentImageFailed(item) {
    const nextFailedImages = new Set(failedDepartmentImages.value);
    nextFailedImages.add(departmentItemKey(item));
    failedDepartmentImages.value = nextFailedImages;
}

function compactSummary(text, limit = 250) {
    const value = String(text ?? '').replace(/\s+/g, ' ').trim();

    if (value.length <= limit) {
        return value;
    }

    return `${value.slice(0, Math.max(0, limit - 3)).trimEnd()}...`;
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

function departmentSourceLabel(source) {
    return String(source ?? '')
        .replace(/-lkt$/i, '')
        .replace(/-/g, ' ')
        .toUpperCase();
}

onMounted(() => {
    if (slides.value.length <= 1) {
        return;
    }

    sliderInterval = window.setInterval(() => {
        nextSlide();
    }, 5000);
});

onBeforeUnmount(() => {
    if (sliderInterval) {
        window.clearInterval(sliderInterval);
    }
});
</script>

<template>
    <div class="home-page">
        <section class="hero-slider">
            <div class="hero-slider__backdrop">
                <transition-group name="hero-fade">
                    <div
                        v-for="(slide, index) in slides"
                        v-show="currentSlide === index"
                        :key="slide.image"
                        class="hero-slide"
                        :style="{ backgroundImage: `linear-gradient(rgba(7, 33, 76, 0.5), rgba(7, 33, 76, 0.55)), url('${slide.image}')` }"
                    />
                </transition-group>
            </div>

            <div class="page-container hero-slider__layout" :class="{ 'hero-slider__layout--with-widgets': heroWidgets.length }">
                <div class="hero-slider__content">
                    <div class="hero-slider__surface">
                        <div class="hero-slider__badge">{{ siteData.hero.eyebrow }}</div>
                        <h1 class="hero-slider__title">
                            <span class="hero-slider__title-accent">{{ siteData.hero.title }}</span>
                        </h1>
                        <p class="hero-slider__headline">{{ heroHeadline }}</p>
                        <p class="hero-slider__tagline">{{ heroTagline }}</p>

                        <form class="hero-search" @submit.prevent="submitSearch">
                            <label for="portal-search" class="sr-only">Cari berita, layanan, pengumuman, atau dokumen</label>
                            <input
                                id="portal-search"
                                v-model="keyword"
                                type="text"
                                class="hero-search__input"
                                :placeholder="siteData.hero.search_placeholder"
                            />
                            <button type="submit" class="hero-search__button">Cari</button>
                            <div v-if="filteredResults.length > 0" class="hero-search__results">
                                <RouterLink
                                    v-for="item in filteredResults"
                                    :key="`${item.type}-${item.title}`"
                                    :to="item.path"
                                    class="hero-search__result"
                                >
                                    <span class="hero-search__result-type">{{ item.type }}</span>
                                    <span>{{ item.title }}</span>
                                </RouterLink>
                            </div>
                        </form>

                        <div class="hero-slider__actions">
                            <RouterLink :to="siteData.hero.primary_cta.path" class="button-primary">
                                {{ siteData.hero.primary_cta.label }}
                            </RouterLink>
                            <RouterLink :to="siteData.hero.secondary_cta.path" class="button-secondary-light">
                                {{ siteData.hero.secondary_cta.label }}
                            </RouterLink>
                        </div>
                    </div>
                </div>

                <aside v-if="heroWidgets.length" class="hero-widgets" aria-label="Widget hero beranda">
                    <article
                        v-for="widget in heroWidgets"
                        :key="widget.id"
                        class="hero-widget"
                        :class="`hero-widget--${widget.type}`"
                    >
                        <template v-if="widget.type === 'static_image'">
                            <img
                                class="hero-widget__image"
                                :src="widget.image_url"
                                :alt="widget.image_alt || widget.title"
                                loading="lazy"
                            >
                        </template>

                        <template v-else-if="widget.type === 'link_banner'">
                            <RouterLink v-if="isInternalLink(widget)" :to="widget.link_url" class="hero-widget__banner">
                                <img
                                    class="hero-widget__image"
                                    :src="widget.image_url"
                                    :alt="widget.image_alt || widget.title"
                                    loading="lazy"
                                >
                            </RouterLink>
                            <a
                                v-else
                                class="hero-widget__banner"
                                :href="widget.link_url"
                                :target="widget.link_target || '_self'"
                                :rel="widget.link_target === '_blank' ? 'noopener noreferrer' : null"
                            >
                                <img
                                    class="hero-widget__image"
                                    :src="widget.image_url"
                                    :alt="widget.image_alt || widget.title"
                                    loading="lazy"
                                >
                            </a>
                        </template>

                        <div v-else-if="widget.type === 'html'" class="hero-widget__html" v-html="widget.html_content" />

                        <div v-else-if="widget.type === 'embed'" class="hero-widget__embed">
                            <iframe
                                :src="widget.embed_url"
                                :title="widget.title"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="strict-origin-when-cross-origin"
                            />
                        </div>

                        <div v-else-if="widget.type === 'text_cta'" class="hero-widget__text">
                            <p class="hero-widget__eyebrow">Informasi</p>
                            <h2>{{ widget.title }}</h2>
                            <p>{{ widget.text_body }}</p>
                            <RouterLink v-if="isInternalLink(widget)" :to="widget.link_url" class="button-primary button-primary--compact">
                                {{ widget.cta_label }}
                            </RouterLink>
                            <a
                                v-else
                                class="button-primary button-primary--compact"
                                :href="widget.link_url"
                                :target="widget.link_target || '_self'"
                                :rel="widget.link_target === '_blank' ? 'noopener noreferrer' : null"
                            >
                                {{ widget.cta_label }}
                            </a>
                        </div>
                    </article>
                </aside>
            </div>
        </section>

        <section class="section">
            <div class="page-container">
                <div class="section-heading section-heading--row">
                    <div>
                        <p class="eyebrow">Berita Utama</p>
                        <h2 class="section-title">Informasi terbaru dari Pemerintah Kabupaten Langkat</h2>
                    </div>
                    <RouterLink to="/berita" class="section-link">Lihat semua berita</RouterLink>
                </div>

                <div v-if="newsItems.length" class="home-news-grid mt-4">
                    <RouterLink
                        v-if="newsItems[0]"
                        :to="`/berita/${newsItems[0].slug}`"
                        class="news-feature"
                    >
                        <div class="news-feature__image">
                            <img
                                v-if="newsItems[0].cover_image_url"
                                :src="newsItems[0].cover_image_url"
                                :alt="newsItems[0].title"
                                loading="lazy"
                            >
                            <div v-else class="news-thumb__placeholder news-thumb__placeholder--feature">
                                {{ newsInitials(newsItems[0]) }}
                            </div>
                        </div>
                        <div class="news-feature__content">
                            <div class="news-feature__meta">
                                <span>{{ newsItems[0].category }}</span>
                                <span>{{ newsItems[0].date }}</span>
                            </div>
                            <h3 class="news-feature__title">{{ newsItems[0].title }}</h3>
                            <p class="news-feature__summary">{{ compactSummary(newsItems[0].summary) }}</p>
                        </div>
                    </RouterLink>

                    <div class="home-news-list">
                        <RouterLink
                            v-for="item in newsItems.slice(1, 9)"
                            :key="item.slug"
                            :to="`/berita/${item.slug}`"
                            class="list-card"
                        >
                            <div class="news-thumb">
                                <img
                                    v-if="item.cover_image_url"
                                    :src="item.cover_image_url"
                                    :alt="item.title"
                                    loading="lazy"
                                >
                                <div v-else class="news-thumb__placeholder">{{ newsInitials(item) }}</div>
                            </div>
                            <div class="min-w-0">
                                <p class="list-card__meta">{{ item.category }} | {{ item.date }}</p>
                                <h3 class="list-card__title">{{ item.title }}</h3>
                                <p class="list-card__copy">{{ compactSummary(item.summary) }}</p>
                            </div>
                        </RouterLink>
                    </div>
                </div>

                <PublicEmptyState
                    v-else
                    class="mt-6"
                    eyebrow="Berita"
                    title="Belum ada berita yang dipublikasikan."
                    description="Berita resmi akan tampil setelah editor mempublikasikan artikel melalui panel admin."
                />
            </div>
        </section>

        <section class="section section-muted">
            <div class="page-container">
                <div class="home-quick-panels" :class="{ 'home-quick-panels--two': !departmentNews.enabled }">
                    <div v-if="departmentNews.enabled" class="panel-card panel-card--compact home-opd-panel">
                        <div class="section-heading section-heading--compact">
                            <div>
                                <p class="eyebrow">Kabar OPD</p>
                                <h2 class="section-title section-title--small">{{ departmentNews.title }}</h2>
                            </div>
                        </div>

                        <div v-if="departmentNews.items?.length" class="department-news-list mt-4">
                            <a
                                v-for="item in departmentNews.items.slice(0, 5)"
                                :key="departmentItemKey(item)"
                                :href="item.link_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="department-news-card"
                            >
                                <div class="department-news-card__media">
                                    <img
                                        v-if="hasDepartmentImage(item)"
                                        :src="item.image_url"
                                        :alt="item.title"
                                        class="department-news-card__image"
                                        loading="lazy"
                                        @error="markDepartmentImageFailed(item)"
                                    >
                                    <div v-else class="department-news-card__placeholder">LK</div>
                                </div>
                                <div class="department-news-card__content">
                                    <h3 class="department-news-card__title">{{ item.title }}</h3>
                                    <div class="department-news-card__meta">
                                        <span class="department-news-card__source">{{ departmentSourceLabel(item.source) }}</span>
                                        <span class="department-news-card__date">{{ item.date }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <PublicEmptyState
                            v-else
                            class="mt-4"
                            eyebrow="Kabar OPD"
                            title="Kabar perangkat daerah belum tersedia."
                            description="Data akan tampil otomatis setelah API perangkat daerah dapat diakses."
                        />
                    </div>

                    <div class="panel-card panel-card--compact">
                        <div class="section-heading section-heading--compact">
                            <div>
                                <p class="eyebrow">Pengumuman</p>
                                <h2 class="section-title section-title--small">Informasi resmi yang perlu segera diketahui</h2>
                            </div>
                            <RouterLink to="/pengumuman" class="section-link">Lihat pengumuman</RouterLink>
                        </div>
                        <div v-if="announcementItems.length" class="compact-list mt-4">
                            <article v-for="item in announcementItems.slice(0, 4)" :key="item.title" class="compact-row">
                                <div class="compact-row__icon">PG</div>
                                <div class="min-w-0">
                                    <p class="compact-row__meta">{{ item.date }} | {{ item.type }}</p>
                                    <h3 class="compact-row__title">{{ item.title }}</h3>
                                </div>
                            </article>
                        </div>
                        <PublicEmptyState
                            v-else
                            class="mt-4"
                            eyebrow="Pengumuman"
                            title="Belum ada pengumuman aktif."
                            description="Pengumuman resmi akan tampil setelah dipublikasikan."
                        />
                    </div>

                    <div class="panel-card panel-card--compact">
                        <div class="section-heading section-heading--compact">
                            <div>
                                <p class="eyebrow">Download</p>
                                <h2 class="section-title section-title--small">Dokumen dan file publik yang sering diakses</h2>
                            </div>
                            <RouterLink to="/download" class="section-link">Semua dokumen</RouterLink>
                        </div>
                        <div v-if="downloadItems.length" class="compact-list mt-4">
                            <article v-for="item in downloadItems.slice(0, 4)" :key="item.title" class="compact-row">
                                <div class="compact-row__icon compact-row__icon--yellow">{{ item.format }}</div>
                                <div class="min-w-0">
                                    <p class="compact-row__meta">{{ item.category }}</p>
                                    <h3 class="compact-row__title">{{ item.title }}</h3>
                                </div>
                            </article>
                        </div>
                        <PublicEmptyState
                            v-else
                            class="mt-4"
                            eyebrow="Download"
                            title="Belum ada dokumen publik."
                            description="Dokumen resmi akan tampil setelah tersedia untuk diunduh."
                        />
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-muted">
            <div class="page-container">
                <div class="section-heading section-heading--row">
                    <div>
                        <p class="eyebrow">Layanan dan Aplikasi</p>
                        <h2 class="section-title">Akses cepat menuju kanal utama pemerintah daerah</h2>
                    </div>
                    <RouterLink to="/layanan" class="section-link">Semua layanan</RouterLink>
                </div>

                <div v-if="serviceApps.length" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <template v-for="item in serviceApps" :key="item.id">
                        <RouterLink
                            v-if="isInternalService(item)"
                            :to="item.link_url"
                            class="service-app"
                        >
                            <img v-if="item.logo_url" :src="item.logo_url" :alt="`Logo ${item.title}`" class="service-app__logo">
                            <div v-else class="service-app__icon">{{ serviceInitials(item) }}</div>
                            <div>
                                <h3 class="service-app__title">{{ item.title }}</h3>
                                <p class="service-app__organizer">{{ item.organizer }}</p>
                                <p class="service-app__copy">{{ item.description }}</p>
                            </div>
                        </RouterLink>
                        <a
                            v-else
                            :href="item.link_url"
                            :target="item.link_target || '_self'"
                            :rel="item.link_target === '_blank' ? 'noopener noreferrer' : null"
                            class="service-app"
                        >
                            <img v-if="item.logo_url" :src="item.logo_url" :alt="`Logo ${item.title}`" class="service-app__logo">
                            <div v-else class="service-app__icon">{{ serviceInitials(item) }}</div>
                            <div>
                                <h3 class="service-app__title">{{ item.title }}</h3>
                                <p class="service-app__organizer">{{ item.organizer }}</p>
                                <p class="service-app__copy">{{ item.description }}</p>
                            </div>
                        </a>
                    </template>
                </div>

                <PublicEmptyState
                    v-else
                    class="mt-5"
                    eyebrow="Layanan"
                    title="Daftar layanan belum tersedia."
                    description="Kanal layanan dan aplikasi pemerintah akan tampil setelah data resmi ditambahkan."
                />
            </div>
        </section>

        <section class="section section-muted">
            <div class="page-container">
                <div class="grid gap-4 lg:grid-cols-[1fr_0.9fr]">
                    <div class="panel-card panel-card--blue">
                        <p class="eyebrow">Portal Resmi</p>
                        <h2 class="section-title section-title--light mt-2">Informasi publik dalam satu kanal resmi</h2>
                        <p class="panel-card__copy mt-3">{{ siteData.hero.description }}</p>
                    </div>

                    <div v-if="siteData.services.length" class="grid gap-3">
                        <article v-for="service in siteData.services.slice(0, 3)" :key="service.title" class="info-box">
                            <img v-if="service.logo_url" :src="service.logo_url" :alt="`Logo ${service.title}`" class="info-box__logo">
                            <div v-else class="info-box__icon">{{ serviceInitials(service) }}</div>
                            <div>
                                <h3 class="info-box__title">{{ service.title }}</h3>
                                <p class="info-box__organizer">{{ service.organizer }}</p>
                                <p class="info-box__copy">{{ service.description }}</p>
                            </div>
                        </article>
                    </div>
                    <PublicEmptyState
                        v-else
                        eyebrow="Konten"
                        title="Ringkasan layanan belum tersedia."
                        description="Konten ringkasan akan tampil setelah data layanan resmi ditambahkan."
                    />
                </div>
            </div>
        </section>
    </div>
</template>
