<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import {
    Briefcase,
    Building2,
    Clock,
    FileText,
    TrendingUp,
    Users,
    CheckCircle,
    XCircle,
    Archive,
    Star,
    DollarSign,
    AlertCircle,
    RefreshCw,
} from 'lucide-vue-next';
import admin from '@/routes/admin';

interface Company {
    id: number;
    name: string;
}

interface Position {
    id: number;
    title: string;
    company: Company;
    status: string;
    listing_type: string;
    applications_count: number;
    created_at: string;
}

interface Application {
    id: number;
    position: {
        id: number;
        title: string;
        company: Company;
    };
    user: {
        id: number;
        name: string;
        email: string;
    };
    status: string;
    applied_at: string;
}

defineProps<{
    stats: {
        total_positions: number;
        active_positions: number;
        total_applications: number;
        pending_applications: number;
        total_companies: number;
        total_developers: number;
        total_hrs: number;
        total_revenue: number;
        pending_payments: number;
        failed_payments: number;
        refunded_payments: number;
        total_payments: number;
        completed_payments: number;
    };
    recentPositions: Position[];
    recentApplications: Application[];
    positionsByStatus: Record<string, number>;
    applicationsByStatus: Record<string, number>;
    paymentsByStatus: Record<string, number>;
    monthlyRevenue: Record<string, number>;
}>();

const getStatusBadge = (status: string) => {
    const variants: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; text: string }> = {
        draft: { variant: 'secondary', text: 'Draft' },
        published: { variant: 'default', text: 'Published' },
        expired: { variant: 'destructive', text: 'Expired' },
        archived: { variant: 'outline', text: 'Archived' },
        pending: { variant: 'secondary', text: 'Pending' },
        reviewing: { variant: 'default', text: 'Reviewing' },
        accepted: { variant: 'default', text: 'Accepted' },
        rejected: { variant: 'destructive', text: 'Rejected' },
    };
    return variants[status] || { variant: 'secondary' as const, text: status };
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(amount);
};

const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: admin.dashboard().url,
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Admin Dashboard</h1>
                    <p class="text-muted-foreground">Platform overview and management</p>
                </div>
                <Link :href="admin.positions.index().url">
                    <Button>
                        <Briefcase class="mr-2 h-4 w-4" />
                        Manage Positions
                    </Button>
                </Link>
            </div>

            <!-- Main Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.completed_payments }} completed payments
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Positions</CardTitle>
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_positions }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.active_positions }} active
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Applications</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_applications }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-amber-600">{{ stats.pending_applications }} pending</span>
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Companies</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_companies }}</div>
                        <p class="text-xs text-muted-foreground">Registered companies</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Payment Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Payments</CardTitle>
                        <Clock class="h-4 w-4 text-amber-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.pending_payments) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ paymentsByStatus.pending || 0 }} transactions
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Failed Payments</CardTitle>
                        <AlertCircle class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.failed_payments) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ paymentsByStatus.failed || 0 }} transactions
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Refunded Payments</CardTitle>
                        <RefreshCw class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.refunded_payments) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ paymentsByStatus.refunded || 0 }} transactions
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Payments</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_payments }}</div>
                        <p class="text-xs text-muted-foreground">
                            All payment transactions
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Status Breakdown Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Payments by Status</CardTitle>
                        <CardDescription>Distribution of payment statuses</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="h-4 w-4 text-green-600" />
                                    <span class="text-sm">Completed</span>
                                </div>
                                <Badge variant="default" class="bg-green-600">{{ paymentsByStatus.completed || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-amber-600" />
                                    <span class="text-sm">Pending</span>
                                </div>
                                <Badge variant="secondary">{{ paymentsByStatus.pending || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <XCircle class="h-4 w-4 text-red-600" />
                                    <span class="text-sm">Failed</span>
                                </div>
                                <Badge variant="destructive">{{ paymentsByStatus.failed || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <RefreshCw class="h-4 w-4 text-orange-600" />
                                    <span class="text-sm">Refunded</span>
                                </div>
                                <Badge variant="outline">{{ paymentsByStatus.refunded || 0 }}</Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Positions by Status</CardTitle>
                        <CardDescription>Distribution of position statuses</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <TrendingUp class="h-4 w-4 text-green-600" />
                                    <span class="text-sm">Published</span>
                                </div>
                                <Badge variant="default">{{ positionsByStatus.published || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-gray-500" />
                                    <span class="text-sm">Draft</span>
                                </div>
                                <Badge variant="secondary">{{ positionsByStatus.draft || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <XCircle class="h-4 w-4 text-red-600" />
                                    <span class="text-sm">Expired</span>
                                </div>
                                <Badge variant="destructive">{{ positionsByStatus.expired || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Archive class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm">Archived</span>
                                </div>
                                <Badge variant="outline">{{ positionsByStatus.archived || 0 }}</Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Applications by Status</CardTitle>
                        <CardDescription>Distribution of application statuses</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-amber-600" />
                                    <span class="text-sm">Pending</span>
                                </div>
                                <Badge variant="secondary">{{ applicationsByStatus.pending || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <TrendingUp class="h-4 w-4 text-blue-600" />
                                    <span class="text-sm">Reviewing</span>
                                </div>
                                <Badge variant="default">{{ applicationsByStatus.reviewing || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="h-4 w-4 text-green-600" />
                                    <span class="text-sm">Accepted</span>
                                </div>
                                <Badge variant="default" class="bg-green-600">{{ applicationsByStatus.accepted || 0 }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <XCircle class="h-4 w-4 text-red-600" />
                                    <span class="text-sm">Rejected</span>
                                </div>
                                <Badge variant="destructive">{{ applicationsByStatus.rejected || 0 }}</Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity -->
            <div class="grid gap-4 lg:grid-cols-2">
                <!-- Recent Positions -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Recent Positions</CardTitle>
                                <CardDescription>Latest job postings on the platform</CardDescription>
                            </div>
                            <Link :href="admin.positions.index().url">
                                <Button variant="outline" size="sm">View All</Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentPositions.length > 0" class="space-y-4">
                            <div
                                v-for="position in recentPositions.slice(0, 5)"
                                :key="position.id"
                                class="flex items-center justify-between rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium">{{ position.title }}</p>
                                    <p class="truncate text-sm text-muted-foreground">
                                        {{ position.company.name }}
                                    </p>
                                    <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                        <span>{{ formatDate(position.created_at) }}</span>
                                        <span>•</span>
                                        <span>{{ position.applications_count }} applications</span>
                                    </div>
                                </div>
                                <div class="ml-4 flex flex-col items-end gap-1">
                                    <Badge :variant="getStatusBadge(position.status).variant">
                                        {{ getStatusBadge(position.status).text }}
                                    </Badge>
                                    <Badge v-if="position.listing_type === 'featured'" variant="outline" class="text-amber-600 border-amber-600">
                                        <Star class="mr-1 h-3 w-3" />
                                        Featured
                                    </Badge>
                                    <Badge v-else-if="position.listing_type === 'top'" variant="outline" class="text-amber-600 border-amber-600">
                                        ⭐ Top
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center text-muted-foreground">
                            <Briefcase class="mx-auto mb-2 h-8 w-8" />
                            <p>No positions yet</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Applications -->
                <Card>
                    <CardHeader>
                        <div>
                            <CardTitle>Recent Applications</CardTitle>
                            <CardDescription>Latest applications submitted</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentApplications.length > 0" class="space-y-4">
                            <div
                                v-for="application in recentApplications.slice(0, 5)"
                                :key="application.id"
                                class="flex items-center justify-between rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium">{{ application.user.name }}</p>
                                    <p class="truncate text-sm text-muted-foreground">
                                        Applied for {{ application.position.title }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ application.position.company.name }} • {{ formatDate(application.applied_at) }}
                                    </p>
                                </div>
                                <Badge :variant="getStatusBadge(application.status).variant" class="ml-4">
                                    {{ getStatusBadge(application.status).text }}
                                </Badge>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center text-muted-foreground">
                            <FileText class="mx-auto mb-2 h-8 w-8" />
                            <p>No applications yet</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

        </div>
    </AppLayout>
</template>

