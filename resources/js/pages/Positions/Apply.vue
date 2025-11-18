<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea/index';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { toast } from 'vue-sonner';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Briefcase, Building2, MapPin, DollarSign, AlertCircle } from 'lucide-vue-next';

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

interface CustomQuestion {
    id: number;
    question_text: string;
    is_required: boolean;
    order: number;
}

interface Position {
    id: number;
    title: string;
    slug: string;
    short_description: string;
    long_description: string;
    seniority?: string;
    salary_min?: number;
    salary_max?: number;
    remote_type: string;
    location_restriction?: string;
    company: Company;
    technologies: Technology[];
    customQuestions?: CustomQuestion[];
}

const props = defineProps<{
    position: Position;
}>();

// Initialize form with custom answers object
const customAnswers: Record<number, string> = {};
props.position.customQuestions?.forEach((q) => {
    customAnswers[q.id] = '';
});

const form = useForm({
    cover_letter: '',
    custom_answers: customAnswers,
});

const submit = () => {
    form.post(route('positions.apply.store', props.position.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Application submitted successfully!');
        },
        onError: () => {
            toast.error('There was an error submitting your application. Please check the form.');
        },
    });
};

const breadcrumbs = [
    {
        title: 'Positions',
        href: route('positions.index'),
    },
    {
        title: props.position.title,
        href: route('positions.show', props.position.slug),
    },
    {
        title: 'Apply',
        href: route('positions.apply', props.position.id),
    },
];
</script>

<template>
    <Head :title="`Apply to ${position.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Apply to {{ position.title }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Submit your application for this position
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Cover Letter -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Cover Letter</CardTitle>
                                <CardDescription>
                                    Tell the employer why you're interested in this position (optional)
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Textarea
                                    v-model="form.cover_letter"
                                    placeholder="I am interested in this position because..."
                                    rows="8"
                                    class="w-full"
                                />
                                <p class="mt-2 text-xs text-muted-foreground">
                                    Maximum 5000 characters
                                </p>
                                <p v-if="form.errors.cover_letter" class="mt-2 text-sm text-destructive">
                                    {{ form.errors.cover_letter }}
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Custom Questions -->
                        <Card v-if="position.customQuestions && position.customQuestions.length > 0">
                            <CardHeader>
                                <CardTitle>Additional Questions</CardTitle>
                                <CardDescription>
                                    Please answer the following questions from the employer
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <div
                                    v-for="question in position.customQuestions"
                                    :key="question.id"
                                    class="space-y-2"
                                >
                                    <Label :for="`question-${question.id}`" class="flex items-center gap-2">
                                        {{ question.question_text }}
                                        <span v-if="question.is_required" class="text-destructive">*</span>
                                    </Label>
                                    <Textarea
                                        :id="`question-${question.id}`"
                                        v-model="form.custom_answers[question.id]"
                                        :placeholder="
                                            question.is_required
                                                ? 'Your answer (required)'
                                                : 'Your answer (optional)'
                                        "
                                        rows="4"
                                        class="w-full"
                                    />
                                    <p class="text-xs text-muted-foreground">Maximum 2000 characters</p>
                                    <p
                                        v-if="form.errors[`custom_answers.${question.id}`]"
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors[`custom_answers.${question.id}`] }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end gap-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit(route('positions.show', position.slug))"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <span v-if="form.processing">Submitting...</span>
                                <span v-else>Submit Application</span>
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Position Summary -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Position Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Company -->
                            <div class="flex items-start gap-3">
                                <Building2 class="mt-1 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">{{ position.company.name }}</p>
                                </div>
                            </div>

                            <!-- Seniority -->
                            <div v-if="position.seniority" class="flex items-start gap-3">
                                <Briefcase class="mt-1 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium capitalize">{{ position.seniority }}</p>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="flex items-start gap-3">
                                <MapPin class="mt-1 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">
                                        {{ position.remote_type === 'global' ? 'Worldwide' : position.location_restriction }}
                                    </p>
                                </div>
                            </div>

                            <!-- Salary -->
                            <div v-if="position.salary_min && position.salary_max" class="flex items-start gap-3">
                                <DollarSign class="mt-1 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">
                                        ${{ position.salary_min.toLocaleString() }} - ${{
                                            position.salary_max.toLocaleString()
                                        }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Technologies -->
                    <Card v-if="position.technologies && position.technologies.length > 0">
                        <CardHeader>
                            <CardTitle class="text-base">Technologies</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="tech in position.technologies" :key="tech.id" variant="secondary">
                                    {{ tech.name }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Info Alert -->
                    <Alert>
                        <AlertCircle class="h-4 w-4" />
                        <AlertDescription class="text-sm">
                            Your profile information and CV will be shared with the employer when you submit this
                            application.
                        </AlertDescription>
                    </Alert>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

