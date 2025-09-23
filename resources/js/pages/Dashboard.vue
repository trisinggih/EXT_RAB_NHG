<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement } from 'chart.js'
import { Bar } from 'vue-chartjs'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
]




ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement)

// KPI state
const kpi = ref({
  total_a: 0,
  total_b: 0,
  total_c: 0,
  total_d: 0
})

const chartData = ref({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
  datasets: [
    {
      label: 'Jumlah Project',
      data: Array(12).fill(0), // default 0 semua
      backgroundColor: '#3b82f6'
    }
  ]
})

const chartReactiveData = computed(() => chartData.value)

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
}

// Fetch KPI
onMounted(async () => {
  try {
    const { data } = await axios.get('/hasildashboard')
    kpi.value = data
  } catch (error) {
    console.error('Gagal fetch KPI:', error)
  }
})



onMounted(async () => {
  try {
    const { data } = await axios.get('/hasildashboardchart')

    const projectPerBulan = Array(12).fill(0)
    data.forEach((item: { bulan: number, total_project: number }) => {
      projectPerBulan[item.bulan - 1] = item.total_project
    })

    // ganti seluruh object, bukan cuma array di dalamnya
    chartData.value = {
      labels: chartData.value.labels,
      datasets: [
        {
          label: 'Jumlah Project',
          data: projectPerBulan,
          backgroundColor: '#3b82f6'
        }
      ]
    }
  } catch (e) {
    console.error(e)
  }
})

</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <!-- KPI -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-3 xl:grid-cols-4">
        <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
          <p class="text-sm text-gray-500 dark:text-gray-300">Jumlah Project</p>
          <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
            {{ kpi.total_a }}
          </p>
        </div>
        <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
          <p class="text-sm text-gray-500 dark:text-gray-300">Jumlah Product</p>
          <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
            {{ kpi.total_b }}
          </p>
        </div>
        <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
          <p class="text-sm text-gray-500 dark:text-gray-300">Jumlah Client</p>
          <p class="mt-2 text-xl font-semibold text-gray-900">
            {{ kpi.total_c }}
          </p>
        </div>
        <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
          <p class="text-sm text-gray-500 dark:text-gray-300">Jumlah User</p>
          <p class="mt-2 text-xl font-semibold text-gray-900">
            {{ kpi.total_d }}
          </p>
        </div>
      </div>

      <!-- Chart -->
      <div class="relative min-h-[100vh] flex-1 rounded-xl border md:min-h-min bg-white dark:bg-gray-800 p-5">
        <h3 class="text-base font-semibold mb-4 text-gray-800 dark:text-white">Ringkasan Periode</h3>
        <div class="h-[380px]">
          <Bar :data="chartReactiveData" :options="chartOptions" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
