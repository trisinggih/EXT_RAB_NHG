<script setup lang="ts">
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { X, Trash2 } from 'lucide-vue-next'
import Swal from 'sweetalert2'

interface Projects {
  id: number
  name: string
  client_id: number
  description?: string
  start_date: string
  end_date: string
}

const props = defineProps<{
  projects: Projects
  projectGambar: Array<{ id: number; gambar: string; keterangan: string }>
}>()

// === Form Upload ===
const form = useForm({
  gambar: [] as { file: File; keterangan: string }[],
  project_id: props.projects.id,
})

const previews = ref<{ url: string; file: File; keterangan: string }[]>([])
const dragOver = ref(false)

// === Tambah File ke Preview ===
const addFiles = (files: File[]) => {
  files.forEach((file) => {
    if (!(file instanceof File)) return
    const url = URL.createObjectURL(file)
    previews.value.push({ url, file, keterangan: '' })
    form.gambar.push({ file, keterangan: '' })
  })
}

// === Handle File Input ===
const handleFiles = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (input.files) {
    const files = Array.from(input.files)
    addFiles(files)
    input.value = '' // reset agar bisa upload file yang sama lagi
  }
}

// === Handle Drag & Drop ===
const handleDrop = (e: DragEvent) => {
  e.preventDefault()
  dragOver.value = false
  if (e.dataTransfer?.files) {
    const files = Array.from(e.dataTransfer.files)
    addFiles(files)
  }
}

// === Hapus Preview ===
const removePreview = (index: number) => {
  const item = previews.value[index]
  if (item) URL.revokeObjectURL(item.url)
  previews.value.splice(index, 1)
  form.gambar.splice(index, 1)
}

// === Upload ke Server ===
const uploadFoto = () => {
  if (form.gambar.length === 0) {
    Swal.fire('Peringatan', 'Pilih minimal satu gambar.', 'warning')
    return
  }

  const formData = new FormData()
  form.gambar.forEach((g, i) => {
    formData.append(`gambar[${i}]`, g.file)
    formData.append(`keterangan[${i}]`, g.keterangan)
  })
  formData.append('project_id', props.projects.id.toString())

  router.post(route('project-gambar.store'), formData, {
    onSuccess: () => {
      Swal.fire('Berhasil', 'Foto berhasil diupload!', 'success')
      form.reset()
      previews.value.forEach((p) => URL.revokeObjectURL(p.url))
      previews.value = []
      nextTick(() => router.reload({ only: ['projectGambar'] }))
    },
  })
}

// === Hapus dari Database ===
const deleteFoto = (id: number) => {
  Swal.fire({
    title: 'Hapus foto ini?',
    text: 'Tindakan ini tidak dapat dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('project-gambar.destroy', id), {
        onSuccess: () => {
          Swal.fire('Terhapus!', 'Foto berhasil dihapus.', 'success')
          router.reload({ only: ['projectGambar'] })
        },
      })
    }
  })
}

// === Full Preview + Navigasi ===
const showPreview = ref(false)
const selectedImage = ref<string | null>(null)
const selectedIndex = ref<number>(0)
const previewList = ref<string[]>([])

const openPreview = (url: string, list: string[], index: number) => {
  previewList.value = list
  selectedIndex.value = index
  selectedImage.value = url
  showPreview.value = true
}

const closePreview = () => {
  showPreview.value = false
  selectedImage.value = null
  previewList.value = []
}

const nextImage = () => {
  if (!previewList.value.length) return
  selectedIndex.value = (selectedIndex.value + 1) % previewList.value.length
  selectedImage.value = previewList.value[selectedIndex.value]
}

const prevImage = () => {
  if (!previewList.value.length) return
  selectedIndex.value =
    (selectedIndex.value - 1 + previewList.value.length) %
    previewList.value.length
  selectedImage.value = previewList.value[selectedIndex.value]
}

// === Keyboard Navigation ===
const handleKey = (e: KeyboardEvent) => {
  if (!showPreview.value) return
  if (e.key === 'ArrowRight') nextImage()
  if (e.key === 'ArrowLeft') prevImage()
  if (e.key === 'Escape') closePreview()
}

onMounted(() => window.addEventListener('keydown', handleKey))
onBeforeUnmount(() => window.removeEventListener('keydown', handleKey))
</script>

<template>
  <Head title="Upload Foto Project" />

  <AppLayout>
    <div class="mb-6">
      <h1
        class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary"
      >
        Upload Foto
        <p class="text-white mt-1 text-sm font-normal">
          Tambahkan dokumentasi gambar project.
        </p>
      </h1>
    </div>

    <div class="p-4 space-y-6">
      <!-- === Dropzone === -->
      <div
        class="border-2 border-dashed rounded-xl p-6 text-center transition-all duration-200"
        :class="dragOver ? 'border-primary bg-blue-50' : 'border-gray-300'"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop="handleDrop"
      >
        <p class="text-gray-600 mb-2">Tarik dan lepaskan foto di sini</p>
        <p class="text-sm text-gray-400 mb-3">atau klik untuk memilih</p>

        <input
          type="file"
          multiple
          accept="image/*"
          class="hidden"
          id="fileUpload"
          @change="handleFiles"
        />
        <label
          for="fileUpload"
          class="cursor-pointer bg-primary text-white px-4 py-2 rounded-lg shadow"
        >
          Pilih Foto
        </label>
      </div>

      <!-- === Preview Sebelum Upload === -->
      <div
        v-if="previews.length"
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4"
      >
        <div
          v-for="(p, index) in previews"
          :key="index"
          class="relative border rounded-lg overflow-hidden group bg-white shadow-sm"
        >
          <img
            :src="p.url"
            class="object-cover w-full h-40 border-b cursor-pointer hover:opacity-80 transition"
            alt="Preview"
            @click="openPreview(p.url, previews.map(i => i.url), index)"
          />

          <div class="p-3 space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Keterangan:
            </label>
            <input
              type="text"
              v-model="p.keterangan"
              placeholder="Tuliskan keterangan foto..."
              class="w-full border border-gray-300 rounded-md p-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
              @input="form.gambar[index].keterangan = p.keterangan"
            />
          </div>

          <button
            type="button"
            class="absolute top-1 right-1 bg-black bg-opacity-60 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition"
            @click="removePreview(index)"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- === Tombol Upload === -->
      <div class="flex justify-end">
        <Button
          @click="uploadFoto"
          class="bg-primary hover:bg-primary/80 text-white"
        >
          Upload Foto
        </Button>
      </div>

      <!-- === Foto dari Database === -->
      <div v-if="props.projectGambar.length" class="pt-6">
        <h2 class="text-md font-semibold mb-3">Foto Tersimpan</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          <div
            v-for="(g, index) in props.projectGambar"
            :key="g.id"
            class="relative border rounded-lg overflow-hidden group"
          >
            <img
              :src="`/storage/${g.gambar}`"
              class="object-cover w-full h-32 cursor-pointer hover:opacity-80 transition"
              alt="Gambar project"
              @click="openPreview(`/storage/${g.gambar}`, props.projectGambar.map(i => `/storage/${i.gambar}`), index)"
            />
            <button
              type="button"
              class="absolute top-1 right-1 bg-red-600 bg-opacity-80 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition"
              @click="deleteFoto(g.id)"
            >
              <Trash2 class="w-4 h-4" />
            </button>
            <div class="p-2 text-sm text-gray-600 text-center">
              {{ g.keterangan || 'Tanpa keterangan' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- === Modal Full Preview === -->
    <div
      v-if="showPreview"
      class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50"
      @click.self="closePreview"
    >
      <button
        class="absolute left-4 text-white text-4xl font-bold p-2 rounded-full hover:bg-white/20 transition"
        @click.stop="prevImage"
      >
        ‹
      </button>

      <div class="relative max-w-6xl w-full flex justify-center p-4">
        <img
          :src="selectedImage"
          class="max-h-[90vh] object-contain rounded-lg shadow-lg select-none"
          alt="Full Preview"
        />
        <button
          class="absolute top-4 right-4 bg-black bg-opacity-60 text-white rounded-full p-2 hover:bg-opacity-80 transition"
          @click="closePreview"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <button
        class="absolute right-4 text-white text-4xl font-bold p-2 rounded-full hover:bg-white/20 transition"
        @click.stop="nextImage"
      >
        ›
      </button>
    </div>
  </AppLayout>
</template>
