<script setup lang="ts">
import { ref } from 'vue';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{ items: NavItem[] }>();
const page = usePage();
const opened = ref<string | null>(null);

function toggleMenu(title: string) {
  opened.value = opened.value === title ? null : title;
}
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel></SidebarGroupLabel>
    <SidebarMenu>
      <template v-for="item in items" :key="item.title">
        <!-- Tanpa Child -->
        <SidebarMenuItem v-if="!item.children">
          <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
            <Link :href="item.href">
              <component :is="item.icon" />
              <span>{{ item.title }}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>

        <!-- Dengan Child -->
        <SidebarMenuItem
          v-else
          :has-child="true"
          :is-active="item.children.some(child => child.href === page.url)"
        >
          <SidebarMenuButton :tooltip="item.title" @click="toggleMenu(item.title)">
            <component :is="item.icon" />
            <span>{{ item.title }}</span>
          </SidebarMenuButton>

          <!-- Submenu relatif, mendorong ke bawah -->
          <SidebarMenu
            v-show="opened === item.title"
            class="pl-6 transition-all duration-200 ease-in-out overflow-hidden"
          >
            <SidebarMenuItem v-for="child in item.children" :key="child.title">
              <SidebarMenuButton as-child :is-active="child.href === page.url" :tooltip="child.title">
                <Link :href="child.href">
                  <span>{{ child.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarMenuItem>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>
