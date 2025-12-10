<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea/index';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import RichTextEditor from '@/components/RichTextEditor.vue';
import TechnologySelector from '@/components/TechnologySelector.vue';
import CustomQuestionBuilder, { type CustomQuestion } from '@/components/CustomQuestionBuilder.vue';
import LocationRestrictionSelector from '@/components/LocationRestrictionSelector.vue';
import { toast } from 'vue-sonner';

interface Technology {
    id: number;
    name: string;
    slug: string;
}

interface Company {
    id: number;
    name: string;
}

const props = defineProps<{
    technologies: Technology[];
    companies: Company[];
}>();

const form = useForm({
    title: '',
    short_description: '',
    long_description: '',
    company_id: props.companies.length > 0 ? props.companies[0].id : null,
    seniority: '',
    salary_min: null as number | null,
    salary_max: null as number | null,
    remote_type: 'global',
    location_restriction: '',
    listing_type: 'regular',
    is_external: false,
    external_apply_url: '',
    allow_platform_applications: true,
    technology_ids: [] as number[],
    custom_questions: [] as CustomQuestion[],
});


const submit = () => {
    form.post(route('hr.positions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Position created successfully!');
        },
        onError: () => {
            toast.error('There was an error creating the position. Please check the form.');
        },
    });
};

const breadcrumbs = [
    {
        title: 'HR Dashboard',
        href: route('hr.dashboard'),
    },
    {
        title: 'Positions',
        href: route('hr.positions.index'),
    },
    {
        title: 'Create',
        href: route('hr.positions.create'),
    },
];
</script>

<template>
    <Head title="Create Position" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-7xl p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Create New Position</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Fill in the details below to create a new job position
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <!-- Main Content - 3/4 width -->
                    <div class="space-y-6 lg:col-span-3">
                        <!-- Title -->
                        <Card>
                            <CardContent class="pt-6">
                                <div class="space-y-2">
                                    <Label for="title">Position Title <span class="text-red-500">*</span></Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        placeholder="e.g., Senior Laravel Developer"
                                        :class="{ 'border-red-500': form.errors.title }"
                                    />
                                    <p v-if="form.errors.title" class="text-sm text-red-500">
                                        {{ form.errors.title }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Short Description -->
                        <Card>
                            <CardContent class="pt-6">
                                <div class="space-y-2">
                                    <Label for="short_description">Short Description <span class="text-red-500">*</span></Label>
                                    <Textarea
                                        id="short_description"
                                        v-model="form.short_description"
                                        placeholder="A brief overview that will appear in job listings (max 200 characters)"
                                        rows="3"
                                        maxlength="200"
                                        :class="{ 'border-red-500': form.errors.short_description }"
                                    />
                                    <div class="flex justify-between">
                                        <p
                                            v-if="form.errors.short_description"
                                            class="text-sm text-red-500"
                                        >
                                            {{ form.errors.short_description }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ form.short_description.length }} / 200
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Long Description -->
                        <Card>
                            <CardContent class="pt-6">
                                <div class="space-y-2">
                                    <Label for="long_description">Full Job Description <span class="text-red-500">*</span></Label>
                                    <RichTextEditor
                                        v-model="form.long_description"
                                        placeholder="Provide a detailed description of the role, responsibilities, requirements, and benefits..."
                                    />
                                    <p v-if="form.errors.long_description" class="text-sm text-red-500">
                                        {{ form.errors.long_description }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Application Settings Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Application Settings</CardTitle>
                                <CardDescription>
                                    Configure how candidates will apply for this position
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                                    <!-- Application Method - 1/4 width -->
                                    <div class="lg:col-span-1">
                                        <Label class="text-sm font-medium">Application Method</Label>
                                        <div class="mt-3 space-y-3">
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="platform_application"
                                                    type="radio"
                                                    :checked="!form.is_external"
                                                    @change="form.is_external = false; form.external_apply_url = ''; form.allow_platform_applications = true"
                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600 dark:border-gray-600 dark:bg-background"
                                                />
                                                <Label for="platform_application" class="cursor-pointer font-normal">
                                                    Platform applications
                                                </Label>
                                            </div>

                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="external_application"
                                                    type="radio"
                                                    :checked="form.is_external"
                                                    @change="form.is_external = true; form.allow_platform_applications = false"
                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600 dark:border-gray-600 dark:bg-background"
                                                />
                                                <Label for="external_application" class="cursor-pointer font-normal">
                                                    External application
                                                </Label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Application Content - 3/4 width -->
                                    <div class="lg:col-span-3">
                                        <!-- Custom Questions -->
                                        <div v-if="!form.is_external" class="space-y-4">
                                            <div>
                                                <Label class="text-sm font-medium">Custom Application Questions</Label>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    Add specific questions that candidates must answer when applying for this position.
                                                </p>
                                            </div>
                                            <CustomQuestionBuilder v-model="form.custom_questions" />
                                        </div>

                                        <!-- External Application URL -->
                                        <div v-if="form.is_external" class="space-y-4">
                                            <div>
                                                <Label class="text-sm font-medium" for="external_apply_url_main">External Application URL <span class="text-red-500">*</span></Label>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    Provide the URL or email address where candidates should apply.
                                                </p>

                                                <Input
                                                    id="external_apply_url_main"
                                                    v-model="form.external_apply_url"
                                                    placeholder="https://company.com/apply or apply@company.com"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <p
                                                    v-if="form.errors.external_apply_url"
                                                    class="text-sm text-red-500"
                                                >
                                                    {{ form.errors.external_apply_url }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar - 1/4 width -->
                    <div class="space-y-6 lg:col-span-1">
                        <!-- Location Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">Location</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="remote_type">Remote Type <span class="text-red-500">*</span></Label>
                                    <Select v-model="form.remote_type">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="global">Global</SelectItem>
                                            <SelectItem value="timezone">Timezone</SelectItem>
                                            <SelectItem value="country">Country</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.remote_type" class="text-sm text-red-500">
                                        {{ form.errors.remote_type }}
                                    </p>
                                </div>

                                <LocationRestrictionSelector
                                    v-model="form.location_restriction"
                                    :remote-type="form.remote_type"
                                    :error="form.errors.location_restriction"
                                />
                            </CardContent>
                        </Card>

                        <!-- Basic Info Section -->
                        <Card v-if="companies.length > 1">
                            <CardHeader>
                                <CardTitle class="text-base">Basic Info</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="company_id">Company <span class="text-red-500">*</span></Label>
                                    <Select v-model="form.company_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a company" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="company in companies"
                                                :key="company.id"
                                                :value="company.id.toString()"
                                            >
                                                {{ company.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.company_id" class="text-sm text-red-500">
                                        {{ form.errors.company_id }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Requirements Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">Requirements</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Technologies <span class="text-red-500">*</span></Label>
                                    <TechnologySelector
                                        v-model="form.technology_ids"
                                        :technologies="technologies"
                                    />
                                    <p v-if="form.errors.technology_ids" class="text-sm text-red-500">
                                        {{ form.errors.technology_ids }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="seniority">Seniority Level</Label>
                                    <Select v-model="form.seniority">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select seniority level" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="junior">Junior</SelectItem>
                                            <SelectItem value="mid">Mid-Level</SelectItem>
                                            <SelectItem value="senior">Senior</SelectItem>
                                            <SelectItem value="lead">Lead</SelectItem>
                                            <SelectItem value="principal">Principal</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.seniority" class="text-sm text-red-500">
                                        {{ form.errors.seniority }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Compensation Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">Compensation</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="salary_min">Min Salary (USD)</Label>
                                    <Input
                                        id="salary_min"
                                        v-model.number="form.salary_min"
                                        type="number"
                                        placeholder="80000"
                                        min="0"
                                        step="1"
                                    />
                                    <p v-if="form.errors.salary_min" class="text-sm text-red-500">
                                        {{ form.errors.salary_min }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="salary_max">Max Salary (USD)</Label>
                                    <Input
                                        id="salary_max"
                                        v-model.number="form.salary_max"
                                        type="number"
                                        placeholder="120000"
                                        min="0"
                                        step="1"
                                    />
                                    <p v-if="form.errors.salary_max" class="text-sm text-red-500">
                                        {{ form.errors.salary_max }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Position' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

