<script setup lang="ts">
import SupplierLayout from '@/layouts/app/AppSidebarSupLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <SupplierLayout :breadcrumbs="breadcrumbs">
        <slot />
    </SupplierLayout>
</template>
