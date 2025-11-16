<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import { Briefcase, Building, Clock, Eye, FileText, Users } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

interface Position {
    id: number;
    title: string;
    company: {
        name: string;
    };
    status: string;
    applications_count: number;
    created_at: string;
}

defineProps<{
    positions?: Position[];
    stats?: {
        total_positions: number;
        published_positions: number;
        draft_positions: number;
        total_applications: number;
        pending_applications: number;
        total_views: number;
    };
}>();

const getStatusBadge = (status: string) => {
    const variants = {
        draft: { variant: 'secondary', text: 'Draft' },
        published: { variant: 'default', text: 'Published' },
        expired: { variant: 'destructive', text: 'Expired' },
        archived: { variant: 'outline', text: 'Archived' },
    };
    return variants[status] || variants.draft;
};
</script>

<template>
    <AppLayout>
        <Head title="HR Dashboard" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading title='HR Dashboard' />
                <Link href="/hr/positions/create">
                    <Button>
                        <Briefcase class="mr-2 h-4 w-4" />
                        Post New Position
                    </Button>
                </Link>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Positions</CardTitle>
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.total_positions || 0 }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Published</CardTitle>
                        <Building class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats?.published_positions || 0 }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Drafts</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.draft_positions || 0 }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Applications</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.total_applications || 0 }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <Clock class="h-4 w-4 text-amber-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-amber-600">{{ stats?.pending_applications || 0 }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Views</CardTitle>
                        <Eye class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.total_views || 0 }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Positions -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Your Positions</CardTitle>
                            <CardDescription>Manage your job postings</CardDescription>
                        </div>
                        <Link href="/hr/positions">
                            <Button variant="outline" size="sm">View All</Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="positions && positions.length > 0" class="space-y-4">
                        <div
                            v-for="position in positions"
                            :key="position.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="space-y-1">
                                <Link
                                    :href="`/hr/positions/${position.id}`"
                                    class="font-medium hover:underline"
                                >
                                    {{ position.title }}
                                </Link>
                                <p class="text-sm text-muted-foreground">
                                    {{ position.company.name }}
                                </p>
                                <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                    <span class="flex items-center gap-1">
                                        <Users class="h-3 w-3" />
                                        {{ position.applications_count }} applications
                                    </span>
                                    <span>
                                        Created {{ new Date(position.created_at).toLocaleDateString() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge :variant="getStatusBadge(position.status).variant">
                                    {{ getStatusBadge(position.status).text }}
                                </Badge>
                                <Link :href="`/hr/positions/${position.id}/edit`">
                                    <Button variant="ghost" size="sm">Edit</Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-muted-foreground">
                        <Briefcase class="mx-auto mb-2 h-8 w-8" />
                        <p>No positions yet</p>
                        <Link href="/hr/positions/create" class="mt-2">
                            <Button variant="link">Create Your First Position</Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-3">
                <Link href="/hr/positions/create">
                    <Card class="cursor-pointer transition-colors hover:bg-accent">
                        <CardHeader>
                            <Briefcase class="mb-2 h-8 w-8" />
                            <CardTitle>Post Position</CardTitle>
                            <CardDescription>Create a new job posting</CardDescription>
                        </CardHeader>
                    </Card>
                </Link>

                <Link href="/hr/applications">
                    <Card class="cursor-pointer transition-colors hover:bg-accent">
                        <CardHeader>
                            <Users class="mb-2 h-8 w-8" />
                            <CardTitle>View Applications</CardTitle>
                            <CardDescription>Review candidate applications</CardDescription>
                        </CardHeader>
                    </Card>
                </Link>

                <Link href="/hr/positions">
                    <Card class="cursor-pointer transition-colors hover:bg-accent">
                        <CardHeader>
                            <FileText class="mb-2 h-8 w-8" />
                            <CardTitle>Manage Positions</CardTitle>
                            <CardDescription>Edit and update your postings</CardDescription>
                        </CardHeader>
                    </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

