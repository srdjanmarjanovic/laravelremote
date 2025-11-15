<script setup lang="ts">
import { ComboboxRoot, type ComboboxRootProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'vue';

const props = withDefaults(
    defineProps<
        ComboboxRootProps & {
            class?: HTMLAttributes['class'];
        }
    >(),
    {}
);
</script>

<template>
    <ComboboxRoot
        v-bind="props"
        :class="
            cn(
                'flex h-full w-full flex-col overflow-hidden rounded-md bg-popover text-popover-foreground',
                props.class
            )
        "
        :open="true"
        :model-value="undefined"
    >
        <slot />
    </ComboboxRoot>
</template>

