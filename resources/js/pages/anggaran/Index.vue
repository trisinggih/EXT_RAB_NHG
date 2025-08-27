<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

interface Project {
  id: number;
  name: string;
  client_name: string;
  client_id: number;
  description?: string;
}

const props = defineProps<{
  projects: Project[];
}>();

const projects = ref<Project[]>(props.projects ?? []);
const selectedProjectId = ref<number | null>(null);

// Ambil data project terpilih
const selectedProject = computed(() =>
  projects.value.find((p) => p.id === selectedProjectId.value)
);

// Tipe baris dan detail
interface TableDetail {
  key: string;
  value: string;
}

interface TableRow {
  name: string;
  value: string;
  expanded: boolean;
  details: TableDetail[];
}

// Data tabel utama
const tableRows = ref<TableRow[]>([]);

// Tambah baris utama
const addRow = () => {
  tableRows.value.push({
    name: '',
    value: '',
    expanded: false,
    details: [],
  });
};

// Toggle detail per baris
const toggleExpand = (index: number) => {
  const row = tableRows.value[index];
  if (!row) return;
  row.expanded = !row.expanded;
};

// Tambah baris detail
const addDetailRow = (index: number) => {
  const row = tableRows.value[index];
  if (!row) return;
  row.details.push({ key: '', value: '' });
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Dropdown Project -->
      <div>
        <label for="project-select" class="block mb-1 font-medium">Pilih Project:</label>
        <select
          id="project-select"
          v-model="selectedProjectId"
          class="p-2 border rounded w-full"
        >
          <option disabled value="">-- Pilih Project --</option>
          <option
            v-for="project in projects"
            :key="project.id"
            :value="project.id"
          >
            {{ project.name }}
          </option>
        </select>
      </div>

      <!-- Tabel dan konten setelah pilih project -->
      <div v-if="selectedProject">
        <h2 class="text-lg font-semibold mb-2">Detail Project: {{ selectedProject.name }}</h2>

        <table class="table-auto w-full border border-gray-300 mb-4">
          <thead class="bg-gray-100">
            <tr>
              <th colspan="2" class="border px-4 py-2">Nama</th>
              <th class="border px-4 py-2 w-[200px]">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(row, index) in tableRows" :key="index">
              <!-- Baris utama -->
              <tr>
                <td colspan="2" class="border px-4 py-2">
                  <input
                    v-model="row.name"
                    class="w-full p-1 border rounded"
                    placeholder="Pekerjaan"
                  />
                </td>
                <!-- <td class="border px-4 py-2">
                  <input
                    v-model="row.value"
                    class="w-full p-1 border rounded"
                    placeholder="Masukkan value"
                  />
                </td> -->
                <td class="border px-4 py-2 text-center">
                    <button
                    @click="toggleExpand(index)"
                    class="text-sm p-1 bg-green-600 text-white rounded hover:bg-green-500 mr-1"
                  >
                    {{ row.expanded ? 'Simpan' : 'Simpan' }}
                  </button>

                  <button
                    @click="toggleExpand(index)"
                    class="text-sm p-1 bg-blue-400 text-white rounded hover:bg-blue-500"
                  >
                    {{ row.expanded ? 'Sembunyikan' : 'Lihat Detail' }}
                  </button>
                </td>
              </tr>

              <!-- Sub-tabel detail -->
              <tr v-if="row.expanded">
                <td colspan="3" class="border px-4 py-2 bg-gray-50">
                  <div>
                    <p class="font-semibold mb-2">Detail untuk {{ row.name || '(tanpa nama)' }}:</p>
                    <table class="table-auto w-full border border-gray-300 mb-2">
                      <thead class="bg-gray-100">
                        <tr>
                          <th class="border px-2 py-1">Key</th>
                          <th class="border px-2 py-1">Value</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(detail, dIndex) in row.details" :key="dIndex">
                          <td class="border px-2 py-1">
                            <input
                              v-model="detail.key"
                              class="w-full p-1 border rounded"
                              placeholder="Key"
                            />
                          </td>
                          <td class="border px-2 py-1">
                            <input
                              v-model="detail.value"
                              class="w-full p-1 border rounded"
                              placeholder="Value"
                            />
                          </td>
                        </tr>
                      </tbody>
                    </table>

                    <!-- Tombol tambah detail -->
                    <button
                      @click="addDetailRow(index)"
                      class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700"
                    >
                      + Tambah Detail
                    </button>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>

        <!-- Tombol tambah baris utama -->
        <button
          @click="addRow"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          + Tambah Baris
        </button>
      </div>
    </div>
  </AppLayout>
</template>
