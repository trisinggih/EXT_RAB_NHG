<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUserClient from '@/components/NavUserClient.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu,  SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {  FileBadge, Folder, HandCoins, LayoutGrid, MonitorCog,  Settings, } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user as User;

const client_id = localStorage.getItem("id");
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/clientdashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Projects',
        href: '/client/projects',
        icon: FileBadge,
    },

  
];


</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                        <Link :href="route('dashboard')">
                            <div class="mb-1 flex w-[100px] items-center justify-center rounded-md">
                                <AppLogo class=" fill-current" />
                            </div>
                        </Link>
                    
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUserClient />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
