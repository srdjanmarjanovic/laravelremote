<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table/index';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
import {
    MoreVertical,
    Eye,
    Archive,
    ExternalLink,
    Star,
    StarOff,
    Trash2,
    ChevronDown,
    Search,
    X,
    Building2,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import admin from '@/routes/admin';
import positions from '@/routes/positions';
import { toast } from 'vue-sonner';

interface Technology {
    id: number;
    name: string;
}

interface Company {
    id: number;
    name: string;
    slug: string;
}

interface Creator {
    id: number;
    name: string;
    email: string;
}

interface Position {
    id: number;
    title: string;
    slug: string;
    company: Company;
    creator: Creator;
    status: 'draft' | 'published' | 'expired' | 'archived';
    seniority: string | null;
    remote_type: string;
    listing_type: string;
    applications_count: number;
    published_at: string | null;
    expires_at: string | null;
    created_at: string;
    technologies: Technology[];
}

interface PaginatedPositions {
    data: Position[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    positions: PaginatedPositions;
    filters: {
        search?: string;
        status?: string;
        company_id?: number;
        sort_by?: string;
        sort_order?: string;
    };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

// Selection state
const selectedPositions = ref<number[]>([]);
const selectAll = ref(false);

// Bulk action dialog
const showBulkDialog = ref(false);
const bulkAction = ref<'feature' | 'unfeature' | 'archive' | 'delete' | null>(null);
const isProcessing = ref(false);

const allSelected = computed(() => {
    return props.positions.data.length > 0 && 
           selectedPositions.value.length === props.positions.data.length;
});

const someSelected = computed(() => {
    return selectedPositions.value.length > 0 && 
           selectedPositions.value.length < props.positions.data.length;
});

watch(selectAll, (value) => {
    if (value) {
        selectedPositions.value = props.positions.data.map(p => p.id);
    } else if (allSelected.value) {
        selectedPositions.value = [];
    }
});

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedPositions.value = [];
        selectAll.value = false;
    } else {
        selectedPositions.value = props.positions.data.map(p => p.id);
        selectAll.value = true;
    }
};

const togglePosition = (id: number) => {
    const index = selectedPositions.value.indexOf(id);
    if (index > -1) {
        selectedPositions.value.splice(index, 1);
    } else {
        selectedPositions.value.push(id);
    }
};

const applyFilters = () => {
    router.get(
        admin.positions.index().url,
        {
            search: search.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = 'all';
    router.get(admin.positions.index().url);
};

const hasActiveFilters = computed(() => {
    return search.value || statusFilter.value !== 'all';
});

// Single position actions
const featurePosition = (position: Position) => {
    router.post(admin.positions.feature(position.id).url, {}, {
        preserveScroll: true,
        onSuccess: () => {
            const isFeatured = position.listing_type === 'featured';
            toast.success(isFeatured ? 'Position unfeatured successfully!' : 'Position featured successfully!');
        },
        onError: () => {
            toast.error('Failed to update position');
        },
    });
};

const archivePosition = (position: Position) => {
    if (confirm(`Are you sure you want to archive "${position.title}"?`)) {
        router.post(admin.positions.archive(position.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Position archived successfully!');
            },
            onError: () => {
                toast.error('Failed to archive position');
            },
        });
    }
};

// Bulk actions
const openBulkDialog = (action: 'feature' | 'unfeature' | 'archive' | 'delete') => {
    bulkAction.value = action;
    showBulkDialog.value = true;
};

const executeBulkAction = () => {
    if (!bulkAction.value || selectedPositions.value.length === 0) return;

    isProcessing.value = true;
    router.post(admin.positions.bulkAction().url, {
        action: bulkAction.value,
        position_ids: selectedPositions.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Successfully ${bulkAction.value}d ${selectedPositions.value.length} position(s)`);
            selectedPositions.value = [];
            selectAll.value = false;
            showBulkDialog.value = false;
        },
        onError: () => {
            toast.error(`Failed to ${bulkAction.value} positions`);
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const getBulkActionTitle = () => {
    const titles: Record<string, string> = {
        feature: 'Feature Positions',
        unfeature: 'Unfeature Positions',
        archive: 'Archive Positions',
        delete: 'Delete Positions',
    };
    return titles[bulkAction.value || ''] || 'Confirm Action';
};

const getBulkActionDescription = () => {
    const count = selectedPositions.value.length;
    const descriptions: Record<string, string> = {
        feature: `Are you sure you want to feature ${count} position(s)? Featured positions will be displayed prominently.`,
        unfeature: `Are you sure you want to unfeature ${count} position(s)? They will return to regular listings.`,
        archive: `Are you sure you want to archive ${count} position(s)? Archived positions will no longer be visible to candidates.`,
        delete: `Are you sure you want to permanently delete ${count} position(s)? This action cannot be undone.`,
    };
    return descriptions[bulkAction.value || ''] || '';
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        published: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        expired: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        archived: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return colors[status] || colors.draft;
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: admin.dashboard().url,
    },
    {
        title: 'Positions',
        href: admin.positions.index().url,
    },
];
</script>

<template>
    <Head title="Manage Positions - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Manage Positions</h1>
                    <p class="text-muted-foreground">
                        {{ props.positions.total }} total positions on the platform
                    </p>
                </div>
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
                                placeholder="Search positions..."
                                class="pl-9"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <Select v-model="statusFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-full sm:w-[180px]">
                                <SelectValue placeholder="All Statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Statuses</SelectItem>
                                <SelectItem value="draft">Draft</SelectItem>
                                <SelectItem value="published">Published</SelectItem>
                                <SelectItem value="expired">Expired</SelectItem>
                                <SelectItem value="archived">Archived</SelectItem>
                            </SelectContent>
                        </Select>

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

            <!-- Bulk Actions Bar -->
            <div 
                v-if="selectedPositions.length > 0" 
                class="flex items-center justify-between rounded-lg border bg-muted/50 px-4 py-3"
            >
                <span class="text-sm font-medium">
                    {{ selectedPositions.length }} position(s) selected
                </span>
                <div class="flex items-center gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                Bulk Actions
                                <ChevronDown class="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="openBulkDialog('feature')">
                                <Star class="mr-2 h-4 w-4" />
                                Feature Selected
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="openBulkDialog('unfeature')">
                                <StarOff class="mr-2 h-4 w-4" />
                                Unfeature Selected
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="openBulkDialog('archive')">
                                <Archive class="mr-2 h-4 w-4" />
                                Archive Selected
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem 
                                class="text-destructive focus:text-destructive" 
                                @click="openBulkDialog('delete')"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete Selected
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="selectedPositions = []; selectAll = false"
                    >
                        Clear Selection
                    </Button>
                </div>
            </div>

            <!-- Positions Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-12">
                                    <Checkbox 
                                        :checked="allSelected" 
                                        :indeterminate="someSelected"
                                        @update:checked="toggleSelectAll" 
                                    />
                                </TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead>Company</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Creator</TableHead>
                                <TableHead class="text-center">Applications</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-if="props.positions.data.length === 0"
                                class="hover:bg-transparent"
                            >
                                <TableCell colspan="8" class="py-12 text-center">
                                    <Building2 class="mx-auto mb-4 h-12 w-12 text-muted-foreground/50" />
                                    <p class="text-muted-foreground">No positions found</p>
                                    <p v-if="hasActiveFilters" class="mt-1 text-sm text-muted-foreground">
                                        Try adjusting your filters
                                    </p>
                                </TableCell>
                            </TableRow>
                            <TableRow 
                                v-for="position in props.positions.data" 
                                :key="position.id"
                                class="group"
                            >
                                <TableCell>
                                    <Checkbox 
                                        :checked="selectedPositions.includes(position.id)"
                                        @update:checked="togglePosition(position.id)" 
                                    />
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ position.title }}</span>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            <Badge
                                                v-for="tech in position.technologies.slice(0, 3)"
                                                :key="tech.id"
                                                variant="outline"
                                                class="text-xs"
                                            >
                                                {{ tech.name }}
                                            </Badge>
                                            <Badge
                                                v-if="position.technologies.length > 3"
                                                variant="outline"
                                                class="text-xs"
                                            >
                                                +{{ position.technologies.length - 3 }}
                                            </Badge>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ position.company.name }}</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-col gap-1">
                                        <Badge :class="getStatusColor(position.status)">
                                            {{ position.status }}
                                        </Badge>
                                        <Badge
                                            v-if="position.listing_type === 'top'"
                                            variant="outline"
                                            class="border-amber-500 text-amber-600"
                                        >
                                            ⭐ Top
                                        </Badge>
                                        <Badge
                                            v-else-if="position.listing_type === 'featured'"
                                            variant="outline"
                                            class="border-amber-500 text-amber-600"
                                        >
                                            <Star class="mr-1 h-3 w-3" />
                                            Featured
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="text-sm">{{ position.creator.name }}</span>
                                        <span class="text-xs text-muted-foreground">{{ position.creator.email }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge variant="secondary">
                                        {{ position.applications_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-muted-foreground">
                                        {{ formatDate(position.created_at) }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button 
                                                variant="ghost" 
                                                size="sm"
                                                class="opacity-0 group-hover:opacity-100"
                                            >
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem 
                                                v-if="position.status === 'published'"
                                                as-child
                                            >
                                                <a
                                                    :href="positions.show(position.slug).url"
                                                    target="_blank"
                                                    class="flex cursor-pointer items-center"
                                                >
                                                    <ExternalLink class="mr-2 h-4 w-4" />
                                                    View Public Page
                                                </a>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem 
                                                v-else
                                                as-child
                                            >
                                                <a
                                                    :href="positions.show(position.slug).url"
                                                    target="_blank"
                                                    class="flex cursor-pointer items-center"
                                                >
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    Preview
                                                </a>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem @click="featurePosition(position)">
                                                <template v-if="position.listing_type === 'featured'">
                                                    <StarOff class="mr-2 h-4 w-4" />
                                                    Unfeature
                                                </template>
                                                <template v-else>
                                                    <Star class="mr-2 h-4 w-4" />
                                                    Feature
                                                </template>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="position.status !== 'archived'"
                                                @click="archivePosition(position)"
                                            >
                                                <Archive class="mr-2 h-4 w-4" />
                                                Archive
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="props.positions.last_page > 1"
                        class="flex items-center justify-between border-t px-4 py-3"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Button
                                v-if="props.positions.current_page > 1"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.positions.links[0].url!)"
                            >
                                Previous
                            </Button>
                            <Button
                                v-if="props.positions.current_page < props.positions.last_page"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.positions.links[props.positions.links.length - 1].url!)"
                            >
                                Next
                            </Button>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Showing
                                    <span class="font-medium">
                                        {{ (props.positions.current_page - 1) * props.positions.per_page + 1 }}
                                    </span>
                                    to
                                    <span class="font-medium">
                                        {{ Math.min(props.positions.current_page * props.positions.per_page, props.positions.total) }}
                                    </span>
                                    of
                                    <span class="font-medium">{{ props.positions.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="inline-flex -space-x-px rounded-md shadow-sm">
                                    <Button
                                        v-for="(link, index) in props.positions.links"
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
        </div>

        <!-- Bulk Action Confirmation Dialog -->
        <AlertDialog v-model:open="showBulkDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ getBulkActionTitle() }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ getBulkActionDescription() }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel :disabled="isProcessing">Cancel</AlertDialogCancel>
                    <AlertDialogAction 
                        :disabled="isProcessing"
                        :class="{ 'bg-destructive hover:bg-destructive/90': bulkAction === 'delete' }"
                        @click.prevent="executeBulkAction"
                    >
                        {{ isProcessing ? 'Processing...' : 'Confirm' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>

