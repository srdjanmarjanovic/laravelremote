<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import { Briefcase, FileText } from 'lucide-vue-next';
import developer from '@/routes/developer';
import positions from '@/routes/positions';
import { edit as editProfile } from '@/routes/profile';

interface Application {
    id: number;
    position: {
        title: string;
        company: {
            name: string;
        };
    };
    applied_at: string;
}

interface DeveloperProfile {
    summary: string | null;
    cv_path: string | null;
}

defineProps<{
    applications?: Application[];
    profile?: DeveloperProfile;
    stats?: {
        total_applications: number;
    };
}>();

const breadcrumbs = [
    {
        title: 'Developer Dashboard',
        href: developer.dashboard().url,
    },
];
</script>

<template>
    <Head title="Developer Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <h1 class="text-2xl font-bold">Developer Dashboard</h1>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-2 md:grid-cols-3">
                        <a :href="positions.index().url" class="block">
                            <Button variant="outline" class="w-full">
                                <Briefcase class="mr-2 h-4 w-4" />
                                Browse Positions
                            </Button>
                        </a>
                        <Link :href="editProfile().url">
                            <Button variant="outline" class="w-full">
                                <FileText class="mr-2 h-4 w-4" />
                                Update Profile
                            </Button>
                        </Link>
                        <Link :href="developer.applications.index().url">
                            <Button variant="outline" class="w-full">
                                <Briefcase class="mr-2 h-4 w-4" />
                                View Applications
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Profile Completion Alert -->
            <Card v-if="!profile?.summary || !profile?.cv_path" class="border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-amber-900 dark:text-amber-100">
                        <FileText class="h-5 w-5" />
                        Complete Your Profile
                    </CardTitle>
                    <CardDescription class="text-amber-700 dark:text-amber-300">
                        Complete your profile to start applying for positions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Link :href="editProfile().url">
                        <Button>Complete Profile</Button>
                    </Link>
                </CardContent>
            </Card>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-1">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Applications</CardTitle>
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.total_applications || 0 }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Applications -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Recent Applications</CardTitle>
                            <CardDescription>Your most recent job applications</CardDescription>
                        </div>
                        <Link :href="developer.applications.index().url">
                            <Button variant="outline" size="sm">View All</Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="applications && applications.length > 0" class="space-y-4">
                        <div
                            v-for="application in applications"
                            :key="application.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="space-y-1">
                                <Link
                                    :href="`/positions/${application.position.slug || application.position.id}`"
                                    class="font-medium hover:underline"
                                >
                                    {{ application.position.title }}
                                </Link>
                                <p class="text-sm text-muted-foreground">
                                    {{ application.position.company.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Applied {{ new Date(application.applied_at).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-muted-foreground">
                        <Briefcase class="mx-auto mb-2 h-8 w-8" />
                        <p>No applications yet</p>
                        <a :href="positions.index().url" class="mt-2 inline-block">
                            <Button variant="link">Browse Positions</Button>
                        </a>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

