<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SupplierLayout from '@/layouts/SupplierLayout.vue';
import { Head, useForm, router  } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Supplier {
  id: number;
  name: string;
  telp: string;
  address: string;
  pic: string;
}

interface Material {
  id: number;
  name: string;
  satuan: string;
  panjang: number;
  lebar: number;
  tinggi: number;
}

interface ExistingMaterial {
  id: number;
  name: string;
  price: number;
}

const props = defineProps<{
  supplier: Supplier;
  materials: ExistingMaterial[];
  ms_material: Material[];
}>();

const form = useForm({
  material_id: null,
  supplier_id: props.supplier.id,
  price: '',
  link: '',
});

const selectedMaterial = ref<Material | null>(null);

const adding = ref(false);

const submitMaterial = () => {
  adding.value = true;
  form.post('/supplier/suppliermaterials', {
    onSuccess: () => {
      form.reset();
      form.supplier_id = props.supplier.id;
      selectedMaterial.value = null;

      router.reload({ only: ['materials'] });
    },
    onFinish: () => {
      adding.value = false;
    },
  });
};

const handleMaterialDeletion = (materialID: number) => {
  if (confirm('Are you sure you want to delete this material?')) {
    router.delete(route('supplier.supplier.material.destroy', { id: materialID }), {
      preserveScroll: true,
      onSuccess: () => {
        props.materials = props.materials.filter(material => material.id !== materialID)
        router.reload({ only: ['materials'] });
        },
      onError: (err) => {
        console.error('Gagal menghapus:', err)
      }
    });
  }
}


// Watch for material_id changes
watch(
  () => form.material_id,
  (newId) => {
    const selected = props.ms_material.find(item => item.id === Number(newId));
    selectedMaterial.value = selected || null;
    form.material_id = selectedMaterial.value?.id || 0;
    console.log(selectedMaterial.value);
  }
);

// format angka ke ribuan (1.000.000)
const formatNumber = (value) => {
  if (!value) return ''
  return new Intl.NumberFormat('id-ID').format(value)
}

// simpan tampilan input
const displayPrice = ref(formatNumber(form.price))

// setiap kali user ubah tampilan → simpan nilai asli ke form.price
watch(displayPrice, (val) => {
  const numeric = val.replace(/\D/g, '') // hapus karakter non-angka
  form.price = Number(numeric || 0)
  displayPrice.value = formatNumber(numeric)
})
</script>

<template>
  <Head title="Edit a supplier" />

  <SupplierLayout :breadcrumbs="[{ title: 'Edit a supplier', href: `/suppliers/${props.supplier.id}` }]">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Data Material Supplier
        <p class="text-white mt-1 text-sm font-normal">Informasi material data supplier.</p>
      </h1>
    </div>

    <div class="p-4 bg-white rounded shadow-md">
      <!-- Form Input Material -->
      <form @submit.prevent="submitMaterial" class="mb-6 space-y-4">
        <!-- Dropdown Material -->
        <div>
          <label for="material_id" class="block mb-1">Nama Material</label>
          <select
            class="border rounded px-3 py-2 w-full"
            v-model="form.material_id"
            id="material_id"
            name="material_id"
          >
            <option disabled value="">Pilih Material</option>
            <option
              v-for="(msma, index) in props.ms_material"
              :key="index"
              :value="msma.id"
            >
              {{ msma.name }}
            </option>
          </select>
        </div>

        <!-- Auto-filled Fields -->
        <div class="grid grid-cols-2 gap-2 w-full">
          <div>
            <Label for="satuan" class="block mb-1">Satuan</Label>
            <Input
              id="satuan"
              type="text"
              :value="selectedMaterial?.satuan || ''"
              disabled
            />
          </div>
     
        </div>

        <!-- Harga -->
        <div>
          <Label for="price" class="block mb-1">Harga Material</Label>
          <Input
            id="price"
            v-model="displayPrice"
            type="text"
            placeholder="Masukkan harga material"
            required
          />
        </div>

        <div>
          <Label for="link" class="block mb-1">Link</Label>
          <Input
            id="link"
            v-model="form.link"
            type="text"
            placeholder="Masukkan link jika ada"
          />
        </div>

        <!-- Submit Button -->
        <Button type="submit" :disabled="adding">
          {{ adding ? 'Menyimpan...' : 'Tambah Material' }}
        </Button>
      </form>

      <!-- Tabel Data Material -->
      <table class="w-full border-collapse border border-gray-300">
        <thead class="bg-gray-100">
          <tr>
            <th class="border border-gray-300 px-4 py-2 text-left w-[40px]">#</th>
            <th class="border border-gray-300 px-4 py-2 text-left">Nama Material</th>
            <th class="border border-gray-300 px-4 py-2 text-left">Satuan</th>
            <th class="border border-gray-300 px-4 py-2 text-left">Price</th>
            <th class="border border-gray-300 px-4 py-2 text-left">Link</th>
            <th class="border border-gray-300 px-4 py-2 text-left w-[100px]">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(material, index) in props.materials"
            :key="index"
            class="hover:bg-gray-50"
          >
            <td class="border border-gray-300 px-4 py-2">{{ index + 1 }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ material.material_name }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ material.material_satuan }}</td> 
            <td class="border border-gray-300 px-4 py-2">Rp {{ material.price.toLocaleString('id-ID') }}</td>
            <td class="border border-gray-300 px-4 py-2">
              <a :href="material.link" target="_blank"><button
                class="text-blue-500 hover:underline"
              >
                Klik Link
              </button></a>
            </td>
            <td class="border border-gray-300 px-4 py-2">
              <Button
                class="bg-red-500 text-white px-2"
                @click="handleMaterialDeletion(material.id)"
              >
                Hapus
              </Button>
            </td>
          </tr>
          <tr v-if="props.materials.length === 0">
            <td colspan="4" class="text-center p-4 text-gray-500">Belum ada data material.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </SupplierLayout>
</template>
