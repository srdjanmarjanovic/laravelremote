<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';

// Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    isSocialUser: boolean;
}>();

const confirmationInput = useTemplateRef('confirmationInput');
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall
            title="Delete account"
            description="Delete your account and all of its resources"
        />
        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warning</p>
                <p class="text-sm">
                    Please proceed with caution, this cannot be undone.
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button"
                        >Delete account</Button
                    >
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => confirmationInput?.$el?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-3">
                            <DialogTitle
                                >Are you sure you want to delete your
                                account?</DialogTitle
                            >
                            <DialogDescription>
                                Once your account is deleted, all of its
                                resources and data will also be permanently
                                deleted. Please enter your
                                {{ isSocialUser ? 'email' : 'password' }} to
                                confirm you would like to permanently delete
                                your account.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label
                                v-if="isSocialUser"
                                for="email"
                                class="sr-only"
                                >Email</Label
                            >
                            <Label v-else for="password" class="sr-only"
                                >Password</Label
                            >
                            <Input
                                v-if="isSocialUser"
                                id="email"
                                type="email"
                                name="email"
                                ref="confirmationInput"
                                placeholder="your-email@example.com"
                            />
                            <Input
                                v-else
                                id="password"
                                type="password"
                                name="password"
                                ref="confirmationInput"
                                placeholder="Password"
                            />
                            <InputError
                                :message="isSocialUser ? errors.email : errors.password"
                            />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Cancel
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                Delete account
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
