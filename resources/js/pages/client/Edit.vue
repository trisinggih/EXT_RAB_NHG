<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';

interface Client {
    id: number;
    name: string;
    telp: string;
    address: string;
}


const props = defineProps<{
  client: Client;
}>()


const form = useForm({
    name: props.client.name,
    telp: props.client.telp,
    address: props.client.address,
});

// this function is going to handle the user information submit.
const handleInput = () => {
    form.put(route('clients.update', { client: props.client }));
};
</script>

<template>
    <Head title="Edit a client" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a client', href: `/clients/${props.client.id}` }]">
        <div class="mb-6 ">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
                Edit Client
                <p class="text-white mt-1 text-sm font-normal">Kelola daftar client Anda.</p>
            </h1>
        
        </div>
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
                <div class="space-y-2">
                    <Label for="client">Name</Label>
                    <Input v-model="form.name" id="client" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="telp">Telp</Label>
                    <Input v-model="form.telp" id="telp" type="number" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.telp">{{ form.errors.telp }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="address">Address</Label>
                    <Input v-model="form.address" id="address" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.address">{{ form.errors.address }}</div>
                </div>
                

                <Button type="submit" :disabled="form.processing"> Edit Client  </Button>
            </form>
        </div>
    </AppLayout>
</template>
