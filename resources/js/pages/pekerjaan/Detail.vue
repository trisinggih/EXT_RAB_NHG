<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Pekerjaan {
  id: number;
  name: string;
  description?: string;
}

interface DetailPekerjaan {
  id: number;
  pekerjaan_id: number;
  name: string;
  satuan:string;
  jumlah: number;
  biaya: number;
  subtotal: number;
}

const props = defineProps<{
  pekerjaan: Pekerjaan;
  details: DetailPekerjaan[];
}>();

const form = useForm({
  pekerjaan_id: props.pekerjaan.id,
  name: '',
  satuan:'',
  jumlah: 0,
  biaya: 0,
  subtotal: 0,
});

const adding = ref(false);

// fungsi untuk format angka ribuan
const formatNumber = (val: number | string): string => {
  if (!val) return '';
  return new Intl.NumberFormat('id-ID').format(Number(val));
};

// ---- Input reactive dengan tampilan format angka ----
const displayJumlah = ref('');
const displayBiaya = ref('');
const displaySubtotal = ref('');

// Update nilai form saat user mengetik
watch(displayJumlah, (val) => {
  const numeric = Number(val.replace(/\D/g, '')) || 0;
  form.jumlah = numeric;
  displayJumlah.value = formatNumber(numeric);
});

watch(displayBiaya, (val) => {
  const numeric = Number(val.replace(/\D/g, '')) || 0;
  form.biaya = numeric;
  displayBiaya.value = formatNumber(numeric);
});

// Hitung subtotal otomatis setiap kali jumlah/biaya berubah
watch([() => form.jumlah, () => form.biaya], ([jumlah, biaya]) => {
  form.subtotal = jumlah * biaya;
  displaySubtotal.value = formatNumber(form.subtotal);
});

// Submit form
const submitMaterial = () => {
  adding.value = true;
  form.post(route('pekerjaan.details.store'), {
    onSuccess: () => {
      form.reset();
      displayJumlah.value = '';
      displayBiaya.value = '';
      displaySubtotal.value = '';
      form.pekerjaan_id = props.pekerjaan.id;
      router.reload({ only: ['details'] });
    },
    onFinish: () => {
      adding.value = false;
    },
  });
};

// Hapus data
const handleDetailDeletion = (id: number) => {
  if (confirm('Yakin ingin menghapus data ini?')) {
    router.delete(route('pekerjaan.details.destroy', { id }), {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['details'] });
      },
    });
  }
};
</script>

<template>
  <Head title="Detail Pekerjaan" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Daftar Pekerjaan', href: '/pekerjaan' },
      { title: props.pekerjaan.name, href: `/pekerjaan/${props.pekerjaan.id}` },
    ]"
  >
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Data Detail Pekerjaan
        <p class="text-white mt-1 text-sm font-normal">Informasi detail pekerjaan.</p>
      </h1>
    </div>

    <div class="p-6 bg-white rounded-lg shadow-md">
      <!-- Form Input -->
      <form @submit.prevent="submitMaterial" class="space-y-4 mb-6">
        <div class="grid grid-cols-2 gap-4">

            <div>
            <Label for="name" class="mb-1 block">Nama Pekerjaan</Label>
            <Input id="name" v-model="form.name" type="text" placeholder="Masukkan nama detail pekerjaan" />
            </div>

             <div>
            <Label for="satuan" class="mb-1 block">Satuan</Label>
            <Input id="satuan" v-model="form.satuan" type="text" placeholder="Masukkan satuan" />
            </div>

        </div>


        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label for="jumlah">Jumlah</Label>
            <Input
              id="jumlah"
              v-model="displayJumlah"
              type="text"
              placeholder="0"
              inputmode="numeric"
            />
          </div>

          <div>
            <Label for="biaya">Biaya (Rp)</Label>
            <Input
              id="biaya"
              v-model="displayBiaya"
              type="text"
              placeholder="0"
              inputmode="numeric"
            />
          </div>

          <div>
            <Label for="subtotal">Subtotal (Rp)</Label>
            <Input
              id="subtotal"
              v-model="displaySubtotal"
              type="text"
              readonly
              class="bg-gray-100 font-semibold"
            />
          </div>
        </div>

        <Button type="submit" :disabled="adding" class="mt-3">
          {{ adding ? 'Menyimpan...' : 'Tambah Detail' }}
        </Button>
      </form>

      <!-- Table -->
      <table class="w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="border border-gray-300 px-3 py-2 text-left w-[40px]">#</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Nama Detail</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Jumlah</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Satuan</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Biaya</th>
            <th class="border border-gray-300 px-3 py-2 text-left">Subtotal</th>
            <th class="border border-gray-300 px-3 py-2 text-center w-[100px]">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(detail, index) in props.details"
            :key="detail.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="border border-gray-300 px-3 py-2">{{ index + 1 }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ detail.name }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ formatNumber(detail.jumlah) }}</td>
            <td class="border border-gray-300 px-3 py-2">{{ detail.satuan }}</td>
            <td class="border border-gray-300 px-3 py-2">Rp {{ formatNumber(detail.biaya) }}</td>
            <td class="border border-gray-300 px-3 py-2">Rp {{ formatNumber(detail.subtotal) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-center">
              <Button
                class="bg-red-500 text-white hover:bg-red-600 text-xs"
                @click="handleDetailDeletion(detail.id)"
              >
                Hapus
              </Button>
            </td>
          </tr>

          <tr v-if="props.details.length === 0">
            <td colspan="6" class="text-center p-4 text-gray-500 italic">
              Belum ada data detail pekerjaan.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
