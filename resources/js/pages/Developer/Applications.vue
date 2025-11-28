<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import { Briefcase, Building2, Calendar, CheckCircle, Clock, MapPin, XCircle } from 'lucide-vue-next';
import developer from '@/routes/developer';

interface Technology {
    id: number;
    name: string;
    slug: string;
}

interface Company {
    id: number;
    name: string;
    logo?: string;
}

interface Position {
    id: number;
    title: string;
    slug: string;
    seniority?: string;
    remote_type: string;
    location_restriction?: string;
    company: Company;
    technologies: Technology[];
}

interface Application {
    id: number;
    position: Position;
    status: string;
    applied_at: string;
}

interface PaginatedApplications {
    data: Application[];
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

const props = defineProps<{
    applications: PaginatedApplications;
}>();

const getStatusConfig = (status: string) => {
    const configs: Record<
        string,
        { variant: 'default' | 'secondary' | 'destructive' | 'outline'; icon: any; text: string; class: string }
    > = {
        pending: {
            variant: 'secondary',
            icon: Clock,
            text: 'Pending',
            class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        },
        reviewing: {
            variant: 'default',
            icon: Clock,
            text: 'Reviewing',
            class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        },
        accepted: {
            variant: 'default',
            icon: CheckCircle,
            text: 'Accepted',
            class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        },
        rejected: {
            variant: 'destructive',
            icon: XCircle,
            text: 'Rejected',
            class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        },
    };
    return configs[status] || configs.pending;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const breadcrumbs = [
    {
        title: 'Developer Dashboard',
        href: developer.dashboard().url,
    },
    {
        title: 'My Applications',
        href: developer.applications.index().url,
    },
];
</script>

<template>
    <Head title="My Applications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">My Applications</h1>
                    <p class="text-sm text-muted-foreground">
                        Track your job applications and their status
                    </p>
                </div>
                <Link href="/positions">
                    <Button>Browse Positions</Button>
                </Link>
            </div>

            <!-- Applications List -->
            <div v-if="applications.data.length > 0" class="space-y-4">
                <Card v-for="application in applications.data" :key="application.id">
                    <CardHeader>
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="flex-1">
                                <CardTitle class="mb-2">
                                    <Link
                                        :href="`/positions/${application.position.slug}`"
                                        class="hover:text-primary transition-colors"
                                    >
                                        {{ application.position.title }}
                                    </Link>
                                </CardTitle>
                                <CardDescription class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <Building2 class="h-4 w-4" />
                                        <span>{{ application.position.company.name }}</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span v-if="application.position.seniority" class="flex items-center gap-1">
                                            <Briefcase class="h-3 w-3" />
                                            {{ application.position.seniority }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <MapPin class="h-3 w-3" />
                                            {{
                                                application.position.remote_type === 'global'
                                                    ? 'Global'
                                                    : application.position.location_restriction
                                            }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <Calendar class="h-3 w-3" />
                                            Applied {{ formatDate(application.applied_at) }}
                                        </span>
                                    </div>
                                </CardDescription>
                            </div>
                            <div class="flex flex-col gap-2">
                                <Badge
                                    :class="getStatusConfig(application.status).class"
                                    class="w-fit"
                                >
                                    <component :is="getStatusConfig(application.status).icon" class="mr-1 h-3 w-3" />
                                    {{ getStatusConfig(application.status).text }}
                                </Badge>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- Technologies -->
                        <div
                            v-if="application.position.technologies && application.position.technologies.length > 0"
                            class="flex flex-wrap gap-2"
                        >
                            <Badge
                                v-for="tech in application.position.technologies"
                                :key="tech.id"
                                variant="outline"
                                class="text-xs"
                            >
                                {{ tech.name }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else class="border-dashed">
                <CardContent class="flex flex-col items-center justify-center py-12 text-center">
                    <Briefcase class="mb-4 h-12 w-12 text-muted-foreground" />
                    <h3 class="mb-2 text-lg font-semibold">No Applications Yet</h3>
                    <p class="mb-4 text-sm text-muted-foreground">
                        You haven't applied to any positions yet. Start browsing to find your next opportunity!
                    </p>
                    <Link href="/positions">
                        <Button>Browse Positions</Button>
                    </Link>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="applications.last_page > 1" class="flex items-center justify-center gap-2">
                <Link
                    v-for="link in applications.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'px-3 py-2 text-sm rounded-md',
                        link.active
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted hover:bg-muted/80 text-muted-foreground',
                        !link.url && 'opacity-50 cursor-not-allowed',
                    ]"
                    :disabled="!link.url"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>

