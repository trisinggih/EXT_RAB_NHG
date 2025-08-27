<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Material {
    id: number;
    name: string;
    jumlah: number;
    satuan: string;
    panjang: number;
    lebar: number;
    tinggi: number;
    estimasi_price: number;
}

const props = defineProps<{ material: Material }>();

// our form data.
const form = useForm({
    name: props.material.name,
    jumlah: props.material.jumlah,
    satuan: props.material.satuan,
    panjang: props.material.panjang,
    lebar: props.material.lebar,
    tinggi: props.material.tinggi,
    estimasi_price: props.material.estimasi_price,
});

// this function is going to handle the user information submit.
const handleInput = () => {
    // we route to the products.store route with the form's data to create a new product inside the database.
    form.put(route('materials.update', { material: props.material }));
};
</script>

<template>
    <Head title="Edit a material" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a material', href: `/materials/${props.material.id}` }]">
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

        <div class="space-y-2">
          <Label for="name">Jumlah</Label>
          <Input
            v-model="form.jumlah"
            id="jumlah"
            type="number"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.jumlah">{{ form.errors.jumlah }}</div>
        </div>

        <div class="space-y-2">
          <Label for="name">Panjang</Label>
          <Input
            v-model="form.panjang"
            id="panjang"
            type="number"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.panjang">{{ form.errors.panjang }}</div>
        </div>

        <div class="space-y-2">
          <Label for="name">Lebar</Label>
          <Input
            v-model="form.lebar"
            id="lebar"
            type="number"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.lebar">{{ form.errors.lebar }}</div>
        </div>

        <div class="space-y-2">
          <Label for="name">Tinggi</Label>
          <Input
            v-model="form.tinggi"
            id="tinggi"
            type="number"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.tinggi">{{ form.errors.tinggi }}</div>
        </div>

        <div class="space-y-2">
          <Label for="name">Estimasi Price</Label>
          <Input
            v-model="form.estimasi_price"
            id="estimasi_price"
            type="number"
            placeholder=""
          />
          <div class="text-sm text-red-600" v-if="form.errors.estimasi_price">{{ form.errors.estimasi_price }}</div>
        </div>


                <Button type="submit" :disabled="form.processing"> Edit Pekerjaan </Button>
            </form>
        </div>
    </AppLayout>
</template>
