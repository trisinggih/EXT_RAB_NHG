<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Projects {
    id: number;
    name: string;
    client_id: number;
    description?: string;
    start_date: string;
    end_date: string;
}

const props = defineProps<{
  projects: Projects;
  clients: Array<{ id: number; name: string }>; 
}>()

const clients = computed(() => props.clients)

// our form data.
const form = useForm({
    name: props.projects.name,
    client_id: props.projects.client_id,
    description: props.projects.description,
    start_date: props.projects.start_date,
    end_date: props.projects.end_date,
});

// this function is going to handle the user information submit.
const handleInput = () => {
    form.put(route('projects.update', { project: props.projects }
    ));
};
</script>

<template>
    <Head title="Edit a project" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a project', href: `/projects/${props.projects.id}` }]">
        <div class="mb-6">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
                Edit Project
                <p class="text-white mt-1 text-sm font-normal">Kelola daftar project Anda.</p>
            </h1>
        </div>
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input v-model="form.name" id="name" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="price">Client</Label>
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
                    <Textarea v-model="form.description" id="description" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description }}</div>
                </div>

                <Button type="submit" :disabled="form.processing"> Edit Project </Button>
            </form>
        </div>
    </AppLayout>
</template>
