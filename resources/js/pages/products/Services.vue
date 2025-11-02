<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, defineProps, reactive } from 'vue';
import axios from "axios"

const detailOptions = ref([])

interface Product {
  id: number;
  name: string;
}

interface Pekerjaan {
  id: number;
  name: string;
}
interface ProductPekerjaan{
    id: number;
    product_id: number;
    pekerjaan_id: number;
    pekerjaan_name: string;
}

interface ProductDetail{
    id: number;
    product_id: number;
    pekerjaan_id: number;
    tambahan: string;
    jumlah: number;
    satuan: string;
    estimasi_price: number;
}

const props = defineProps<{
  product: Product;
  pekerjaan: Pekerjaan[];
  productpekerjaan:ProductPekerjaan[];
  productdetail: ProductDetail[];
}>();

// state modal
const showPekerjaanModal = ref(false);
const showDetailModal = ref(false);

// form pekerjaan
const pekerjaanForm = useForm({
  product_id: props.product.id,
  pekerjaan_id: null as number | null,
});

// form detail
const detailForm = useForm({
  product_id: props.product.id,
  pekerjaan_id: "",
  tambahan: "",
  jumlah: "",
  satuan: "",
  estimasi_price: "",
});

function formatNumber(value) {
  if (!value) return "";
  return new Intl.NumberFormat("id-ID").format(value);
}

function updateNumber(event, field) {
  const raw = event.target.value.replace(/\D/g, "");
  detailForm[field] = raw ? parseInt(raw) : "";
  event.target.value = formatNumber(detailForm[field]);
}

// simpan pekerjaan
const savePekerjaan = () => {
  pekerjaanForm.post(route('productpekerjaan.store'), {
    onSuccess: () => {
      showPekerjaanModal.value = false;
      pekerjaanForm.reset();
    },
  });
};

// simpan detail
const saveDetail = () => {
  detailForm.post(route('productpekerjaan.storedetail'), {
    onSuccess: () => {
      showDetailModal.value = false;
      detailForm.reset();
    },
  });
};

// hapus pekerjaan
const deletePekerjaan = (id: number) => {
  if (confirm('Yakin hapus pekerjaan ini?')) {
    router.delete(route('productpekerjaan.destroy', id));
  }
};

// productpekerjaan.destroydetail
// hapus detail
const deleteDetail = (id: number) => {
  if (confirm('Yakin hapus detail ini?')) {
    router.delete(route('productpekerjaan.destroydetail', id));
  }
};


async function openDetailModal(pekerjaanId, productpekerjaanId) {
  detailForm.pekerjaan_id = productpekerjaanId
  showDetailModal.value = true

  // 🔄 Load data select dari API
  try {
      const res = await axios.get(`/pekerjaan/${pekerjaanId}/detailjson`)
      detailOptions.value = res.data
    } catch (error) {
      console.error("Gagal memuat data detail:", error)
    }
  }

  function onSelectDetail(e) {
  const selectedId = parseInt(e.target.value)
    const selected = detailOptions.value.find(d => d.id === selectedId)

    if (selected) {
      detailForm.satuan = selected.satuan
      detailForm.estimasi_price = selected.biaya
      detailForm.jumlah = selected.jumlah || 1
      detailForm.tambahan = selected.name
    }
  }



</script>

<template>
  <Head title="Detail Product" />

  <AppLayout :breadcrumbs="[{ title: 'Detail Product', href: `/products` }]">
    <div class="mb-6">
      <h1
        class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary"
      >
        Detail Bill of Services
        <p class="text-white mt-1 text-sm font-normal">
          Perhitungan estimasi pekerjaan <b>{{ props.product.name }}</b>
        </p>
      </h1>
    </div>

    <div class="p-4 mb-3">
      <!-- tombol tambah pekerjaan -->
      <button
        @click="showPekerjaanModal = true"
        class="btn bg-primary p-3 text-white rounded cursor-pointer"
      >
        Tambah Work Order / Pekerjaan
      </button>

      <!-- list pekerjaan -->
      <table class="w-full text-sm mt-3 border">
        <tbody>
          <tr
            v-for="(p, idx) in props.productpekerjaan"
            :key="idx"
            class="odd:bg-white even:bg-gray-50 hover:bg-gray-100"
          >
            <td>
                <table class="w-full text-sm">
                    <tr>
                        <td class="px-4 py-3 border  font-bold text-red-800">
                            {{ p.pekerjaan_name }}
                        </td>
                        <td class="px-4 py-3 border w-[220px]">
                            <div class="flex gap-1" v-if="p.pekerjaan_id !== 2">
                                <!-- <button
                                @click="
                                    () => {
                                    detailForm.pekerjaan_id = p.id;
                                    showDetailModal = true;

                                    }
                                "
                                class="btn bg-primary p-2 text-white rounded"
                                >
                                Tambah Detail
                                </button> -->
                                 <button
                                    @click="openDetailModal(p.pekerjaan_id, p.id)"
                                    class="btn bg-primary p-2 text-white rounded"
                                  >
                                    Tambah Detail
                                  </button>
                                <button
                                @click="deletePekerjaan(p.id)"
                                class="btn bg-red-500 p-2 text-white rounded"
                                >
                                Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">

                            <table class="w-full text-sm ml-5">
                                <tr v-for="(d, idx) in props.productdetail.filter(dd => dd.pekerjaan_id === p.id)" :key="idx" >
                                    <td class="px-3 py-2 border ">
                                        {{ d.tambahan }}
                                    </td>
                                    <td class="px-3 py-2 border  w-[100px]">
                                        {{ d.jumlah }}
                                    </td>
                                    <td class="px-3 py-2 border  w-[100px]">
                                        {{ d.satuan }}
                                    </td>
                                    <td class="px-3 py-2 border  w-[200px]">
                                        {{ Number(d.estimasi_price).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}
                                    </td>
                                    <td class="px-3 py-2 border  w-[200px]">
                                        {{ Number((d.estimasi_price*d.jumlah)).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}
                                    </td>
                                    <td class="px-3 py-2 border w-[150px]">
                                        <button class="btn bg-red-500 p-1 text-white rounded" @click="deleteDetail(d.id)">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>

            </td>
            
          
            
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Pekerjaan -->
    <div
      v-if="showPekerjaanModal"
      class="fixed inset-0 bg-opacity-10 flex items-center justify-center z-50" style="background-color: rgb(0, 0, 0, 0.5);"
    >
      <div class="bg-white rounded shadow-lg p-6 w-[400px]">
        <h2 class="font-semibold text-lg mb-3">Tambah Pekerjaan</h2>
        <select class="border p-2 w-full mb-3" v-model="pekerjaanForm.pekerjaan_id">
            <option v-for="(p, idx) in props.pekerjaan" :key="idx" :value="p.id" class="border p-2 w-full mb-3">
                {{ p.name }}
            </option>
        </select>
        <div class="flex justify-end gap-2">
          <button
            @click="showPekerjaanModal = false"
            class="px-4 py-2 bg-gray-300 rounded"
          >
            Batal
          </button>
          <button
            @click="savePekerjaan"
            class="px-4 py-2 bg-primary text-white rounded"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Detail -->
    <div
      v-if="showDetailModal"
      class="fixed inset-0 bg-opacity-10 flex items-center justify-center z-50"
      style="background-color: rgb(0, 0, 0, 0.5);"
    >
      <div class="bg-white rounded shadow-lg p-6 w-[500px]">
        <h2 class="font-semibold text-lg mb-3">Tambah Detail</h2>

        <div class="grid gap-3">
          
          <div>
            <label class="block text-sm font-medium mb-1">Komponen</label>
            <select
                @change="onSelectDetail"
                class="border rounded p-2 w-full"
              >
                <option value="">-- Pilih Detail --</option>
                <option
                  v-for="d in detailOptions"
                  :key="d.id"
                  :value="d.id"
                >
                  {{ d.name }} — Rp {{ d.biaya.toLocaleString() }}
                </option>
              </select>
          </div>

          <hr></hr>

          <div>
            <label class="block text-sm font-medium mb-1">Detail</label>
            <input
              type="text"
              class="border p-2 w-full rounded"
              placeholder=""
               v-model="detailForm.tambahan"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Jumlah</label>
            <input
              type="text"
              class="border p-2 w-full rounded"
              placeholder="Contoh: 10"
              :value="formatNumber(detailForm.jumlah)"
              @input="updateNumber($event, 'jumlah')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Satuan</label>
            <input
              v-model="detailForm.satuan"
              type="text"
              class="border p-2 w-full rounded"
              placeholder="Contoh: Unit"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Estimasi Harga /Unit</label>
            <input
              type="text"
              class="border p-2 w-full rounded"
              placeholder="Contoh: 1,000,000"
              :value="formatNumber(detailForm.estimasi_price)"
              @input="updateNumber($event, 'estimasi_price')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Total Harga</label>
            <input
              type="text"
              class="border p-2 w-full rounded"
              readonly
              :value="formatNumber((detailForm.estimasi_price * detailForm.jumlah))"
            />
          </div>

        </div>

        <div class="flex justify-end gap-2 mt-4">
          <button
            @click="showDetailModal = false"
            class="px-4 py-2 bg-gray-300 rounded"
          >
            Batal
          </button>
          <button
            @click="saveDetail"
            class="px-4 py-2 bg-primary text-white rounded"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
