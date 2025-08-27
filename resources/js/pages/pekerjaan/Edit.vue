<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Pekerjaan {
    id: number;
    name: string;
    description?: string;
}

const props = defineProps<{ pekerjaan: Pekerjaan }>();

// our form data.
const form = useForm({
    name: props.pekerjaan.name,
    description: props.pekerjaan.description,
});

// this function is going to handle the user information submit.
const handleInput = () => {
    // we route to the products.store route with the form's data to create a new product inside the database.
    form.put(route('pekerjaans.update', { pekerjaan: props.pekerjaan }));
};
</script>

<template>
    <Head title="Edit a pekerjaan" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a product', href: `/pekerjaans/${props.pekerjaan.id}` }]">
        <div class="mb-6">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
            Edit Pekerjaan
            <p class="text-white mt-1 text-sm font-normal">Tambah data pekerjaan baru.</p>
        </h1>
        </div>
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input v-model="form.name" id="name" type="text" placeholder="Enter the name for the product..." />
                    <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea v-model="form.description" id="description" type="text" placeholder="Enter the description for the product..." />
                    <div class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description }}</div>
                </div>

                <Button type="submit" :disabled="form.processing"> Edit Pekerjaan </Button>
            </form>
        </div>
    </AppLayout>
</template>
