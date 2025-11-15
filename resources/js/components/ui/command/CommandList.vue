<script setup lang="ts">
import { ComboboxContent, ComboboxViewport } from 'reka-ui';
import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <ComboboxContent>
        <ComboboxViewport
            :class="cn('max-h-[300px] overflow-y-auto overflow-x-hidden', props.class)"
        >
            <slot />
        </ComboboxViewport>
    </ComboboxContent>
</template>

