<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import ApplicationReviewModal from '@/components/ApplicationReviewModal.vue';
import positions from '@/routes/positions';
import developer from '@/routes/developer';
import { edit as editProfile } from '@/routes/profile';

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

interface User {
    id: number;
    name: string;
    email: string;
    developer_profile?: DeveloperProfile | null;
}

const props = defineProps<{
    position: Position;
}>();

const page = usePage();
const modalOpen = ref(false);

const user = page.props.auth?.user as User;

// Open modal on mount
onMounted(() => {
    modalOpen.value = true;
});

const handleModalClose = () => {
    modalOpen.value = false;
    // Navigate back to position show page when modal closes
    // Use a small delay to allow the modal close animation to complete
    setTimeout(() => {
        router.visit(positions.show(props.position.slug).url);
    }, 150);
};
</script>

<template>
    <Head :title="`Apply to ${position.title}`" />

    <div class="fixed inset-0 z-50 bg-black/80">
        <ApplicationReviewModal
            :open="modalOpen"
            :position="position"
            :user="user"
            :profile-url="editProfile().url"
            @update:open="handleModalClose"
        />
    </div>
</template>

