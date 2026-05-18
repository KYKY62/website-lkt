<script setup>
import { RouterLink } from 'vue-router';
import { brandLogoUrl, mainNavigation, siteData } from '../siteData';

function isExternal(path) {
    return /^https?:\/\//.test(path);
}
</script>

<template>
    <footer class="site-footer">
        <div class="site-footer__inner">
            <div class="site-footer__brand">
                <img :src="brandLogoUrl" alt="Lambang Kabupaten Langkat" class="site-footer__logo">
                <div class="space-y-3">
                    <p class="site-footer__eyebrow">Kabupaten Langkat</p>
                    <h2 class="site-footer__title">{{ siteData.identity.name }}</h2>
                    <p class="site-footer__copy">{{ siteData.identity.tagline }}</p>
                </div>
            </div>

            <div>
                <h3 class="site-footer__heading">Navigasi</h3>
                <div class="site-footer__links">
                    <template v-for="item in mainNavigation" :key="item.path">
                        <a
                            v-if="isExternal(item.path)"
                            :href="item.path"
                            :target="item.target ?? '_self'"
                            rel="noreferrer"
                            class="site-footer__link"
                        >
                            {{ item.label }}
                        </a>
                        <RouterLink v-else :to="item.path" class="site-footer__link">
                            {{ item.label }}
                        </RouterLink>
                    </template>
                </div>
            </div>

            <div class="site-footer__contact">
                <h3 class="site-footer__heading">Kontak</h3>
                <p>{{ siteData.identity.address }}</p>
                <p>{{ siteData.identity.phone }}</p>
                <p>{{ siteData.identity.email }}</p>
                <p>{{ siteData.identity.office_hours }}</p>
            </div>
        </div>
    </footer>
</template>
