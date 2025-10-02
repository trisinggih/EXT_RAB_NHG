<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
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
const productpekerjaan = ref([]);

const projectproduct = ref([]);

const suppliermaterial = ref([]);

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

const formProduct = useForm({
  project_id: null as number | null,
  product_id: null as number | null,
  keterangan: '',
});

const formDetail = useForm({
  id: null as number | null,
  project_id: null as number | null,
  pekerjaan_id: null as number | null,
  rab: '',
  tambahan: '',    
  satuan: '',  
  qty: 1,
  harga: 0,
  type: 'bom',
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
  { name: 'RAB Pertama', content: 'Ini adalah konten untuk tab pertama.' },
  { name: 'RAB Kedua', content: 'Konten untuk tab kedua muncul di sini.' },
  { name: 'RAB Final', content: 'Konten untuk tab ketiga tampil di sini.' }
];

const selectTab = (index: number) => {
  selectedTab.value = index;
};

const showAddJobModal = ref(false);
const showAddProductModal = ref(false);
const showSupplierModal = ref(false);
const loadProjectData = async (projectId) => {
  if (!projectId) return;

  try {
    const response = await axios.get(`/projectproduct/${projectId}`);
    projectproduct.value = response.data.map(item => ({
      ...item,
      detail: item.detail ? JSON.parse(item.detail) : []
    }));

    if (projectproduct.value.length > 0) {
      const ids = projectproduct.value.map(p => p.product_id).join(",");
      const response2 = await axios.get(`/productpekerjaan/${ids}`);
      productpekerjaan.value = response2.data;
    }

    const response3 = await axios.get(`/projectpekerjaan/${projectId}`);
    projectPekerjaan.value = response3.data.map(item => ({
      ...item,
      detail: item.detail ? JSON.parse(item.detail) : []
    }));

  } catch (err) {
    console.error("Gagal load project data:", err);
  }
};



const handleSubmit = () => {
  form.project_id = selectedProjectId.value;
  console.log(form.project_id);

  form.post(route('anggaran.pekerjaan'), {
    onSuccess: async () => {
      showAddJobModal.value = false;
      form.reset();
      if (selectedProjectId.value) {
        try {
          const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
          projectPekerjaan.value = response.data;
          await loadProjectData(selectedProjectId.value);
        } catch (error) {
          console.error("Gagal refresh project pekerjaan:", error);
        }
      }
    },
  });

};

const handleSubmitProduct = async () => {
  try {
    formProduct.project_id = selectedProjectId.value;

    const response = await axios.post(
      "https://api-rab.erainovasi.id/simpanproduct",
      formProduct
    );

    router.visit(window.location.pathname, { replace: true });

    // console.log("Berhasil simpan:", response.data);
    // showAddProductModal.value = false;
    // formProduct.reset();

    // if (selectedProjectId.value) {
    //   const pekerjaan = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    //   projectPekerjaan.value = pekerjaan.data;
    // }
  } catch (error) {
    console.error("Gagal simpan product:", error);
  }
};



const handleSaveDetail = () => {
  formDetail.project_id = selectedProjectId.value;
  formDetail.pekerjaan_id = selectedPekerjaanId.value;
  formDetail.rab = detailTab.value;
  formDetail.post(route('anggaran.detail'), {
    onSuccess: async () => {
      showAddDetailModal.value = false;
      formDetail.reset();
      selectedPekerjaanId.value = null;

      if (selectedProjectId.value) {
        try {
          const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
          projectPekerjaan.value = response.data;
          await loadProjectData(selectedProjectId.value);
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
  await loadProjectData(newId);
});



const handleApproveRabAwal = async () => {
  if (!selectedProjectId.value) return;
  try {
    await axios.get(`/projectrabawal/${selectedProjectId.value}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;

    await loadProjectData(selectedProjectId.value);

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
    await loadProjectData(selectedProjectId.value);
    alert("RAB Awal berhasil di-approve!");
  } catch (error) {
    console.error("Gagal approve RAB Awal:", error);
  }
};

const deleteDetail = async (detailId: number) => {
  if (!confirm("Yakin ingin menghapus detail ini?")) return;

  try {
    await axios.get(`/anggarandelete/${detailId}/${selectedProjectId.value}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;
    await loadProjectData(selectedProjectId.value);
    alert("Detail berhasil dihapus!");

  } catch (error) {
    console.error("Gagal hapus detail:", error);
    alert("Terjadi kesalahan saat menghapus detail.");
  }
};

const updateDetail = async (detail: any) => {
  formDetail.id = detail.id;
  formDetail.tambahan = detail.tambahan;
  formDetail.satuan = detail.satuan;
  formDetail.harga = detail.total_estimasi_price;
  formDetail.qty = detail.total_jumlah;
  formDetail.type = detail.type;

  showAddDetailModal.value = true;
}

const infoSupplier = async (materialId: number) => {
  try {
    const response = await axios.get(`/suppliersmaterial/${materialId}`);
    suppliermaterial.value = response.data;
    console.log(response.data);
    showSupplierModal.value = true;
  }catch{
    console.log('errr');
  }
}


const deletePekerjaan = async (detailId: number) => {
  if (!confirm("Yakin ingin menghapus detail ini?")) return;

  try {
    await axios.get(`/anggaranpekerjaandelete/${detailId}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;
    await loadProjectData(selectedProjectId.value);
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

const downloadRAB = (projectId) => {
  window.location.href = `/projects/${projectId}/export-rab`
}

</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
       <div>
        <label for="project-select" class="block mb-1" :style="{
          'font-size' : '14px',
          'margin-bottom' : '10px',
        }">Pilih Project:</label>
        <select
          id="project-select"
          v-model="selectedProjectId"
          class="p-2 border rounded w-full"
          :style="{
            'padding' : '15px',
            'border-radius' : '5px',
            'font-size' : '14px'
          }"
        >
          <option disabled value="">-- Pilih Project --</option>
          <option :style="{
            'color': 'black',
            'font-size' : '14px'
          }" v-for="project in projects" :key="project.id" :value="project.id">
            {{ project.name }}
          </option>
        </select>
      </div>
      </h1>
    </div>
    <div class="py-2 px-5 space-y-6">

      <!-- Tab Navigation -->
      <div class="mb-6">
        <ul class="flex p-2">
          <li v-for="(tab, index) in tabs" :key="index" class="mr-6">
            <a
              href="#"
              @click.prevent="selectTab(index)"
              :style="{
                'font-size' : '16px',
                'padding' : '5px'
              }"
              :class="{
                'text-blue-600 p-3  border-b-2 border-blue-600': selectedTab === index,
                'text-gray-600 p-3': selectedTab !== index
              }"
              class="pb-2  hover:text-blue-600"
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

            <div class="alert bg-green-100 w-full mb-5 p-3 rounded" role="alert">
                <h4 class="font-bold mb-2" :style="{
                  'font-size' : '16px'
                }">
                  List Project Pengerjaan
                      <button  class="btn bg-primary p-2 text-white mb-2 cursor-pointer float-right" @click="showAddProductModal = true">+ Tambah Product</button>
                </h4>
                <ol>
                        <li v-for="value in projectproduct" :key="value.id">
                          {{ value.keterangan  }}
                        </li>
                  </ol>

                  
            </div>

            <button v-if="selectedProject.rab !== 1" class="btn bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end" @click="downloadRAB(selectedProjectId)"><i class="fa-solid fa-download"></i> Download RAB</button>
         
            <div class="p-0" v-if="selectedProject.rab === 1">

              <button v-if="projectproduct.length !== 0" class="btn bg-primary p-2 text-white mb-2 cursor-pointer" @click="showAddJobModal = true">+ Tambah Pekerjaan</button>
              <button v-if="projectproduct.length !== 0" @click="handleApproveRabAwal" class="btn bg-yellow-500 p-2 text-white ml-2 mb-2 cursor-pointer" >Approve RAB Awal</button>

              <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.pekerjaan_id">
                <tbody >
                  <tr style="background-color: antiquewhite;" >
                    <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                    <td class="border px-2 py-1 text-left font-bold w-[180px]">
                      <button
                        class="p-1 btn text-sm bg-primary text-white cursor-pointer"
                        @click="() => { 
                          selectedPekerjaanId = value.pekerjaan_id; 
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

                    <tr v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)" 
                        :key="dtl.id">
                      <td class="border px-2 py-2 text-center w-[220px]">
                        <button v-if="dtl.material_id !== null" class="btn btn-sm bg-yellow-600 text-white px-2 cursor-pointer mr-2"  @click="infoSupplier(dtl.material_id)">Info Supplier</button>
                        <button class="btn btn-sm bg-green-600 text-white px-2 cursor-pointer mr-2"  @click="updateDetail(dtl)">Edit</button>
                        <button class="btn btn-sm bg-red-600 text-white px-2 cursor-pointer"  @click="deleteDetail(dtl.tambahan)">x</button>
                      </td>
                      <td class="border px-2 py-2">{{ dtl.tambahan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.satuan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ Number(dtl.total_estimasi_price).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.total_jumlah }}</td>
                      <td class="border px-2 py-2 text-right" colspan="2">{{ Number((dtl.total_estimasi_price * dtl.total_jumlah)).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                    </tr>



                </tbody>

              </table>
            </div>

            <div class="p-0" v-if="selectedProject.rab !== 1">
             
              <button v-if="selectedProject.rab !== 2" class="btn bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end" @click="downloadRAB(selectedProjectId)"><i class="fa-solid fa-download"></i> Download RAB</button>

              <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.pekerjaan_id">
                <tbody >
                 <tr style="background-color: antiquewhite;" >
                    <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                  </tr>

                    <tr v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)" 
                        :key="dtl.id">
                      <td class="border px-2 py-2">{{ dtl.tambahan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.satuan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ Number(dtl.total_estimasi_price).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.total_jumlah }}</td>
                      <td class="border px-2 py-2 text-right" colspan="2">{{ Number((dtl.total_estimasi_price * dtl.total_jumlah)).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                    </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div v-if="selectedTab === 1">

           <div class="alert bg-green-100 w-full mb-5 p-3 rounded" role="alert">
                <h4 class="font-bold mb-2">
                  List Project Pengerjaan
                      
                </h4>
                <ol>
                        <li v-for="value in projectproduct" :key="value.id">
                          {{ value.keterangan  }}
                        </li>
                  </ol>
            </div>

            
          <div v-if="selectedProject">
            <div class=" p-0">
              <div class="p-0" v-if="selectedProject.rab === 2">
                <button class="btn bg-primary p-2 text-white mb-2 cursor-pointer" @click="showAddJobModal = true">+ Tambah Pekerjaan</button>
                <button @click="handleApproveRabKedua" class="btn bg-yellow-500 p-2 text-white ml-2 mb-2 cursor-pointer" >Approve RAB Kedua</button>
                <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.pekerjaan_id">
                  <tbody >
                    <tr style="background-color: antiquewhite;" >
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

                      <tr v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)" 
                        :key="dtl.id">
                      <td class="border px-2 py-2 text-center w-[220px]">
                        <button v-if="dtl.material_id !== null" class="btn btn-sm bg-yellow-600 text-white px-2 cursor-pointer mr-2"  @click="infoSupplier(dtl.material_id)">Info Supplier</button>
                        <button class="btn btn-sm bg-green-600 text-white px-2 cursor-pointer mr-2"  @click="updateDetail(dtl)">Edit</button>
                        <button class="btn btn-sm bg-red-600 text-white px-2 cursor-pointer"  @click="deleteDetail(dtl.tambahan)">x</button>
                      </td>
                      <td class="border px-2 py-2">{{ dtl.tambahan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.satuan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ Number(dtl.total_estimasi_price).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.total_jumlah }}</td>
                      <td class="border px-2 py-2 text-right" colspan="2">{{ Number((dtl.total_estimasi_price * dtl.total_jumlah)).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                    </tr>
                  </tbody>

                </table>
              </div>

              <div class="p-0" v-if="selectedProject.rab !== 2 && selectedProject.rab !== 1">
                
                <button v-if="selectedProject.rab !== 2" class="btn bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end" @click="downloadRAB(selectedProjectId)"><i class="fa-solid fa-download"></i> Download RAB</button>

                <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.pekerjaan_id">
                  <tbody >
                    <tr style="background-color: antiquewhite;" >
                      <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                    </tr>

                    <tr v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)" 
                        :key="dtl.id">
                      <td class="border px-2 py-2">{{ dtl.tambahan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.satuan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ Number(dtl.total_estimasi_price).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.total_jumlah }}</td>
                      <td class="border px-2 py-2 text-right" colspan="2">{{ Number((dtl.total_estimasi_price * dtl.total_jumlah)).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>


            </div>
          </div>
        </div>
          <div v-if="selectedTab === 2">
             <div class="alert bg-green-100 w-full mb-5 p-3 rounded" role="alert">
                <h4 class="font-bold mb-2">
                  List Project Pengerjaan
                      
                </h4>
                <ol>
                        <li v-for="value in projectproduct" :key="value.id">
                          {{ value.keterangan  }}
                        </li>
                  </ol>
            </div>
            <div v-if="selectedProject">

           

            <div class=" p-0">

              <div class="p-0" v-if="selectedProject.rab === 3">
                <button v-if="selectedProject.rab !== 2" class="btn bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end" @click="downloadRAB(selectedProjectId)"><i class="fa-solid fa-download"></i> Download RAB</button>

                <table class="w-full border-collapse border text-sm" v-for="value in projectPekerjaan" :key="value.pekerjaan_id">
                  <tbody >
                    <tr style="background-color: antiquewhite;" >
                      <td colspan="5" class="border px-2 py-1 text-left font-bold">{{ value.pekerjaan_name }}</td>
                    </tr>
                     <tr v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)" 
                        :key="dtl.id">
                      <td class="border px-2 py-2">{{ dtl.tambahan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.satuan }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ Number(dtl.total_estimasi_price).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
                      <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.total_jumlah }}</td>
                      <td class="border px-2 py-2 text-right" colspan="2">{{ Number((dtl.total_estimasi_price * dtl.total_jumlah)).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}</td>
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
  class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50" style="background-color: rgba(0, 0, 0, 0.5);"
>
  <div class="bg-white rounded shadow-lg p-6 w-[400px] space-y-4">
    <h2 class="text-lg font-semibold">Tambah Pekerjaan</h2>
    <form class="w-12/12 space-y-4" @submit.prevent="handleSubmit">
      <input type="hidden" v-model="form.project_id" />
    <div>
      <label class="block mb-1">Pilih Pekerjaan:</label>
      <select v-model="form.pekerjaan_id" class="w-full border p-2 rounded">
        <option disabled value="">-- Pilih Pekerjaan --</option>
        <option v-for="job in productpekerjaan" :key="job.id" :value="job.id">
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


<div
  v-if="showAddProductModal"
  class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50" style="background-color: rgba(0, 0, 0, 0.5);"
>
  <div class="bg-white rounded shadow-lg p-6 w-[400px] space-y-4">
    <h2 class="text-lg font-semibold">Tambah Product</h2>
    <form class="w-12/12 space-y-4" @submit.prevent="handleSubmitProduct">
      <input type="hidden" v-model="formProduct.project_id" />
    <div>
      <label class="block mb-1">Pilih Product:</label>
      <select v-model="formProduct.product_id" class="w-full border p-2 rounded">
        <option disabled value="">-- Pilih Product --</option>
        <option v-for="job in product" :key="job.id" :value="job.id">
          {{ job.name }}
        </option>
      </select>
    </div>

    <div>
        <label class="block mb-1">Keterangan:</label>
        <input type="text" v-model="formProduct.keterangan" class="w-full border p-2 rounded" />

    </div>

    <div class="flex justify-end space-x-2 mt-4">
      <button
        @click="showAddProductModal = false"
        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
      >
        Batal
      </button>
      <button
        type="submit" :disabled="formProduct.processing"
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
  class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50" style="background-color: rgba(0, 0, 0, 0.5);"
>
  <div class="bg-white rounded shadow-lg p-6 w-[500px] space-y-4">
    <h2 class="text-lg font-semibold">Tambah Detail</h2>
    <!-- Tab Content -->
    <div  class="space-y-3">
      <label>Nama Item:</label>
      <input type="text" v-model="formDetail.tambahan" class="w-full border p-2 rounded" />
      <label>Satuan:</label>
      <input type="text" v-model="formDetail.satuan" class="w-full border p-2 rounded" />
      <label>Harga:</label>
      <input type="number" v-model="formDetail.harga" class="w-full border p-2 rounded" />
      <label>Qty:</label>
      <input type="number" v-model="formDetail.qty" class="w-full border p-2 rounded" />
      <label>Total:</label>
      <input type="number" :value="formDetail.harga * formDetail.qty" class="w-full border p-2 rounded" />
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


<!-- Modal Tambah Detail -->
<div
  v-if="showSupplierModal"
  class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50" style="background-color: rgba(0, 0, 0, 0.5);"
>
  <div class="bg-white rounded shadow-lg p-6 w-[60%] space-y-4">
    <h2 class="text-lg font-semibold">Info Supplier</h2>
    <!-- Tab Content -->
    <div  class="space-y-3">

      <table class="w-full border-collapse border text-sm">
        <tr class="text-center p-4"  v-for="(value, index) in suppliermaterial" :key="value.id">
          <td class="border px-2 py-2">
              {{ index + 1 }}
          </td>
          <td class="border px-2 py-2">
              {{ value.supplier_name }}
          </td>
          <td class="border px-2 py-2">
              {{ value.material_name }}
          </td>
          <td class="border px-2 py-2">
              {{ value.price }}
          </td>
          <td class="border px-2 py-2">
              {{ value.material_satuan }}
          </td>
          <td class="border px-2 py-2">
              <a :href="value.link" target="_blank"><button class="btn cursor-pointer bg-yellow-500 text-white p-2">Link</button></a>
          </td>
        </tr>
      </table>

    </div>
    <!-- Actions -->
    <div class="flex justify-end space-x-2 mt-4">
      <button
        @click="() => { showSupplierModal = false;  }"
        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
      >
        Tutup
      </button>


    </div>
  </div>
</div>



  </AppLayout>
</template>
