<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarClient from '@/components/AppSidebarClient.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebarClient />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <!-- <AppSidebarHeader :breadcrumbs="breadcrumbs" /> -->
            <slot />
        </AppContent>
    </AppShell>
</template>
