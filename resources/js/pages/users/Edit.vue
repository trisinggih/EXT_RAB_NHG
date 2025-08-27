<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';

interface User {
    id: number;
    name: string;
    jabatan: string;
    role_id: number;
    email: string;
    password: string;
}


const props = defineProps<{
  user: User;
  roles: Array<{ id: number; role: string }>; 
}>()

const roles = computed(() => props.roles)



// our form data.
const form = useForm({
    name: props.user.name,
    jabatan: props.user.jabatan,
    role_id: props.user.role_id,
    email: props.user.email,
    password: props.user.password,
});

// this function is going to handle the user information submit.
const handleInput = () => {
    form.put(route('users.update', { user: props.user }));
};
</script>

<template>
    <Head title="Edit a user" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a user', href: `/users/${props.user.id}` }]">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Edit User
        <p class="text-white mt-1 text-sm font-normal">Kelola data user baru.</p>
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
                    <Label for="jabatan">Jabatan</Label>
                    <Input v-model="form.jabatan" id="jabatan" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.jabatan">{{ form.errors.jabatan }}</div>
                </div>

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

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input v-model="form.email" id="email" type="email" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.email">{{ form.errors.email }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="password">Password</Label>
                    <Input v-model="form.password" id="password" type="password" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.password">{{ form.errors.password }}</div>
                </div>


                <Button type="submit" :disabled="form.processing"> Edit User  </Button>
            </form>
        </div>
    </AppLayout>
</template>
