<script setup lang="ts">
import { edit, update } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { FileText, Upload, X, Download, Github, Linkedin, Globe, AlertCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';
import developer from '@/routes/developer';
import { type BreadcrumbItem } from '@/types';

interface DeveloperProfile {
    id?: number;
    user_id?: number;
    summary: string | null;
    cv_path: string | null;
    profile_photo_path: string | null;
    github_url: string | null;
    linkedin_url: string | null;
    portfolio_url: string | null;
    other_links: string[] | null;
}

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    isSocialUser: boolean;
    developerProfile?: DeveloperProfile;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
const isDeveloper = computed(() => user.role === 'developer');

// Form setup
const form = useForm({
    name: user.name,
    email: user.email,
    developer_profile: isDeveloper.value
        ? {
              summary: props.developerProfile?.summary || '',
              cv: null as File | null,
              profile_photo: null as File | null,
              github_url: props.developerProfile?.github_url || '',
              linkedin_url: props.developerProfile?.linkedin_url || '',
              portfolio_url: props.developerProfile?.portfolio_url || '',
          }
        : undefined,
});

// Developer profile form handling
const cvInput = ref<HTMLInputElement | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);
const showDeleteCvDialog = ref(false);
const showDeletePhotoDialog = ref(false);
const selectedCvName = ref<string>('');
const selectedPhotoPreview = ref<string>('');

const handleCvChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0] && isDeveloper.value && form.developer_profile) {
        form.developer_profile.cv = target.files[0];
        selectedCvName.value = target.files[0].name;
    }
};

const handlePhotoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0] && isDeveloper.value && form.developer_profile) {
        form.developer_profile.profile_photo = target.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            selectedPhotoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(target.files[0]);
    }
};

const removeCv = () => {
    if (isDeveloper.value && form.developer_profile) {
        form.developer_profile.cv = null;
    }
    selectedCvName.value = '';
    if (cvInput.value) {
        cvInput.value.value = '';
    }
};

const removePhoto = () => {
    if (isDeveloper.value && form.developer_profile) {
        form.developer_profile.profile_photo = null;
    }
    selectedPhotoPreview.value = '';
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

const submit = () => {
    // Transform the form data to ensure nested structure is correct
    form.transform((data) => {
        const transformed: Record<string, any> = {
            name: data.name,
            email: data.email,
        };

        // Only include developer_profile if user is a developer
        if (isDeveloper.value && data.developer_profile) {
            transformed.developer_profile = {
                summary: data.developer_profile.summary || '',
                github_url: data.developer_profile.github_url || '',
                linkedin_url: data.developer_profile.linkedin_url || '',
                portfolio_url: data.developer_profile.portfolio_url || '',
            };

            // Only include files if they exist
            if (data.developer_profile.cv) {
                transformed.developer_profile.cv = data.developer_profile.cv;
            }
            if (data.developer_profile.profile_photo) {
                transformed.developer_profile.profile_photo = data.developer_profile.profile_photo;
            }
        }

        return transformed;
    }).patch(update().url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Profile updated successfully!');
            selectedCvName.value = '';
            selectedPhotoPreview.value = '';
        },
        onError: () => {
            toast.error('There was an error updating your profile. Please check the form.');
        },
    });
};

const downloadCv = () => {
    window.location.href = developer.profile.cv.download().url;
};

const deleteCv = () => {
    showDeleteCvDialog.value = true;
};

const performDeleteCv = () => {
    router.delete(developer.profile.cv.delete().url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('CV deleted successfully!');
            showDeleteCvDialog.value = false;
        },
        onError: () => {
            toast.error('Failed to delete CV');
        },
    });
};

const deletePhoto = () => {
    showDeletePhotoDialog.value = true;
};

const performDeletePhoto = () => {
    router.delete(developer.profile.photo.delete().url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Profile photo deleted successfully!');
            showDeletePhotoDialog.value = false;
        },
        onError: () => {
            toast.error('Failed to delete profile photo');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Update your name and email address</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Name <span class="text-red-500">*</span></Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="name"
                                    placeholder="Full name"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Email address <span class="text-red-500">*</span></Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="username"
                                    placeholder="Email address"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-if="mustVerifyEmail && !user.email_verified_at">
                        <CardContent class="pt-6">
                            <p class="text-sm text-muted-foreground">
                                Your email address is unverified.
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                >
                                    Click here to resend the verification email.
                                </Link>
                            </p>

                            <div
                                v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-green-600"
                            >
                                A new verification link has been sent to your email
                                address.
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Developer Profile Fields -->
                    <template v-if="isDeveloper">
                        <!-- Profile Completion Alert -->
                        <Alert
                            v-if="!developerProfile?.summary || !developerProfile?.cv_path"
                            class="mb-6"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <AlertDescription>
                                Please complete your profile with a summary and CV to apply for positions.
                            </AlertDescription>
                        </Alert>

                        <!-- Profile Photo -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Profile Photo</CardTitle>
                                <CardDescription>Upload a professional photo (optional)</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                                    <!-- Current or Preview Photo -->
                                    <div
                                        v-if="selectedPhotoPreview || developerProfile?.profile_photo_path"
                                        class="h-32 w-32 overflow-hidden rounded-lg border"
                                    >
                                        <img
                                            :src="selectedPhotoPreview || `/storage/${developerProfile?.profile_photo_path}`"
                                            alt="Profile photo"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>

                                    <div class="flex-1 space-y-3">
                                        <input
                                            ref="photoInput"
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="handlePhotoChange"
                                        />
                                        <div class="flex flex-wrap gap-2">
                                            <Button type="button" variant="outline" @click="photoInput?.click()">
                                                <Upload class="mr-2 h-4 w-4" />
                                                {{ selectedPhotoPreview ? 'Change Photo' : 'Upload Photo' }}
                                            </Button>
                                            <Button
                                                v-if="selectedPhotoPreview"
                                                type="button"
                                                variant="outline"
                                                @click="removePhoto"
                                            >
                                                <X class="mr-2 h-4 w-4" />
                                                Remove
                                            </Button>
                                            <Button
                                                v-if="developerProfile?.profile_photo_path && !selectedPhotoPreview"
                                                type="button"
                                                variant="destructive"
                                                @click="deletePhoto"
                                            >
                                                <X class="mr-2 h-4 w-4" />
                                                Delete Current
                                            </Button>
                                        </div>
                                        <p
                                            v-if="form.errors['developer_profile.profile_photo']"
                                            class="text-sm text-destructive"
                                        >
                                            {{ form.errors['developer_profile.profile_photo'] }}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Summary -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Professional Summary <span class="text-red-500">*</span></CardTitle>
                                <CardDescription>Brief overview of your experience and skills</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Textarea
                                    v-model="form.developer_profile!.summary"
                                    placeholder="I am a full-stack developer with 5 years of experience..."
                                    rows="6"
                                    class="w-full"
                                    required
                                />
                                <p class="mt-2 text-xs text-muted-foreground">Required for job applications</p>
                                <p
                                    v-if="form.errors['developer_profile.summary']"
                                    class="mt-2 text-sm text-destructive"
                                >
                                    {{ form.errors['developer_profile.summary'] }}
                                </p>
                            </CardContent>
                        </Card>

                        <!-- CV Upload -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Curriculum Vitae (CV) <span class="text-red-500">*</span></CardTitle>
                                <CardDescription>Upload your CV/Resume (PDF, DOC, or DOCX)</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <input
                                    ref="cvInput"
                                    type="file"
                                    accept=".pdf,.doc,.docx"
                                    class="hidden"
                                    @change="handleCvChange"
                                />

                                <div class="flex flex-wrap items-center gap-2">
                                    <Button type="button" variant="outline" @click="cvInput?.click()">
                                        <Upload class="mr-2 h-4 w-4" />
                                        {{ selectedCvName || developerProfile?.cv_path ? 'Replace CV' : 'Upload CV' }}
                                    </Button>

                                    <Button
                                        v-if="developerProfile?.cv_path && !selectedCvName"
                                        type="button"
                                        variant="outline"
                                        @click="downloadCv"
                                    >
                                        <Download class="mr-2 h-4 w-4" />
                                        Download Current CV
                                    </Button>

                                    <Button
                                        v-if="developerProfile?.cv_path && !selectedCvName"
                                        type="button"
                                        variant="destructive"
                                        @click="deleteCv"
                                    >
                                        <X class="mr-2 h-4 w-4" />
                                        Delete CV
                                    </Button>
                                </div>

                                <div v-if="selectedCvName" class="flex items-center gap-2 rounded-md bg-muted p-3">
                                    <FileText class="h-4 w-4" />
                                    <span class="flex-1 text-sm">{{ selectedCvName }}</span>
                                    <Button type="button" variant="ghost" size="sm" @click="removeCv">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>

                                <div
                                    v-else-if="developerProfile?.cv_path"
                                    class="flex items-center gap-2 rounded-md bg-muted p-3"
                                >
                                    <FileText class="h-4 w-4 text-green-600" />
                                    <span class="flex-1 text-sm">CV uploaded</span>
                                </div>

                                <p class="text-xs text-muted-foreground">Required for job applications</p>
                                <p v-if="form.errors['developer_profile.cv']" class="text-sm text-destructive">
                                    {{ form.errors['developer_profile.cv'] }}
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Links -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Professional Links</CardTitle>
                                <CardDescription>Add links to your online profiles (optional)</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="github_url" class="flex items-center gap-2">
                                        <Github class="h-4 w-4" />
                                        GitHub URL
                                    </Label>
                                    <Input
                                        id="github_url"
                                        v-model="form.developer_profile!.github_url"
                                        type="url"
                                        placeholder="https://github.com/yourusername"
                                    />
                                    <p v-if="form.errors['developer_profile.github_url']" class="text-sm text-destructive">
                                        {{ form.errors['developer_profile.github_url'] }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="linkedin_url" class="flex items-center gap-2">
                                        <Linkedin class="h-4 w-4" />
                                        LinkedIn URL
                                    </Label>
                                    <Input
                                        id="linkedin_url"
                                        v-model="form.developer_profile!.linkedin_url"
                                        type="url"
                                        placeholder="https://linkedin.com/in/yourprofile"
                                    />
                                    <p v-if="form.errors['developer_profile.linkedin_url']" class="text-sm text-destructive">
                                        {{ form.errors['developer_profile.linkedin_url'] }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="portfolio_url" class="flex items-center gap-2">
                                        <Globe class="h-4 w-4" />
                                        Portfolio URL
                                    </Label>
                                    <Input
                                        id="portfolio_url"
                                        v-model="form.developer_profile!.portfolio_url"
                                        type="url"
                                        placeholder="https://yourportfolio.com"
                                    />
                                    <p v-if="form.errors['developer_profile.portfolio_url']" class="text-sm text-destructive">
                                        {{ form.errors['developer_profile.portfolio_url'] }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </template>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            data-test="update-profile-button"
                            >Save</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="form.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser :is-social-user="isSocialUser" />

            <!-- Delete CV Confirmation Dialog -->
            <AlertDialog v-model:open="showDeleteCvDialog">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete CV?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete your CV? This action cannot be undone. You will need to upload a new CV to apply for positions.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            class="bg-destructive hover:bg-destructive/90"
                            @click.prevent="performDeleteCv"
                        >
                            Delete CV
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <!-- Delete Profile Photo Confirmation Dialog -->
            <AlertDialog v-model:open="showDeletePhotoDialog">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Profile Photo?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to delete your profile photo? This action cannot be undone.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            class="bg-destructive hover:bg-destructive/90"
                            @click.prevent="performDeletePhoto"
                        >
                            Delete Photo
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </SettingsLayout>
    </AppLayout>
</template>
