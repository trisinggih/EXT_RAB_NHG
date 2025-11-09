<script setup lang="ts">
import SupplierLayout from '@/layouts/SupplierLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';

const props = defineProps({
  material: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Material', href: '/material' },
];

// ✅ Search input dengan debounce
const searchInput = ref(props.filters.search || '');
let timer: ReturnType<typeof setTimeout>;

watch(searchInput, (v) => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(route('supplier.materials.indexSup'), { search: v }, { preserveScroll: true, replace: true });
  }, 400);
});

// Hapus pencarian
const clearSearch = () => {
  searchInput.value = '';
  router.get(route('supplier.materials.indexSup'), {}, { preserveScroll: true, replace: true });
};


const handlePekerjaanDeletion = (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
    router.delete(route('supplier.materials.destroy', { id }), {
      preserveScroll: true,
      onSuccess: () => {
        alert('Data berhasil dihapus.');
      },
      onError: (err) => {
        console.error('Gagal menghapus:', err);
        alert('Terjadi kesalahan saat menghapus data.');
      },
    });
  }
};

// Ganti halaman
const changePage = (url: string | null) => {
  if (url) router.visit(url, { preserveScroll: true });
};
</script>

<template>
  <Head title="Material" />
  <SupplierLayout :breadcrumbs="breadcrumbs">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Material List
        <p class="text-white mt-1 text-sm font-normal">Kelola daftar material Anda.</p>
      </h1>
    </div>

    <div class="p-6 space-y-4">

      <!-- Controls -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <Link :href="route('supplier.materials.create')">
            <Button class="bg-primary text-white">Tambah Material</Button>
          </Link>
        </div>

        <div class="relative">
          <input
            v-model="searchInput"
            type="text"
            placeholder="Cari nama atau satuan..."
            class="border border-gray-300 rounded-lg pl-9 pr-8 py-2 text-sm w-72 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
          />
          <!-- search icon -->
          <svg
            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 opacity-60"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
          <button
            v-if="searchInput"
            @click="clearSearch"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            aria-label="Clear search"
          >
            ×
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-auto rounded border border-gray-200 shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 sticky top-0 z-10">
            <tr class="text-gray-700">
              <th class="px-4 py-3 border-b border-gray-200 text-left">No</th>
              <th class="px-4 py-3 border-b border-gray-200 text-left">Name</th>
              <th class="px-4 py-3 border-b border-gray-200 text-left">Satuan</th>
              <th class="px-4 py-3 border-b border-gray-200 text-left w-[100px]">Action</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(u, i) in props.material.data"
              :key="u.id"
              class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition-colors"
            >
              <td class="px-4 py-3 border-b border-gray-200 text-center w-[60px]">
                {{ i + 1 + ((props.material.current_page - 1) * props.material.per_page) }}
              </td>
              <td class="px-4 py-3 border-b border-gray-200 font-medium text-gray-800">{{ u.name }}</td>
              <td class="px-4 py-3 border-b border-gray-200">{{ u.satuan ?? '-' }}</td>

              <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap">
                <div class="flex gap-1">
                  <Link :href="route('supplier.materials.edit', { id: u.id })">
                    <Button class="bg-yellow-500 text-white">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" />
                      </svg>
                    </Button>
                  </Link>
                  <Button class="bg-red-600 text-white" @click="handlePekerjaanDeletion(u.id)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M6 7h12v2H6zm2 3h8v9H8zm3-6h2v2h-2z" />
                    </svg>
                  </Button>
                </div>
              </td>
            </tr>

            <tr v-if="props.material.data.length === 0">
              <td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada data yang cocok.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex justify-between items-center text-sm">
        <div class="text-gray-500">
          Menampilkan
          <strong>
            {{ props.material.from || 0 }}–{{ props.material.to || 0 }}
          </strong>
          dari {{ props.material.total }} data
        </div>

        <div class="inline-flex overflow-hidden rounded-xl border border-gray-200">
          <button
            v-for="link in props.material.links"
            :key="link.label"
            v-html="link.label"
            @click="changePage(link.url)"
            :disabled="!link.url"
            :class="[
              'px-3 py-1 border-l border-gray-200',
              link.active ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
              !link.url ? 'opacity-50 cursor-not-allowed' : ''
            ]"
          />
        </div>
      </div>

    </div>
  </SupplierLayout>
</template>
