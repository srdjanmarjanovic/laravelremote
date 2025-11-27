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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
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
import { Plus, MoreVertical, Eye, Edit, Archive, ExternalLink } from 'lucide-vue-next';
import { ref } from 'vue';
import hr from '@/routes/hr';
import positions from '@/routes/positions';
import { toast } from 'vue-sonner';

interface Technology {
    id: number;
    name: string;
}

interface Company {
    id: number;
    name: string;
}

interface Position {
    id: number;
    title: string;
    slug: string;
    company: Company;
    status: 'draft' | 'published' | 'expired' | 'archived';
    seniority: string | null;
    remote_type: string;
    listing_type: string;
    applications_count: number;
    views_count: number;
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
    };
    companies: Company[];
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const companyFilter = ref(props.filters.company_id?.toString() || 'all');

const applyFilters = () => {
    router.get(
        hr.positions.index().url,
        {
            search: search.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value || undefined,
            company_id: companyFilter.value === 'all' ? undefined : companyFilter.value || undefined,
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
    companyFilter.value = 'all';
    router.get(hr.positions.index().url);
};

const archivePosition = (position: Position) => {
    if (confirm(`Are you sure you want to archive "${position.title}"? Archived positions will no longer be visible to candidates.`)) {
        router.post(hr.positions.archive(position.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Position archived successfully!');
            },
        });
    }
};

const getStatusColor = (status: string) => {
    const colors = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        published: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        expired: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        archived: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return colors[status as keyof typeof colors] || colors.draft;
};

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const breadcrumbs = [
    {
        title: 'HR Dashboard',
        href: hr.dashboard().url,
    },
    {
        title: 'Positions',
        href: hr.positions.index().url,
    },
];
</script>

<template>
    <Head title="Manage Positions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Job Positions</h1>
                <Link :href="hr.positions.create().url">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Create Position
                    </Button>
                </Link>
            </div>
                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="grid gap-4 md:grid-cols-4">
                        <Input
                            v-model="search"
                            placeholder="Search positions..."
                            @keyup.enter="applyFilters"
                        />

                        <Select v-model="statusFilter" @update:model-value="applyFilters">
                            <SelectTrigger>
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

                        <Select v-model="companyFilter" @update:model-value="applyFilters">
                            <SelectTrigger>
                                <SelectValue placeholder="All Companies" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Companies</SelectItem>
                                <SelectItem
                                    v-for="company in companies"
                                    :key="company.id"
                                    :value="company.id.toString()"
                                >
                                    {{ company.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Button variant="outline" @click="clearFilters">Clear Filters</Button>
                    </div>
                </div>

                <!-- Positions Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Position</TableHead>
                                <TableHead>Company</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Seniority</TableHead>
                                <TableHead class="text-center">Applications</TableHead>
                                <TableHead class="text-center">Views</TableHead>
                                <TableHead>Published</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-if="props.positions.data.length === 0"
                                class="hover:bg-transparent"
                            >
                                <TableCell colspan="8" class="text-center text-gray-500">
                                    No positions found. Create your first position to get started!
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="position in props.positions.data" :key="position.id" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800" @click="router.visit(hr.positions.show(position.id).url)">
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ position.title }}
                                        </span>
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
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ position.company.name }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusColor(position.status)">
                                        {{ position.status }}
                                    </Badge>
                                    <Badge
                                        v-if="position.listing_type === 'top'"
                                        class="ml-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                                    >
                                        ⭐ Top
                                    </Badge>
                                    <Badge
                                        v-else-if="position.listing_type === 'featured'"
                                        class="ml-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                                    >
                                        Featured
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span
                                        v-if="position.seniority"
                                        class="capitalize text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ position.seniority }}
                                    </span>
                                    <span v-else class="text-sm text-gray-400">N/A</span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ position.applications_count }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ position.views_count }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(position.published_at) }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right" @click.stop>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="sm">
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
                                                v-if="position.status !== 'published'"
                                                as-child
                                            >
                                                <a
                                                    :href="hr.positions.preview(position.id).url"
                                                    target="_blank"
                                                    class="flex cursor-pointer items-center"
                                                >
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    Preview
                                                </a>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="hr.positions.show(position.id).url"
                                                    class="flex cursor-pointer items-center"
                                                >
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    View Details
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="hr.positions.edit(position.id).url"
                                                    class="flex cursor-pointer items-center"
                                                >
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="position.status !== 'archived'"
                                                class="flex cursor-pointer items-center text-amber-600 focus:text-amber-600"
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
                        class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Button
                                v-if="props.positions.current_page > 1"
                                variant="outline"
                                @click="router.get(props.positions.links[0].url!)"
                            >
                                Previous
                            </Button>
                            <Button
                                v-if="props.positions.current_page < props.positions.last_page"
                                variant="outline"
                                @click="
                                    router.get(props.positions.links[props.positions.links.length - 1].url!)
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
                                        {{ (props.positions.current_page - 1) * props.positions.per_page + 1 }}
                                    </span>
                                    to
                                    <span class="font-medium">
                                        {{
                                            Math.min(
                                                props.positions.current_page * props.positions.per_page,
                                                props.positions.total
                                            )
                                        }}
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
                </div>
            </div>
    </AppLayout>
</template>

