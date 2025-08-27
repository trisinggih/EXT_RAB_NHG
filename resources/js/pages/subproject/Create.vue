<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label"
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create a product',
        href: '/products/create',
    },
];

// our form data.
const form = useForm({
    name: '',
    price: 0,
    description: '',
});

// this function is going to handle the user information submit.
const handleInput = () => {
    // we route to the products.store route with the form's data to create a new product inside the database.
    form.post(route('products.store'));
}


</script>

<template>
    <Head title="Create a product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input v-model="form.name" id="name" type="text" placeholder="Enter the name for the product..."></Input>
                    <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="price">Price</Label>
                    <Input v-model="form.price" id="price" type="number" placeholder="Enter the price for the product..."></Input>
                    <div class="text-sm text-red-600" v-if="form.errors.price">{{ form.errors.price }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea v-model="form.description" id="description" type="text" placeholder="Enter the description for the product..."/>
                    <div class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description }}</div>
                </div>

                <Button type="submit" :disabled="form.processing">
                    Add product
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
