<script setup>
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { downloadItems } from '../siteData';
</script>

<template>
    <section class="section">
        <div class="page-container public-page">
            <article class="page-intro">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Download</p>
                        <h1 class="section-title">Pusat dokumen publik dan bahan unduhan resmi.</h1>
                    </div>
                </div>
            </article>

            <div v-if="downloadItems.length" class="content-list">
                <article v-for="item in downloadItems" :key="item.slug" class="feature-card content-list-card">
                    <div class="content-list-card__body">
                        <p class="content-meta">{{ item.category }} | {{ item.date }}</p>
                        <h2 class="content-title content-title--list">{{ item.title }}</h2>
                        <p v-if="item.description" class="content-summary mt-2">{{ item.description }}</p>
                    </div>
                    <div class="content-list-card__actions">
                        <span class="chip chip-light">{{ item.format }}</span>
                        <a v-if="item.file_url" :href="item.file_url" class="button button--primary">Unduh</a>
                    </div>
                </article>
            </div>

            <PublicEmptyState
                v-else
                eyebrow="Download"
                title="Belum ada dokumen publik."
                description="Dokumen resmi akan tampil setelah file tersedia untuk diunduh."
            />
        </div>
    </section>
</template>
