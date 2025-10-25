<script setup lang="ts">
import { ref } from 'vue'
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'

// ✅ Tambahkan prop baru: isCollapsed
defineProps<{
  items: NavItem[]
  isCollapsed?: boolean
}>()

const page = usePage()
const opened = ref<string | null>(null)

function toggleMenu(title: string) {
  opened.value = opened.value === title ? null : title
}
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel></SidebarGroupLabel>

    <SidebarMenu>
      <template v-for="item in items" :key="item.title">
        <!-- 🔹 Item tanpa anak -->
        <SidebarMenuItem v-if="!item.children">
          <SidebarMenuButton
            as-child
            :is-active="item.href === page.url"
            :tooltip="isCollapsed ? item.title : undefined"
          >
            <Link :href="item.href" class="flex items-center gap-2">
              <component :is="item.icon" class="w-5 h-5" />
              <!-- hanya tampil jika sidebar tidak collapse -->
              <span v-if="!isCollapsed">{{ item.title }}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>

        <!-- 🔹 Item dengan anak -->
        <SidebarMenuItem
          v-else
          :has-child="true"
          :is-active="item.children.some(child => child.href === page.url)"
        >
          <SidebarMenuButton
            :tooltip="isCollapsed ? item.title : undefined"
            @click="toggleMenu(item.title)"
            class="flex items-center gap-2"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span v-if="!isCollapsed">{{ item.title }}</span>
          </SidebarMenuButton>

          <!-- Submenu -->
          <SidebarMenu
            v-show="opened === item.title && !isCollapsed"
            class="pl-6 transition-all duration-200 ease-in-out overflow-hidden"
          >
            <SidebarMenuItem v-for="child in item.children" :key="child.title">
              <SidebarMenuButton
                as-child
                :is-active="child.href === page.url"
                :tooltip="isCollapsed ? child.title : undefined"
              >
                <Link :href="child.href" class="flex items-center gap-2">
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
