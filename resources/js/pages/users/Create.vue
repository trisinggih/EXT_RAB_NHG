<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label";
import { Button } from '@/components/ui/button';
import { onMounted, ref, computed } from 'vue';

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Create a user',
    href: '/users/create',
  },
];

const props = defineProps({
  roles: { type: Array, default: () => [] }
})

const roles = computed(() => props.roles)

// Form
const form = useForm({
  name: '',
  jabatan: '',
  role_id: '',
  email: '',
  password: '',
});

const handleSubmit = () => {
  form.post(route('users.store'));
};
</script>

<template>
  <Head title="Create a user" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Create User
        <p class="text-white mt-1 text-sm font-normal">Kelola data user baru.</p>
      </h1>
    </div>

    <div class="p-4">
      <form class="w-8/12 space-y-4" @submit.prevent="handleSubmit">
        <!-- Name -->
        <div class="space-y-2">
          <Label for="name">Name</Label>
          <Input
            v-model="form.name"
            id="name"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
        </div>

        <!-- Jabatan -->
        <div class="space-y-2">
          <Label for="jabatan">Jabatan</Label>
          <Input
            v-model="form.jabatan"
            id="jabatan"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.jabatan">{{ form.errors.jabatan }}</div>
        </div>

        <!-- Role -->
        <div class="space-y-2">
          <Label for="role_id">Role</Label>
          <select v-model="form.role_id" class="border rounded px-3 py-2 w-full">
            <option disabled value="">Select a role</option>
            <option v-for="role in roles" :key="role.id" :value="String(role.id)">
              {{ role.role }}
            </option>
          </select>

          <div class="text-sm text-red-600" v-if="form.errors.role_id">{{ form.errors.role_id }}</div>
        </div>

        <!-- Email -->
        <div class="space-y-2">
          <Label for="email">Email</Label>
          <Input
            v-model="form.email"
            id="email"
            type="email"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.email">{{ form.errors.email }}</div>
        </div>

        <!-- Password -->
        <div class="space-y-2">
          <Label for="password">Password</Label>
          <Input
            v-model="form.password"
            id="password"
            type="password"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.password">{{ form.errors.password }}</div>
        </div>

        <!-- Submit -->
        <Button type="submit" :disabled="form.processing">
          Add User
        </Button>
      </form>
    </div>
  </AppLayout>
</template>
