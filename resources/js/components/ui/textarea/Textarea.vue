<script setup lang="ts">
import { cn } from '@/lib/utils';
import { useAttrs } from 'vue';

interface Props {
    class?: string;
    modelValue?: string;
}

const props = withDefaults(defineProps<Props>(), {
    class: '',
    modelValue: '',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const attrs = useAttrs();

const handleInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <textarea
        :value="modelValue"
        :class="
            cn(
                'flex min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-ring/50 placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                props.class
            )
        "
        v-bind="attrs"
        @input="handleInput"
    />
</template>

