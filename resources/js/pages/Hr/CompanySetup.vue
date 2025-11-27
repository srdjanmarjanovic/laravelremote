<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Upload, X, Building2, Globe, AlertCircle, Twitter, Linkedin, Github } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';
import hr from '@/routes/hr';

interface Company {
    id?: number;
    name: string | null;
    description: string | null;
    logo: string | null;
    website: string | null;
    social_links: {
        twitter?: string;
        linkedin?: string;
        github?: string;
    } | null;
}

const props = defineProps<{
    company: Company | null;
    isEditing?: boolean;
}>();

const logoInput = ref<HTMLInputElement | null>(null);
const selectedLogoPreview = ref<string>('');

const isNewCompany = computed(() => !props.company?.id);
const pageTitle = computed(() => props.isEditing ? 'Edit Company Profile' : 'Company Setup');

const form = useForm({
    name: props.company?.name || '',
    description: props.company?.description || '',
    logo: null as File | null,
    website: props.company?.website || '',
    social_links: {
        twitter: props.company?.social_links?.twitter || '',
        linkedin: props.company?.social_links?.linkedin || '',
        github: props.company?.social_links?.github || '',
    },
});

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.logo = target.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            selectedLogoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(target.files[0]);
    }
};

const removeLogo = () => {
    form.logo = null;
    selectedLogoPreview.value = '';
    if (logoInput.value) {
        logoInput.value.value = '';
    }
};

const submit = () => {
    const endpoint = isNewCompany.value ? hr.company.store().url : hr.company.update().url;
    const method = isNewCompany.value ? 'post' : 'put';

    form[method](endpoint, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(isNewCompany.value ? 'Company profile created!' : 'Company profile updated!');
        },
        onError: () => {
            toast.error('There was an error saving your company profile. Please check the form.');
        },
    });
};

const deleteLogo = () => {
    if (confirm('Are you sure you want to delete the company logo?')) {
        router.delete(hr.company.logo.delete().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Logo deleted successfully!');
            },
        });
    }
};

const breadcrumbs = computed(() => [
    {
        title: 'HR Dashboard',
        href: hr.dashboard().url,
    },
    {
        title: props.isEditing ? 'Edit Company' : 'Company Setup',
        href: props.isEditing ? hr.company.edit().url : hr.company.setup().url,
    },
]);
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Company Profile</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ isNewCompany ? 'Set up your company profile to start posting positions' : 'Update your company information' }}
                </p>
            </div>

            <!-- Profile Completion Alert -->
            <Alert v-if="isNewCompany || !company?.description" class="mb-6">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>
                    Please complete your company profile with a name and description to start posting job positions.
                </AlertDescription>
            </Alert>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Company Logo -->
                <Card>
                    <CardHeader>
                        <CardTitle>Company Logo</CardTitle>
                        <CardDescription>Upload your company logo (optional)</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                            <!-- Current or Preview Logo -->
                            <div
                                v-if="selectedLogoPreview || company?.logo"
                                class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-lg border bg-background"
                            >
                                <img
                                    :src="selectedLogoPreview || `/storage/${company?.logo}`"
                                    alt="Company logo"
                                    class="h-full w-full object-contain p-2"
                                />
                            </div>
                            <div
                                v-else
                                class="flex h-32 w-32 items-center justify-center rounded-lg border bg-muted"
                            >
                                <Building2 class="h-12 w-12 text-muted-foreground" />
                            </div>

                            <div class="flex-1 space-y-3">
                                <input
                                    ref="logoInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleLogoChange"
                                />
                                <div class="flex flex-wrap gap-2">
                                    <Button type="button" variant="outline" @click="logoInput?.click()">
                                        <Upload class="mr-2 h-4 w-4" />
                                        {{ selectedLogoPreview ? 'Change Logo' : 'Upload Logo' }}
                                    </Button>
                                    <Button
                                        v-if="selectedLogoPreview"
                                        type="button"
                                        variant="outline"
                                        @click="removeLogo"
                                    >
                                        <X class="mr-2 h-4 w-4" />
                                        Remove
                                    </Button>
                                    <Button
                                        v-if="company?.logo && !selectedLogoPreview"
                                        type="button"
                                        variant="destructive"
                                        @click="deleteLogo"
                                    >
                                        <X class="mr-2 h-4 w-4" />
                                        Delete Current
                                    </Button>
                                </div>
                                <p class="text-xs text-muted-foreground">Recommended: Square image, at least 200x200px</p>
                                <p v-if="form.errors.logo" class="text-sm text-destructive">
                                    {{ form.errors.logo }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Company Name -->
                <Card>
                    <CardHeader>
                        <CardTitle>Company Name <span class="text-red-500">*</span></CardTitle>
                        <CardDescription>The official name of your company</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Input
                            v-model="form.name"
                            placeholder="Acme Inc."
                            class="w-full"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Company Description -->
                <Card>
                    <CardHeader>
                        <CardTitle>Company Description <span class="text-red-500">*</span></CardTitle>
                        <CardDescription>Tell developers about your company, culture, and mission</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Textarea
                            v-model="form.description"
                            placeholder="We are a fast-growing tech company focused on..."
                            rows="6"
                            class="w-full"
                            required
                        />
                        <p class="mt-2 text-xs text-muted-foreground">Required to post job positions</p>
                        <p v-if="form.errors.description" class="mt-2 text-sm text-destructive">
                            {{ form.errors.description }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Website -->
                <Card>
                    <CardHeader>
                        <CardTitle>Company Website</CardTitle>
                        <CardDescription>Your company's main website (optional)</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <Globe class="h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="form.website"
                                type="url"
                                placeholder="https://yourcompany.com"
                                class="flex-1"
                            />
                        </div>
                        <p v-if="form.errors.website" class="mt-2 text-sm text-destructive">
                            {{ form.errors.website }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Social Links -->
                <Card>
                    <CardHeader>
                        <CardTitle>Social Links</CardTitle>
                        <CardDescription>Add links to your company's social profiles (optional)</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="twitter" class="flex items-center gap-2">
                                <Twitter class="h-4 w-4" />
                                Twitter / X
                            </Label>
                            <Input
                                id="twitter"
                                v-model="form.social_links.twitter"
                                type="url"
                                placeholder="https://twitter.com/yourcompany"
                            />
                            <p v-if="form.errors['social_links.twitter']" class="text-sm text-destructive">
                                {{ form.errors['social_links.twitter'] }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="linkedin" class="flex items-center gap-2">
                                <Linkedin class="h-4 w-4" />
                                LinkedIn
                            </Label>
                            <Input
                                id="linkedin"
                                v-model="form.social_links.linkedin"
                                type="url"
                                placeholder="https://linkedin.com/company/yourcompany"
                            />
                            <p v-if="form.errors['social_links.linkedin']" class="text-sm text-destructive">
                                {{ form.errors['social_links.linkedin'] }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="github" class="flex items-center gap-2">
                                <Github class="h-4 w-4" />
                                GitHub
                            </Label>
                            <Input
                                id="github"
                                v-model="form.social_links.github"
                                type="url"
                                placeholder="https://github.com/yourcompany"
                            />
                            <p v-if="form.errors['social_links.github']" class="text-sm text-destructive">
                                {{ form.errors['social_links.github'] }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-4">
                    <Button
                        v-if="!isNewCompany && company?.description"
                        type="button"
                        variant="outline"
                        @click="router.visit(hr.dashboard().url)"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>{{ isNewCompany ? 'Create Company Profile' : 'Update Company Profile' }}</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>



