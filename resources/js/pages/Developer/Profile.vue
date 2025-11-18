<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { FileText, Upload, X, Download, Github, Linkedin, Globe, AlertCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import developer from '@/routes/developer';

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

const props = defineProps<{
    profile: DeveloperProfile;
}>();

const cvInput = ref<HTMLInputElement | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    summary: props.profile.summary || '',
    cv: null as File | null,
    profile_photo: null as File | null,
    github_url: props.profile.github_url || '',
    linkedin_url: props.profile.linkedin_url || '',
    portfolio_url: props.profile.portfolio_url || '',
});

const selectedCvName = ref<string>('');
const selectedPhotoPreview = ref<string>('');

const handleCvChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.cv = target.files[0];
        selectedCvName.value = target.files[0].name;
    }
};

const handlePhotoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.profile_photo = target.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            selectedPhotoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(target.files[0]);
    }
};

const removeCv = () => {
    form.cv = null;
    selectedCvName.value = '';
    if (cvInput.value) {
        cvInput.value.value = '';
    }
};

const removePhoto = () => {
    form.profile_photo = null;
    selectedPhotoPreview.value = '';
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

const submit = () => {
    form.post(developer.profile.update().url, {
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
    if (confirm('Are you sure you want to delete your CV?')) {
        router.delete(developer.profile.cv.delete().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('CV deleted successfully!');
            },
        });
    }
};

const deletePhoto = () => {
    if (confirm('Are you sure you want to delete your profile photo?')) {
        router.delete(developer.profile.photo.delete().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Profile photo deleted successfully!');
            },
        });
    }
};

const breadcrumbs = [
    {
        title: 'Developer Dashboard',
        href: developer.dashboard().url,
    },
    {
        title: 'Profile',
        href: developer.profile.edit().url,
    },
];
</script>

<template>
    <Head title="Developer Profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Developer Profile</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Complete your profile to start applying for positions
                </p>
            </div>

            <!-- Profile Completion Alert -->
            <Alert v-if="!profile.summary || !profile.cv_path" class="mb-6">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>
                    Please complete your profile with a summary and CV to apply for positions.
                </AlertDescription>
            </Alert>

            <form @submit.prevent="submit" class="space-y-6">
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
                                v-if="selectedPhotoPreview || profile.profile_photo_path"
                                class="h-32 w-32 overflow-hidden rounded-lg border"
                            >
                                <img
                                    :src="selectedPhotoPreview || `/storage/${profile.profile_photo_path}`"
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
                                        v-if="profile.profile_photo_path && !selectedPhotoPreview"
                                        type="button"
                                        variant="destructive"
                                        @click="deletePhoto"
                                    >
                                        <X class="mr-2 h-4 w-4" />
                                        Delete Current
                                    </Button>
                                </div>
                                <p v-if="form.errors.profile_photo" class="text-sm text-destructive">
                                    {{ form.errors.profile_photo }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle>Professional Summary *</CardTitle>
                        <CardDescription>Brief overview of your experience and skills</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Textarea
                            v-model="form.summary"
                            placeholder="I am a full-stack developer with 5 years of experience..."
                            rows="6"
                            class="w-full"
                            required
                        />
                        <p class="mt-2 text-xs text-muted-foreground">Required for job applications</p>
                        <p v-if="form.errors.summary" class="mt-2 text-sm text-destructive">
                            {{ form.errors.summary }}
                        </p>
                    </CardContent>
                </Card>

                <!-- CV Upload -->
                <Card>
                    <CardHeader>
                        <CardTitle>Curriculum Vitae (CV) *</CardTitle>
                        <CardDescription>Upload your CV/Resume (PDF, DOC, or DOCX)</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <input ref="cvInput" type="file" accept=".pdf,.doc,.docx" class="hidden" @change="handleCvChange" />

                        <div class="flex flex-wrap items-center gap-2">
                            <Button type="button" variant="outline" @click="cvInput?.click()">
                                <Upload class="mr-2 h-4 w-4" />
                                {{ selectedCvName || profile.cv_path ? 'Replace CV' : 'Upload CV' }}
                            </Button>

                            <Button
                                v-if="profile.cv_path && !selectedCvName"
                                type="button"
                                variant="outline"
                                @click="downloadCv"
                            >
                                <Download class="mr-2 h-4 w-4" />
                                Download Current CV
                            </Button>

                            <Button
                                v-if="profile.cv_path && !selectedCvName"
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

                        <div v-else-if="profile.cv_path" class="flex items-center gap-2 rounded-md bg-muted p-3">
                            <FileText class="h-4 w-4 text-green-600" />
                            <span class="flex-1 text-sm">CV uploaded</span>
                        </div>

                        <p class="text-xs text-muted-foreground">Required for job applications</p>
                        <p v-if="form.errors.cv" class="text-sm text-destructive">
                            {{ form.errors.cv }}
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
                                v-model="form.github_url"
                                type="url"
                                placeholder="https://github.com/yourusername"
                            />
                            <p v-if="form.errors.github_url" class="text-sm text-destructive">
                                {{ form.errors.github_url }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="linkedin_url" class="flex items-center gap-2">
                                <Linkedin class="h-4 w-4" />
                                LinkedIn URL
                            </Label>
                            <Input
                                id="linkedin_url"
                                v-model="form.linkedin_url"
                                type="url"
                                placeholder="https://linkedin.com/in/yourprofile"
                            />
                            <p v-if="form.errors.linkedin_url" class="text-sm text-destructive">
                                {{ form.errors.linkedin_url }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="portfolio_url" class="flex items-center gap-2">
                                <Globe class="h-4 w-4" />
                                Portfolio URL
                            </Label>
                            <Input
                                id="portfolio_url"
                                v-model="form.portfolio_url"
                                type="url"
                                placeholder="https://yourportfolio.com"
                            />
                            <p v-if="form.errors.portfolio_url" class="text-sm text-destructive">
                                {{ form.errors.portfolio_url }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-4">
                    <Button type="button" variant="outline" @click="router.visit(developer.dashboard().url)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Save Profile</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

