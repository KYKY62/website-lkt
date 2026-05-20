<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';

const props = defineProps({
    html: {
        type: String,
        default: '',
    },
});

const root = ref(null);

function executeScripts() {
    if (!root.value) {
        return;
    }

    root.value.innerHTML = props.html ?? '';

    root.value.querySelectorAll('script').forEach((scriptNode) => {
        const executableScript = document.createElement('script');

        Array.from(scriptNode.attributes).forEach((attribute) => {
            executableScript.setAttribute(attribute.name, attribute.value);
        });

        executableScript.textContent = scriptNode.textContent ?? '';
        scriptNode.replaceWith(executableScript);
    });
}

onMounted(executeScripts);

watch(
    () => props.html,
    () => nextTick(executeScripts)
);
</script>

<template>
    <div ref="root"></div>
</template>
