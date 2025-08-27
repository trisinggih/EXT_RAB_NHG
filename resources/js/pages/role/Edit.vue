<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Role {
    id: number;
    role: string;
    description?: string;
}

const props = defineProps<{ role: Role }>();

const form = useForm({
    role: props.role.role,
    description: props.role.description,
});


const handleInput = () => {
    form.put(route('roles.update', { role: props.role }));
};
</script>

<template>
    <Head title="Edit a role" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a role', href: `/roles/${props.role.id}` }]">
        <div class="mb-6 ">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
                Edit Role
                <p class="text-white mt-1 text-sm font-normal">Kelola daftar role Anda.</p>
            </h1>
        
        </div>
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
                <div class="space-y-2">
                    <Label for="name">Role</Label>
                    <Input v-model="form.role" id="name" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.role">{{ form.errors.role }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea v-model="form.description" id="description" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description }}</div>
                </div>

                <Button type="submit" :disabled="form.processing"> Edit product </Button>
            </form>
        </div>
    </AppLayout>
</template>
