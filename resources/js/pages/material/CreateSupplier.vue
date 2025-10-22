<script setup lang="ts">
import SupplierLayout from '@/layouts/SupplierLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label";
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Create a material',
    href: '/materials/create',
  },
];

// Form
const form = useForm({
  name: '',
  satuan: '',
});

const handleSubmit = () => {
  form.post(route('supplier.materials.store'));
};
</script>

<template>
  <Head title="Create a material" />

  <SupplierLayout :breadcrumbs="breadcrumbs">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Create Material
        <p class="text-white mt-1 text-sm font-normal">Tambah data material baru.</p>
      </h1>
    </div>

    <div class="p-4">
      <form class="w-8/12 space-y-4" @submit.prevent="handleSubmit">
        <!-- Name -->
        <div class="space-y-2">
          <Label for="name">Nama Material</Label>
          <Input
            v-model="form.name"
            id="name"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
        </div>


        <div class="space-y-2">
          <Label for="name">Satuan</Label>
          <Input
            v-model="form.satuan"
            id="satuan"
            type="text"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.satuan">{{ form.errors.satuan }}</div>
        </div>

      

        <!-- Submit -->
        <Button type="submit" :disabled="form.processing">
          Tambah Material
        </Button>
      </form>
    </div>
  </SupplierLayout>
</template>
