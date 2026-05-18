<template>
    <article class="public-empty-state">
        <p class="eyebrow">{{ eyebrow }}</p>
        <h2>{{ title }}</h2>
        <p>{{ description }}</p>
    </article>
</template>

<script setup>
defineProps({
    eyebrow: {
        type: String,
        default: 'Konten',
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
});
</script>
