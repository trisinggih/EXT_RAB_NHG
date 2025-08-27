<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label";
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Create a pekerjaan',
    href: '/pekerjaans/create',
  },
];

// Form
const form = useForm({
  name: '',
  description: '',
});

const handleSubmit = () => {
  form.post(route('pekerjaans.store'));
};
</script>

<template>
  <Head title="Create a pekerjaan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Create Pekerjaan
        <p class="text-white mt-1 text-sm font-normal">Tambah data pekerjaan baru.</p>
      </h1>
    </div>

    <div class="p-4">
      <form class="w-8/12 space-y-4" @submit.prevent="handleSubmit">
        <!-- Name -->
        <div class="space-y-2">
          <Label for="name">Nama Pekerjaan</Label>
          <Input
            v-model="form.name"
            id="name"
            type="text"
            placeholder="Masukkan nama pekerjaan..."
          />
          <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <Label for="description">Deskripsi</Label>
          <Textarea
            v-model="form.description"
            id="description"
            placeholder="Masukkan deskripsi pekerjaan..."
          />
          <div class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description }}</div>
        </div>

        <!-- Submit -->
        <Button type="submit" :disabled="form.processing">
          Tambah Pekerjaan
        </Button>
      </form>
    </div>
  </AppLayout>
</template>
