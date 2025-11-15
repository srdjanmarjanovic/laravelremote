<script setup lang="ts">
import { ComboboxItem, type ComboboxItemProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'vue';

const props = defineProps<
    ComboboxItemProps & {
        class?: HTMLAttributes['class'];
    }
>();
</script>

<template>
    <ComboboxItem
        v-bind="props"
        :class="
            cn(
                'relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-hidden data-[disabled=true]:pointer-events-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[disabled=true]:opacity-50',
                props.class
            )
        "
    >
        <slot />
    </ComboboxItem>
</template>

