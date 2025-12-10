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
import { Search, X, Users as UsersIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import admin from '@/routes/admin';

interface User {
    id: number;
    name: string;
    email: string;
    role: string | null;
    created_at: string;
    applications_count: number;
    created_positions_count: number;
    developer_profile?: {
        id: number;
    };
    companies?: Array<{
        id: number;
        name: string;
    }>;
}

interface PaginatedUsers {
    data: User[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    users: PaginatedUsers;
    filters: {
        search?: string;
        role?: string;
        sort_by?: string;
        sort_order?: string;
    };
}>();

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || 'all');

const applyFilters = () => {
    router.get(
        admin.users.index().url,
        {
            search: search.value || undefined,
            role: roleFilter.value === 'all' ? undefined : roleFilter.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    search.value = '';
    roleFilter.value = 'all';
    router.get(admin.users.index().url);
};

const hasActiveFilters = computed(() => {
    return search.value || roleFilter.value !== 'all';
});

const getRoleBadge = (role: string | null) => {
    if (!role) {
        return { variant: 'secondary' as const, text: 'No Role' };
    }
    const badges: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; text: string }> = {
        admin: { variant: 'default', text: 'Admin' },
        hr: { variant: 'secondary', text: 'HR' },
        developer: { variant: 'outline', text: 'Developer' },
    };
    return badges[role] || { variant: 'secondary' as const, text: role };
};

const formatDate = (date: string) => {
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
        title: 'Users',
        href: admin.users.index().url,
    },
];
</script>

<template>
    <Head title="Manage Users - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Manage Users</h1>
                    <p class="text-muted-foreground">
                        {{ props.users.total }} total users on the platform
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
                                placeholder="Search users..."
                                class="pl-9"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <Select v-model="roleFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-full sm:w-[180px]">
                                <SelectValue placeholder="All Roles" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Roles</SelectItem>
                                <SelectItem value="admin">Admin</SelectItem>
                                <SelectItem value="hr">HR</SelectItem>
                                <SelectItem value="developer">Developer</SelectItem>
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

            <!-- Users Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Applications</TableHead>
                                <TableHead>Positions Created</TableHead>
                                <TableHead>Companies</TableHead>
                                <TableHead>Joined</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-if="props.users.data.length === 0"
                                class="hover:bg-transparent"
                            >
                                <TableCell colspan="6" class="py-12 text-center">
                                    <UsersIcon class="mx-auto mb-4 h-12 w-12 text-muted-foreground/50" />
                                    <p class="text-muted-foreground">No users found</p>
                                    <p v-if="hasActiveFilters" class="mt-1 text-sm text-muted-foreground">
                                        Try adjusting your filters
                                    </p>
                                </TableCell>
                            </TableRow>
                            <TableRow 
                                v-for="user in props.users.data" 
                                :key="user.id"
                            >
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ user.name }}</span>
                                        <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getRoleBadge(user.role).variant">
                                        {{ getRoleBadge(user.role).text }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ user.applications_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ user.created_positions_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div v-if="user.companies && user.companies.length > 0" class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="company in user.companies.slice(0, 2)"
                                            :key="company.id"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            {{ company.name }}
                                        </Badge>
                                        <Badge
                                            v-if="user.companies.length > 2"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            +{{ user.companies.length - 2 }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-sm text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-muted-foreground">
                                        {{ formatDate(user.created_at) }}
                                    </span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="props.users.last_page > 1"
                        class="flex items-center justify-between border-t px-4 py-3"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Button
                                v-if="props.users.current_page > 1"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.users.links[0].url!)"
                            >
                                Previous
                            </Button>
                            <Button
                                v-if="props.users.current_page < props.users.last_page"
                                variant="outline"
                                size="sm"
                                @click="router.get(props.users.links[props.users.links.length - 1].url!)"
                            >
                                Next
                            </Button>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Showing
                                    <span class="font-medium">
                                        {{ (props.users.current_page - 1) * props.users.per_page + 1 }}
                                    </span>
                                    to
                                    <span class="font-medium">
                                        {{ Math.min(props.users.current_page * props.users.per_page, props.users.total) }}
                                    </span>
                                    of
                                    <span class="font-medium">{{ props.users.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="inline-flex -space-x-px rounded-md shadow-sm">
                                    <Button
                                        v-for="(link, index) in props.users.links"
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

