<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import RichTextEditor from '@/components/RichTextEditor.vue';
import TechnologySelector from '@/components/TechnologySelector.vue';
import CustomQuestionBuilder, { type CustomQuestion } from '@/components/CustomQuestionBuilder.vue';
import { ChevronLeft, ChevronRight, Check } from 'lucide-vue-next';

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

const currentStep = ref(1);
const totalSteps = 4;

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
    status: 'draft',
    is_featured: false,
    is_external: false,
    external_apply_url: '',
    allow_platform_applications: true,
    expires_at: '',
    technologies: [] as number[],
    custom_questions: [] as CustomQuestion[],
});

const nextStep = () => {
    if (currentStep.value < totalSteps) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submit = () => {
    form.post(route('hr.positions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

const getStepStatus = (step: number) => {
    if (step < currentStep.value) return 'completed';
    if (step === currentStep.value) return 'current';
    return 'upcoming';
};

const steps = [
    { number: 1, name: 'Basic Info', description: 'Title, company, and description' },
    { number: 2, name: 'Requirements', description: 'Technologies and seniority' },
    { number: 3, name: 'Compensation', description: 'Salary and location' },
    { number: 4, name: 'Application', description: 'Custom questions and settings' },
];
</script>

<template>
    <Head title="Create Position" />

    <AppLayout title="Create Position">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Create New Position
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <!-- Progress Steps -->
                <nav aria-label="Progress" class="mb-8">
                    <ol role="list" class="flex items-center">
                        <li
                            v-for="(step, stepIdx) in steps"
                            :key="step.name"
                            :class="[
                                stepIdx !== steps.length - 1 ? 'pr-8 sm:pr-20' : '',
                                'relative flex-1',
                            ]"
                        >
                            <template v-if="getStepStatus(step.number) === 'completed'">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="h-0.5 w-full bg-indigo-600" />
                                </div>
                                <a
                                    href="#"
                                    class="relative flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 hover:bg-indigo-900"
                                    @click.prevent="currentStep = step.number"
                                >
                                    <Check class="h-5 w-5 text-white" aria-hidden="true" />
                                    <span class="sr-only">{{ step.name }}</span>
                                </a>
                            </template>
                            <template v-else-if="getStepStatus(step.number) === 'current'">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="h-0.5 w-full bg-gray-200 dark:bg-gray-700" />
                                </div>
                                <a
                                    href="#"
                                    class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white dark:bg-gray-800"
                                    aria-current="step"
                                >
                                    <span
                                        class="text-indigo-600 dark:text-indigo-400"
                                        aria-hidden="true"
                                    >
                                        {{ step.number }}
                                    </span>
                                    <span class="sr-only">{{ step.name }}</span>
                                </a>
                            </template>
                            <template v-else>
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="h-0.5 w-full bg-gray-200 dark:bg-gray-700" />
                                </div>
                                <a
                                    href="#"
                                    class="group relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white hover:border-gray-400 dark:border-gray-600 dark:bg-gray-800"
                                >
                                    <span
                                        class="text-gray-500 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-200"
                                        aria-hidden="true"
                                    >
                                        {{ step.number }}
                                    </span>
                                    <span class="sr-only">{{ step.name }}</span>
                                </a>
                            </template>
                        </li>
                    </ol>
                </nav>

                <!-- Form Content -->
                <form @submit.prevent="submit">
                    <!-- Step 1: Basic Info -->
                    <Card v-if="currentStep === 1">
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Provide the essential details about this position
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="space-y-2">
                                <Label for="title">Position Title *</Label>
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

                            <div class="space-y-2">
                                <Label for="company_id">Company *</Label>
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

                            <div class="space-y-2">
                                <Label for="short_description">Short Description *</Label>
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
                                    <p class="text-sm text-gray-500">
                                        {{ form.short_description.length }} / 200
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="long_description">Full Job Description *</Label>
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

                    <!-- Step 2: Requirements -->
                    <Card v-if="currentStep === 2">
                        <CardHeader>
                            <CardTitle>Requirements & Skills</CardTitle>
                            <CardDescription>
                                Specify the technologies and experience level required
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="space-y-2">
                                <Label>Technologies *</Label>
                                <TechnologySelector
                                    v-model="form.technologies"
                                    :technologies="technologies"
                                />
                                <p v-if="form.errors.technologies" class="text-sm text-red-500">
                                    {{ form.errors.technologies }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="seniority">Seniority Level</Label>
                                <Select v-model="form.seniority">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select seniority level" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Not specified</SelectItem>
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

                    <!-- Step 3: Compensation -->
                    <Card v-if="currentStep === 3">
                        <CardHeader>
                            <CardTitle>Compensation & Location</CardTitle>
                            <CardDescription>
                                Define salary range and remote work details
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="salary_min">Minimum Salary (USD)</Label>
                                    <Input
                                        id="salary_min"
                                        v-model.number="form.salary_min"
                                        type="number"
                                        placeholder="80000"
                                        min="0"
                                        step="1000"
                                    />
                                    <p v-if="form.errors.salary_min" class="text-sm text-red-500">
                                        {{ form.errors.salary_min }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="salary_max">Maximum Salary (USD)</Label>
                                    <Input
                                        id="salary_max"
                                        v-model.number="form.salary_max"
                                        type="number"
                                        placeholder="120000"
                                        min="0"
                                        step="1000"
                                    />
                                    <p v-if="form.errors.salary_max" class="text-sm text-red-500">
                                        {{ form.errors.salary_max }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="remote_type">Remote Type *</Label>
                                <Select v-model="form.remote_type">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="global">Global - Work from anywhere</SelectItem>
                                        <SelectItem value="timezone">Timezone Specific</SelectItem>
                                        <SelectItem value="country">Country Specific</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.remote_type" class="text-sm text-red-500">
                                    {{ form.errors.remote_type }}
                                </p>
                            </div>

                            <div v-if="form.remote_type !== 'global'" class="space-y-2">
                                <Label for="location_restriction">Location Restriction</Label>
                                <Input
                                    id="location_restriction"
                                    v-model="form.location_restriction"
                                    :placeholder="
                                        form.remote_type === 'timezone'
                                            ? 'e.g., UTC-5 to UTC+2'
                                            : 'e.g., USA, Canada, UK'
                                    "
                                />
                                <p
                                    v-if="form.errors.location_restriction"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.location_restriction }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="expires_at">Expiration Date</Label>
                                <Input
                                    id="expires_at"
                                    v-model="form.expires_at"
                                    type="date"
                                />
                                <p v-if="form.errors.expires_at" class="text-sm text-red-500">
                                    {{ form.errors.expires_at }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Step 4: Application Settings -->
                    <Card v-if="currentStep === 4">
                        <CardHeader>
                            <CardTitle>Application Settings</CardTitle>
                            <CardDescription>
                                Configure how candidates will apply for this position
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="space-y-4">
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_external"
                                        v-model:checked="form.is_external"
                                    />
                                    <Label for="is_external" class="cursor-pointer font-normal">
                                        This is an external position (applications handled elsewhere)
                                    </Label>
                                </div>

                                <div v-if="form.is_external" class="space-y-2 ml-6">
                                    <Label for="external_apply_url">External Application URL *</Label>
                                    <Input
                                        id="external_apply_url"
                                        v-model="form.external_apply_url"
                                        type="url"
                                        placeholder="https://company.com/careers/apply"
                                    />
                                    <p
                                        v-if="form.errors.external_apply_url"
                                        class="text-sm text-red-500"
                                    >
                                        {{ form.errors.external_apply_url }}
                                    </p>
                                </div>

                                <div v-if="!form.is_external" class="flex items-center space-x-2">
                                    <Checkbox
                                        id="allow_platform_applications"
                                        v-model:checked="form.allow_platform_applications"
                                    />
                                    <Label
                                        for="allow_platform_applications"
                                        class="cursor-pointer font-normal"
                                    >
                                        Allow applications through this platform
                                    </Label>
                                </div>
                            </div>

                            <Separator />

                            <div class="space-y-2">
                                <Label>Custom Application Questions</Label>
                                <p class="text-sm text-gray-500 mb-4">
                                    Add custom questions that candidates must answer when applying
                                </p>
                                <CustomQuestionBuilder v-model="form.custom_questions" />
                            </div>

                            <Separator />

                            <div class="space-y-2">
                                <Label for="status">Publication Status *</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">Save as Draft</SelectItem>
                                        <SelectItem value="published">Publish Now</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="text-sm text-red-500">
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Navigation Buttons -->
                    <div class="mt-6 flex items-center justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            @click="prevStep"
                            :disabled="currentStep === 1"
                        >
                            <ChevronLeft class="mr-2 h-4 w-4" />
                            Previous
                        </Button>

                        <div class="text-sm text-gray-500">
                            Step {{ currentStep }} of {{ totalSteps }}
                        </div>

                        <Button
                            v-if="currentStep < totalSteps"
                            type="button"
                            @click="nextStep"
                        >
                            Next
                            <ChevronRight class="ml-2 h-4 w-4" />
                        </Button>

                        <Button
                            v-else
                            type="submit"
                            :disabled="form.processing"
                        >
                            <Check class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Creating...' : 'Create Position' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

