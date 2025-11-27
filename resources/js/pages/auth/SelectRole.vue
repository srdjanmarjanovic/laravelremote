<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { Briefcase, Code } from 'lucide-vue-next';
</script>

<template>
    <AuthBase
        title="Choose your account type"
        description="Select how you'll be using your account"
    >
        <Head title="Select Account Type" />

        <Form
            action="/select-account-type"
            method="post"
            v-slot="{ processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-4">
                <Label
                    for="role-developer"
                    class="flex cursor-pointer items-start gap-4 rounded-lg border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground has-[:checked]:border-primary"
                >
                    <input
                        type="radio"
                        id="role-developer"
                        name="role"
                        value="developer"
                        class="mt-1 h-4 w-4 shrink-0"
                        checked
                    />
                    <div class="flex flex-1 items-start gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <Code class="h-6 w-6 text-primary" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <p class="text-base font-semibold leading-none">
                                Developer Account
                            </p>
                            <p class="text-sm text-muted-foreground">
                                I'm looking for remote development opportunities.
                                I want to browse positions, create my profile,
                                and submit applications.
                            </p>
                        </div>
                    </div>
                </Label>

                <Label
                    for="role-hr"
                    class="flex cursor-pointer items-start gap-4 rounded-lg border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground has-[:checked]:border-primary"
                >
                    <input
                        type="radio"
                        id="role-hr"
                        name="role"
                        value="hr"
                        class="mt-1 h-4 w-4 shrink-0"
                    />
                    <div class="flex flex-1 items-start gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <Briefcase class="h-6 w-6 text-primary" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <p class="text-base font-semibold leading-none">
                                HR / Recruiter Account
                            </p>
                            <p class="text-sm text-muted-foreground">
                                I'm hiring developers. I want to post job
                                positions, review applications, and manage my
                                company's hiring process.
                            </p>
                        </div>
                    </div>
                </Label>
            </div>

            <Button
                type="submit"
                class="w-full"
                :disabled="processing"
                data-test="complete-registration-button"
            >
                Continue
            </Button>
        </Form>
    </AuthBase>
</template>

