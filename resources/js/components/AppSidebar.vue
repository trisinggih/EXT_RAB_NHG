<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu,  SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {  FileBadge, Folder, HandCoins, LayoutGrid, MonitorCog,  Settings, Menu, X } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { ref,computed } from "vue";
import { usePage } from '@inertiajs/vue3';

const page = usePage()
const user = page.props.auth.user as { role_id: number }

const isCollapsed = ref(false);

function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value;
}



const adminNavItems: NavItem[] = [
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
      { title: 'Users', href: '/users' },
      { title: 'Roles', href: '/roles' },
      { title: 'Clients', href: '/clients' },
      { title: 'Material', href: '/materials' },
      { title: 'Suppliers', href: '/suppliers' },
    ],
  },
  {
    title: 'Front',
    href: '#',
    icon: MonitorCog,
    children: [
      { title: 'Banners', href: '/banners' },
      { title: 'Blogs', href: '/blogs' },
    ],
  },
  {
    title: 'Referensi',
    href: '#',
    icon: Folder,
    children: [
      { title: 'Pekerjaan', href: '/pekerjaan' },
      { title: 'Production', href: '/bom' },
    ],
  },
  {
    title: 'Projects',
    href: '/projects',
    icon: FileBadge,
  },
  {
    title: 'Anggaran',
    href: '/anggaran',
    icon: HandCoins,
  },
]

// definisi menu untuk user biasa (misal role != 1)
const userNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
  },
//  {
//     title: 'Referensi',
//     href: '#',
//     icon: Folder,
//     children: [
//       { title: 'Pekerjaan', href: '/pekerjaan' },
//       { title: 'Production', href: '/bom' },
//     ],
//   },
  {
    title: 'Pekerjaan',
    href: '/pekerjaan',
    icon: Folder,
  },
  {
    title: 'Production',
    href: '/bom',
    icon: FileBadge,
  },
  // {
  //   title: 'Anggaran',
  //   href: '/anggaran',
  //   icon: HandCoins,
  // },
]

const engineerrNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'Projects',
    href: '/projects',
    icon: FileBadge,
  },
]


const mainNavItems = computed<NavItem[]>(() => {
  if (user.role_id == 1) {
    return adminNavItems
  } else if (user.role_id == 10) {
    return engineerrNavItems
  } else {
    return userNavItems
  }
})

</script>

<template>
  <div :class="[
        'flex',
        isCollapsed ? 'w-[80px]' : 'w-[260px]'
      ]">
    <!-- Tombol show/hide -->
    <button
      @click="toggleSidebar"
      :class="[
        'absolute top-4 z-50 bg-primary-500 text-black p-2 rounded-md hover:bg-primary-600 transition-all',
        isCollapsed ? 'left-[80px]' : 'left-[200px]'
      ]"
    >
      <component :is="isCollapsed ? X : Menu" class="w-5 h-5" />
    </button>

    <!-- Sidebar -->
    <Sidebar
      :class="[
        'transition-all duration-300',
        isCollapsed ? 'w-[80px]' : 'w-[260px]'
      ]"
      collapsible="icon"
      variant="inset"
    >
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <Link :href="route('dashboard')">
              <div
                class="mb-1 flex items-center justify-center rounded-md"
                :class="[isCollapsed ? 'w-[50px]' : 'w-[100px]']"
              >
                <AppLogo class="fill-current" />
              </div>
            </Link>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        <NavMain :items="mainNavItems" :is-collapsed="isCollapsed" />
      </SidebarContent>

      <SidebarFooter>
        <NavUser />
      </SidebarFooter>
    </Sidebar>

    <!-- Konten utama -->
    <div class="flex-1 p-5 transition-all duration-300" :class="{ 'ml-[0px]': isCollapsed, 'ml-[0px]': !isCollapsed }">
      <slot />
    </div>
  </div>
</template>

