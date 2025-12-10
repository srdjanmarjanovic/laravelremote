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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Search, X, FileText, ArchiveX, Eye } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import admin from '@/routes/admin';
import positions from '@/routes/positions';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Position {
    id: number;
    title: string;
    slug: string;
}

interface Company {
    id: number;
    name: string;
}

interface Application {
    id: number;
    position: Position & { company: Company };
    user: User;
    status: 'pending' | 'reviewing' | 'accepted' | 'rejected';
    applied_at: string;
    user_archived: boolean;
}

interface PaginatedApplications {
    data: Application[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    applications: PaginatedApplications;
    filters: {
        search?: string;
        status?: string;
        position_id?: number;
        company_id?: number;
        sort_by?: string;
        sort_order?: string;
    };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const applyFilters = () => {
    router.get(
        admin.applications.index().url,
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
    router.get(admin.applications.index().url);
};

const hasActiveFilters = computed(() => {
    return search.value || statusFilter.value !== 'all';
});

const getStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        reviewing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        accepted: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return colors[status as keyof typeof colors] || colors.pending;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: admin.dashboard().url,
    },
    {
        title: 'Applications',
        href: admin.applications.index().url,
    },
];
</script>

<template>
    <Head title="Manage Applications - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Manage Applications</h1>
                    <p class="text-muted-foreground">
                        {{ props.applications.total }} total applications on the platform
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
                                placeholder="Search applications..."
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
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="reviewing">Reviewing</SelectItem>
                                <SelectItem value="accepted">Accepted</SelectItem>
                                <SelectItem value="rejected">Rejected</SelectItem>
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

            <!-- Applications Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Applicant</TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead>Company</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Applied Date</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-if="props.applications.data.length === 0"
                                class="hover:bg-transparent"
                            >
                                <TableCell colspan="6" class="py-12 text-center">
                                    <FileText class="mx-auto mb-4 h-12 w-12 text-muted-foreground/50" />
                                    <p class="text-muted-foreground">No applications found</p>
                                    <p v-if="hasActiveFilters" class="mt-1 text-sm text-muted-foreground">
                                        Try adjusting your filters
                                    </p>
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-for="application in props.applications.data"
                                :key="application.id"
                                :class="{
                                    'cursor-not-allowed opacity-60': application.user_archived
                                }"
                            >
                                <TableCell>
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">
                                                {{ application.user.name }}
                                            </span>
                                            <Badge
                                                v-if="application.user_archived"
                                                variant="secondary"
                                            >
                                                <ArchiveX class="mr-1 h-3 w-3" />
                                                Archived
                                            </Badge>
                                        </div>
                                        <span class="text-sm text-muted-foreground">
                                            {{ application.user.email }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Link
                                        :href="positions.show(application.position.slug).url"
                                        target="_blank"
                                        class="font-medium hover:underline"
                                    >
                                        {{ application.position.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ application.position.company.name }}</span>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusColor(application.status)">
                                        {{ application.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-muted-foreground">
                                        {{ formatDate(application.applied_at) }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Link
                                        :href="positions.show(application.position.slug).url"
                                        target="_blank"
                                    >
                                        <Button variant="ghost" size="sm">
                                            <Eye class="mr-2 h-4 w-4" />
                                            View
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="props.applications.last_page > 1"
                        class="flex items-center justify-between border-t px-4 py-3"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Button
                                v-if="props.applications.current_page > 1"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.applications.links[0].url!)"
                            >
                                Previous
                            </Button>
                            <Button
                                v-if="props.applications.current_page < props.applications.last_page"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.applications.links[props.applications.links.length - 1].url!)"
                            >
                                Next
                            </Button>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Showing
                                    <span class="font-medium">
                                        {{ (props.applications.current_page - 1) * props.applications.per_page + 1 }}
                                    </span>
                                    to
                                    <span class="font-medium">
                                        {{ Math.min(props.applications.current_page * props.applications.per_page, props.applications.total) }}
                                    </span>
                                    of
                                    <span class="font-medium">{{ props.applications.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="inline-flex -space-x-px rounded-md shadow-sm">
                                    <Button
                                        v-for="(link, index) in props.applications.links"
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
    </AppLayout>
</template>

