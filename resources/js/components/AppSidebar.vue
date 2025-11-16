<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Briefcase, Users, FileText, Settings as SettingsIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Main navigation items based on user role
const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // HR-specific navigation
    if (user.value?.role === 'hr') {
        items.push(
            {
                title: 'Positions',
                href: '/hr/positions',
                icon: Briefcase,
            },
            {
                title: 'Applications',
                href: route('hr.applications.index'),
                icon: FileText,
            }
        );
    }

    // Developer-specific navigation
    if (user.value?.role === 'developer') {
        items.push(
            {
                title: 'My Applications',
                href: route('developer.applications.index'),
                icon: FileText,
            },
            {
                title: 'Profile',
                href: route('developer.profile.edit'),
                icon: Users,
            }
        );
    }

    // Admin-specific navigation
    if (user.value?.role === 'admin') {
        items.push(
            {
                title: 'Manage Positions',
                href: route('admin.positions.index'),
                icon: Briefcase,
            },
            {
                title: 'Settings',
                href: route('settings.profile'),
                icon: SettingsIcon,
            }
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
