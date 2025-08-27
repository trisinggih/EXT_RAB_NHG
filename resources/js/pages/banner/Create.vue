<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label";
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Create a banner',
    href: '/banners/create',
  },
];

// Form
const form = useForm({
  title_banner: '',
  img_banner: null,       // file object
  link_banner: '',
});

// Submit handler
const handleSubmit = () => {
  form.post(route('banners.store'), {
    forceFormData: true, // penting agar file dikirim sebagai multipart/form-data
  });
};
</script>

<template>
  <Head title="Create a banner" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Create Banner
        <p class="text-white mt-1 text-sm font-normal">Kelola banner promosi Anda.</p>
      </h1>
    </div>

    <div class="p-4">
      <form class="w-8/12 space-y-4" @submit.prevent="handleSubmit">
        <!-- Title -->
        <div class="space-y-2">
          <Label for="title_banner">Title (Optional)</Label>
          <Input
            v-model="form.title_banner"
            id="title_banner"
            type="text"
            placeholder="Enter banner title..."
          />
          <div class="text-sm text-red-600" v-if="form.errors.title_banner">{{ form.errors.title_banner }}</div>
        </div>

        <!-- Image Upload -->
        <div class="space-y-2">
          <Label for="img_banner">Banner Image</Label>
          <Input
            id="img_banner"
            type="file"
            accept="image/*"
            @change="(e) => form.img_banner = e.target.files[0]"
          />
          <div class="text-sm text-red-600" v-if="form.errors.img_banner">{{ form.errors.img_banner }}</div>
        </div>

        <!-- Link -->
        <div class="space-y-2">
          <Label for="link_banner">Banner Link</Label>
          <Input
            v-model="form.link_banner"
            id="link_banner"
            type="text"
            placeholder="Enter banner link (e.g., https://example.com)..."
          />
          <div class="text-sm text-red-600" v-if="form.errors.link_banner">{{ form.errors.link_banner }}</div>
        </div>

        <!-- Submit -->
        <Button type="submit" :disabled="form.processing">
          Add Banner
        </Button>
      </form>
    </div>
  </AppLayout>
</template>
