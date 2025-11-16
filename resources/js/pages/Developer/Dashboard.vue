<script setup lang="ts">
// import AppShell from '@/components/AppShell.vue';
// import Heading from '@/components/Heading.vue';
// import { Badge } from '@/components/ui/badge';
// import { Button } from '@/components/ui/button';
// import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
// import { Briefcase, CheckCircle, Clock, FileText, XCircle } from 'lucide-vue-next';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';


interface Application {
    id: number;
    position: {
        title: string;
        company: {
            name: string;
        };
    };
    status: string;
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
        pending: number;
        accepted: number;
        rejected: number;
    };
}>();

const getStatusBadge = (status: string) => {
    const variants = {
        pending: { variant: 'secondary', icon: Clock, text: 'Pending' },
        reviewing: { variant: 'default', icon: Clock, text: 'Reviewing' },
        accepted: { variant: 'default', icon: CheckCircle, text: 'Accepted' },
        rejected: { variant: 'destructive', icon: XCircle, text: 'Rejected' },
    };
    return variants[status] || variants.pending;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>


<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>

