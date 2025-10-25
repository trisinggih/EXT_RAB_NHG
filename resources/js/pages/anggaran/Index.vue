<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

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

    await loadProjectData(selectedProjectId.value);

    alert("✅ RAB Awal berhasil di-approve!");
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

    await loadProjectData(selectedProjectId.value);

    alert("✅ RAB Kedua berhasil di-approve!");
  } catch (error) {
    console.error("Gagal approve RAB Kedua:", error);
    alert("❌ Terjadi kesalahan saat meng-approve RAB Awal. Silakan coba lagi.");
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

    await loadProjectData(selectedProjectId.value);

    alert("✅ RAB Final berhasil di-approve!");
  } catch (error) {
    console.error("Gagal approve RAB Final:", error);
    alert("❌ Terjadi kesalahan saat meng-approve RAB Final. Silakan coba lagi.");
  }
};

// const handleApproveRabKedua = async () => {
//   if (!selectedProjectId.value) return;
//   try {
//     await axios.get(`/projectrabkedua/${selectedProjectId.value}`);

//     const response = await axios.get(`/projectpekerjaan/${selectedProjectId.value}`);
//     projectPekerjaan.value = response.data;
//     await loadProjectData(selectedProjectId.value);
//     alert("RAB Awal berhasil di-approve!");
//   } catch (error) {
//     console.error("Gagal approve RAB Awal:", error);
//   }
// };

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

const downloadRABPDF = async () => {
  const doc = new jsPDF("p", "mm", "a4");

  const logoUrl = "/images/nhg.png"; // Ganti dengan path logo kamu (PNG/JPG)
  const logoWidth = 20;
  const logoHeight = 20;

  try {
    const logo = await fetch(logoUrl).then((r) => r.blob());
    const reader = new FileReader();
    reader.onload = () => {
      const logoData = reader.result;

      // Logo di kiri atas
      doc.addImage(logoData, "PNG", 15, 10, logoWidth, logoHeight);

      // Nama Perusahaan
      doc.setFontSize(16);
      doc.setFont(undefined, "bold");
      doc.text("CV. NUSANTARA HYDRO GATE", 105, 20, { align: "center" });

      // Alamat & Kontak
      doc.setFontSize(9);
      doc.setFont(undefined, "normal");
      doc.text(
        "Jl. Raya Solo No. 123, Solo - Jawa Tengah | Telp. (0271) 123-4567 | www.nhg.com",
        105,
        26,
        { align: "center" }
      );

      // Garis pemisah
      doc.setLineWidth(0.5);
      doc.line(15, 30, 195, 30);

      // === Judul Dokumen ===
      doc.setFontSize(13);
      doc.setFont(undefined, "bold");
      doc.text("RENCANA ANGGARAN BIAYA (RAB)", 105, 40, { align: "center" });

      doc.setFontSize(11);
      doc.setFont(undefined, "normal");
      doc.text(selectedProject.value?.name || "Nama Proyek", 105, 47, { align: "center" });

      let currentY = 55;
      let grandTotalAll = 0;

      // === LOOP PEKERJAAN ===
      projectPekerjaan.value.forEach((pekerjaan) => {
        doc.setFont(undefined, "bold");
        doc.setFillColor(255, 240, 220);
        doc.rect(15, currentY - 4, 180, 7, "F");
        doc.text(pekerjaan.pekerjaan_name.toUpperCase(), 17, currentY);
        currentY += 3;

        const rows = [];
        let subtotal = 0;

        pekerjaan.detail
          .filter((d) => d.pekerjaan_id === pekerjaan.pekerjaan_id)
          .forEach((dtl) => {
            const total = dtl.total_estimasi_price * dtl.total_jumlah;
            subtotal += total;
            rows.push([
              dtl.tambahan,
              dtl.satuan || "-",
              formatCurrency(dtl.total_estimasi_price),
              dtl.total_jumlah,
              formatCurrency(total),
            ]);
          });

        autoTable(doc, {
          startY: currentY + 2,
          head: [["Uraian", "Sat", "Harga Satuan", "Qty", "Jumlah Harga"]],
          body: rows,
          theme: "grid",
          styles: { fontSize: 9 },
          headStyles: { fillColor: [21, 21, 21], halign: "center" },
          columnStyles: {
            0: { cellWidth: 60 },
            1: { cellWidth: 20, halign: "center" },
            2: { cellWidth: 35, halign: "right" },
            3: { cellWidth: 15, halign: "center" },
            4: { cellWidth: 35, halign: "right" },
          },
        });

        currentY = doc.lastAutoTable.finalY + 2;
        doc.setFont(undefined, "bold");
        doc.text("Subtotal:", 100, currentY);
        doc.text(formatCurrency(subtotal), 200, currentY, { align: "right" });
        currentY += 8;
        grandTotalAll += subtotal;
      });

      // === Bagian Total ===
      const profit = grandTotalAll * 0.2;
      const feeKantor = grandTotalAll * 0.2;
      const feeStaf = grandTotalAll * 0.02;
      const feeKonsultan = grandTotalAll * 0.03;
      const feeBendera = grandTotalAll * 0.03;
      const feeMarketing = grandTotalAll * 0.02;
      const pph = grandTotalAll * 0.0265;
      const ppn = grandTotalAll * 0.11;

      const grandTotalFinal =
        grandTotalAll +
        profit +
        feeKantor +
        feeStaf +
        feeKonsultan +
        feeBendera +
        feeMarketing +
        pph +
        ppn;

      doc.setFontSize(10);
      doc.setFont(undefined, "bold");
      doc.text("Total Awal:", 150, currentY);
      doc.text(formatCurrency(grandTotalAll), 200, currentY, { align: "right" });
      currentY += 6;

      autoTable(doc, {
        startY: currentY,
        body: [
          ["a. Profit 20%", formatCurrency(profit)],
          ["b. Fee Kantor 20%", formatCurrency(feeKantor)],
          ["c. Fee Staf 2%", formatCurrency(feeStaf)],
          ["d. Fee Konsultan 3%", formatCurrency(feeKonsultan)],
          ["e. Fee Bendera 3%", formatCurrency(feeBendera)],
          ["f. Fee Marketing 2%", formatCurrency(feeMarketing)],
          ["g. PPh 2.65%", formatCurrency(pph)],
          ["h. PPN 11%", formatCurrency(ppn)],
        ],
        theme: "plain",
        styles: { fontSize: 9 },
        columnStyles: {
          0: { cellWidth: 100 },
          1: { cellWidth: 60, halign: "right" },
        },
      });

      currentY = doc.lastAutoTable.finalY + 4;
      doc.setFontSize(11);
      doc.setFont(undefined, "bold");
      doc.text("TOTAL AKHIR (Grand Total)", 120, currentY);
      doc.setTextColor(0, 100, 0);
      doc.text(formatCurrency(grandTotalFinal), 200, currentY, { align: "right" });
      doc.setTextColor(0, 0, 0);
      currentY += 8;

      doc.setFontSize(9);
      doc.setFont(undefined, "normal");
      doc.text(`Terbilang: ${terbilang(Math.round(grandTotalFinal))}`, 15, currentY);

      // === Tanda tangan ===
      currentY += 20;
      doc.setFontSize(10);
      doc.text("Dibuat oleh,", 30, currentY);
      doc.text("Diperiksa oleh,", 100, currentY);
      doc.text("Disetujui oleh,", 160, currentY);

      doc.text("(_________________)", 28, currentY + 20);
      doc.text("(_________________)", 98, currentY + 20);
      doc.text("(_________________)", 158, currentY + 20);

      doc.save(`RAB_${selectedProject.value.name || "Project"}.pdf`);
    };
    reader.readAsDataURL(logo);
  } catch (err) {
    console.error("Gagal memuat logo:", err);
  }
};
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

        <!-- RAB 1 -->

          <div v-if="selectedTab === 0">
            <div v-if="selectedProject">
              <div class="alert bg-green-100 w-full mb-5 p-3 rounded" role="alert">
                <h4 class="font-bold mb-2 text-[16px]">
                  List Project Pengerjaan
                  <button
                    v-if="selectedProject.rab === 1"
                    class="btn bg-primary p-2 text-white mb-2 cursor-pointer float-right"
                    @click="showAddProductModal = true"
                  >
                    + Tambah Product
                  </button>
                </h4>
                <ol>
                  <li v-for="value in projectproduct" :key="value.id">
                    {{ value.keterangan }}
                  </li>
                </ol>
              </div>

              <button
                v-if="selectedProject.rab !== 1"
                class="btn bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                @click="downloadRABPDF"
              >
                <i class="fa-solid fa-download"></i> Download RAB
              </button>

              <!-- Jika RAB aktif -->
              <div class="p-0" v-if="selectedProject.rab === 1">
                <button
                  v-if="projectproduct.length !== 0"
                  class="btn bg-primary p-2 text-white mb-2 cursor-pointer"
                  @click="showAddJobModal = true"
                >
                  + Tambah Pekerjaan
                </button>
                <button
                  v-if="projectproduct.length !== 0"
                  @click="handleApproveRabAwal"
                  class="btn bg-yellow-500 p-2 text-white ml-2 mb-2 cursor-pointer"
                >
                  Approve RAB Awal
                </button>


                <table
                  class="w-full border-collapse border text-sm"
                  v-for="value in projectPekerjaan"
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
                      v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)"
                      :key="dtl.id"
                    >
                      <td class="border px-2 py-2 text-center w-[220px]">
                        <button
                          v-if="dtl.material_id !== null"
                          class="btn btn-sm bg-yellow-600 text-white px-2 cursor-pointer mr-2"
                          @click="infoSupplier(dtl.material_id)"
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
                      <td class="border px-2 py-2">{{ dtl.tambahan }}</td>
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
                  </tbody>
                </table>

                <!-- TOTAL DAN RINCIAN -->
                <div v-if="projectPekerjaan && projectPekerjaan.length > 0" class="mt-6 ">
                  <!-- <div class="text-right font-semibold text-lg mb-2">
                    Total Awal:
                    <span class="text-blue-600">{{ formatCurrency(grandTotal) }}</span>
                  </div> -->

                  <table class="w-full text-sm border mt-3">
                    <tbody>
                      <tr class="font-bold border-t bg-gray-100">
                        <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AWAL</td>
                        <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                          {{ formatCurrency(grandTotal) }}
                        </td>
                      </tr>
                      <tr><td class="px-2 py-2 border">a. Profit 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(profit) }}</td></tr>
                      <tr><td class="px-2 py-2 border">b. Fee Kantor 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKantor) }}</td></tr>
                      <tr><td class="px-2 py-2 border">c. Fee Staf 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeStaf) }}</td></tr>
                      <tr><td class="px-2 py-2 border">d. Fee Konsultan 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKonsultan) }}</td></tr>
                      <tr><td class="px-2 py-2 border">e. Fee Bendera 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeBendera) }}</td></tr>
                      <tr><td class="px-2 py-2 border">f. Fee Marketing 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeMarketing) }}</td></tr>
                      <tr><td class="px-2 py-2 border">g. PPh 2.65%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(pph) }}</td></tr>
                      <tr><td class="px-2 py-2 border">h. PPN 11%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(ppn) }}</td></tr>
                      <tr class="font-bold border-t bg-gray-100">
                        <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AKHIR (Grand Total)</td>
                        <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                          {{ formatCurrency(grandTotalFinal) }}
                        </td>
                      </tr>
                    </tbody>
                  
                      
                  
                  </table>
                </div>
              </div>


              <!-- Jika RAB aktif -->
              <div class="p-0" v-if="selectedProject.rab !== 1">


                <table
                  class="w-full border-collapse border text-sm"
                  v-for="value in projectPekerjaan"
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
                      v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)"
                      :key="dtl.id"
                    >

                      <td colspan="2" class="border px-2 py-2">{{ dtl.tambahan }}</td>
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
                  </tbody>
                </table>

                <!-- TOTAL DAN RINCIAN -->
                <div v-if="projectPekerjaan && projectPekerjaan.length > 0" class="mt-6 border-t pt-4">
         

                  <table class="w-full text-sm border mt-3">
                    <tbody>
                      <tr class="font-bold border-t bg-gray-100">
                        <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AWAL</td>
                        <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                          {{ formatCurrency(grandTotal) }}
                        </td>
                      </tr>
                      <tr><td class="px-2 py-2 border">a. Profit 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(profit) }}</td></tr>
                      <tr><td class="px-2 py-2 border">b. Fee Kantor 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKantor) }}</td></tr>
                      <tr><td class="px-2 py-2 border">c. Fee Staf 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeStaf) }}</td></tr>
                      <tr><td class="px-2 py-2 border">d. Fee Konsultan 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKonsultan) }}</td></tr>
                      <tr><td class="px-2 py-2 border">e. Fee Bendera 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeBendera) }}</td></tr>
                      <tr><td class="px-2 py-2 border">f. Fee Marketing 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeMarketing) }}</td></tr>
                      <tr><td class="px-2 py-2 border">g. PPh 2.65%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(pph) }}</td></tr>
                      <tr><td class="px-2 py-2 border">h. PPN 11%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(ppn) }}</td></tr>
                      <tr class="font-bold border-t bg-gray-100">
                        <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AKHIR (Grand Total)</td>
                        <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                          {{ formatCurrency(grandTotalFinal) }}
                        </td>
                      </tr>
                    </tbody>
                  
                      
                  
                  </table>


                </div>
              </div>






            </div>
          </div>



        <!-- RAB 2 -->


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
              
                <!-- Jika RAB aktif -->
                <div class="p-0" v-if="selectedProject.rab === 2">
                  <button
                    v-if="projectproduct.length !== 0"
                    class="btn bg-primary p-2 text-white mb-2 cursor-pointer"
                    @click="showAddJobModal = true"
                  >
                    + Tambah Pekerjaan
                  </button>
                  <button
                    v-if="projectproduct.length !== 0"
                    @click="handleApproveRabKedua"
                    class="btn bg-yellow-500 p-2 text-white ml-2 mb-2 cursor-pointer"
                  >
                    Approve RAB Kedua
                  </button>


                  <table
                    class="w-full border-collapse border text-sm"
                    v-for="value in projectPekerjaan"
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
                        v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)"
                        :key="dtl.id"
                      >
                        <td class="border px-2 py-2 text-center w-[220px]">
                          <button
                            v-if="dtl.material_id !== null"
                            class="btn btn-sm bg-yellow-600 text-white px-2 cursor-pointer mr-2"
                            @click="infoSupplier(dtl.material_id)"
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
                        <td class="border px-2 py-2">{{ dtl.tambahan }}</td>
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
                    </tbody>
                  </table>

                  <!-- TOTAL DAN RINCIAN -->
                  <div v-if="projectPekerjaan && projectPekerjaan.length > 0" class="mt-6 border-t pt-4">
                   

                    <table class="w-full text-sm border mt-3">
                    <tbody>
                      <tr class="font-bold border-t bg-gray-100">
                        <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AWAL</td>
                        <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                          {{ formatCurrency(grandTotal) }}
                        </td>
                      </tr>
                      <tr><td class="px-2 py-2 border">a. Profit 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(profit) }}</td></tr>
                      <tr><td class="px-2 py-2 border">b. Fee Kantor 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKantor) }}</td></tr>
                      <tr><td class="px-2 py-2 border">c. Fee Staf 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeStaf) }}</td></tr>
                      <tr><td class="px-2 py-2 border">d. Fee Konsultan 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKonsultan) }}</td></tr>
                      <tr><td class="px-2 py-2 border">e. Fee Bendera 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeBendera) }}</td></tr>
                      <tr><td class="px-2 py-2 border">f. Fee Marketing 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeMarketing) }}</td></tr>
                      <tr><td class="px-2 py-2 border">g. PPh 2.65%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(pph) }}</td></tr>
                      <tr><td class="px-2 py-2 border">h. PPN 11%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(ppn) }}</td></tr>
                      <tr class="font-bold border-t bg-gray-100">
                        <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AKHIR (Grand Total)</td>
                        <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                          {{ formatCurrency(grandTotalFinal) }}
                        </td>
                      </tr>
                    </tbody>
                  
                      
                  
                  </table>


                  </div>
                </div>

                <button
                  v-if="selectedProject.rab !== 1 && selectedProject.rab !== 2"
                  class="btn bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                  @click="downloadRABPDF"
                >
                  <i class="fa-solid fa-download"></i> Download RAB
                </button>

                <!-- Jika RAB aktif -->
                <div class="p-0" v-if="selectedProject.rab !== 1 && selectedProject.rab !== 2">


                  <table
                    class="w-full border-collapse border text-sm"
                    v-for="value in projectPekerjaan"
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
                        v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)"
                        :key="dtl.id"
                      >
                        <td colspan="2" class="border px-2 py-2">{{ dtl.tambahan }}</td>
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
                    </tbody>
                  </table>

                  <!-- TOTAL DAN RINCIAN -->
                  <div v-if="projectPekerjaan && projectPekerjaan.length > 0" class="mt-6 border-t pt-4">
                    

                    <table class="w-full text-sm border mt-3">
                    <tbody>
                      <tr class="font-bold border-t bg-gray-100">
                        <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AWAL</td>
                        <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                          {{ formatCurrency(grandTotal) }}
                        </td>
                      </tr>
                      <tr><td class="px-2 py-2 border">a. Profit 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(profit) }}</td></tr>
                      <tr><td class="px-2 py-2 border">b. Fee Kantor 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKantor) }}</td></tr>
                      <tr><td class="px-2 py-2 border">c. Fee Staf 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeStaf) }}</td></tr>
                      <tr><td class="px-2 py-2 border">d. Fee Konsultan 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKonsultan) }}</td></tr>
                      <tr><td class="px-2 py-2 border">e. Fee Bendera 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeBendera) }}</td></tr>
                      <tr><td class="px-2 py-2 border">f. Fee Marketing 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeMarketing) }}</td></tr>
                      <tr><td class="px-2 py-2 border">g. PPh 2.65%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(pph) }}</td></tr>
                      <tr><td class="px-2 py-2 border">h. PPN 11%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(ppn) }}</td></tr>
                      <tr class="font-bold border-t bg-gray-100">
                        <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AKHIR (Grand Total)</td>
                        <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                          {{ formatCurrency(grandTotalFinal) }}
                        </td>
                      </tr>
                    </tbody>
                  
                      
                  
                  </table>


                  </div>
                </div>


            </div>
          </div>
        </div>


        <!-- RAB 3 -->

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

            <div v-if="selectedProject.rab !== 1 && selectedProject.rab !== 2 && selectedProject.rab !== 3">
                  <button
                      v-if="selectedProject.rab === 3"
                      class="btn bg-green-500 p-2 text-white ml-2 mb-4 cursor-pointer w-full float-end"
                      @click="downloadRABPDF"
                    >
                      <i class="fa-solid fa-download"></i> Download RAB Final
                  </button>

                <div class="p-0">


                      <table
                        class="w-full border-collapse border text-sm"
                        v-for="value in projectPekerjaan"
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
                            v-for="dtl in value.detail.filter(d => d.pekerjaan_id === value.pekerjaan_id)"
                            :key="dtl.id"
                          >
                            <td colspan="2" class="border px-2 py-2">{{ dtl.tambahan }}</td>
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
                        </tbody>
                      </table>

                      <!-- TOTAL DAN RINCIAN -->
                      <div v-if="projectPekerjaan && projectPekerjaan.length > 0" class="mt-6 border-t pt-4">
                        

                        <table class="w-full text-sm border mt-3">
                        <tbody>
                          <tr class="font-bold border-t bg-gray-100">
                            <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AWAL</td>
                            <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                              {{ formatCurrency(grandTotal) }}
                            </td>
                          </tr>
                          <tr><td class="px-2 py-2 border">a. Profit 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(profit) }}</td></tr>
                          <tr><td class="px-2 py-2 border">b. Fee Kantor 20%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKantor) }}</td></tr>
                          <tr><td class="px-2 py-2 border">c. Fee Staf 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeStaf) }}</td></tr>
                          <tr><td class="px-2 py-2 border">d. Fee Konsultan 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeKonsultan) }}</td></tr>
                          <tr><td class="px-2 py-2 border">e. Fee Bendera 3%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeBendera) }}</td></tr>
                          <tr><td class="px-2 py-2 border">f. Fee Marketing 2%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(feeMarketing) }}</td></tr>
                          <tr><td class="px-2 py-2 border">g. PPh 2.65%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(pph) }}</td></tr>
                          <tr><td class="px-2 py-2 border">h. PPN 11%</td><td class="px-2 py-2 text-right border">{{ formatCurrency(ppn) }}</td></tr>
                          <tr class="font-bold border-t bg-gray-100">
                            <td class="px-2 py-2 text-left  font-semibold text-lg">TOTAL AKHIR (Grand Total)</td>
                            <td class="px-2 py-2 text-right text-green-700  font-semibold text-lg">
                              {{ formatCurrency(grandTotalFinal) }}
                            </td>
                          </tr>
                        </tbody>
                      
                          
                      
                      </table>


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
