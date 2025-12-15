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
import hr from '@/routes/hr';
import developer from '@/routes/developer';
import admin from '@/routes/admin';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Briefcase, Users, FileText, Building2, Code } from 'lucide-vue-next';
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
                href: hr.positions.index().url,
                icon: Briefcase,
            },
            {
                title: 'Applications',
                href: hr.applications.index().url,
                icon: FileText,
            }
        );
    }

    // Developer-specific navigation
    if (user.value?.role === 'developer') {
        items.push(
            {
                title: 'My Applications',
                href: developer.applications.index().url,
                icon: FileText,
            },
            {
                title: 'Profile',
                href: developer.profile.edit().url,
                icon: Users,
            }
        );
    }

    // Admin-specific navigation
    if (user.value?.role === 'admin') {
        items.push(
            {
                title: 'Positions',
                href: admin.positions.index().url,
                icon: Briefcase,
            },
            {
                title: 'Users',
                href: admin.users.index().url,
                icon: Users,
            },
            {
                title: 'Companies',
                href: admin.companies.index().url,
                icon: Building2,
            },
            {
                title: 'Technologies',
                href: admin.technologies.index().url,
                icon: Code,
            },
            {
                title: 'Applications',
                href: admin.applications.index().url,
                icon: FileText,
            }
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [];
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
