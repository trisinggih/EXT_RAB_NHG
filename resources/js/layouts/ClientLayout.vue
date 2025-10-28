<script setup lang="ts">
import ClientLayout from '@/layouts/app/AppSidebarCliLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <ClientLayout :breadcrumbs="breadcrumbs">
        <slot />
    </ClientLayout>
</template>
