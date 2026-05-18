<script setup>
import PreFooterWidgets from './components/PreFooterWidgets.vue';
import SiteFooter from './components/SiteFooter.vue';
import SiteHeader from './components/SiteHeader.vue';
</script>

<template>
    <div class="page-shell">
        <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
        <SiteHeader />
        <main id="main-content" class="relative z-10">
            <router-view />
        </main>
        <PreFooterWidgets />
        <SiteFooter />
    </div>
</template>
