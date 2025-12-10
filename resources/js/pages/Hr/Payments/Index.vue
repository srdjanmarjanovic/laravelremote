<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { ArrowUpDown, DollarSign, Filter, Search } from 'lucide-vue-next';
import hr from '@/routes/hr';
import { computed, ref } from 'vue';

interface Company {
    id: number;
    name: string;
}

interface Position {
    id: number;
    title: string;
    company: Company;
}

interface User {
    id: number;
    name: string;
}

interface Payment {
    id: number;
    amount: number;
    tier: 'regular' | 'featured' | 'top';
    type: 'initial' | 'upgrade';
    provider: string;
    status: 'pending' | 'completed' | 'failed' | 'refunded';
    provider_payment_id: string | null;
    created_at: string;
    position: Position;
    user: User;
}

interface PaginatedPayments {
    data: Payment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Filters {
    status?: string;
    type?: string;
    tier?: string;
    search?: string;
    sort_by?: string;
    sort_order?: string;
}

const props = defineProps<{
    payments: PaginatedPayments;
    filters: Filters;
}>();

const form = useForm({
    status: props.filters.status || '',
    type: props.filters.type || '',
    tier: props.filters.tier || '',
    search: props.filters.search || '',
    sort_by: props.filters.sort_by || 'created_at',
    sort_order: props.filters.sort_order || 'desc',
});

const applyFilters = () => {
    form.get(hr.payments.index().url, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.reset();
    form.get(hr.payments.index().url, {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleSort = (field: string) => {
    if (form.sort_by === field) {
        form.sort_order = form.sort_order === 'asc' ? 'desc' : 'asc';
    } else {
        form.sort_by = field;
        form.sort_order = 'desc';
    }
    applyFilters();
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const getStatusBadgeVariant = (status: string): string => {
    const variants: Record<string, string> = {
        completed: 'default',
        pending: 'secondary',
        failed: 'destructive',
        refunded: 'outline',
    };
    return variants[status] || 'secondary';
};

const getTierBadgeVariant = (tier: string): string => {
    const variants: Record<string, string> = {
        top: 'default',
        featured: 'secondary',
        regular: 'outline',
    };
    return variants[tier] || 'outline';
};

const totalSpent = computed(() => {
    return props.payments.data
        .filter((p) => p.status === 'completed')
        .reduce((sum, p) => sum + p.amount, 0);
});

const breadcrumbs = [
    {
        title: 'HR Dashboard',
        href: hr.dashboard().url,
    },
    {
        title: 'Payment History',
        href: hr.payments.index().url,
    },
];
</script>

<template>
    <Head title="Payment History" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-7xl p-4">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Payment History</h1>
                    <p class="mt-2 text-muted-foreground">
                        View and manage all your position payment transactions.
                    </p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="mb-6 grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Payments</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ payments.total }}</div>
                        <p class="text-xs text-muted-foreground">All time transactions</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Spent</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(totalSpent) }}</div>
                        <p class="text-xs text-muted-foreground">Completed payments</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Payments</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ payments.data.filter((p) => p.status === 'pending').length }}
                        </div>
                        <p class="text-xs text-muted-foreground">Awaiting completion</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-5 w-5" />
                        Filters
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Search</label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="form.search"
                                    placeholder="Search by position..."
                                    class="pl-8"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Status</label>
                            <Select v-model="form.status" @update:model-value="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All statuses</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="failed">Failed</SelectItem>
                                    <SelectItem value="refunded">Refunded</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Type</label>
                            <Select v-model="form.type" @update:model-value="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All types</SelectItem>
                                    <SelectItem value="initial">Initial</SelectItem>
                                    <SelectItem value="upgrade">Upgrade</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Tier</label>
                            <Select v-model="form.tier" @update:model-value="applyFilters">
                                <SelectTrigger>
                                    <SelectValue placeholder="All tiers" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All tiers</SelectItem>
                                    <SelectItem value="regular">Regular</SelectItem>
                                    <SelectItem value="featured">Featured</SelectItem>
                                    <SelectItem value="top">Top</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <Button variant="outline" @click="clearFilters">Clear Filters</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Payments Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Transactions</CardTitle>
                    <CardDescription>All your payment transactions</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        <Button
                                            variant="ghost"
                                            class="h-8 p-0"
                                            @click="toggleSort('created_at')"
                                        >
                                            Date
                                            <ArrowUpDown class="ml-2 h-4 w-4" />
                                        </Button>
                                    </TableHead>
                                    <TableHead>Position</TableHead>
                                    <TableHead>Tier</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>
                                        <Button
                                            variant="ghost"
                                            class="h-8 p-0"
                                            @click="toggleSort('amount')"
                                        >
                                            Amount
                                            <ArrowUpDown class="ml-2 h-4 w-4" />
                                        </Button>
                                    </TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Provider</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="payments.data.length === 0">
                                    <TableCell colspan="7" class="text-center text-muted-foreground">
                                        No payments found.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="payment in payments.data" :key="payment.id">
                                    <TableCell class="text-sm">
                                        {{ formatDate(payment.created_at) }}
                                    </TableCell>
                                    <TableCell>
                                        <Link
                                            :href="hr.positions.show(payment.position.id).url"
                                            class="font-medium hover:underline"
                                        >
                                            {{ payment.position.title }}
                                        </Link>
                                        <p class="text-xs text-muted-foreground">
                                            {{ payment.position.company.name }}
                                        </p>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getTierBadgeVariant(payment.tier)">
                                            {{ payment.tier.charAt(0).toUpperCase() + payment.tier.slice(1) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {{ payment.type.charAt(0).toUpperCase() + payment.type.slice(1) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="font-semibold">
                                        {{ formatCurrency(payment.amount) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusBadgeVariant(payment.status)">
                                            {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-sm text-muted-foreground">
                                        {{ payment.provider.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="payments.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (payments.current_page - 1) * payments.per_page + 1 }} to
                            {{ Math.min(payments.current_page * payments.per_page, payments.total) }} of
                            {{ payments.total }} payments
                        </div>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                :disabled="payments.current_page === 1"
                                @click="
                                    form.get(
                                        payments.links.find((l) => l.label === '&laquo; Previous')?.url || '',
                                    );
                                "
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                :disabled="payments.current_page === payments.last_page"
                                @click="
                                    form.get(
                                        payments.links.find((l) => l.label === 'Next &raquo;')?.url || '',
                                    );
                                "
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

