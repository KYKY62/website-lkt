<script setup>
import { RouterLink } from 'vue-router';
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { serviceItems, siteData } from '../siteData';

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
</script>

<template>
    <section class="section">
        <div class="page-container public-page">
            <article class="page-intro">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Layanan</p>
                        <h1 class="section-title">Akses cepat menuju layanan dan informasi perangkat daerah.</h1>
                    </div>
                    <p class="section-copy">Daftar layanan resmi akan tampil setelah data dipublikasikan.</p>
                </div>
            </article>

            <div v-if="serviceItems.length" class="service-directory">
                <article v-for="service in serviceItems" :key="service.id" class="service-card">
                    <div class="service-card__header">
                        <img v-if="service.logo_url" :src="service.logo_url" :alt="`Logo ${service.title}`" class="service-card__logo">
                        <div v-else class="service-card__logo service-card__logo--text">{{ serviceInitials(service) }}</div>
                        <div>
                            <p class="content-meta">{{ service.organizer }}</p>
                            <h2 class="content-title">{{ service.title }}</h2>
                        </div>
                    </div>

                    <p class="service-card__copy">{{ service.description }}</p>

                    <RouterLink v-if="isInternalService(service)" :to="service.link_url" class="button button--primary service-card__button">
                        Buka Layanan
                    </RouterLink>
                    <a
                        v-else
                        :href="service.link_url"
                        :target="service.link_target || '_self'"
                        :rel="service.link_target === '_blank' ? 'noopener noreferrer' : null"
                        class="button button--primary service-card__button"
                    >
                        Buka Layanan
                    </a>
                </article>
            </div>

            <PublicEmptyState
                v-else
                eyebrow="Layanan"
                title="Daftar layanan belum tersedia."
                description="Layanan dan aplikasi resmi akan tampil setelah data ditambahkan."
            />

            <div v-if="siteData.related_links.length" class="grid gap-6 lg:grid-cols-2">
                <article class="feature-card">
                    <p class="eyebrow">Tautan Terkait</p>
                    <div class="mt-6 space-y-4">
                        <div v-for="item in siteData.related_links" :key="item.label" class="surface-card surface-card--subtle">
                            <h3 class="content-title">{{ item.label }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ item.description }}</p>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
