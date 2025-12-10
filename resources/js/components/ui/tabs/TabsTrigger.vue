<script setup lang="ts">
import { type HTMLAttributes, computed } from 'vue';
import { TabsTrigger, type TabsTriggerProps } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<TabsTriggerProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});
</script>

<template>
    <TabsTrigger
        v-bind="delegatedProps"
        :class="
            cn(
                'inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium ring-offset-white transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-accent data-[state=active]:text-accent-foreground data-[state=active]:shadow dark:ring-offset-gray-950 dark:focus-visible:ring-gray-300',
                props.class
            )
        "
    >
        <slot />
    </TabsTrigger>
</template>

