<script setup lang="ts">
import { ComboboxGroup, type ComboboxGroupProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'vue';

const props = defineProps<
    ComboboxGroupProps & {
        class?: HTMLAttributes['class'];
        heading?: string;
    }
>();
</script>

<template>
    <ComboboxGroup
        v-bind="props"
        :class="
            cn(
                'overflow-hidden p-1 text-foreground [&_[cmdk-group-heading]]:px-2 [&_[cmdk-group-heading]]:py-1.5 [&_[cmdk-group-heading]]:text-xs [&_[cmdk-group-heading]]:font-medium [&_[cmdk-group-heading]]:text-muted-foreground',
                props.class
            )
        "
    >
        <div v-if="heading" cmdk-group-heading="">
            {{ heading }}
        </div>
        <slot />
    </ComboboxGroup>
</template>

