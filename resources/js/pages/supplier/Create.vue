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
    title: 'Create a supplier',
    href: '/clients/create',
  },
];

// Form
const form = useForm({
  name: '',
  telp: '',
  address: '',
  pic: '',
  email: '',
  password: ''
});

const handleSubmit = () => {
  form.post(route('suppliers.store'));
};
</script>

<template>
  <Head title="Create a supplier" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Create Supplier
        <p class="text-white mt-1 text-sm font-normal">Tambah data supplier baru.</p>
      </h1>
    </div>

    <div class="p-4">
      <form class="w-8/12 space-y-4" @submit.prevent="handleSubmit">
        <!-- Client Name -->
        <div class="space-y-2">
          <Label for="name">Nama Supplier</Label>
          <Input
            v-model="form.name"
            id="name"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
        </div>

        <!-- Telephone -->
        <div class="space-y-2">
          <Label for="telp">Phone Number</Label>
          <Input
            v-model="form.telp"
            id="telp"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.telp">{{ form.errors.telp }}</div>
        </div>

        <div class="space-y-2">
          <Label for="telp">PIC</Label>
          <Input
            v-model="form.pic"
            id="pic"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.pic">{{ form.errors.pic }}</div>
        </div>


        <!-- Address -->
        <div class="space-y-2">
          <Label for="address">Address</Label>
          <Textarea
            v-model="form.address"
            id="address"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.address">{{ form.errors.address }}</div>
        </div>

        <div class="space-y-2">
          <Label for="telp">Email</Label>
          <Input
            v-model="form.email"
            id="email"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.email">{{ form.errors.email }}</div>
        </div>

        <div class="space-y-2">
          <Label for="telp">Password</Label>
          <Input
            v-model="form.password"
            id="password"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.password">{{ form.errors.password }}</div>
        </div>

        <!-- Submit -->
        <Button type="submit" :disabled="form.processing">
          Add Supplier
        </Button>
      </form>
    </div>
  </AppLayout>
</template>
