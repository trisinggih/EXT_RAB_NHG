<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import SupplierLayout from '@/layouts/SupplierLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Material {
    id: number;
    name: string;
    satuan: string;
}

const props = defineProps<{ material: Material }>();

// our form data.
const form = useForm({
    name: props.material.name,
    satuan: props.material.satuan,
});

const handleInput = () => {
    form.put(route('supplier.materials.update', { material: props.material }));
};
</script>

<template>
    <Head title="Edit a material" />

    <SupplierLayout :breadcrumbs="[{ title: 'Edit a material', href: `/materials/${props.material.id}` }]">
        <div class="mb-6">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
            Edit Material
            <p class="text-white mt-1 text-sm font-normal">Tambah data material baru.</p>
        </h1>
        </div>
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
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



                <Button type="submit" :disabled="form.processing"> Edit Pekerjaan </Button>
            </form>
        </div>
    </SupplierLayout>
</template>
