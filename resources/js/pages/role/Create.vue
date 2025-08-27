<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label";
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Create a role',
    href: '/roles/create',
  },
];

const form = useForm({
  role: '',
  description: '',
});

const handleSubmit = () => {
  form.post(route('roles.store'));
};
</script>

<template>
  <Head title="Create a role" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mb-6 ">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
                Create Role
                <p class="text-white mt-1 text-sm font-normal">Kelola daftar role Anda.</p>
            </h1>
        
    </div>
    <div class="p-4">
      <form class="w-8/12 space-y-4" @submit.prevent="handleSubmit">
        <div class="space-y-2">
          <Label for="role">Role</Label>
          <Input
            v-model="form.role"
            id="role"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.role">{{ form.errors.role }}</div>
        </div>

        <div class="space-y-2">
          <Label for="description">Description</Label>
          <Textarea
            v-model="form.description"
            id="description"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description }}</div>
        </div>

        <Button type="submit" :disabled="form.processing">
          Add role
        </Button>
      </form>
    </div>
  </AppLayout>
</template>
