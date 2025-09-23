<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref, computed, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
// import Modal from '@/components/Modal.vue';

interface Material {
  id: number;
  name: string;
  satuan: string;
  jumlah: number;
  panjang: number;
  lebar: number;
  tinggi: number;    
  estimasi_price: number;
}

interface Product {
  id: number;
  name: string;
  price: number;
  description?: string;
}

const props = defineProps<{
  material: Material[];
  product: Product;
}>();

const material = ref<Material[]>(props.material ?? []);

const source = computed(() => material.value);

const handleProductMaterialDeletion = (productId: number) => {
  if (confirm('Are you sure you want to delete this product?')) {
    router.delete(route('productmaterial.destroy', { id: productId }));
  }
};

// State untuk modal
const isModalVisible = ref(false);
const openModal = () => {
  isModalVisible.value = true;
};

// Debounced search
const searchInput = ref('');
const search = ref('');
let timer: ReturnType<typeof setTimeout>;

watch(
  () => searchInput.value,
  (v) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      search.value = v.trim();
      page.value = 1;
    }, 300);
  }
);


const pageSize = ref(10);
const page = ref(1);

const norm = (s: string | number | null | undefined) => (s ?? '').toString().toLowerCase();

const filtered = computed(() => {
  if (!search.value) return source.value;
  const q = norm(search.value);
  return source.value.filter((u) => {
    const hay = `${norm(u.id)} ${norm(u.name)} `;
    return hay.includes(q);
  });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / pageSize.value)));

watch([pageSize, filtered], () => {
  if (page.value > totalPages.value) page.value = totalPages.value;
});

const pageItems = computed(() => {
  const start = (page.value - 1) * pageSize.value;
  return filtered.value.slice(start, start + pageSize.value);
});

const startIndex = computed(() => Math.min(filtered.value.length, (page.value - 1) * pageSize.value));
const endIndex = computed(() => Math.min(filtered.value.length, startIndex.value + pageSize.value));
</script>

<template>
    <Head title="Edit a product" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a product', href: `/products` }]">
        <div class="mb-6">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
                Detail Bill of Material
                <p class="text-white mt-1 text-sm font-normal">Perhitungan estimasi pekerjaan <b>{{ props.product.name }}</b> <h5 class="float-right font-bold">Rp.{{ props.product.price }}</h5></p>
            </h1>
        </div>
        <div class="px-4">
                <div class="w-full h-auto  mb-4 px-0 py-1">
                    <button class="p-2 bg-blue-400 rounded font-semibold text-white" @click="openModal">Tambah Pekerjaan</button>
                </div>
                 <!-- Table -->
                <div class="overflow-auto rounded border border-gray-200 shadow-sm">
                    <table class="w-full text-sm">
                    <tbody>
                        <tr
                        v-for="(u,i) in pageItems"
                        :key="u.id"
                        class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition-colors"
                        >
                        <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap w-[50px]">{{ startIndex + i + 1 }}</td>
                        <td class="px-4 py-3 border-b border-gray-200 font-medium text-gray-800">{{ u.name }}</td>
    
                        <td class="px-4 py-3 border-b border-gray-200 w-[100px]">
                            <div class="flex gap-1">
        
                            <Button class="bg-red-600 text-white" @click="handleProductMaterialDeletion(u.id)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor">
                                <path d="M6 7h12v2H6zm2 3h8v9H8zm3-6h2v2h-2z" />
                                </svg>
                            </Button>
                            </div>
                        </td>
                        </tr>
                  
                    </tbody>
                    </table>
                </div>



        </div>

        <Modal
            :title="'Tambah Pekerjaan'"
            :isVisible="isModalVisible"
            @update:isVisible="isModalVisible = $event"
        >
            <template #content>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama</label>
                        <input type="text" v-model="form.nama"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    </div>

                    <!-- Select Pekerjaan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pekerjaan</label>
                        <select v-model="form.job_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Pilih Pekerjaan --</option>
                            <template v-for="job in jobs" :key="job.id">
                                <option :value="job.id">{{ job.name }}</option>
                            </template>
                        </select>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Simpan
                        </button>
                    </div>
                </form>
            </template>
        </Modal>


    </AppLayout>
</template>
