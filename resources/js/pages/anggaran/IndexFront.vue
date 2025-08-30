<script setup lang="ts">
import BlankLayout from '@/layouts/BlankLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
];

interface Project {
  id: number;
  name: string;
  client_name: string;
  client_id: number;
  description?: string;
  rab?: number;
}

const projectPekerjaan = ref([]);

interface Pekerjaan {
  id: number;
  name: string;
}

interface Product {
  id: number;
  name: string;
}

interface Jasa {
  id: number;
  name: string;
}

const form = useForm({
  project_id: null as number | null,
  pekerjaan_id: null as number | null,
});

const formDetail = useForm({
  project_id: null as number | null,
  pekerjaan_id: null as number | null,
  type: 'produk' as 'produk' | 'jasa' | 'manual',
  rab: 1 as number,
  product_id: null as number | null, 
  jasa_id: null as number | null,
  tambahan: '',    
  satuan: '',  
  qty: 1,
  harga: 0,
});

const props = defineProps<{
  projects: Project[];
  pekerjaan?: Pekerjaan | null;
  product?: Product[] | null;
  jasa?: Jasa[] | null;
}>();

const projects = ref<Project[]>(props.projects ?? []);
const pekerjaan = ref<Pekerjaan | null>(props.pekerjaan ?? null);
const selectedProjectId = ref<number | null>(null);
const selectedProject = computed(() => 
  projects.value.find((p) => p.id === selectedProjectId.value)
);

const selectedTab = ref(0); 

const tabs = [
  { name: 'RAB Pertama', content: 'Ini adalah konten untuk tab pertama.' }
];

const selectTab = (index: number) => {
  selectedTab.value = index;
};

const showAddJobModal = ref(false);

const handleSubmit = () => {
  form.project_id = selectedProjectId.value;
  form.post(route('anggaran.pekerjaan'), {
    onSuccess: async () => {
      showAddJobModal.value = false;
      form.reset();
      if (selectedProjectId.value) {
        try {
          const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
          projectPekerjaan.value = response.data;
        } catch (error) {
          console.error("Gagal refresh project pekerjaan:", error);
        }
      }
    },
  });
};

const handleSaveDetail = () => {
  if (!selectedPekerjaanId.value) return;
  formDetail.project_id = selectedProjectId.value;
  formDetail.pekerjaan_id = selectedPekerjaanId.value;
  formDetail.type = detailTab.value;
  formDetail.rab = 1;
  formDetail.post(route('anggaran.detail'), {
    onSuccess: async () => {
      showAddDetailModal.value = false;
      formDetail.reset();
      selectedPekerjaanId.value = null;

      if (selectedProjectId.value) {
        try {
          const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
          projectPekerjaan.value = response.data;
        } catch (error) {
          console.error("Gagal refresh project pekerjaan:", error);
        }
      }
    },
    onError: (errors) => {
      console.error('Error simpan detail:', errors);
    },
  });
};


watch(selectedProjectId, async (newId) => {
  if (newId) {
    const response = await axios.get(`/projectpekerjaan/${newId}`);
    projectPekerjaan.value = response.data;
  }
});

const handleApproveRabAwal = async () => {
  if (!selectedProjectId.value) return;
  try {
    await axios.get(`/projectrabawal/${selectedProjectId.value}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;
    alert("RAB Awal berhasil di-approve!");
  } catch (error) {
    console.error("Gagal approve RAB Awal:", error);
  }
};

const handleApproveRabKedua = async () => {
  if (!selectedProjectId.value) return;
  try {
    await axios.get(`/projectrabkedua/${selectedProjectId.value}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;
    alert("RAB Awal berhasil di-approve!");
  } catch (error) {
    console.error("Gagal approve RAB Awal:", error);
  }
};

const deleteDetail = async (detailId: number) => {
  if (!confirm("Yakin ingin menghapus detail ini?")) return;

  try {
    await axios.get(`/anggarandelete/${detailId}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;
    alert("Detail berhasil dihapus!");

  } catch (error) {
    console.error("Gagal hapus detail:", error);
    alert("Terjadi kesalahan saat menghapus detail.");
  }
};


const deletePekerjaan = async (detailId: number) => {
  if (!confirm("Yakin ingin menghapus detail ini?")) return;

  try {
    await axios.get(`/anggaranpekerjaandelete/${detailId}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;
    alert("Detail berhasil dihapus!");

  } catch (error) {
    console.error("Gagal hapus detail:", error);
    alert("Terjadi kesalahan saat menghapus detail.");
  }
};




// Detail Pekerjaan

const showAddDetailModal = ref(false);
const selectedPekerjaanId = ref<number | null>(null);
const detailTab = ref<'produk' | 'jasa' | 'manual'>('produk'); // default



</script>

<template>
  <BlankLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Pilih Project -->
      <div>
        <label for="project-select" class="block mb-1 font-medium">Pilih Project:</label>
        <select
          id="project-select"
          v-model="selectedProjectId"
          class="p-2 border rounded w-full"
        >
          <option disabled value="">-- Pilih Project --</option>
          <option v-for="project in projects" :key="project.id" :value="project.id">
            {{ project.name }}
          </option>
        </select>
      </div>

      <!-- Tab Navigation -->
      <div class="mb-6">
        <ul class="flex border-b">
          <li v-for="(tab, index) in tabs" :key="index" class="mr-6">
            <a
              href="#"
              @click.prevent="selectTab(index)"
              :class="{
                'text-blue-600 border-b-2 border-blue-600': selectedTab === index,
                'text-gray-600': selectedTab !== index
              }"
              class="pb-2 hover:text-blue-600"
            >
              {{ tab.name }}
            </a>
          </li>
        </ul>
      </div>

      <!-- Tab Content -->
      <div class="py-0">

        <div v-if="selectedTab === 0">

          <div v-if="selectedProject">
         
            <div class="p-0" v-if="selectedProject.rab === 1">
              <button class="btn bg-primary p-2 text-white mb-2 cursor-pointer" @click="showAddJobModal = true">+ Tambah Pekerjaan</button>
              <button @click="handleApproveRabAwal" class="btn bg-yellow-500 p-2 text-white ml-2 mb-2 cursor-pointer" >Approve RAB Awal</button>
              <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.id">
                <tbody >
                  <tr >
                    <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                    <td class="border px-2 py-1 text-left font-bold w-[180px]">
                      <button
                        class="p-1 btn text-sm bg-primary text-white cursor-pointer"
                        @click="() => { 
                          selectedPekerjaanId = value.id; 
                          showAddDetailModal = true; 
                          detailTab = 'produk'; 
                        }"
                      >
                        Tambah Detail
                      </button>
                      <button
                        class="btn btn-sm bg-red-600 text-white p-1 px-2 cursor-pointer ml-1"
                      @click="deletePekerjaan(value.id)"
                      >
                        x
                      </button>

                    </td>
                  </tr>

                    <tr v-for="detail in value.details.filter(d => d.pekerjaan_id === value.id)" 
                        :key="detail.id">
                      <td class="border px-2 py-1 text-center">
                        <button class="btn btn-sm bg-red-600 text-white px-2 cursor-pointer"  @click="deleteDetail(detail.id)">x</button>
                      </td>
                      <td class="border px-2 py-1">{{ detail.tambahan }}</td>
                      <td class="border px-2 py-1 text-center">{{ detail.satuan }}</td>
                      <td class="border px-2 py-1 text-center">{{ detail.jumlah }}</td>
                      <td class="border px-2 py-1 text-right">{{ detail.estimasi_price }}</td>
                      <td class="border px-2 py-1 text-right">{{ detail.estimasi_price * detail.jumlah }}</td>
                    </tr>



                </tbody>

              </table>
            </div>

            <div class="p-0" v-if="selectedProject.rab !== 1">
              <h5 class="font-bold mb-2">RAB Pertama telah di-approve. Tidak dapat menambah atau mengubah data.</h5>
              <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.id">
                <tbody >
                  <tr >
                    <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                  </tr>

                    <tr v-for="detail in value.details.filter(d => d.pekerjaan_id === value.id)" 
                        :key="detail.id">
                      <td class="border px-2 py-1">{{ detail.tambahan }}</td>
                      <td class="border px-2 py-1 text-center">{{ detail.satuan }}</td>
                      <td class="border px-2 py-1 text-center">{{ detail.jumlah }}</td>
                      <td class="border px-2 py-1 text-right">{{ detail.estimasi_price }}</td>
                      <td class="border px-2 py-1 text-right">{{ detail.estimasi_price * detail.jumlah }}</td>
                    </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div v-if="selectedTab === 1">
          <div v-if="selectedProject">
            <div class=" p-0">

              <div class="p-0" v-if="selectedProject.rab === 2">
                <button class="btn bg-primary p-2 text-white mb-2 cursor-pointer" @click="showAddJobModal = true">+ Tambah Pekerjaan</button>
                <button @click="handleApproveRabKedua" class="btn bg-yellow-500 p-2 text-white ml-2 mb-2 cursor-pointer" >Approve RAB Kedua</button>
                <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.id">
                  <tbody >
                    <tr >
                      <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                      <td class="border px-2 py-1 text-left font-bold w-[180px]">
                        <button
                          class="p-1 btn text-sm bg-primary text-white cursor-pointer"
                          @click="() => { 
                            selectedPekerjaanId = value.id; 
                            showAddDetailModal = true; 
                            detailTab = 'produk'; 
                          }"
                        >
                          Tambah Detail
                        </button>
                        <button
                          class="btn btn-sm bg-red-600 text-white p-1 px-2 cursor-pointer ml-1"
                        @click="deletePekerjaan(value.id)"
                        >
                          x
                        </button>

                      </td>
                    </tr>

                      <tr v-for="detail in value.details.filter(d => d.pekerjaan_id === value.id)" 
                          :key="detail.id">
                        <td class="border px-2 py-1 text-center">
                          <button class="btn btn-sm bg-red-600 text-white px-2 cursor-pointer"  @click="deleteDetail(detail.id)">x</button>
                        </td>
                        <td class="border px-2 py-1">{{ detail.tambahan }}</td>
                        <td class="border px-2 py-1 text-center">{{ detail.satuan }}</td>
                        <td class="border px-2 py-1 text-center">{{ detail.jumlah }}</td>
                        <td class="border px-2 py-1 text-right">{{ detail.estimasi_price }}</td>
                        <td class="border px-2 py-1 text-right">{{ detail.estimasi_price * detail.jumlah }}</td>
                      </tr>



                  </tbody>

                </table>
              </div>

              <div class="p-0" v-if="selectedProject.rab !== 2">
                <h5 class="font-bold mb-2">RAB Ke Dua telah di-approve. Tidak dapat menambah atau mengubah data.</h5>
                <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.id">
                  <tbody >
                    <tr >
                      <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                    </tr>

                      <tr v-for="detail in value.details.filter(d => d.pekerjaan_id === value.id)" 
                          :key="detail.id">
                        <td class="border px-2 py-1">{{ detail.tambahan }}</td>
                        <td class="border px-2 py-1 text-center">{{ detail.satuan }}</td>
                        <td class="border px-2 py-1 text-center">{{ detail.jumlah }}</td>
                        <td class="border px-2 py-1 text-right">{{ detail.estimasi_price }}</td>
                        <td class="border px-2 py-1 text-right">{{ detail.estimasi_price * detail.jumlah }}</td>
                      </tr>
                  </tbody>
                </table>
              </div>


            </div>
          </div>
        </div>
          <div v-if="selectedTab === 2">
            <div v-if="selectedProject">
            <div class=" p-0">

              <div class="p-0" v-if="selectedProject.rab === 3">
                <h5 class="font-bold mb-2">RAB Final telah di-approve</h5>
                <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.id">
                  <tbody >
                    <tr >
                      <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                    </tr>
                      <tr v-for="detail in value.details.filter(d => d.pekerjaan_id === value.id)" 
                          :key="detail.id">
                        <td class="border px-2 py-1">{{ detail.tambahan }}</td>
                        <td class="border px-2 py-1 text-center">{{ detail.satuan }}</td>
                        <td class="border px-2 py-1 text-center">{{ detail.jumlah }}</td>
                        <td class="border px-2 py-1 text-right">{{ detail.estimasi_price }}</td>
                        <td class="border px-2 py-1 text-right">{{ detail.estimasi_price * detail.jumlah }}</td>
                      </tr>
                  </tbody>
                </table>
              </div>



            </div>
          </div>
        </div>
      </div>

    </div>


<!-- MOdal Pekerjaan -->


<!-- Modal Tambah Pekerjaan -->
<div
  v-if="showAddJobModal"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
  <div class="bg-white rounded shadow-lg p-6 w-[400px] space-y-4">
    <h2 class="text-lg font-semibold">Tambah Pekerjaan</h2>
    <form class="w-8/12 space-y-4" @submit.prevent="handleSubmit">
      <input type="hidden" v-model="form.project_id" />
    <div>
      <label class="block mb-1">Pilih Pekerjaan:</label>
      <select v-model="form.pekerjaan_id" class="w-full border p-2 rounded">
        <option disabled value="">-- Pilih Pekerjaan --</option>
        <option v-for="job in pekerjaan" :key="job.id" :value="job.id">
          {{ job.name }}
        </option>
      </select>
    </div>

    <div class="flex justify-end space-x-2 mt-4">
      <button
        @click="showAddJobModal = false"
        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
      >
        Batal
      </button>
      <button
        type="submit" :disabled="form.processing"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Simpan
      </button>
    </div>
    </form>
  </div>
</div>


<!-- Modal Tambah Detail -->
<div
  v-if="showAddDetailModal"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
  <div class="bg-white rounded shadow-lg p-6 w-[500px] space-y-4">
    <h2 class="text-lg font-semibold">Tambah Detail</h2>

    <!-- Tab Switch -->
    <div class="flex space-x-4 border-b pb-2">
      <button 
        v-for="tab in ['produk','jasa','manual']" 
        :key="tab"
        @click="detailTab = tab"
        :class="[
          'px-3 py-1 rounded',
          detailTab === tab ? 'bg-blue-600 text-white' : 'bg-gray-200'
        ]"
      >
        {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
      </button>
    </div>

    <!-- Tab Content -->
    <div v-if="detailTab === 'produk'" class="space-y-3">
      <label>Pilih Produk:</label>
      <select class="w-full border p-2 rounded" v-model="formDetail.product_id">
        <option disabled selected>-- Pilih Produk --</option>
        <option v-for="p in product" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>

      <label>Qty:</label>
      <input type="number" v-model="formDetail.qty" class="w-full border p-2 rounded" />
    </div>

    <div v-if="detailTab === 'jasa'" class="space-y-3">
      <label>Pilih Jasa:</label>
      <select class="w-full border p-2 rounded" v-model="formDetail.jasa_id">
        <option disabled selected>-- Pilih Jasa --</option>
        <option v-for="j in jasa" :key="j.id" :value="j.id">{{ j.name }}</option>
      </select>

      <label>Qty:</label>
      <input type="number" v-model="formDetail.qty" class="w-full border p-2 rounded" />
    </div>

    <div v-if="detailTab === 'manual'" class="space-y-3">
      <label>Nama Item:</label>
      <input type="text" v-model="formDetail.tambahan" class="w-full border p-2 rounded" />

      <label>Satuan:</label>
      <input type="text" v-model="formDetail.satuan" class="w-full border p-2 rounded" />

      <label>Qty:</label>
      <input type="number" v-model="formDetail.qty" class="w-full border p-2 rounded" />

      <label>Harga:</label>
      <input type="number" v-model="formDetail.harga" class="w-full border p-2 rounded" />
    </div>

    <!-- Actions -->
    <div class="flex justify-end space-x-2 mt-4">
      <button
        @click="() => { showAddDetailModal = false; selectedPekerjaanId = null; }"
        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
      >
        Batal
      </button>
      <button
          type="button"
          :disabled="formDetail.processing"
          @click="handleSaveDetail"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
        Simpan
      </button>

    </div>
  </div>
</div>



  </BlankLayout>
</template>
