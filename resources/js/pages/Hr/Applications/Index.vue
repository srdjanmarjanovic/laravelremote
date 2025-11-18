<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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
import { Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import hr from '@/routes/hr';
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
    cover_letter: string | null;
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
    };
    positions: Position[];
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const positionFilter = ref(props.filters.position_id?.toString() || 'all');

const applyFilters = () => {
    router.get(
        hr.applications.index().url,
        {
            search: search.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value || undefined,
            position_id: positionFilter.value === 'all' ? undefined : positionFilter.value || undefined,
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
    positionFilter.value = 'all';
    router.get(hr.applications.index().url);
};

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
        title: 'HR Dashboard',
        href: hr.dashboard().url,
    },
    {
        title: 'Applications',
        href: hr.applications.index().url,
    },
];
</script>

<template>
    <Head title="Applications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <h1 class="text-2xl font-bold">Job Applications</h1>
                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="grid gap-4 md:grid-cols-4">
                        <Input
                            v-model="search"
                            placeholder="Search applicants..."
                            @keyup.enter="applyFilters"
                        />

                        <Select v-model="statusFilter" @update:model-value="applyFilters">
                            <SelectTrigger>
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

                        <Select v-model="positionFilter" @update:model-value="applyFilters">
                            <SelectTrigger>
                                <SelectValue placeholder="All Positions" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Positions</SelectItem>
                                <SelectItem
                                    v-for="position in props.positions"
                                    :key="position.id"
                                    :value="position.id.toString()"
                                >
                                    {{ position.title }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Button variant="outline" @click="clearFilters">Clear Filters</Button>
                    </div>
                </div>

                <!-- Applications Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
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
                                <TableCell colspan="6" class="text-center text-gray-500">
                                    No applications found.
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-for="application in props.applications.data"
                                :key="application.id"
                            >
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ application.user.name }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ application.user.email }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Link
                                        :href="positions.show(application.position.slug).url"
                                        target="_blank"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                                    >
                                        {{ application.position.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ application.position.company.name }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusColor(application.status)">
                                        {{ application.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(application.applied_at) }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Link :href="hr.applications.show(application.id).url">
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
                        class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Button
                                v-if="props.applications.current_page > 1"
                                variant="outline"
                                @click="router.get(props.applications.links[0].url!)"
                            >
                                Previous
                            </Button>
                            <Button
                                v-if="props.applications.current_page < props.applications.last_page"
                                variant="outline"
                                @click="
                                    router.get(props.applications.links[props.applications.links.length - 1].url!)
                                "
                            >
                                Next
                            </Button>
                        </div>
                        <div
                            class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing
                                    <span class="font-medium">
                                        {{
                                            (props.applications.current_page - 1) *
                                            props.applications.per_page +
                                            1
                                        }}
                                    </span>
                                    to
                                    <span class="font-medium">
                                        {{
                                            Math.min(
                                                props.applications.current_page * props.applications.per_page,
                                                props.applications.total
                                            )
                                        }}
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
                </div>
            </div>
    </AppLayout>
</template>

