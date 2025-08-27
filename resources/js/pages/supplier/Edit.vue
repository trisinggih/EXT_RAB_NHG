<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';

interface Supplier {
    id: number;
    name: string;
    telp: string;
    address: string;
    pic: string;
}


const props = defineProps<{
  supplier: Supplier;
}>()


const form = useForm({
    name: props.supplier.name,
    telp: props.supplier.telp,
    pic: props.supplier.pic,
    address: props.supplier.address,
});

// this function is going to handle the user information submit.
const handleInput = () => {
    form.put(route('suppliers.update', { supplier: props.supplier }));
};

</script>

<template>
    <Head title="Edit a supplier" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a supplier', href: `/suppliers/${props.supplier.id}` }]">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Edit Supplier
        <p class="text-white mt-1 text-sm font-normal">Tambah data supplier baru.</p>
      </h1>
    </div>
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
                <div class="space-y-2">
                    <Label for="client">Name</Label>
                    <Input v-model="form.name" id="name" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="telp">Telp</Label>
                    <Input v-model="form.telp" id="telp" type="number" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.telp">{{ form.errors.telp }}</div>
                </div>

                 <div class="space-y-2">
                    <Label for="telp">PIC</Label>
                    <Input v-model="form.pic" id="pic" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.pic">{{ form.errors.pic }}</div>
                </div>

                <div class="space-y-2">
                    <Label for="address">Address</Label>
                    <Input v-model="form.address" id="address" type="text" placeholder="" />
                    <div class="text-sm text-red-600" v-if="form.errors.address">{{ form.errors.address }}</div>
                </div>
                

                <Button type="submit" :disabled="form.processing"> Edit Supplier  </Button>
            </form>
        </div>
    </AppLayout>
</template>
