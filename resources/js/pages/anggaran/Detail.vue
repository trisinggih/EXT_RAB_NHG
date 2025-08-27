<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];


</script>

<template>
    <!-- <Head title="Dashboard" /> -->
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class=" p-6">
    <h2 class="text-center text-lg font-bold mb-4 uppercase border-b pb-2">
      Analisa Harga Satuan Pekerjaan
    </h2>
    
    <div class="mb-4">
      <label class="font-semibold">Pekerjaan :</label>
      <input type="text" value="PENGGANTIAN MEKANIK DAN DAUN PINTU" 
             class="border px-2 py-1 w-full mt-1">
    </div>

    <table class="w-full border-collapse border text-sm">
      <thead class="bg-blue-200">
        <tr>
          <th class="border px-2 py-1 w-8">No.</th>
          <th class="border px-2 py-1">Uraian</th>
          <th class="border px-2 py-1 w-24">Satuan</th>
          <th class="border px-2 py-1 w-24">Kuantitas</th>
          <th class="border px-2 py-1 w-32">Harga Satuan (Rp)</th>
          <th class="border px-2 py-1 w-32">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <!-- Baris contoh -->
        <tr>
          <td class="border px-2 py-1 text-center">1</td>
          <td class="border px-2 py-1">Tukang</td>
          <td class="border px-2 py-1 text-center">oh</td>
          <td class="border px-2 py-1 text-center">1.0</td>
          <td class="border px-2 py-1 text-right">101.000</td>
          <td class="border px-2 py-1 text-right">101.000</td>
        </tr>
        <tr>
          <td class="border px-2 py-1 text-center">2</td>
          <td class="border px-2 py-1">Pekerja</td>
          <td class="border px-2 py-1 text-center">oh</td>
          <td class="border px-2 py-1 text-center">1.0</td>
          <td class="border px-2 py-1 text-right">91.000</td>
          <td class="border px-2 py-1 text-right">91.000</td>
        </tr>
      </tbody>
      <tfoot class="bg-gray-100 font-semibold">
        <tr>
          <td colspan="5" class="border px-2 py-1 text-right">Jumlah Total</td>
          <td class="border px-2 py-1 text-right">192.000</td>
        </tr>
      </tfoot>
    </table>
  </div>
         

    </AppLayout>
</template>
