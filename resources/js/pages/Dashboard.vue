<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement } from 'chart.js'
import { defineComponent } from 'vue'
import { Bar } from 'vue-chartjs'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];


// Register Chart.js modules
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement)

// KPI data
const kpi = ref({
  totalProjects: 12,
  totalJobs: 35,
  cashIn: 25000000,
  cashOut: 17500000
})

// Format Rupiah
const formatIDR = (num) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num)
}

const chartData = {
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
  datasets: [
    {
      label: 'Jumlah Project',
      data: [2, 4, 3, 5, 6, 7],
      backgroundColor: '#3b82f6'
    },
    {
      label: 'Kas Masuk',
      data: [4, 7, 3, 8, 5, 6],
      borderColor: '#10b981',
      backgroundColor: '#10b98133',
      type: 'line',
      fill: true,
      tension: 0.3
    }
  ]
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
}

</script>

<template>
    <!-- <Head title="Dashboard" /> -->

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <!-- KPI Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3 xl:grid-cols-4">
                <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-300">Jumlah Project</p>
                <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ kpi.totalProjects.toLocaleString('id-ID') }}
                </p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-300">Jumlah Pekerjaan</p>
                <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ kpi.totalJobs.toLocaleString('id-ID') }}
                </p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-300">Kas Masuk</p>
                <p class="mt-2 text-xl font-semibold text-emerald-600">
                    {{ formatIDR(kpi.cashIn) }}
                </p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-300">Kas Keluar</p>
                <p class="mt-2 text-xl font-semibold text-rose-600">
                    {{ formatIDR(kpi.cashOut) }}
                </p>
                </div>
            </div>

            <!-- Grafik -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border bg-white dark:bg-gray-800 p-5">
                <h3 class="text-base font-semibold mb-4 text-gray-800 dark:text-white">Ringkasan Periode</h3>
                <div class="h-[380px]">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </div>
            </div>

    </AppLayout>
</template>
