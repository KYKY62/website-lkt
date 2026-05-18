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

            <div v-if="downloadItems.length" class="grid gap-5">
                <article v-for="item in downloadItems" :key="item.title" class="feature-card flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="content-meta">{{ item.category }}</p>
                        <h2 class="content-title">{{ item.title }}</h2>
                    </div>
                    <span class="chip chip-light">{{ item.format }}</span>
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
