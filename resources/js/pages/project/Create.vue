<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label"
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { Textarea } from '@/components/ui/textarea';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create a project',
        href: '/projects/create',
    },
];


const props = defineProps({
  clients: { type: Array, default: () => [] }
});

const clients = computed(() => props.clients);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
  name: '',
  price: 0,
  description: '',
  client_id: '',
  start_date: today, 
  end_date: today,   
});

const handleInput = () => {
  form.post(route('projects.store'));
};

</script>

<template>
    <Head title="Create a project" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mb-6">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
                Create Project
                <p class="text-white mt-1 text-sm font-normal">Kelola daftar project Anda.</p>
            </h1>
        </div>
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input v-model="form.name" id="name" type="text" placeholder=""></Input>
                    <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="price">Client</Label>
                    <!-- <Input v-model="form.client_id" id="client_id" type="number" placeholder="Enter the price for the product..."></Input> -->
                     <select v-model="form.client_id" class="border rounded px-3 py-2 w-full">
                      <option disabled value="">Select a client</option>
                      <option v-for="cl in clients" :key="cl.id" :value="String(cl.id)">
                        {{ cl.name }}
                      </option>
                    </select>
                    <div class="text-sm text-red-600" v-if="form.errors.client_id">{{ form.errors.client_id }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea v-model="form.description" id="description" type="text" placeholder="Enter the description for the product..."/>
                    <div class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description }}</div>
                </div>

                <Button type="submit" :disabled="form.processing">
                    Add Project
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
