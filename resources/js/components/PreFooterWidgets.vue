<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { preFooterWidgets } from '../siteData';
import RawHtmlWidget from './RawHtmlWidget.vue';

const route = useRoute();
const emptyColumns = { left: [], right: [] };

const normalizedPath = computed(() => {
    if (route.path === '/') {
        return '/';
    }

    return route.path.replace(/\/+$/, '') || '/';
});

const columns = computed(() => preFooterWidgets[normalizedPath.value] ?? emptyColumns);
const hasWidgets = computed(() => [...(columns.value.left ?? []), ...(columns.value.right ?? [])].length > 0);

function isInternalLink(widget) {
    return widget.link_url?.startsWith('/') && widget.link_target !== '_blank';
}
</script>

<template>
    <section v-if="hasWidgets" class="pre-footer-widgets" aria-label="Widget tambahan halaman">
        <div class="page-container pre-footer-widgets__grid">
            <div
                v-for="columnName in ['left', 'right']"
                :key="columnName"
                class="pre-footer-widgets__column"
                :class="`pre-footer-widgets__column--${columnName}`"
            >
                <article
                    v-for="widget in columns[columnName]"
                    :key="widget.id"
                    class="pre-footer-widget"
                    :class="`pre-footer-widget--${widget.type}`"
                >
                    <template v-if="widget.type === 'static_image'">
                        <img
                            class="pre-footer-widget__image"
                            :src="widget.image_url"
                            :alt="widget.image_alt || widget.title"
                            loading="lazy"
                        >
                    </template>

                    <template v-else-if="widget.type === 'link_banner'">
                        <RouterLink v-if="isInternalLink(widget)" :to="widget.link_url" class="pre-footer-widget__banner">
                            <img
                                class="pre-footer-widget__image"
                                :src="widget.image_url"
                                :alt="widget.image_alt || widget.title"
                                loading="lazy"
                            >
                        </RouterLink>
                        <a
                            v-else
                            class="pre-footer-widget__banner"
                            :href="widget.link_url"
                            :target="widget.link_target || '_self'"
                            :rel="widget.link_target === '_blank' ? 'noopener noreferrer' : null"
                        >
                            <img
                                class="pre-footer-widget__image"
                                :src="widget.image_url"
                                :alt="widget.image_alt || widget.title"
                                loading="lazy"
                            >
                        </a>
                    </template>

                    <RawHtmlWidget
                        v-else-if="widget.type === 'html'"
                        class="pre-footer-widget__html content-body"
                        :html="widget.html_content"
                    />

                    <div v-else-if="widget.type === 'embed'" class="pre-footer-widget__embed">
                        <iframe
                            :src="widget.embed_url"
                            :title="widget.title"
                            loading="lazy"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                        />
                    </div>

                    <div v-else-if="widget.type === 'text_cta'" class="pre-footer-widget__text">
                        <p class="pre-footer-widget__eyebrow">Informasi Terkait</p>
                        <h2>{{ widget.title }}</h2>
                        <p>{{ widget.text_body }}</p>
                        <RouterLink v-if="isInternalLink(widget)" :to="widget.link_url" class="button button--primary">
                            {{ widget.cta_label }}
                        </RouterLink>
                        <a
                            v-else
                            class="button button--primary"
                            :href="widget.link_url"
                            :target="widget.link_target || '_self'"
                            :rel="widget.link_target === '_blank' ? 'noopener noreferrer' : null"
                        >
                            {{ widget.cta_label }}
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
