<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { FileText, Mail, Calendar, ChevronLeft, Save, Download } from 'lucide-vue-next';
import hr from '@/routes/hr';
import positions from '@/routes/positions';
import developer from '@/routes/developer';

interface User {
    id: number;
    name: string;
    email: string;
}

interface DeveloperProfile {
    summary: string | null;
    cv_path: string | null;
    github_url: string | null;
    linkedin_url: string | null;
    portfolio_url: string | null;
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

interface CustomQuestion {
    id: number;
    question_text: string;
    is_required: boolean;
}

interface Application {
    id: number;
    position: Position & { company: Company; custom_questions: CustomQuestion[] };
    user: User & { developer_profile: DeveloperProfile | null };
    status: 'pending' | 'reviewing' | 'accepted' | 'rejected';
    cover_letter: string | null;
    custom_answers: Record<string, string> | null;
    applied_at: string;
    reviewed_by_user_id: number | null;
}

const props = defineProps<{
    application: Application;
}>();

const form = useForm({
    status: props.application.status,
    _method: 'PATCH',
});

const submit = () => {
    form.post(hr.applications.update(props.application.id).url, {
        preserveScroll: true,
    });
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
        month: 'long',
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
    {
        title: 'Details',
        href: hr.applications.show(props.application.id).url,
    },
];
</script>

<template>
    <Head :title="`Application from ${application.user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <div class="flex items-center space-x-4">
                <Link :href="hr.applications.index().url">
                    <Button variant="ghost" size="sm">
                        <ChevronLeft class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                </Link>
                <h1 class="text-2xl font-bold">Application Details</h1>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Applicant Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Applicant Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ application.user.name }}
                                        </h3>
                                        <div class="mt-1 flex items-center text-sm text-gray-500">
                                            <Mail class="mr-2 h-4 w-4" />
                                            {{ application.user.email }}
                                        </div>
                                        <div class="mt-1 flex items-center text-sm text-gray-500">
                                            <Calendar class="mr-2 h-4 w-4" />
                                            Applied {{ formatDate(application.applied_at) }}
                                        </div>
                                    </div>
                                    <Badge :class="getStatusColor(application.status)">
                                        {{ application.status }}
                                    </Badge>
                                </div>

                                <Separator />

                                <!-- Profile Links -->
                                <div v-if="application.user.developer_profile" class="space-y-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        Professional Links
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            v-if="application.user.developer_profile.github_url"
                                            :href="application.user.developer_profile.github_url"
                                            target="_blank"
                                            class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                        >
                                            GitHub
                                        </a>
                                        <a
                                            v-if="application.user.developer_profile.linkedin_url"
                                            :href="application.user.developer_profile.linkedin_url"
                                            target="_blank"
                                            class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                        >
                                            LinkedIn
                                        </a>
                                        <a
                                            v-if="application.user.developer_profile.portfolio_url"
                                            :href="application.user.developer_profile.portfolio_url"
                                            target="_blank"
                                            class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                        >
                                            Portfolio
                                        </a>
                                    </div>
                                </div>

                                <!-- CV Download -->
                                <div v-if="application.user.developer_profile?.cv_path">
                                    <a
                                        :href="developer.profile.cv.download(application.user.id).url"
                                        target="_blank"
                                    >
                                        <Button variant="outline" class="w-full">
                                            <Download class="mr-2 h-4 w-4" />
                                            Download CV
                                        </Button>
                                    </a>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Cover Letter -->
                        <Card v-if="application.cover_letter">
                            <CardHeader>
                                <CardTitle>Cover Letter</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">
                                    {{ application.cover_letter }}
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Profile Summary -->
                        <Card v-if="application.user.developer_profile?.summary">
                            <CardHeader>
                                <CardTitle>Profile Summary</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">
                                    {{ application.user.developer_profile.summary }}
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Custom Answers -->
                        <Card
                            v-if="
                                application.custom_answers &&
                                Object.keys(application.custom_answers).length > 0
                            "
                        >
                            <CardHeader>
                                <CardTitle>Custom Questions</CardTitle>
                                <CardDescription>
                                    Answers to position-specific questions
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div
                                    v-for="question in application.position.custom_questions"
                                    :key="question.id"
                                    class="space-y-2"
                                >
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ question.question_text }}
                                        <span
                                            v-if="question.is_required"
                                            class="ml-1 text-xs text-red-500"
                                        >
                                            *
                                        </span>
                                    </h4>
                                    <p class="text-gray-700 dark:text-gray-300">
                                        {{
                                            application.custom_answers?.[question.id.toString()] ||
                                            'No answer provided'
                                        }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Update Status -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Update Status</CardTitle>
                                <CardDescription>
                                    Change the application status
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="submit" class="space-y-4">
                                    <div class="space-y-2">
                                        <Select v-model="form.status">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="pending">Pending</SelectItem>
                                                <SelectItem value="reviewing">Reviewing</SelectItem>
                                                <SelectItem value="accepted">Accepted</SelectItem>
                                                <SelectItem value="rejected">Rejected</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p v-if="form.errors.status" class="text-sm text-red-500">
                                            {{ form.errors.status }}
                                        </p>
                                    </div>

                                    <Button type="submit" class="w-full" :disabled="form.processing">
                                        <Save class="mr-2 h-4 w-4" />
                                        {{ form.processing ? 'Saving...' : 'Update Status' }}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        <!-- Position Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Position</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ application.position.title }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ application.position.company.name }}
                                    </p>
                                </div>

                                <Link
                                    :href="positions.show(application.position.slug).url"
                                    target="_blank"
                                >
                                    <Button variant="outline" class="w-full">
                                        <FileText class="mr-2 h-4 w-4" />
                                        View Position
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
    </AppLayout>
</template>

