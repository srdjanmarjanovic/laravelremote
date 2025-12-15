<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';
import admin from '@/routes/admin';

interface Technology {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    open: boolean;
    technologies: Technology[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'technology-created', technology: Technology): void;
}>();

const form = useForm({
    name: '',
    icon: '',
});

const handleClose = () => {
    if (!form.processing) {
        emit('update:open', false);
        form.reset();
        form.clearErrors();
    }
};

const submit = () => {
    const technologyName = form.name;
    form.post(admin.technologies.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Technology created successfully!');
            handleClose();
            // Reload the page to get updated technologies list
            router.reload({
                only: ['technologies'],
                onSuccess: (page) => {
                    // Find the newly created technology in the updated technologies list
                    const updatedTechnologies = page.props.technologies as Technology[];
                    const newTech = updatedTechnologies.find((t) => t.name === technologyName);
                    if (newTech) {
                        emit('technology-created', newTech);
                    }
                },
            });
        },
        onError: () => {
            toast.error('There was an error creating the technology. Please check the form.');
        },
    });
};

// Reset form when modal closes
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        form.reset();
        form.clearErrors();
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Add New Technology</DialogTitle>
                <DialogDescription>
                    Create a new technology that can be used in position requirements.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">
                        Technology Name <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="e.g., Laravel, React, Vue.js"
                        :class="{ 'border-red-500': form.errors.name }"
                        autofocus
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="icon">Icon (Optional)</Label>
                    <Input
                        id="icon"
                        v-model="form.icon"
                        placeholder="e.g., laravel, react, vue"
                        :class="{ 'border-red-500': form.errors.icon }"
                    />
                    <p v-if="form.errors.icon" class="text-sm text-red-500">
                        {{ form.errors.icon }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Icon identifier for the technology (optional)
                    </p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose" :disabled="form.processing">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Technology' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
