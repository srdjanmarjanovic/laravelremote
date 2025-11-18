<script setup lang="ts">
import { computed } from 'vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

interface Technology {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    technologies: Technology[];
    modelValue: number[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number[]];
}>();

// Convert modelValue (array of IDs) to array of technology objects
const selectedTechnologies = computed(() => {
    return props.technologies.filter(tech => props.modelValue.includes(tech.id));
});

const handleUpdate = (selectedItems: Technology[]) => {
    const selectedIds = selectedItems.map(tech => tech.id);
    emit('update:modelValue', selectedIds);
};
</script>

<template>
    <div>
        <Multiselect
            :model-value="selectedTechnologies"
            @update:model-value="handleUpdate"
            :options="technologies"
            :multiple="true"
            :close-on-select="false"
            :clear-on-select="true"
            :preserve-search="false"
            placeholder="Select technologies"
            label="name"
            track-by="id"
            :preselect-first="false"/>
    </div>
</template>



<style>
/* Custom styling to match shadcn/ui select components */
.multiselect {
    min-height: 40px;
}

.multiselect__tags {
    min-height: 40px;
    padding: 8px 40px 0 8px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    background: var(--background);
    color: var(--foreground);
    font-size: 0.875rem;
    line-height: 1.25rem;
    transition: border-color 0.2s;
}

.multiselect__tags:hover {
    border-color: var(--border);
}

.multiselect__tags:focus-within {
    outline: 2px solid var(--ring);
    outline-offset: 2px;
}

.multiselect__tag {
    background: var(--primary);
    color: var(--primary-foreground);
    border-radius: var(--radius-sm);
    padding: 4px 26px 4px 10px;
    margin-bottom: 8px;
    font-size: 0.875rem;
}

.multiselect__tag-icon:after {
    color: var(--primary-foreground);
}

.multiselect__tag-icon:hover {
    background: color-mix(in srgb, var(--primary) 80%, transparent);
}

.multiselect__option--highlight {
    background: var(--accent);
    color: var(--accent-foreground);
}

.multiselect__option--selected {
    background: var(--accent);
    color: var(--accent-foreground);
    font-weight: 500;
}

.multiselect__option--selected.multiselect__option--highlight {
    background: var(--accent);
    color: var(--accent-foreground);
}

.multiselect__input,
.multiselect__single {
    background: transparent;
    color: var(--foreground);
    font-size: 0.875rem;
}

.multiselect__content-wrapper {
    border: 1px solid var(--border);
    border-top: none;
    background: var(--popover);
    border-radius: 0 0 var(--radius-md) var(--radius-md);
}

.multiselect__content {
    background: var(--popover);
}

.multiselect__option {
    color: var(--popover-foreground);
    padding: 8px 12px;
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.multiselect__option:after {
    display: none;
}

.multiselect__placeholder {
    color: var(--muted-foreground);
    padding-top: 0;
    margin-bottom: 8px;
    font-size: 0.875rem;
}

.multiselect__select {
    height: 40px;
}

.multiselect__spinner {
    border-color: var(--border);
    border-right-color: var(--primary);
}
</style>