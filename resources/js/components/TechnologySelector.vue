<script setup lang="ts">
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { X } from 'lucide-vue-next';

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

const open = ref(false);
const searchQuery = ref('');

const selectedTechnologies = ref<Technology[]>(
    props.technologies.filter((tech) => props.modelValue.includes(tech.id))
);

watch(
    () => props.modelValue,
    (newValue) => {
        selectedTechnologies.value = props.technologies.filter((tech) =>
            newValue.includes(tech.id)
        );
    }
);

const filteredTechnologies = ref<Technology[]>([]);

watch(
    () => searchQuery.value,
    (query) => {
        if (!query) {
            filteredTechnologies.value = props.technologies;
            return;
        }
        filteredTechnologies.value = props.technologies.filter((tech) =>
            tech.name.toLowerCase().includes(query.toLowerCase())
        );
    },
    { immediate: true }
);

const toggleTechnology = (technology: Technology) => {
    const currentIds = [...props.modelValue];
    const index = currentIds.indexOf(technology.id);

    if (index > -1) {
        currentIds.splice(index, 1);
    } else {
        currentIds.push(technology.id);
    }

    emit('update:modelValue', currentIds);
    open.value = false;
};

const removeTechnology = (technologyId: number) => {
    emit(
        'update:modelValue',
        props.modelValue.filter((id) => id !== technologyId)
    );
};

const isSelected = (technologyId: number) => {
    return props.modelValue.includes(technologyId);
};
</script>

<template>
    <div>
        <div v-if="selectedTechnologies.length > 0" class="mb-3 flex flex-wrap gap-2">
            <Badge
                v-for="tech in selectedTechnologies"
                :key="tech.id"
                variant="secondary"
                class="flex items-center gap-1"
            >
                {{ tech.name }}
                <button
                    type="button"
                    @click.stop="removeTechnology(tech.id)"
                    class="ml-1 rounded-full hover:bg-gray-300 dark:hover:bg-gray-600"
                >
                    <X class="h-3 w-3" />
                </button>
            </Badge>
        </div>

        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button variant="outline" class="w-full justify-start">
                    {{
                        selectedTechnologies.length > 0
                            ? `${selectedTechnologies.length} selected`
                            : 'Select technologies...'
                    }}
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-[400px] p-0" align="start">
                <Command>
                    <CommandInput v-model="searchQuery" placeholder="Search technologies..." />
                    <CommandList>
                        <CommandEmpty>No technologies found.</CommandEmpty>
                        <CommandGroup>
                            <CommandItem
                                v-for="technology in filteredTechnologies"
                                :key="technology.id"
                                :value="technology.name"
                                @select="toggleTechnology(technology)"
                                class="cursor-pointer"
                            >
                                <div
                                    :class="[
                                        'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                                        isSelected(technology.id)
                                            ? 'bg-primary text-primary-foreground'
                                            : 'opacity-50 [&_svg]:invisible',
                                    ]"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="h-4 w-4"
                                    >
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </div>
                                <span>{{ technology.name }}</span>
                            </CommandItem>
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    </div>
</template>

