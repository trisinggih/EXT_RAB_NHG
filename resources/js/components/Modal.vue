<template>
  <div v-if="isVisible" class="fixed inset-0  flex justify-center items-center z-50" :style="{ backgroundColor: 'rgba(0, 0, 0, 0.7)' }">
    <div class="bg-white p-6 rounded shadow max-w-md w-full">
      <h2 class="text-xl font-bold mb-4">{{ title }}</h2>
      <p class="mb-4">{{ content }}</p>
      <button @click="$emit('update:isVisible', false)" class="bg-blue-500 text-white px-4 py-2 rounded">
        Close
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  content: string;
  isVisible: boolean;
}>();
</script>
