<script setup lang="ts">
import { PopoverTrigger, type PopoverTriggerProps } from 'reka-ui';
import { type HTMLAttributes } from 'vue';

const props = defineProps<PopoverTriggerProps & { class?: HTMLAttributes['class'] }>();
</script>

<template>
    <PopoverTrigger v-bind="props">
        <slot />
    </PopoverTrigger>
</template>

