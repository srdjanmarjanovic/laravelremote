<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge/index';
import { toast } from 'vue-sonner';
import { Check, Sparkles, Star } from 'lucide-vue-next';

interface Position {
    id: number;
    title: string;
    company: {
        name: string;
    };
    technologies: Array<{ id: number; name: string }>;
}

interface Pricing {
    regular: number;
    featured: number;
    top: number;
}

const props = defineProps<{
    position: Position;
    pricing: Pricing;
}>();

const form = useForm({
    tier: 'regular' as 'regular' | 'featured' | 'top',
});

const tiers = [
    {
        value: 'regular',
        label: 'Regular',
        price: props.pricing.regular,
        description: 'Standard listing',
        features: ['30 days visibility', 'Standard placement', 'Basic analytics'],
        icon: Check,
    },
    {
        value: 'featured',
        label: 'Featured',
        price: props.pricing.featured,
        description: 'Highlighted listing',
        features: ['30 days visibility', 'Highlighted placement', 'Enhanced analytics', 'Priority support'],
        icon: Sparkles,
        popular: true,
    },
    {
        value: 'top',
        label: 'Top',
        price: props.pricing.top,
        description: 'Maximum visibility',
        features: ['30 days visibility', 'Pinned at top', 'Full analytics', 'Priority support', 'Featured badge'],
        icon: Star,
    },
];

const submit = () => {
    form.post(route('hr.positions.payment.checkout', props.position.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Redirecting to payment...');
        },
        onError: () => {
            toast.error('There was an error processing your payment. Please try again.');
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
        title: 'Payment',
        href: route('hr.positions.payment', props.position.id),
    },
];
</script>

<template>
    <Head title="Complete Payment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-5xl p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Complete Payment</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Choose a listing tier to publish your position
                </p>
            </div>

            <!-- Position Summary -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle class="text-base">Position Summary</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div>
                            <span class="text-sm font-medium">Title:</span>
                            <span class="text-sm ml-2">{{ position.title }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium">Company:</span>
                            <span class="text-sm ml-2">{{ position.company.name }}</span>
                        </div>
                        <div v-if="position.technologies.length > 0">
                            <span class="text-sm font-medium">Technologies:</span>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <Badge
                                    v-for="tech in position.technologies"
                                    :key="tech.id"
                                    variant="secondary"
                                >
                                    {{ tech.name }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Payment Form -->
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mb-6">
                    <Card
                        v-for="tier in tiers"
                        :key="tier.value"
                        class="cursor-pointer transition-all hover:shadow-lg"
                        :class="{
                            'ring-2 ring-primary': form.tier === tier.value,
                            'border-primary': tier.popular,
                        }"
                        @click="form.tier = tier.value"
                    >
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <component :is="tier.icon" class="h-5 w-5" />
                                    <CardTitle class="text-lg">{{ tier.label }}</CardTitle>
                                </div>
                                <Badge v-if="tier.popular" variant="default">Popular</Badge>
                            </div>
                            <CardDescription>{{ tier.description }}</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <div class="text-3xl font-bold">${{ tier.price }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">for 30 days</div>
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="feature in tier.features"
                                    :key="feature"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <Check class="h-4 w-4 text-green-500 flex-shrink-0" />
                                    <span>{{ feature }}</span>
                                </div>
                            </div>

                            <Button
                                type="button"
                                :variant="form.tier === tier.value ? 'default' : 'outline'"
                                class="w-full"
                                @click.stop="form.tier = tier.value"
                            >
                                {{ form.tier === tier.value ? 'Selected' : 'Select' }}
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <div class="flex items-center justify-between">
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.visit(route('hr.positions.index'))"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Processing...' : `Pay $${pricing[form.tier]}` }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

