<script setup lang="ts">
import { PopoverRoot, type PopoverRootProps } from 'reka-ui';
import { type HTMLAttributes } from 'vue';

const props = defineProps<PopoverRootProps & { class?: HTMLAttributes['class'] }>();
</script>

<template>
    <PopoverRoot v-bind="props">
        <slot />
    </PopoverRoot>
</template>

