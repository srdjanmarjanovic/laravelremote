<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const handleClick = (e: MouseEvent) => {
    e.preventDefault();
    window.location.href = home().url;
};
</script>

<template>
    <a :href="home().url" @click="handleClick" class="flex items-center gap-x-2">
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
        >
            <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
        </div>
        <div class="ml-1 grid flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold"
                >Back to public page</span
            >
        </div>
    </a>
</template>
