<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement } from 'chart.js'
import { Bar } from 'vue-chartjs'
import { usePage } from '@inertiajs/vue3';

const hal = usePage()
const user = hal.props.auth.user as { name: string }

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
]





</script>

<template>
  <ClientLayout :breadcrumbs="breadcrumbs">
     <main class="flex-1 flex items-center justify-center">
      <div class="flex flex-col items-center space-y-4">
        <!-- Kotak merah -->
        <div class="w-40 h-40 ">
          <img
              src="/images/nhg.png"
              alt="Logo"
              className=" w-full h-auto object-contain"
          />
        </div>

        <!-- Teks -->
        <h2 class=" font-semibold tracking-wide uppercase">
          SELAMAT DATANG, <span class="text-blue-900">{{ user.name }}</span>
        </h2>
      </div>
    </main>
  </ClientLayout>
</template>
