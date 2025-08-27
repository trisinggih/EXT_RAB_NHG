<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu,  SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {  FileBadge, Folder, HandCoins, LayoutGrid, MonitorCog,  Settings, } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Master Data',
        href: '#',
        icon: Settings,
        children: [
            {
                title: 'Users',
                href: '/users',
            },
            {
                title: 'Roles',
                href: '/roles',
            },
             {
                title: 'Clients',
                href: '/clients',
            },
             {
                title: 'Suppliers',
                href: '/suppliers',
            },
        ],  
    },
    {
        title: 'Front',
        href: '#',
        icon: MonitorCog,
        children: [
            {
                title: 'Banners',
                href: '/banners',
            },
            {
                title: 'Blogs',
                href: '/blogs',
            }
        ],  
    },
    {
        title: 'Referensi',
        href: '#',
        icon: Folder,
        children: [
            {
                title: 'Pekerjaan',
                href: '/pekerjaan',
            },
            {
                title: 'Produk',
                href: '/products',
            },
            {
                title: 'Material',
                href: '/materials',
            }
        ],  
    },
    {
        title: 'Projects',
        href: '/projects',
        icon: FileBadge ,
    },
    {
        title: 'Anggaran',
        href: '/anggaran',
        icon: HandCoins ,
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
