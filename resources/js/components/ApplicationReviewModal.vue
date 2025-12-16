<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { toast } from 'vue-sonner';
import { 
    User, 
    Mail, 
    FileText, 
    Link as LinkIcon, 
    AlertCircle,
    Edit,
    CheckCircle
} from 'lucide-vue-next';
import positions from '@/routes/positions';

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
    custom_questions?: CustomQuestion[];
}

interface DeveloperProfile {
    summary: string | null;
    cv_path: string | null;
    github_url: string | null;
    linkedin_url: string | null;
    portfolio_url: string | null;
    other_links?: string[] | null;
}

interface UserData {
    id: number;
    name: string;
    email: string;
    developer_profile?: DeveloperProfile | null;
}

const props = defineProps<{
    open: boolean;
    position: Position;
    user: UserData;
    profileUrl: string;
    cvDownloadUrl?: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const page = usePage();

// Initialize form with custom answers
const customAnswers: Record<number, string> = {};
props.position.custom_questions?.forEach((q) => {
    customAnswers[q.id] = '';
});

const form = useForm({
    custom_answers: customAnswers,
});

const hasCustomQuestions = computed(() => {
    return props.position.custom_questions && props.position.custom_questions.length > 0;
});

const submit = () => {
    form.post(positions.apply.store(props.position.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:open', false);
            toast.success('Your application has been submitted successfully!');
            // Navigate back to position show page to update the UI
            router.visit(positions.show(props.position.slug).url, {
                preserveScroll: true,
            });
        },
        onError: () => {
            toast.error('There was an error submitting your application. Please check the form.');
        },
    });
};

const handleClose = () => {
    if (!form.processing) {
        emit('update:open', false);
        form.reset();
        form.clearErrors();
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Review Your Application</DialogTitle>
                <DialogDescription>
                    Review your profile information and answer any additional questions before submitting
                    your application.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6">
                <!-- Profile Information -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base">Your Profile</CardTitle>
                            <a :href="profileUrl" target="_blank">
                                <Button variant="outline" size="sm">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit Profile
                                </Button>
                            </a>
                        </div>
                        <CardDescription>
                            This information will be shared with the employer
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Basic Info -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex items-start gap-3">
                                <User class="mt-1 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Name</p>
                                    <p class="text-sm">{{ user.name }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <Mail class="mt-1 h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Email</p>
                                    <p class="text-sm">{{ user.email }}</p>
                                </div>
                            </div>
                        </div>

                        <Separator v-if="user.developer_profile" />

                        <!-- Developer Profile -->
                        <div v-if="user.developer_profile" class="space-y-4">
                            <!-- Summary -->
                            <div v-if="user.developer_profile.summary">
                                <p class="mb-1 text-sm font-medium text-muted-foreground">Summary</p>
                                <p class="text-sm">{{ user.developer_profile.summary }}</p>
                            </div>

                            <!-- CV -->
                            <div v-if="user.developer_profile.cv_path" class="flex items-center gap-2">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">CV attached</span>
                                <Badge variant="secondary" class="ml-2">
                                    <CheckCircle class="mr-1 h-3 w-3" />
                                    Available
                                </Badge>
                            </div>

                            <!-- Links -->
                            <div v-if="user.developer_profile.github_url || user.developer_profile.linkedin_url || user.developer_profile.portfolio_url">
                                <p class="mb-2 text-sm font-medium text-muted-foreground">Links</p>
                                <div class="flex flex-wrap gap-2">
                                    <a
                                        v-if="user.developer_profile.github_url"
                                        :href="user.developer_profile.github_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        <LinkIcon class="h-3 w-3" />
                                        GitHub
                                    </a>
                                    <a
                                        v-if="user.developer_profile.linkedin_url"
                                        :href="user.developer_profile.linkedin_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        <LinkIcon class="h-3 w-3" />
                                        LinkedIn
                                    </a>
                                    <a
                                        v-if="user.developer_profile.portfolio_url"
                                        :href="user.developer_profile.portfolio_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        <LinkIcon class="h-3 w-3" />
                                        Portfolio
                                    </a>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Custom Questions -->
                <Card v-if="hasCustomQuestions">
                    <CardHeader>
                        <CardTitle class="text-base">Additional Questions</CardTitle>
                        <CardDescription>
                            Please answer the following questions from the employer
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div
                            v-for="question in position.custom_questions"
                            :key="question.id"
                            class="space-y-2"
                        >
                            <Label :for="`question-${question.id}`" class="flex items-center gap-2">
                                {{ question.question_text }}
                                <span v-if="question.is_required" class="text-red-500">*</span>
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

                <!-- Info Alert -->
                <Alert v-if="!hasCustomQuestions">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>
                        This position does not require any additional information. Click "Submit Application"
                        to complete your application.
                    </AlertDescription>
                </Alert>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="handleClose" :disabled="form.processing">
                    Cancel
                </Button>
                <Button type="button" @click="submit" :disabled="form.processing">
                    <span v-if="form.processing">Submitting...</span>
                    <span v-else>Submit Application</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

