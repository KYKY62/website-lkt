<script setup>
import { computed, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { brandLogoUrl, mainNavigation, siteData } from '../siteData';

const isOpen = ref(false);
const openSubmenu = ref(null);
const route = useRoute();

const activePath = computed(() => route.path);

function isExternal(path) {
    return /^https?:\/\//.test(path);
}

function isActive(path, children = []) {
    if (isExternal(path)) {
        return false;
    }

    if (path === '/') {
        return activePath.value === '/';
    }

    if (activePath.value === path || activePath.value.startsWith(`${path}/`)) {
        return true;
    }

    return children.some((child) => !isExternal(child.path) && (activePath.value === child.path || activePath.value.startsWith(`${child.path}/`)));
}

function toggleSubmenu(index) {
    openSubmenu.value = openSubmenu.value === index ? null : index;
}
</script>

<template>
    <header class="site-header">
        <div class="site-header__inner">
            <RouterLink to="/" class="site-header__brand">
                <span class="site-header__logo">
                    <img :src="brandLogoUrl" alt="Lambang Kabupaten Langkat" class="site-header__logo-image">
                </span>
                <div>
                    <p class="site-header__eyebrow">Portal Resmi</p>
                    <p class="site-header__title">{{ siteData.identity.name }}</p>
                </div>
            </RouterLink>

            <nav class="site-header__nav" aria-label="Navigasi utama">
                <div
                    v-for="(item, index) in mainNavigation"
                    :key="`${item.label}-${item.path}`"
                    class="site-header__item"
                    @mouseleave="openSubmenu = null"
                >
                    <div class="site-header__nav-group">
                        <a
                            v-if="isExternal(item.path)"
                            :href="item.path"
                            :target="item.target ?? '_self'"
                            rel="noreferrer"
                            class="site-header__link"
                            :class="{ 'is-active': isActive(item.path, item.children ?? []) }"
                        >
                            {{ item.label }}
                        </a>
                        <RouterLink
                            v-else
                            :to="item.path"
                            class="site-header__link"
                            :class="{ 'is-active': isActive(item.path, item.children ?? []) }"
                        >
                            {{ item.label }}
                        </RouterLink>

                        <button
                            v-if="item.children?.length"
                            type="button"
                            class="site-header__submenu-trigger"
                            :class="{ 'is-active': isActive(item.path, item.children ?? []) || openSubmenu === index }"
                            :aria-expanded="openSubmenu === index ? 'true' : 'false'"
                            aria-haspopup="true"
                            @click="toggleSubmenu(index)"
                        >
                            Submenu
                        </button>
                    </div>

                    <div v-if="item.children?.length" class="site-header__submenu" :style="{ display: openSubmenu === index ? 'block' : null }">
                        <div class="site-header__submenu-panel">
                            <template v-for="child in item.children" :key="`${item.label}-${child.label}-${child.path}`">
                                <a
                                    v-if="isExternal(child.path)"
                                    :href="child.path"
                                    :target="child.target ?? '_self'"
                                    rel="noreferrer"
                                    class="site-header__submenu-link"
                                    :class="{ 'is-active': isActive(child.path) }"
                                >
                                    {{ child.label }}
                                </a>
                                <RouterLink
                                    v-else
                                    :to="child.path"
                                    class="site-header__submenu-link"
                                    :class="{ 'is-active': isActive(child.path) }"
                                >
                                    {{ child.label }}
                                </RouterLink>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="site-header__actions">
                <RouterLink to="/kontak" class="button-primary button-primary--compact">Kontak</RouterLink>
                <button
                    type="button"
                    class="site-header__menu-button"
                    @click="isOpen = !isOpen"
                >
                    Menu
                </button>
            </div>
        </div>

        <div v-if="isOpen" class="site-header__mobile">
            <div class="site-header__mobile-menu">
                <div
                    v-for="item in mainNavigation"
                    :key="`mobile-${item.label}-${item.path}`"
                    class="site-header__mobile-group"
                >
                    <a
                        v-if="isExternal(item.path)"
                        :href="item.path"
                        :target="item.target ?? '_self'"
                        rel="noreferrer"
                        class="site-header__mobile-link"
                        :class="{ 'is-active': isActive(item.path, item.children ?? []) }"
                        @click="isOpen = false"
                    >
                        {{ item.label }}
                    </a>
                    <RouterLink
                        v-else
                        :to="item.path"
                        class="site-header__mobile-link"
                        :class="{ 'is-active': isActive(item.path, item.children ?? []) }"
                        @click="isOpen = false"
                    >
                        {{ item.label }}
                    </RouterLink>

                    <div v-if="item.children?.length" class="site-header__mobile-submenu">
                        <template v-for="child in item.children" :key="`mobile-${item.label}-${child.label}-${child.path}`">
                            <a
                                v-if="isExternal(child.path)"
                                :href="child.path"
                                :target="child.target ?? '_self'"
                                rel="noreferrer"
                                class="site-header__mobile-sublink"
                                @click="isOpen = false"
                            >
                                {{ child.label }}
                            </a>
                            <RouterLink
                                v-else
                                :to="child.path"
                                class="site-header__mobile-sublink"
                                @click="isOpen = false"
                            >
                                {{ child.label }}
                            </RouterLink>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
