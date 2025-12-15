<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table/index';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Search, X, Code, Plus, MoreVertical, Edit, Trash2, ArrowUpDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import admin from '@/routes/admin';

interface Technology {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    positions_count: number;
    created_at: string;
}

interface PaginatedTechnologies {
    data: Technology[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    technologies: PaginatedTechnologies;
    filters: {
        search?: string;
        sort_by?: string;
        sort_order?: string;
    };
}>();

const search = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort_by || 'name');
const sortOrder = ref(props.filters.sort_order || 'asc');

const applyFilters = () => {
    router.get(
        admin.technologies.index().url,
        {
            search: search.value || undefined,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    search.value = '';
    sortBy.value = 'name';
    sortOrder.value = 'asc';
    router.get(admin.technologies.index().url);
};

const toggleSort = (field: string) => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = 'asc';
    }
    applyFilters();
};

const hasActiveFilters = computed(() => {
    return search.value || sortBy.value !== 'name' || sortOrder.value !== 'asc';
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Add Technology Modal
const showAddModal = ref(false);
const addForm = useForm({
    name: '',
    icon: '',
});

const openAddModal = () => {
    showAddModal.value = true;
    addForm.reset();
    addForm.clearErrors();
};

const submitAdd = () => {
    addForm.post(admin.technologies.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Technology created successfully!');
            showAddModal.value = false;
            addForm.reset();
            router.reload({ only: ['technologies'] });
        },
        onError: () => {
            toast.error('There was an error creating the technology. Please check the form.');
        },
    });
};

// Edit Technology Modal
const showEditModal = ref(false);
const technologyToEdit = ref<Technology | null>(null);
const editForm = useForm({
    name: '',
    icon: '',
});

const openEditModal = (technology: Technology) => {
    technologyToEdit.value = technology;
    editForm.name = technology.name;
    editForm.icon = technology.icon || '';
    showEditModal.value = true;
    editForm.clearErrors();
};

const submitEdit = () => {
    if (!technologyToEdit.value) {
        return;
    }

    editForm.put(admin.technologies.update({ technology: technologyToEdit.value.id }).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Technology updated successfully!');
            showEditModal.value = false;
            technologyToEdit.value = null;
            editForm.reset();
            router.reload({ only: ['technologies'] });
        },
        onError: () => {
            toast.error('There was an error updating the technology. Please check the form.');
        },
    });
};

// Delete Technology
const showDeleteDialog = ref(false);
const technologyToDelete = ref<Technology | null>(null);

const deleteTechnology = (technology: Technology) => {
    technologyToDelete.value = technology;
    showDeleteDialog.value = true;
};

const performDelete = () => {
    if (!technologyToDelete.value) {
        return;
    }

    router.delete(admin.technologies.destroy({ technology: technologyToDelete.value.id }).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Technology deleted successfully!');
            showDeleteDialog.value = false;
            technologyToDelete.value = null;
            router.reload({ only: ['technologies'] });
        },
        onError: (errors) => {
            toast.error(errors.technology || 'Failed to delete technology');
            showDeleteDialog.value = false;
        },
    });
};

const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: admin.dashboard().url,
    },
    {
        title: 'Technologies',
        href: admin.technologies.index().url,
    },
];
</script>

<template>
    <Head title="Manage Technologies - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Manage Technologies</h1>
                    <p class="text-muted-foreground">
                        {{ props.technologies.total }} total technologies on the platform
                    </p>
                </div>
                <Button @click="openAddModal">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Technology
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search technologies..."
                                class="pl-9"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <Button @click="applyFilters">
                            <Search class="mr-2 h-4 w-4" />
                            Search
                        </Button>

                        <Button 
                            v-if="hasActiveFilters" 
                            variant="ghost" 
                            @click="clearFilters"
                        >
                            <X class="mr-2 h-4 w-4" />
                            Clear
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Technologies Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button
                                        class="flex items-center gap-2 hover:text-foreground"
                                        @click="toggleSort('name')"
                                    >
                                        Name
                                        <ArrowUpDown class="h-4 w-4" />
                                    </button>
                                </TableHead>
                                <TableHead>Slug</TableHead>
                                <TableHead>Icon</TableHead>
                                <TableHead>
                                    <button
                                        class="flex items-center gap-2 hover:text-foreground"
                                        @click="toggleSort('positions_count')"
                                    >
                                        Positions
                                        <ArrowUpDown class="h-4 w-4" />
                                    </button>
                                </TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead class="w-[70px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-if="props.technologies.data.length === 0"
                                class="hover:bg-transparent"
                            >
                                <TableCell colspan="6" class="py-12 text-center">
                                    <Code class="mx-auto mb-4 h-12 w-12 text-muted-foreground/50" />
                                    <p class="text-muted-foreground">No technologies found</p>
                                    <p v-if="hasActiveFilters" class="mt-1 text-sm text-muted-foreground">
                                        Try adjusting your filters
                                    </p>
                                </TableCell>
                            </TableRow>
                            <TableRow 
                                v-for="technology in props.technologies.data" 
                                :key="technology.id"
                            >
                                <TableCell>
                                    <div class="font-medium">{{ technology.name }}</div>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-muted-foreground font-mono">
                                        {{ technology.slug }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <span v-if="technology.icon" class="text-sm text-muted-foreground">
                                        {{ technology.icon }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground/50">—</span>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ technology.positions_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-muted-foreground">
                                        {{ formatDate(technology.created_at) }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="openEditModal(technology)">
                                                <Edit class="mr-2 h-4 w-4" />
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                class="text-destructive focus:text-destructive"
                                                @click="deleteTechnology(technology)"
                                            >
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="props.technologies.last_page > 1"
                        class="flex items-center justify-between border-t px-4 py-3"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Button
                                v-if="props.technologies.current_page > 1"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.technologies.links[0].url!)"
                            >
                                Previous
                            </Button>
                            <Button
                                v-if="props.technologies.current_page < props.technologies.last_page"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.technologies.links[props.technologies.links.length - 1].url!)"
                            >
                                Next
                            </Button>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Showing
                                    <span class="font-medium">
                                        {{ (props.technologies.current_page - 1) * props.technologies.per_page + 1 }}
                                    </span>
                                    to
                                    <span class="font-medium">
                                        {{ Math.min(props.technologies.current_page * props.technologies.per_page, props.technologies.total) }}
                                    </span>
                                    of
                                    <span class="font-medium">{{ props.technologies.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="inline-flex -space-x-px rounded-md shadow-sm">
                                    <Button
                                        v-for="(link, index) in props.technologies.links"
                                        :key="index"
                                        :variant="link.active ? 'default' : 'outline'"
                                        :disabled="!link.url"
                                        size="sm"
                                        class="rounded-none first:rounded-l-md last:rounded-r-md"
                                        @click="link.url ? router.get(link.url) : null"
                                    >
                                        <span v-html="link.label" />
                                    </Button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Add Technology Modal -->
            <Dialog v-model:open="showAddModal">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Add New Technology</DialogTitle>
                        <DialogDescription>
                            Create a new technology that can be used in position requirements.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitAdd" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="add-name">
                                Technology Name <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="add-name"
                                v-model="addForm.name"
                                placeholder="e.g., Laravel, React, Vue.js"
                                :class="{ 'border-red-500': addForm.errors.name }"
                                autofocus
                            />
                            <p v-if="addForm.errors.name" class="text-sm text-red-500">
                                {{ addForm.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="add-icon">Icon (Optional)</Label>
                            <Input
                                id="add-icon"
                                v-model="addForm.icon"
                                placeholder="e.g., laravel, react, vue"
                                :class="{ 'border-red-500': addForm.errors.icon }"
                            />
                            <p v-if="addForm.errors.icon" class="text-sm text-red-500">
                                {{ addForm.errors.icon }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Icon identifier for the technology (optional)
                            </p>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showAddModal = false" :disabled="addForm.processing">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="addForm.processing">
                                {{ addForm.processing ? 'Creating...' : 'Create Technology' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Edit Technology Modal -->
            <Dialog v-model:open="showEditModal">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Edit Technology</DialogTitle>
                        <DialogDescription>
                            Update the technology information.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="edit-name">
                                Technology Name <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="edit-name"
                                v-model="editForm.name"
                                placeholder="e.g., Laravel, React, Vue.js"
                                :class="{ 'border-red-500': editForm.errors.name }"
                                autofocus
                            />
                            <p v-if="editForm.errors.name" class="text-sm text-red-500">
                                {{ editForm.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="edit-icon">Icon (Optional)</Label>
                            <Input
                                id="edit-icon"
                                v-model="editForm.icon"
                                placeholder="e.g., laravel, react, vue"
                                :class="{ 'border-red-500': editForm.errors.icon }"
                            />
                            <p v-if="editForm.errors.icon" class="text-sm text-red-500">
                                {{ editForm.errors.icon }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Icon identifier for the technology (optional)
                            </p>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showEditModal = false" :disabled="editForm.processing">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="editForm.processing">
                                {{ editForm.processing ? 'Updating...' : 'Update Technology' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Confirmation Dialog -->
            <AlertDialog v-model:open="showDeleteDialog">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Technology?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete "{{ technologyToDelete?.name }}"? 
                            <span v-if="technologyToDelete && technologyToDelete.positions_count > 0" class="font-medium text-destructive">
                                This technology is used by {{ technologyToDelete.positions_count }} position(s) and cannot be deleted.
                            </span>
                            <span v-else>
                                This action cannot be undone.
                            </span>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            v-if="!technologyToDelete || technologyToDelete.positions_count === 0"
                            class="bg-destructive hover:bg-destructive/90"
                            @click.prevent="performDelete"
                        >
                            Delete Technology
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </AppLayout>
</template>
