<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import $ from "jquery";
import "select2";

import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import html2canvas from "html2canvas";

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
  product_id:null as number | null,
});

const formProduct = useForm({
  project_id: null as number | null,
  product_id: null as number | null,
  keterangan: '',
  jumlah:'',
  satuan:'',
});

const formDetail = useForm({
  id: null as number | null,
  project_id: null as number | null,
  pekerjaan_id: null as number | null,
  product_id: null as number | null,
  rab: '',
  tambahan: '',    
  satuan: '',  
  qty: 1,
  harga: 0,
  type: 'bom',
});

function formatNumber(value) {
  if (!value) return "";
  return new Intl.NumberFormat("id-ID").format(value);
}

function formatCurrency(value) {
  return Number(value || 0).toLocaleString("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  });
}

const grandTotal = computed(() => {
  if (!projectPekerjaan?.value || projectPekerjaan.value.length === 0) return 0;
  return projectPekerjaan.value.reduce((acc, pekerjaan) => {
    const totalPerPekerjaan = pekerjaan.detail.reduce((sum, d) => {
      const subtotal = (d.total_estimasi_price || 0) * (d.total_jumlah || 0);
      return sum + subtotal;
    }, 0);
    return acc + totalPerPekerjaan;
  }, 0);
});

// 💰 Persentase tambahan
const profit = computed(() => grandTotal.value * 0.20);
const feeKantor = computed(() => grandTotal.value * 0.20);
const feeStaf = computed(() => grandTotal.value * 0.02);
const feeKonsultan = computed(() => grandTotal.value * 0.03);
const feeBendera = computed(() => grandTotal.value * 0.03);
const feeMarketing = computed(() => grandTotal.value * 0.02);
const pph = computed(() => grandTotal.value * 0.0265);
const ppn = computed(() => grandTotal.value * 0.11);

// 💎 Total Akhir
const grandTotalFinal = computed(() => {
  return (
    grandTotal.value +
    profit.value +
    feeKantor.value +
    feeStaf.value +
    feeKonsultan.value +
    feeBendera.value +
    feeMarketing.value +
    pph.value +
    ppn.value
  );
});

function updateNumber(event, field) {
  const raw = event.target.value.replace(/\D/g, ""); // hapus semua karakter non-digit
  formDetail[field] = raw ? parseInt(raw) : 0;
  event.target.value = formatNumber(formDetail[field]);
}

const props = defineProps<{
  projects: Project[];
  pekerjaan?: Pekerjaan | null;
  product?: Product[] | null;
  jasa?: Jasa[] | null;
  selectedProject: Object;
}>();

const projects = ref<Project[]>(props.projects ?? []);
const pekerjaan = ref<Pekerjaan | null>(props.pekerjaan ?? null);
const selectedProjectId = ref<number | null>(null);
const product = ref<Product[]>(props.product ?? []);
const detailTab = ref<'produk' | 'jasa' | 'manual'>('produk');


const selectedProject = computed(() => 
  projects.value.find((p) => p.id === selectedProjectId.value)

);

const selectedTab = ref(0); 

const tabs = [
  { name: 'RAB Pertama', content: 'Ini adalah konten untuk tab pertama.' },
  { name: 'RAB Kedua', content: 'Konten untuk tab kedua muncul di sini.' },
  { name: 'RAB Final', content: 'Konten untuk tab ketiga tampil di sini.' }
];

const selectTab = async (index: number) => {
  selectedTab.value = index;

  await loadProjectData(selectedProjectId.value);
};

const showAddJobModal = ref(false);
const showAddProductModal = ref(false);
const showSupplierModal = ref(false);
const showIsiData = ref(false);
const loadProjectData = async (projectId) => {
   showIsiData.value = false;
  if (!projectId) return;

  try {
    const response = await axios.get(`/projectproduct/${projectId}/${selectedTab.value}`);
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

    showIsiData.value = true;

  } catch (err) {
    console.error("Gagal load project data:", err);
  }
};



const handleSubmit = () => {
  form.project_id = selectedProjectId.value;
  console.log(form.project_id);

  form.post(route('anggaran.pekerjaan'), {
    preserveScroll: true,
    onSuccess: async () => {
      // showAddJobModal.value = false;
      form.reset();
      if (selectedProjectId.value) {
        try {
          const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
          projectPekerjaan.value = response.data;
          clearFormProduct();
          showAddJobModal.value = false;
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
     showAddProductModal.value = false;
     if (selectedProjectId.value) {
        try {
          const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
          projectPekerjaan.value = response.data;
          await loadProjectData(selectedProjectId.value);
        } catch (error) {
          console.error("Gagal refresh project pekerjaan:", error);
        }
      }

    // router.visit(window.location.pathname, { replace: true });
    

  } catch (error) {
    console.error("Gagal simpan product:", error);
  }
};


const selectedMaterialId = ref<number | null>(null)
const selectedPekerjaannewId = ref<number | null>(null)
const selectedProductId = ref<number | null>(null)


const selectSupplier = async (supplier) => {
  if (!confirm(`Pilih supplier ${supplier.supplier_name}?`)) return;

  try {
    await router.put(route('projectdetail.updateSupplier'), {
      project_id: selectedProjectId.value,
      product_id: selectedProductId.value,
      pekerjaan_id: selectedPekerjaannewId.value, 
      material_id: selectedMaterialId.value,
      supplier_id: supplier.supplier_id,
      estimasi_price: supplier.price,
    }, {
      preserveScroll: true,
    });

  
    if (selectedProjectId.value) {
      try {
        const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
        projectPekerjaan.value = response.data;
        await loadProjectData(selectedProjectId.value);
      } catch (error) {
        console.error("Gagal refresh project pekerjaan:", error);
      }
    }

    alert('Supplier berhasil dipilih!');
    showSupplierModal.value = false;
  } catch (error) {
    console.error("Gagal memilih supplier:", error);
  }
};




const handleSaveDetail = () => {
  formDetail.project_id = selectedProjectId.value;
  // formDetail.pekerjaan_id = selectedPekerjaanId.value;
  // formDetail.product_id = selectedProductId.value; 'produk' | 'jasa' | 'manual'
  if(detailTab.value == 'produk'){
    formDetail.rab = "Awal";
  }else if(detailTab.value == 'jasa'){
    formDetail.rab = "Kedua";
  }else{
    formDetail.rab = "Final";
  }
  
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

  // 🔹 Tambahkan pertanyaan konfirmasi sebelum lanjut
  const confirmed = window.confirm("Apakah Anda yakin ingin meng-approve RAB Awal untuk proyek ini?");
  if (!confirmed) {
    return; // batal jika user menekan Cancel
  }

  try {
    // 🔹 Proses approve jika user menekan OK
    await axios.get(`/projectrabawal/${selectedProjectId.value}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;

    // await loadProjectData(selectedProjectId.value);

    alert("✅ RAB Awal berhasil di-approve!");
    router.visit(window.location.pathname, { replace: true });
  } catch (error) {
    console.error("Gagal approve RAB Awal:", error);
    alert("❌ Terjadi kesalahan saat meng-approve RAB Awal. Silakan coba lagi.");
  }
};

const handleApproveRabKedua = async () => {
  if (!selectedProjectId.value) return;

  // 🔹 Tambahkan pertanyaan konfirmasi sebelum lanjut
  const confirmed = window.confirm("Apakah Anda yakin ingin meng-approve RAB Kedua untuk proyek ini?");
  if (!confirmed) {
    return; // batal jika user menekan Cancel
  }

  try {
    // 🔹 Proses approve jika user menekan OK
    await axios.get(`/projectrabkedua/${selectedProjectId.value}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;

    // await loadProjectData(selectedProjectId.value);

    alert("✅ RAB Kedua berhasil di-approve!");
    router.visit(window.location.pathname, { replace: true });
  } catch (error) {
    console.error("Gagal approve RAB Kedua:", error);
    alert("❌ Terjadi kesalahan saat meng-approve RAB Awal. Silakan coba lagi.");
  }
};


const handleGenerateRAB = async (dana) => {
  if (!selectedProjectId.value) return;

  // 🔹 Tambahkan pertanyaan konfirmasi sebelum lanjut
  const confirmed = window.confirm("Apakah Anda yakin ingin generate?");
  if (!confirmed) {
    return; // batal jika user menekan Cancel
  }

  try {
    // 🔹 Proses approve jika user menekan OK
    await axios.get(`/generaterab/${selectedProjectId.value}/${dana}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;

    // await loadProjectData(selectedProjectId.value);

    alert("✅ RAB Kedua berhasil di-generate!");
    router.visit(window.location.pathname, { replace: true });
  } catch (error) {
    console.error("Gagal approve RAB Kedua:", error);
    alert("❌ Terjadi kesalahan saat Generate. Silakan coba lagi.");
  }
};


const handleApproveRabFinal = async () => {
  if (!selectedProjectId.value) return;

  // 🔹 Tambahkan pertanyaan konfirmasi sebelum lanjut
  const confirmed = window.confirm("Apakah Anda yakin ingin meng-approve RAB Final untuk proyek ini?");
  if (!confirmed) {
    return; // batal jika user menekan Cancel
  }

  try {
    // 🔹 Proses approve jika user menekan OK
    await axios.get(`/projectrabfinal/${selectedProjectId.value}`);

    const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
    projectPekerjaan.value = response.data;

    // await loadProjectData(selectedProjectId.value);

    alert("✅ RAB Final berhasil di-approve!");
    router.visit(window.location.pathname, { replace: true });
  } catch (error) {
    console.error("Gagal approve RAB Final:", error);
    alert("❌ Terjadi kesalahan saat meng-approve RAB Final. Silakan coba lagi.");
  }
};


const deleteDetail = async (detailId: number) => {
  if (!confirm("Yakin ingin menghapus detail ini?")) return;

  try {
    await axios.get(`/anggarandelete/${detailId}/${selectedProjectId.value}`);

    if (selectedProjectId.value) {
      try {
        const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
        projectPekerjaan.value = response.data;
        await loadProjectData(selectedProjectId.value);
      } catch (error) {
        console.error("Gagal refresh project pekerjaan:", error);
      }
    }

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
  formDetail.product_id = detail.product_id;

  showAddDetailModal.value = true;
}

const infoSupplier = async (materialId: number, pekerjaan_id: number, product_id: number, project_id: number) => {
  try {
    const response = await axios.get(`/suppliersmaterial/${materialId}`)
    suppliermaterial.value = response.data

    // simpan id yang sedang aktif
    selectedMaterialId.value = materialId
    selectedPekerjaannewId.value = pekerjaan_id
    selectedProductId.value = product_id

    showSupplierModal.value = true
  } catch (error) {
    console.error('Gagal mengambil data supplier:', error)
  }
}


const deletePekerjaan = async (detailId: number) => {
  if (!confirm("Yakin ingin menghapus detail ini?")) return;

  try {
    await axios.get(`/anggaranpekerjaandelete/${selectedPekerjaanId}`);

    if (selectedProjectId.value) {
      try {
        const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
        projectPekerjaan.value = response.data;
        await loadProjectData(selectedProjectId.value);
      } catch (error) {
        console.error("Gagal refresh project pekerjaan:", error);
      }
    }

  } catch (error) {
    console.error("Gagal hapus detail:", error);
    alert("Terjadi kesalahan saat menghapus detail.");
  }
};




// Detail Pekerjaan

const showAddDetailModal = ref(false);
const selectedPekerjaanId = ref<number | null>(null);
 // default

const downloadRAB = (projectId) => {
  window.location.href = `/projects/${projectId}/export-rab`
}


const terbilang = (angka) => {
  // Fungsi sederhana konversi angka ke kata Indonesia
  const satuan = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan"];
  const belasan = ["Sepuluh", "Sebelas", "Dua Belas", "Tiga Belas", "Empat Belas", "Lima Belas", "Enam Belas", "Tujuh Belas", "Delapan Belas", "Sembilan Belas"];
  const puluhan = ["", "", "Dua Puluh", "Tiga Puluh", "Empat Puluh", "Lima Puluh", "Enam Puluh", "Tujuh Puluh", "Delapan Puluh", "Sembilan Puluh"];

  const toWords = (n) => {
    if (n < 10) return satuan[n];
    if (n < 20) return belasan[n - 10];
    if (n < 100) return puluhan[Math.floor(n / 10)] + " " + satuan[n % 10];
    if (n < 1000) return satuan[Math.floor(n / 100)] + " Ratus " + toWords(n % 100);
    if (n < 1000000) return toWords(Math.floor(n / 1000)) + " Ribu " + toWords(n % 1000);
    if (n < 1000000000) return toWords(Math.floor(n / 1000000)) + " Juta " + toWords(n % 1000000);
    return "";
  };

  return toWords(angka).trim() + " Rupiah";
};

const rabView = ref(null);



const downloadRABPDF = (data, product, project, type) => {

  const url = `/rab/pdf2?project=${project}&type=${type}`;
  window.open(url, '_blank');
};

const downloadRABExcel = (data, product, project, type) => {
  const url = `/rab/excel2?project=${project}&type=${type}`;
  window.open(url, '_blank');
};

const productNameInput = ref("");

function updateProductId() {
  const selected = product.value.find(
    (p) => p.name.toLowerCase() === productNameInput.value.toLowerCase()
  );
  formProduct.product_id = selected ? selected.id : "";
}


function clearFormProduct() {
  productNameInput.value = "";
  formProduct.value = {
    project_id: "",
    product_id: "",
    jumlah: "",
    satuan: "",
    keterangan: "",
    processing: false,
  };
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
      <div class="py-0" id="isidata" v-if="showIsiData">

        <!-- RAB 1 -->

          <div v-if="selectedTab === 0">
            <div v-if="selectedProject">
              <div class="bg-green-50 border border-green-200 w-full mb-5 p-4 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-3">
                  <h4 class="font-bold text-[16px] text-green-800">
                    List Project Pengerjaan (RAB 1)
                  </h4>
                  <button
                    v-if="selectedProject.rab === 1"
                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm font-medium"
                    @click="showAddProductModal = true; clearFormProduct();"
                  >
                    + Tambah Product
                  </button>
                </div>

                <table class="w-full text-sm border-collapse">
                  <thead>
                    <tr class="bg-green-200 text-green-900">
                      <th class="border border-green-300 px-3 py-2 text-left">No</th>
                      <th class="border border-green-300 px-3 py-2 text-left">Nama Produk</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Total</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Jumlah</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Satuan</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Grand Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(value, index) in projectproduct"
                      :key="value.id"
                      class="hover:bg-green-100 transition"
                    >
                      <td class="border border-green-300 px-3 py-2 text-center">{{ index + 1 }}</td>
                      <td class="border border-green-300 px-3 py-2">{{ value.product_name }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ Number(value.total).toLocaleString('id-ID') }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ value.jumlah }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ value.satuan }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ Number(value.grandtotal).toLocaleString('id-ID') }}</td>

                    </tr>
                    <tr v-if="projectproduct.length === 0">
                      <td colspan="4" class="border border-green-300 px-3 py-3 text-center text-gray-500 italic">
                        Belum ada produk ditambahkan.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>


              <div class="w-full grid grid-cols-2">

                <div class="p-2"> 

                  <button
                    v-if="selectedProject.rab !== 1"
                    class="btn m-3 bg-yellow-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                    @click="downloadRABPDF(projectPekerjaan, projectproduct, selectedProjectId, 'Awal')" 
                  >
                    <i class="fa-solid fa-download"></i> DOWNLOAD PDF RAB
                  </button>

                </div>
                <div class="p-2">

                  <button
                    v-if="selectedProject.rab !== 1"
                    class="btn m-3 bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                     @click="downloadRABExcel(projectPekerjaan, projectproduct, selectedProjectId, 'Awal')" 
                  >
                    <i class="fa-solid fa-download"></i> DOWNLOAD EXCEL RAB
                  </button>

                </div>
              

              </div>

  
      

              <!-- Jika RAB aktif -->
              <div class="p-0 mt-2" v-if="selectedProject.rab === 1">
                
              <div
                  v-if="projectproduct.length !== 0"
                  class="border-l-4 border-yellow-500 bg-yellow-50 p-4 rounded-lg shadow-sm mb-4 animate-fade-in"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01M12 5a7 7 0 00-7 7v5h14v-5a7 7 0 00-7-7z" />
                      </svg>
                      <p class="text-yellow-800 font-medium">
                        Pastikan data RAB Awal sudah benar sebelum melakukan approval.
                      </p>
                    </div>
                    <button
                      @click="handleApproveRabAwal"
                      class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-yellow-400"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 13l4 4L19 7" />
                      </svg>
                      Approve RAB Awal
                    </button>
                  </div>
                </div>

                <div v-for="product in projectproduct" :key="product.id" class="mb-6 mt-0">
                  <h3 class="font-bold text-lg p-3 bg-primary text-white">
                    {{ product.product_name }}
                  </h3>

                  <table
                    class="w-full border-collapse border text-sm"
                    v-for="value in projectPekerjaan.filter(p => p.product_id == product.product_id)"
                    :key="value.pekerjaan_id"
                  >
                    <tbody>
                      <tr style="background-color: antiquewhite;">
                        <td colspan="5" class="border px-2 py-1 text-left font-bold">
                          {{ value.pekerjaan_name }}
                        </td>
                        <td class="border px-2 py-1 text-left font-bold w-[180px]">
                          <button
                            class="p-1 btn text-sm bg-primary text-white cursor-pointer"
                            @click="() => { 
                              selectedPekerjaanId = value.pekerjaan_id; 
                              formDetail.pekerjaan_id = value.pekerjaan_id; 
                              formDetail.product_id = value.product_id; 
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

                      <!-- DETAIL ITEM -->
                      <tr
                        v-for="dtl in value.detail.filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Awal')"
                        :key="dtl.id"
                      >
                        <td class="border px-2 py-2 text-center w-[220px]">
                          <button
                            v-if="dtl.material_id !== null"
                            class="btn btn-sm bg-yellow-600 text-white px-2 cursor-pointer mr-2"
                            @click="infoSupplier(dtl.material_id, dtl.pekerjaan_id, dtl.product_id)"
                          >
                            Info Supplier
                          </button>
                          <button
                            class="btn btn-sm bg-green-600 text-white px-2 cursor-pointer mr-2"
                            @click="updateDetail(dtl)"
                          >
                            Edit
                          </button>
                          <button
                            class="btn btn-sm bg-red-600 text-white px-2 cursor-pointer"
                            @click="deleteDetail(dtl.tambahan)"
                          >
                            x
                          </button>
                        </td>

                        <td class="border px-2 py-2 text-center">
                          {{ dtl.tambahan }}
                          <span
                            v-if="dtl.supplier_name"
                            class="ml-2 inline-block bg-green-500 text-white text-xs px-2 py-1 rounded-full"
                          >
                            {{ dtl.supplier_name }}
                          </span>
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.satuan }}</td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ formatCurrency(dtl.total_estimasi_price) }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.total_jumlah }}
                        </td>

                        <td class="border px-2 py-2 text-right" colspan="2">
                          {{ formatCurrency(dtl.total_estimasi_price * dtl.total_jumlah) }}
                        </td>
                      </tr>

                      <!-- TOTAL PER PEKERJAAN -->
                      <tr class="bg-green-100 font-bold">
                        <td colspan="5" class="border px-2 py-2 text-right">Total Pekerjaan</td>
                        <td class="border px-2 py-2 text-right">
                          {{ formatCurrency(
                            value.detail
                              .filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Awal')
                              .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                          ) }}
                        </td>
                      </tr>

                    </tbody>
                  </table>

                  <!-- ============================== -->
                  <!-- 🔥 TOTAL PER PRODUCT DI SINI  -->
                  <!-- ============================== -->
                  <div class="text-right font-bold text-lg mt-2 mb-6 bg-blue-50 border p-3 rounded">
                    Total Product: 
                    {{ formatCurrency(
                      projectPekerjaan
                        .filter(p => p.product_id == product.product_id)
                        .flatMap(p => p.detail.filter(d => d.rab == 'Awal'))
                        .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                    ) }}
                  </div>

                </div>




              </div>


              <!-- Jika RAB aktif -->
              <div class="p-0 mt-2" id="rab-preview" ref="rabView" v-if="selectedProject.rab !== 1">


                <div v-for="product in projectproduct" :key="product.id" class="mb-6 mt-2">
                  <h3 class="font-bold text-lg p-3 bg-primary text-white">
                    {{ product.product_name }}
                  </h3>

                  <table
                    class="w-full border-collapse border text-sm"
                    v-for="value in projectPekerjaan.filter(p => p.product_id == product.product_id)"
                    :key="value.pekerjaan_id"
                  >
                    <tbody>
                      <tr style="background-color: antiquewhite;">
                        <td colspan="6" class="border px-2 py-1 text-left font-bold">
                          {{ value.pekerjaan_name }}
                        </td>
                      </tr>

                      <!-- DETAIL ITEM -->
                      <tr
                        v-for="dtl in value.detail.filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Awal')"
                        :key="dtl.id"
                      >

                        <td colspan="2" class="border px-2 py-2 text-center" style="width: 40%;">
                          {{ dtl.tambahan }}
                          <span
                            v-if="dtl.supplier_name"
                            class="ml-2 inline-block bg-green-500 text-white text-xs px-2 py-1 rounded-full"
                          >
                            {{ dtl.supplier_name }}
                          </span>
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.satuan }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ formatCurrency(dtl.total_estimasi_price) }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.total_jumlah }}
                        </td>

                        <td class="border px-2 py-2 text-right" colspan="2">
                          {{ formatCurrency(dtl.total_estimasi_price * dtl.total_jumlah) }}
                        </td>
                      </tr>

                      <!-- ================================= -->
                      <!-- 🔥 TOTAL PER PEKERJAAN -->
                      <!-- ================================= -->
                      <tr class="bg-green-100 font-bold">
                        <td colspan="5" class="border px-2 py-2 text-right">
                          Total Pekerjaan
                        </td>
                        <td class="border px-2 py-2 text-right">
                          {{ formatCurrency(
                            value.detail
                              .filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Awal')
                              .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                          ) }}
                        </td>
                      </tr>

                    </tbody>
                  </table>

                  <!-- =================================== -->
                  <!-- 🔥 TOTAL PER PRODUCT -->
                  <!-- =================================== -->
                  <div class="text-right font-bold text-lg mt-2 mb-6 bg-blue-50 border p-3 rounded">
                    Total Product:
                    {{ formatCurrency(
                      projectPekerjaan
                        .filter(p => p.product_id == product.product_id)
                        .flatMap(p => p.detail.filter(d => d.rab == 'Awal'))
                        .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                    ) }}
                  </div>

                </div>




              </div>






            </div>
          </div>






           <!-- RAB 2 -->


          <div v-if="selectedTab === 1">

            <div class="bg-green-50 border border-green-200 w-full mb-5 p-4 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-3">
                  <h4 class="font-bold text-[16px] text-green-800">
                    List Project Pengerjaan (RAB 2)
                  </h4>
                </div>

                <table class="w-full text-sm border-collapse">
                  <thead>
                    <tr class="bg-green-200 text-green-900">
                     <th class="border border-green-300 px-3 py-2 text-left">No</th>
                      <th class="border border-green-300 px-3 py-2 text-left">Nama Produk</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Total</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Jumlah</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Satuan</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Grand Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(value, index) in projectproduct"
                      :key="value.id"
                      class="hover:bg-green-100 transition"
                    >
                       <td class="border border-green-300 px-3 py-2 text-center">{{ index + 1 }}</td>
                      <td class="border border-green-300 px-3 py-2">{{ value.product_name }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ Number(value.total).toLocaleString('id-ID') }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ value.jumlah }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ value.satuan }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ Number(value.grandtotal).toLocaleString('id-ID') }}</td>

                    </tr>
                    <tr v-if="projectproduct.length === 0">
                      <td colspan="4" class="border border-green-300 px-3 py-3 text-center text-gray-500 italic">
                        Belum ada produk ditambahkan.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              
            <div v-if="selectedProject">
              <div class=" p-0">
                
                  <!-- Jika RAB aktif -->
                  <div class="p-0" v-if="selectedProject.rab === 2">

                    
                    

                <div
                  v-if="projectproduct.length !== 0"
                  class="border-l-4 border-yellow-500 bg-yellow-50 p-4 rounded-lg shadow-sm mb-4 animate-fade-in"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01M12 5a7 7 0 00-7 7v5h14v-5a7 7 0 00-7-7z" />
                      </svg>
                      <p class="text-yellow-800 font-medium">
                        Pastikan data RAB Kedua sudah benar sebelum melakukan approval.
                      </p>
                    </div>
                    <button
                      @click="handleApproveRabKedua"
                      class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-yellow-400"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 13l4 4L19 7" />
                      </svg>
                      Approve RAB Kedua
                    </button>

                     <button
                      @click="handleGenerateRAB(5000000)"
                      class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-yellow-400"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 13l4 4L19 7" />
                      </svg>
                      Generate RAB
                    </button>
                  </div>
                </div>

                  <div  v-for="product in projectproduct" :key="product.id" class="mb-6 mt-2">
                    <h3 class="font-bold text-lg p-3 bg-primary text-white">
                      {{ product.product_name }}
                    </h3>


                      <table
                      class="w-full border-collapse border text-sm"
                      v-for="value in projectPekerjaan.filter(p => p.product_id == product.product_id)"
                      :key="value.pekerjaan_id"
                    >
                    
                      <tbody>
                        <tr style="background-color: antiquewhite;">
                          <td colspan="5" class="border px-2 py-1 text-left font-bold">
                            {{ value.pekerjaan_name }}
                          </td>
                          <td class="border px-2 py-1 text-left font-bold w-[180px]">
                            <button
                              class="p-1 btn text-sm bg-primary text-white cursor-pointer"
                              @click="() => { 
                                selectedPekerjaanId = value.pekerjaan_id; 
                                formDetail.pekerjaan_id = value.pekerjaan_id; 
                                formDetail.product_id = value.product_id; 
                                showAddDetailModal = true; 
                                detailTab = 'jasa'; 
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

                        <!-- DETAIL ITEM -->
                        <tr
                        v-for="dtl in value.detail.filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Awal')"
                        :key="dtl.id"
                      >
                        <td class="border px-2 py-2 text-center w-[220px]">
                          <button
                            v-if="dtl.material_id !== null"
                            class="btn btn-sm bg-yellow-600 text-white px-2 cursor-pointer mr-2"
                            @click="infoSupplier(dtl.material_id, dtl.pekerjaan_id, dtl.product_id)"
                          >
                            Info Supplier
                          </button>
                          <button
                            class="btn btn-sm bg-green-600 text-white px-2 cursor-pointer mr-2"
                            @click="updateDetail(dtl)"
                          >
                            Edit
                          </button>
                          <button
                            class="btn btn-sm bg-red-600 text-white px-2 cursor-pointer"
                            @click="deleteDetail(dtl.tambahan)"
                          >
                            x
                          </button>
                        </td>

                        <td class="border px-2 py-2 text-center">
                          {{ dtl.tambahan }}
                          <span
                            v-if="dtl.supplier_name"
                            class="ml-2 inline-block bg-green-500 text-white text-xs px-2 py-1 rounded-full"
                          >
                            {{ dtl.supplier_name }}
                          </span>
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.satuan }}</td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ formatCurrency(dtl.total_estimasi_price) }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.total_jumlah }}
                        </td>

                        <td class="border px-2 py-2 text-right" colspan="2">
                          {{ formatCurrency(dtl.total_estimasi_price * dtl.total_jumlah) }}
                        </td>
                      </tr>

                      <!-- TOTAL PER PEKERJAAN -->
                      <tr class="bg-green-100 font-bold">
                        <td colspan="5" class="border px-2 py-2 text-right">Total Pekerjaan</td>
                        <td class="border px-2 py-2 text-right">
                          {{ formatCurrency(
                            value.detail
                              .filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Kedua')
                              .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                          ) }}
                        </td>
                      </tr>

                      </tbody>
                    </table>

                  <!-- ============================== -->
                  <!-- 🔥 TOTAL PER PRODUCT DI SINI  -->
                  <!-- ============================== -->
                  <div class="text-right font-bold text-lg mt-2 mb-6 bg-blue-50 border p-3 rounded">
                    Total Product: 
                    {{ formatCurrency(
                      projectPekerjaan
                        .filter(p => p.product_id == product.product_id)
                        .flatMap(p => p.detail.filter(d => d.rab == 'Kedua'))
                        .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                    ) }}
                  </div>





                  </div>
                  

    
                  </div>

      

                  <div class="w-full grid grid-cols-2"  v-if="selectedProject.rab !== 1 && selectedProject.rab !== 2">

                  <div class="p-2"> 

                    <button
                      v-if="selectedProject.rab !== 1"
                      class="btn m-3 bg-yellow-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                      @click="downloadRABPDF(projectPekerjaan, projectproduct, selectedProjectId, 'Kedua')" 
                    >
                      <i class="fa-solid fa-download"></i> DOWNLOAD PDF RAB
                    </button>

                  </div>
                  <div class="p-2">

                    <button
                      v-if="selectedProject.rab !== 1"
                      class="btn m-3 bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                      @click="downloadRABExcel(projectPekerjaan, projectproduct, selectedProjectId, 'Kedua' )" 
                    >
                      <i class="fa-solid fa-download"></i> DOWNLOAD EXCEL RAB
                    </button>

                  </div>
                

                </div>

                  <!-- Jika RAB aktif -->
                  <div class="p-0" v-if="selectedProject.rab !== 1 && selectedProject.rab !== 2">


                    <div v-for="product in projectproduct" :key="product.id" class="mb-6 mt-2">
                    <h3 class="font-bold text-lg p-3 bg-primary text-white">
                      {{ product.product_name }}
                    </h3>


                      <table
                        class="w-full border-collapse border text-sm"
                        v-for="value in projectPekerjaan.filter(p => p.product_id == product.product_id)"
                        :key="value.pekerjaan_id"
                      >
                      <tbody>
                        <tr style="background-color: antiquewhite;">
                          <td colspan="6" class="border px-2 py-1 text-left font-bold">
                            {{ value.pekerjaan_name }}
                          </td>
            
                        </tr>

                        <!-- DETAIL ITEM -->
                    <tr
                        v-for="dtl in value.detail.filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Kedua')"
                        :key="dtl.id"
                      >

                        <td colspan="2" class="border px-2 py-2 text-center" style="width: 40%;">
                          {{ dtl.tambahan }}
                          <span
                            v-if="dtl.supplier_name"
                            class="ml-2 inline-block bg-green-500 text-white text-xs px-2 py-1 rounded-full"
                          >
                            {{ dtl.supplier_name }}
                          </span>
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.satuan }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ formatCurrency(dtl.total_estimasi_price) }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.total_jumlah }}
                        </td>

                        <td class="border px-2 py-2 text-right" colspan="2">
                          {{ formatCurrency(dtl.total_estimasi_price * dtl.total_jumlah) }}
                        </td>
                      </tr>

                      <!-- ================================= -->
                      <!-- 🔥 TOTAL PER PEKERJAAN -->
                      <!-- ================================= -->
                      <tr class="bg-green-100 font-bold">
                        <td colspan="5" class="border px-2 py-2 text-right">
                          Total Pekerjaan
                        </td>
                        <td class="border px-2 py-2 text-right">
                          {{ formatCurrency(
                            value.detail
                              .filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Kedua')
                              .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                          ) }}
                        </td>
                      </tr>

                    </tbody>
                  </table>

                  <!-- =================================== -->
                  <!-- 🔥 TOTAL PER PRODUCT -->
                  <!-- =================================== -->
                  <div class="text-right font-bold text-lg mt-2 mb-6 bg-blue-50 border p-3 rounded">
                    Total Product:
                    {{ formatCurrency(
                      projectPekerjaan
                        .filter(p => p.product_id == product.product_id)
                        .flatMap(p => p.detail.filter(d => d.rab == 'Kedua'))
                        .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                    ) }}
                  </div>

                  </div>

                
                  </div>


              </div>
            </div>
          </div>







           <!-- RAB 3 -->


          <div v-if="selectedTab === 2">

            <div class="bg-green-50 border border-green-200 w-full mb-5 p-4 rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-3">
                  <h4 class="font-bold text-[16px] text-green-800">
                    List Project Pengerjaan (RAB 3)
                  </h4>
                </div>

                <table class="w-full text-sm border-collapse">
                  <thead>
                    <tr class="bg-green-200 text-green-900">
                     <th class="border border-green-300 px-3 py-2 text-left">No</th>
                      <th class="border border-green-300 px-3 py-2 text-left">Nama Produk</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Total</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Jumlah</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Satuan</th>
                      <th class="border border-green-300 px-3 py-2 text-center">Grand Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(value, index) in projectproduct"
                      :key="value.id"
                      class="hover:bg-green-100 transition"
                    >
                       <td class="border border-green-300 px-3 py-2 text-center">{{ index + 1 }}</td>
                      <td class="border border-green-300 px-3 py-2">{{ value.product_name }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ Number(value.total).toLocaleString('id-ID') }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ value.jumlah }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ value.satuan }}</td>
                      <td class="border border-green-300 px-3 py-2 text-center">{{ Number(value.grandtotal).toLocaleString('id-ID') }}</td>

                    </tr>
                    <tr v-if="projectproduct.length === 0">
                      <td colspan="4" class="border border-green-300 px-3 py-3 text-center text-gray-500 italic">
                        Belum ada produk ditambahkan.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              
            <div v-if="selectedProject">
              <div class=" p-0">
                
                  <!-- Jika RAB aktif -->
                  <div class="p-0" v-if="selectedProject.rab === 3">
              
                    <!-- <button
                      v-if="projectproduct.length !== 0"
                      @click="handleApproveRabFinal"
                      class="btn bg-yellow-500 p-2 text-white ml-2 mb-2 cursor-pointer"
                    >
                      Approve RAB Final
                    </button> -->

                <div
                  v-if="projectproduct.length !== 0"
                  class="border-l-4 border-yellow-500 bg-yellow-50 p-4 rounded-lg shadow-sm mb-4 animate-fade-in"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01M12 5a7 7 0 00-7 7v5h14v-5a7 7 0 00-7-7z" />
                      </svg>
                      <p class="text-yellow-800 font-medium">
                        Pastikan data RAB Final sudah benar sebelum melakukan approval.
                      </p>
                    </div>
                    <button
                      @click="handleApproveRabFinal"
                      class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-yellow-400"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 13l4 4L19 7" />
                      </svg>
                      Approve RAB Final
                    </button>
                  </div>
                </div>



                  <div  v-for="product in projectproduct" :key="product.id" class="mb-6 mt-2">
                    <h3 class="font-bold text-lg p-3 bg-primary text-white">
                      {{ product.product_name }}
                       <!-- <button
                      v-if="projectproduct.length !== 0"
                      class="btn bg-green-500 p-2 text-white float-right cursor-pointer"
                      style="font-size: 15px;margin-bottom: 10px;margin-top: -4px;"
                      @click=" () => { form.product_id = product.product_id; form.pekerjaan_id = product.pekerjaan_id; showAddJobModal = true; } "
                    >
                      + Tambah Pekerjaan
                    </button> -->
                    </h3>


                      <table
                      class="w-full border-collapse border text-sm"
                      v-for="value in projectPekerjaan.filter(p => p.product_id == product.product_id)"
                      :key="value.pekerjaan_id"
                    >
                      <tbody>
                        <tr style="background-color: antiquewhite;">
                          <td colspan="5" class="border px-2 py-1 text-left font-bold">
                            {{ value.pekerjaan_name }}
                          </td>
                          <td class="border px-2 py-1 text-left font-bold w-[180px]">
                            <button
                              class="p-1 btn text-sm bg-primary text-white cursor-pointer"
                              @click="() => { 
                                selectedPekerjaanId = value.pekerjaan_id; 
                                formDetail.pekerjaan_id = value.pekerjaan_id; 
                                formDetail.product_id = value.product_id; 
                                showAddDetailModal = true; 
                                detailTab = 'lain'; 
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

                        <!-- DETAIL ITEM -->
                         <tr
                        v-for="dtl in value.detail.filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Final')"
                        :key="dtl.id"
                      >
                        <td class="border px-2 py-2 text-center w-[220px]">
                          <button
                            v-if="dtl.material_id !== null"
                            class="btn btn-sm bg-yellow-600 text-white px-2 cursor-pointer mr-2"
                            @click="infoSupplier(dtl.material_id, dtl.pekerjaan_id, dtl.product_id)"
                          >
                            Info Supplier
                          </button>
                          <button
                            class="btn btn-sm bg-green-600 text-white px-2 cursor-pointer mr-2"
                            @click="updateDetail(dtl)"
                          >
                            Edit
                          </button>
                          <button
                            class="btn btn-sm bg-red-600 text-white px-2 cursor-pointer"
                            @click="deleteDetail(dtl.tambahan)"
                          >
                            x
                          </button>
                        </td>

                        <td class="border px-2 py-2 text-center">
                          {{ dtl.tambahan }}
                          <span
                            v-if="dtl.supplier_name"
                            class="ml-2 inline-block bg-green-500 text-white text-xs px-2 py-1 rounded-full"
                          >
                            {{ dtl.supplier_name }}
                          </span>
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">{{ dtl.satuan }}</td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ formatCurrency(dtl.total_estimasi_price) }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.total_jumlah }}
                        </td>

                        <td class="border px-2 py-2 text-right" colspan="2">
                          {{ formatCurrency(dtl.total_estimasi_price * dtl.total_jumlah) }}
                        </td>
                      </tr>

                      <!-- TOTAL PER PEKERJAAN -->
                      <tr class="bg-green-100 font-bold">
                        <td colspan="5" class="border px-2 py-2 text-right">Total Pekerjaan</td>
                        <td class="border px-2 py-2 text-right">
                          {{ formatCurrency(
                            value.detail
                              .filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Final')
                              .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                          ) }}
                        </td>
                      </tr>

                      </tbody>
                    </table>

                  <!-- ============================== -->
                  <!-- 🔥 TOTAL PER PRODUCT DI SINI  -->
                  <!-- ============================== -->
                  <div class="text-right font-bold text-lg mt-2 mb-6 bg-blue-50 border p-3 rounded">
                    Total Product: 
                    {{ formatCurrency(
                      projectPekerjaan
                        .filter(p => p.product_id == product.product_id)
                        .flatMap(p => p.detail.filter(d => d.rab == 'Final'))
                        .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                    ) }}
                  </div>





                  </div>
                  

    
                  </div>

      

                  <div class="w-full grid grid-cols-2"  v-if="selectedProject.rab !== 1 && selectedProject.rab !== 2 && selectedProject.rab !== 3">

                  <div class="p-2"> 

                    <button
                      v-if="selectedProject.rab !== 1"
                      class="btn m-3 bg-yellow-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                      @click="downloadRABPDF(projectPekerjaan, projectproduct, selectedProjectId, 'Final')" 
                    >
                      <i class="fa-solid fa-download"></i> DOWNLOAD PDF RAB
                    </button>

                  </div>
                  <div class="p-2">

                    <button
                      v-if="selectedProject.rab !== 1"
                      class="btn m-3 bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                      @click="downloadRABExcel(projectPekerjaan, projectproduct, selectedProjectId, 'Final')" 
                    >
                      <i class="fa-solid fa-download"></i> DOWNLOAD EXCEL RAB
                    </button>

                  </div>
                

                </div>

                  <!-- Jika RAB aktif -->
                  <div class="p-0" v-if="selectedProject.rab !== 1 && selectedProject.rab !== 2 && selectedProject.rab !== 3">


                    <div v-for="product in projectproduct" :key="product.id" class="mb-6 mt-2">
                    <h3 class="font-bold text-lg p-3 bg-primary text-white">
                      {{ product.product_name }}
                    </h3>


                      <table
                      class="w-full border-collapse border text-sm"
                      v-for="value in projectPekerjaan.filter(p => p.product_id == product.product_id)"
                      :key="value.pekerjaan_id"
                    >
                      <tbody>
                        <tr style="background-color: antiquewhite;">
                          <td colspan="6" class="border px-2 py-1 text-left font-bold">
                            {{ value.pekerjaan_name }}
                          </td>
            
                        </tr>

                        <!-- DETAIL ITEM -->
                        <tr
                        v-for="dtl in value.detail.filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Final')"
                        :key="dtl.id"
                      >

                        <td colspan="2" class="border px-2 py-2 text-center" style="width: 40%;">
                          {{ dtl.tambahan }}
                          <span
                            v-if="dtl.supplier_name"
                            class="ml-2 inline-block bg-green-500 text-white text-xs px-2 py-1 rounded-full"
                          >
                            {{ dtl.supplier_name }}
                          </span>
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.satuan }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ formatCurrency(dtl.total_estimasi_price) }}
                        </td>

                        <td class="border px-2 py-2 text-center w-[150px]">
                          {{ dtl.total_jumlah }}
                        </td>

                        <td class="border px-2 py-2 text-right" colspan="2">
                          {{ formatCurrency(dtl.total_estimasi_price * dtl.total_jumlah) }}
                        </td>
                      </tr>

                      <!-- ================================= -->
                      <!-- 🔥 TOTAL PER PEKERJAAN -->
                      <!-- ================================= -->
                      <tr class="bg-green-100 font-bold">
                        <td colspan="5" class="border px-2 py-2 text-right">
                          Total Pekerjaan
                        </td>
                        <td class="border px-2 py-2 text-right">
                          {{ formatCurrency(
                            value.detail
                              .filter(d => d.pekerjaan_id == value.pekerjaan_id && d.rab == 'Final')
                              .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                          ) }}
                        </td>
                      </tr>

                    </tbody>
                  </table>

                  <!-- =================================== -->
                  <!-- 🔥 TOTAL PER PRODUCT -->
                  <!-- =================================== -->
                  <div class="text-right font-bold text-lg mt-2 mb-6 bg-blue-50 border p-3 rounded">
                    Total Product:
                    {{ formatCurrency(
                      projectPekerjaan
                        .filter(p => p.product_id == product.product_id)
                        .flatMap(p => p.detail.filter(d => d.rab == 'Final'))
                        .reduce((sum, d) => sum + (d.total_estimasi_price * d.total_jumlah), 0)
                    ) }}
                  </div>




                  </div>

                
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
      <input type="hidden" v-model="form.product_id" />
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
        type="submit"
        :disabled="formProduct.processing"
        class="px-4 py-2 flex items-center justify-center gap-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-70 disabled:cursor-not-allowed"
      >
        <span v-if="!formProduct.processing">Simpan</span>
        <span v-else class="flex items-center">
          <svg
            class="animate-spin h-5 w-5 text-white mr-2"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
            ></path>
          </svg>
          Proses...
        </span>
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
    <!-- <div>
      <label class="block mb-1">Pilih Product:</label>
      <select v-model="formProduct.product_id" class="w-full border p-2 rounded">
        <option disabled value="">-- Pilih Product --</option>
        <option v-for="job in product" :key="job.id" :value="job.id">
          {{ job.name }}
        </option>
      </select>
    </div> -->
    <div>
          <label class="block mb-1">Pilih Product:</label>
          <input
            list="productList"
            v-model="productNameInput"
            placeholder="Ketik nama produk..."
            class="w-full border p-2 rounded"
            @change="updateProductId"
          />
          <datalist id="productList">
            <option
              v-for="job in product"
              :key="job.id"
              :value="job.name"
            ></option>
          </datalist>
        </div>

    <div>
        <label class="block mb-1">Jumlah:</label>
        <input type="text" v-model="formProduct.jumlah" class="w-full border p-2 rounded" />

    </div>

    <div>
        <label class="block mb-1">Satuan:</label>
        <input type="text" v-model="formProduct.satuan" class="w-full border p-2 rounded" />

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
  class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50"
  style="background-color: rgba(0, 0, 0, 0.5);"
>
  <div class="bg-white rounded shadow-lg p-6 w-[500px] space-y-4">
    <h2 class="text-lg font-semibold">Tambah Detail</h2>

    <div class="space-y-3">
      <label>Nama Item:</label>
      <input
        type="text"
        v-model="formDetail.tambahan"
        class="w-full border p-2 rounded"
      />

      <label>Satuan:</label>
      <input
        type="text"
        v-model="formDetail.satuan"
        class="w-full border p-2 rounded"
      />

      <label>Harga:</label>
      <input
        type="text"
        class="w-full border p-2 rounded"
        placeholder="Contoh: 1.000.000"
        :value="formatNumber(formDetail.harga)"
        @input="updateNumber($event, 'harga')"
      />

      <label>Qty:</label>
      <input
        type="text"
        class="w-full border p-2 rounded"
        placeholder="Contoh: 10"
        :value="formatNumber(formDetail.qty)"
        @input="updateNumber($event, 'qty')"
      />

      <label>Total:</label>
      <input
        type="text"
        class="w-full border p-2 rounded bg-gray-100"
        readonly
        :value="formatNumber(formDetail.harga * formDetail.qty)"
      />
    </div>

    <div class="flex justify-end space-x-2 mt-4">
      <button
        @click="() => { showAddDetailModal = false; }"
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
              <a :href="value.link" target="_blank"><button class="btn cursor-pointer bg-yellow-500 text-white p-2 rounded">Link</button></a>
              <button
                class="btn ml-2 cursor-pointer bg-green-500 text-white p-2 rounded"
                @click="selectSupplier(value)"
              >
                Pilih Supplier
              </button>
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
