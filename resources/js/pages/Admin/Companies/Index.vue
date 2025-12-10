<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import { Search, X, Building2, Plus, MoreVertical, Eye, Edit, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';
import admin from '@/routes/admin';

interface Creator {
    id: number;
    name: string;
    email: string;
}

interface Company {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    created_at: string;
    positions_count: number;
    users_count: number;
    creator: Creator;
}

interface PaginatedCompanies {
    data: Company[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    companies: PaginatedCompanies;
    filters: {
        search?: string;
        sort_by?: string;
        sort_order?: string;
    };
}>();

const search = ref(props.filters.search || '');

const applyFilters = () => {
    router.get(
        admin.companies.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    search.value = '';
    router.get(admin.companies.index().url);
};

const hasActiveFilters = computed(() => {
    return search.value;
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const showDeleteDialog = ref(false);
const companyToDelete = ref<Company | null>(null);

const deleteCompany = (company: Company) => {
    companyToDelete.value = company;
    showDeleteDialog.value = true;
};

const performDelete = () => {
    if (!companyToDelete.value) {
        return;
    }

    router.delete(admin.companies.destroy({ company: companyToDelete.value.id }).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Company deleted successfully!');
            showDeleteDialog.value = false;
            companyToDelete.value = null;
        },
        onError: () => {
            toast.error('Failed to delete company');
        },
    });
};

const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: admin.dashboard().url,
    },
    {
        title: 'Companies',
        href: admin.companies.index().url,
    },
];
</script>

<template>
    <Head title="Manage Companies - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Manage Companies</h1>
                    <p class="text-muted-foreground">
                        {{ props.companies.total }} total companies on the platform
                    </p>
                </div>
                <Button @click="router.visit(admin.companies.create().url)">
                    <Plus class="mr-2 h-4 w-4" />
                    Create Company
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
                                placeholder="Search companies..."
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

            <!-- Companies Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Company</TableHead>
                                <TableHead>Creator</TableHead>
                                <TableHead>Positions</TableHead>
                                <TableHead>Team Members</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead class="w-[70px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-if="props.companies.data.length === 0"
                                class="hover:bg-transparent"
                            >
                                <TableCell colspan="6" class="py-12 text-center">
                                    <Building2 class="mx-auto mb-4 h-12 w-12 text-muted-foreground/50" />
                                    <p class="text-muted-foreground">No companies found</p>
                                    <p v-if="hasActiveFilters" class="mt-1 text-sm text-muted-foreground">
                                        Try adjusting your filters
                                    </p>
                                </TableCell>
                            </TableRow>
                            <TableRow 
                                v-for="company in props.companies.data" 
                                :key="company.id"
                            >
                                <TableCell>
                                    <div class="flex flex-col">
                                        <Link
                                            :href="admin.companies.show({ company: company.id }).url"
                                            class="font-medium text-primary hover:underline"
                                        >
                                            {{ company.name }}
                                        </Link>
                                        <span v-if="company.description" class="text-sm text-muted-foreground line-clamp-1">
                                            {{ company.description }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="text-sm">{{ company.creator.name }}</span>
                                        <span class="text-xs text-muted-foreground">{{ company.creator.email }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ company.positions_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ company.users_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-muted-foreground">
                                        {{ formatDate(company.created_at) }}
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
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="admin.companies.show({ company: company.id }).url"
                                                    class="flex items-center"
                                                >
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    View
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="admin.companies.edit({ company: company.id }).url"
                                                    class="flex items-center"
                                                >
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                class="text-destructive focus:text-destructive"
                                                @click="deleteCompany(company)"
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
                        v-if="props.companies.last_page > 1"
                        class="flex items-center justify-between border-t px-4 py-3"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Button
                                v-if="props.companies.current_page > 1"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.companies.links[0].url!)"
                            >
                                Previous
                            </Button>
                            <Button
                                v-if="props.companies.current_page < props.companies.last_page"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.companies.links[props.companies.links.length - 1].url!)"
                            >
                                Next
                            </Button>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Showing
                                    <span class="font-medium">
                                        {{ (props.companies.current_page - 1) * props.companies.per_page + 1 }}
                                    </span>
                                    to
                                    <span class="font-medium">
                                        {{ Math.min(props.companies.current_page * props.companies.per_page, props.companies.total) }}
                                    </span>
                                    of
                                    <span class="font-medium">{{ props.companies.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="inline-flex -space-x-px rounded-md shadow-sm">
                                    <Button
                                        v-for="(link, index) in props.companies.links"
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

            <!-- Delete Confirmation Dialog -->
            <AlertDialog v-model:open="showDeleteDialog">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Company?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete "{{ companyToDelete?.name }}"? This action cannot be undone and will delete all associated positions and data.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            class="bg-destructive hover:bg-destructive/90"
                            @click.prevent="performDelete"
                        >
                            Delete Company
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </AppLayout>
</template>

