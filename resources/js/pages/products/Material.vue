<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref, computed, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
import Modal from '@/components/Modal.vue';

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

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Products',
    href: '/products',
  },
];

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

const clearSearch = () => {
  searchInput.value = '';
  search.value = '';
};

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
                Edit Material
                <p class="text-white mt-1 text-sm font-normal">Edit data material.</p>
            </h1>
        </div>
        <div class="p-4">

                <div class="w-full h-auto rounded bg-blue-100 mb-4 p-3">
                        <p>{{ props.product.name }} <span class="float-right">Rp.{{ props.product.price }}</span></p>
                </div>

                <div class="w-full h-auto  mb-4 px-0 py-1">
                    <button class="p-2 bg-blue-400 rounded font-semibold text-white" @click="openModal">Add Material</button>
                </div>
                 <!-- Table -->
                <div class="overflow-auto rounded border border-gray-200 shadow-sm">
                    <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr class="text-gray-700">
                        <th class="px-4 py-3 border-b border-gray-200 text-left">No</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-left">Material</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-left">Ukuran</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-left">Jumlah</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-left">Satuan</th>
                        <th class="px-4 py-3 border-b border-gray-200 text-left w-[100px]">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                        v-for="(u,i) in pageItems"
                        :key="u.id"
                        class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition-colors"
                        >
                        <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap">{{ startIndex + i + 1 }}</td>
                        <td class="px-4 py-3 border-b border-gray-200 font-medium text-gray-800">{{ u.name }}</td>
                        <td class="px-4 py-3 border-b border-gray-200 font-medium text-gray-800">{{ u.panjang }} p x {{ u.lebar }} l x {{ u.tinggi }} t</td>
                        <td class="px-4 py-3 border-b border-gray-200">{{ u.jumlah }}</td>
                        <td class="px-4 py-3 border-b border-gray-200">{{ u.satuan   ?? '-' }}</td>
                        <td class="px-4 py-3 border-b border-gray-200">
                            <div class="flex gap-1">
        
                            <Button class="bg-red-600 text-white" @click="handleProductMaterialDeletion(u.id)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor">
                                <path d="M6 7h12v2H6zm2 3h8v9H8zm3-6h2v2h-2z" />
                                </svg>
                            </Button>
                            </div>
                        </td>
                        </tr>
                        <tr v-if="pageItems.length === 0">
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada data yang cocok.</td>
                        </tr>
                    </tbody>
                    </table>
                </div>



        </div>

        <Modal
                        :title="'Modal Title'"
                        :content="'This is the content of the modal.'"
                        :isVisible="isModalVisible"
                        @update:isVisible="isModalVisible = $event"
                    />

    </AppLayout>
</template>
