<script setup>
import { computed } from 'vue';
import PublicEmptyState from '../components/PublicEmptyState.vue';
import { siteData } from '../siteData';

const profile = computed(() => siteData.profile ?? {});
const hasOverview = computed(() => (profile.value.overview ?? []).length > 0);
const hasVision = computed(() => Boolean(profile.value.vision));
const hasMissions = computed(() => (profile.value.missions ?? []).length > 0);
const hasHighlights = computed(() => (profile.value.highlights ?? []).length > 0);
const hasProfileContent = computed(() => hasOverview.value || hasVision.value || hasMissions.value || hasHighlights.value);
</script>

<template>
    <section class="section">
        <div class="page-container public-page">
            <article class="page-intro">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Profil Daerah</p>
                        <h1 class="section-title">Profil resmi Pemerintah Kabupaten Langkat.</h1>
                    </div>
                    <p class="section-copy">
                        Informasi profil daerah akan ditampilkan setelah konten resmi tersedia.
                    </p>
                </div>
            </article>

            <div v-if="hasProfileContent" class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-6">
                    <article v-if="hasOverview" class="feature-card">
                        <p v-for="paragraph in profile.overview" :key="paragraph" class="mb-4 text-sm leading-8 text-slate-600">
                            {{ paragraph }}
                        </p>
                    </article>

                    <article v-if="hasVision" class="feature-card">
                        <p class="eyebrow">Visi</p>
                        <h2 class="content-title content-title--detail mt-4">{{ profile.vision }}</h2>
                    </article>

                    <article v-if="hasMissions" class="feature-card">
                        <p class="eyebrow">Misi</p>
                        <div class="mt-6 space-y-4">
                            <div v-for="mission in profile.missions" :key="mission" class="rounded-3xl bg-slate-50 p-5 text-sm leading-7 text-slate-600">
                                {{ mission }}
                            </div>
                        </div>
                    </article>
                </div>

                <div v-if="hasHighlights" class="surface-card surface-card--dark">
                    <p class="eyebrow">Sorotan</p>
                    <div class="mt-6 space-y-4">
                        <article v-for="item in profile.highlights" :key="item.label" class="rounded-3xl border border-white/10 bg-white/5 p-5">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-400">{{ item.label }}</p>
                            <h3 class="content-title content-title--light mt-3">{{ item.value }}</h3>
                        </article>
                    </div>
                </div>
            </div>

            <PublicEmptyState
                v-else
                eyebrow="Profil"
                title="Konten profil belum tersedia."
                description="Profil daerah, visi, misi, dan sorotan resmi akan tampil setelah konten dipublikasikan."
            />
        </div>
    </section>
</template>
