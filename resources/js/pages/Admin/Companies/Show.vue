<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Building2,
    Globe,
    Twitter,
    Linkedin,
    Github,
    MoreVertical,
    Edit,
    Trash2,
    User,
    Briefcase,
    Calendar,
    Plus,
    Search,
    X,
    UserX,
    Shield,
    Crown,
    Users as UsersIcon,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import admin from '@/routes/admin';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table/index';

interface Creator {
    id: number;
    name: string;
    email: string;
}

interface CompanyUser {
    id: number;
    name: string;
    email: string;
    role: string | null;
    pivot: {
        role: 'owner' | 'admin' | 'member';
        joined_at: string;
    };
}

interface Company {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
    website: string | null;
    social_links: {
        twitter?: string;
        linkedin?: string;
        github?: string;
    } | null;
    created_at: string;
    positions_count: number;
    users_count: number;
    creator: Creator;
    users?: CompanyUser[];
}

interface SearchUser {
    id: number;
    name: string;
    email: string;
    role: string | null;
}

const props = defineProps<{
    company: Company;
}>();

const showDeleteDialog = ref(false);
const showAttachDialog = ref(false);
const showDetachDialog = ref(false);
const showRoleDialog = ref(false);
const userToDetach = ref<CompanyUser | null>(null);
const userToUpdateRole = ref<CompanyUser | null>(null);
const newRole = ref<'owner' | 'admin' | 'member'>('member');
const userSearchQuery = ref('');
const searchResults = ref<SearchUser[]>([]);
const selectedUserId = ref<number | null>(null);
const attachRole = ref<'owner' | 'admin' | 'member'>('member');
const isSearching = ref(false);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

// Debounce search when user types
watch(userSearchQuery, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        searchUsers();
    }, 300); // Wait 300ms after user stops typing
});


const deleteCompany = () => {
    router.delete(admin.companies.destroy({ company: props.company.id }).url, {
        preserveScroll: false,
        onSuccess: () => {
            toast.success('Company deleted successfully!');
        },
        onError: () => {
            toast.error('Failed to delete company');
        },
    });
};

const searchUsers = async () => {
    const query = userSearchQuery.value.trim();
    if (!query) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    try {
        // Get CSRF token from meta tag if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const url = `/admin/companies/${props.company.id}/users/search?search=${encodeURIComponent(query)}`;
        const headers: HeadersInit = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch(url, {
            method: 'GET',
            headers,
            credentials: 'same-origin',
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Search failed:', response.status, errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (Array.isArray(data)) {
            searchResults.value = data;
        } else {
            console.error('Unexpected response format:', data);
            searchResults.value = [];
        }
    } catch (error) {
        console.error('Error searching users:', error);
        toast.error('Failed to search users. Please try again.');
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

const attachUser = () => {
    if (!selectedUserId.value) {
        toast.error('Please select a user');
        return;
    }

    router.post(
        `/admin/companies/${props.company.id}/users`,
        {
            user_id: selectedUserId.value,
            role: attachRole.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User attached successfully!');
                showAttachDialog.value = false;
                userSearchQuery.value = '';
                searchResults.value = [];
                selectedUserId.value = null;
                attachRole.value = 'member';
                router.reload({ only: ['company'] });
            },
            onError: (errors) => {
                toast.error(errors.message || 'Failed to attach user');
            },
        }
    );
};

const openDetachDialog = (user: CompanyUser) => {
    userToDetach.value = user;
    showDetachDialog.value = true;
};

const performDetach = () => {
    if (!userToDetach.value) {
        return;
    }

    router.delete(
        `/admin/companies/${props.company.id}/users/${userToDetach.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User detached successfully!');
                showDetachDialog.value = false;
                userToDetach.value = null;
                router.reload({ only: ['company'] });
            },
            onError: (errors) => {
                toast.error(errors.message || 'Failed to detach user');
            },
        }
    );
};

const openRoleDialog = (user: CompanyUser) => {
    userToUpdateRole.value = user;
    newRole.value = user.pivot.role;
    showRoleDialog.value = true;
};

const performRoleUpdate = () => {
    if (!userToUpdateRole.value) {
        return;
    }

    router.put(
        `/admin/companies/${props.company.id}/users/${userToUpdateRole.value.id}/role`,
        {
            role: newRole.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User role updated successfully!');
                showRoleDialog.value = false;
                userToUpdateRole.value = null;
                router.reload({ only: ['company'] });
            },
            onError: (errors) => {
                toast.error(errors.message || 'Failed to update user role');
            },
        }
    );
};

const getRoleBadgeVariant = (role: string): string => {
    switch (role) {
        case 'owner':
            return 'default';
        case 'admin':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getRoleIcon = (role: string) => {
    switch (role) {
        case 'owner':
            return Crown;
        case 'admin':
            return Shield;
        default:
            return User;
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: admin.dashboard().url,
    },
    {
        title: 'Companies',
        href: admin.companies.index().url,
    },
    {
        title: props.company.name,
        href: admin.companies.show({ company: props.company.id }).url,
    },
];
</script>

<template>
    <Head :title="`${company.name} - Admin`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl p-4">
            <!-- Header -->
            <div class="mb-6 flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <!-- Logo -->
                    <div
                        v-if="company.logo"
                        class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-lg border bg-background"
                    >
                        <img
                            :src="`/storage/${company.logo}`"
                            :alt="`${company.name} logo`"
                            class="h-full w-full object-contain p-2"
                        />
                    </div>
                    <div
                        v-else
                        class="flex h-20 w-20 items-center justify-center rounded-lg border bg-muted"
                    >
                        <Building2 class="h-10 w-10 text-muted-foreground" />
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold">{{ company.name }}</h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Created {{ formatDate(company.created_at) }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="icon">
                            <MoreVertical class="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                            <Link :href="admin.companies.edit({ company: company.id }).url" class="flex items-center">
                                <Edit class="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            class="text-destructive focus:text-destructive"
                            @click="showDeleteDialog = true"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <!-- Stats -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-2">
                            <Briefcase class="h-5 w-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Positions</p>
                                <p class="text-2xl font-bold">{{ company.positions_count }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-2">
                            <User class="h-5 w-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Team Members</p>
                                <p class="text-2xl font-bold">{{ company.users_count }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-muted-foreground" />
                            <div>
                                <p class="text-sm text-muted-foreground">Created</p>
                                <p class="text-sm font-medium">{{ formatDate(company.created_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Description -->
            <Card v-if="company.description" class="mb-6">
                <CardHeader>
                    <CardTitle>About</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="whitespace-pre-wrap text-sm">{{ company.description }}</p>
                </CardContent>
            </Card>

            <!-- Creator Info -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Creator</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center gap-3">
                        <div>
                            <p class="font-medium">{{ company.creator.name }}</p>
                            <p class="text-sm text-muted-foreground">{{ company.creator.email }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Team Members -->
            <Card class="mb-6">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <UsersIcon class="h-5 w-5" />
                            Team Members
                        </CardTitle>
                        <Button size="sm" @click="showAttachDialog = true">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Member
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="!company.users || company.users.length === 0" class="py-8 text-center">
                        <UsersIcon class="mx-auto mb-4 h-12 w-12 text-muted-foreground/50" />
                        <p class="text-muted-foreground">No team members yet</p>
                        <Button
                            variant="outline"
                            size="sm"
                            class="mt-4"
                            @click="showAttachDialog = true"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Add First Member
                        </Button>
                    </div>
                    <Table v-else>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Joined</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in company.users" :key="user.id">
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ user.name }}</span>
                                        <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getRoleBadgeVariant(user.pivot.role)">
                                        <component
                                            :is="getRoleIcon(user.pivot.role)"
                                            class="mr-1 h-3 w-3"
                                        />
                                        {{ user.pivot.role }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm text-muted-foreground">
                                        {{ formatDate(user.pivot.joined_at) }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="openRoleDialog(user)">
                                                <Shield class="mr-2 h-4 w-4" />
                                                Change Role
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                class="text-destructive focus:text-destructive"
                                                @click="openDetachDialog(user)"
                                            >
                                                <UserX class="mr-2 h-4 w-4" />
                                                Remove
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Links -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Website -->
                <Card v-if="company.website">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Globe class="h-5 w-5" />
                            Website
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <a
                            :href="company.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm text-primary hover:underline"
                        >
                            {{ company.website }}
                        </a>
                    </CardContent>
                </Card>

                <!-- Social Links -->
                <Card v-if="company.social_links && (company.social_links.twitter || company.social_links.linkedin || company.social_links.github)">
                    <CardHeader>
                        <CardTitle>Social Links</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <a
                            v-if="company.social_links.twitter"
                            :href="company.social_links.twitter"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 text-sm text-primary hover:underline"
                        >
                            <Twitter class="h-4 w-4" />
                            Twitter / X
                        </a>
                        <a
                            v-if="company.social_links.linkedin"
                            :href="company.social_links.linkedin"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 text-sm text-primary hover:underline"
                        >
                            <Linkedin class="h-4 w-4" />
                            LinkedIn
                        </a>
                        <a
                            v-if="company.social_links.github"
                            :href="company.social_links.github"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 text-sm text-primary hover:underline"
                        >
                            <Github class="h-4 w-4" />
                            GitHub
                        </a>
                    </CardContent>
                </Card>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-end gap-4">
                <Button variant="outline" @click="router.visit(admin.companies.index().url)">
                    Back to Companies
                </Button>
                <Button @click="router.visit(admin.companies.edit({ company: company.id }).url)">
                    <Edit class="mr-2 h-4 w-4" />
                    Edit Company
                </Button>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Company?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete "{{ company.name }}"? This action cannot be undone and will delete all associated positions and data.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive hover:bg-destructive/90"
                        @click.prevent="deleteCompany"
                    >
                        Delete Company
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Attach User Dialog -->
        <AlertDialog v-model:open="showAttachDialog">
            <AlertDialogContent class="max-w-md">
                <AlertDialogHeader>
                    <AlertDialogTitle>Add Team Member</AlertDialogTitle>
                    <AlertDialogDescription>
                        Search for a user to add to this company.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Search Users</label>
                        <div class="relative flex gap-2">
                            <div class="relative flex-1">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="userSearchQuery"
                                    placeholder="Search by name or email..."
                                    class="pl-9"
                                    @keyup.enter="searchUsers"
                                />
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                :disabled="!userSearchQuery.trim() || isSearching"
                                @click="searchUsers"
                            >
                                <Search class="h-4 w-4" />
                            </Button>
                        </div>
                        <p v-if="isSearching" class="text-xs text-muted-foreground">Searching...</p>
                    </div>

                    <div v-if="searchResults.length > 0" class="max-h-48 space-y-2 overflow-y-auto">
                        <div
                            v-for="user in searchResults"
                            :key="user.id"
                            class="flex items-center justify-between rounded-lg border p-3 hover:bg-muted/50"
                            :class="{ 'border-primary bg-muted': selectedUserId === user.id }"
                            @click="selectedUserId = user.id"
                        >
                            <div>
                                <p class="font-medium">{{ user.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                            </div>
                            <Badge v-if="user.role" variant="outline">{{ user.role }}</Badge>
                        </div>
                    </div>
                    <p v-else-if="userSearchQuery && !isSearching" class="text-sm text-muted-foreground text-center">
                        No users found
                    </p>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Role</label>
                        <Select v-model="attachRole">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="member">
                                    <div class="flex items-center gap-2">
                                        <User class="h-4 w-4" />
                                        Member
                                    </div>
                                </SelectItem>
                                <SelectItem value="admin">
                                    <div class="flex items-center gap-2">
                                        <Shield class="h-4 w-4" />
                                        Admin
                                    </div>
                                </SelectItem>
                                <SelectItem value="owner">
                                    <div class="flex items-center gap-2">
                                        <Crown class="h-4 w-4" />
                                        Owner
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="userSearchQuery = ''; searchResults = []; selectedUserId = null">
                        Cancel
                    </AlertDialogCancel>
                    <AlertDialogAction @click.prevent="attachUser" :disabled="!selectedUserId">
                        Add Member
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Detach User Dialog -->
        <AlertDialog v-model:open="showDetachDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Remove Team Member?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to remove "{{ userToDetach?.name }}" from this company? They will lose access to all company resources.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive hover:bg-destructive/90"
                        @click.prevent="performDetach"
                    >
                        Remove Member
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Update Role Dialog -->
        <AlertDialog v-model:open="showRoleDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Change User Role</AlertDialogTitle>
                    <AlertDialogDescription>
                        Update the role for "{{ userToUpdateRole?.name }}".
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <div class="py-4">
                    <Select v-model="newRole">
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="member">
                                <div class="flex items-center gap-2">
                                    <User class="h-4 w-4" />
                                    Member
                                </div>
                            </SelectItem>
                            <SelectItem value="admin">
                                <div class="flex items-center gap-2">
                                    <Shield class="h-4 w-4" />
                                    Admin
                                </div>
                            </SelectItem>
                            <SelectItem value="owner">
                                <div class="flex items-center gap-2">
                                    <Crown class="h-4 w-4" />
                                    Owner
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click.prevent="performRoleUpdate">
                        Update Role
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
