<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router} from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref, computed, watch, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types';

const handleUserDeletion = (userID: number) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('users.destroy', { id: userID }), {
            preserveScroll: true,
            onSuccess: () => {
                users.value = users.value.filter(user => user.id !== userID)
            },
            onError: (err) => {
                console.error('Gagal menghapus:', err)
            }
        });
    }
}


const props = defineProps({
  users: { type: Object, default: null }
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'users',
        href: '/users',
    },
];

const users = ref(props.users?.data ?? [])

onMounted(async () => {
  if (!users.value.length) {
    try {
      const res = await axios.get(route('users.index'))
      users.value = res.data.data // sesuaikan format JSON response
    } catch (e) {
      console.error(e)
    }
  }
})

const source = computed(() => users.value)

// Search dengan debounce agar ringan
const searchInput = ref('')
const search = ref('')
let timer
watch(
  () => searchInput.value,
  (v) => {
    clearTimeout(timer)
    timer = setTimeout(() => {
      search.value = v.trim()
      page.value = 1
    }, 300)
  }
)
const clearSearch = () => {
  searchInput.value = ''
  search.value = ''
}

const pageSize = ref(10)
const page = ref(1)

const norm = (s) => (s ?? '').toString().toLowerCase()

const filtered = computed(() => {
  if (!search.value) return source.value
  const q = norm(search.value)
  return source.value.filter((u) => {
    const hay = `${norm(u.id)} ${norm(u.name)} ${norm(u.email)} ${norm(u.role_id)} ${norm(u.email_verified_at)}`
    return hay.includes(q)
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / pageSize.value)))
watch([pageSize, filtered], () => {
  if (page.value > totalPages.value) page.value = totalPages.value
})

const pageItems = computed(() => {
  const start = (page.value - 1) * pageSize.value
  return filtered.value.slice(start, start + pageSize.value)
})

const startIndex = computed(() => Math.min(filtered.value.length, (page.value - 1) * pageSize.value))
const endIndex = computed(() => Math.min(filtered.value.length, startIndex.value + pageSize.value))

</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mb-6 ">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
                Users List
                <p class="text-white mt-1 text-sm font-normal">Kelola daftar pengguna Anda.</p>
            </h1>
        
        </div>
        <div class="px-6 py-1">
        <!-- Title -->
        

        <!-- Controls -->
        <div class="flex flex-wrap items-center gap-3 justify-between mb-4">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Show</label>
            <select
            v-model.number="pageSize"
            class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>

            <Link :href="route('users.create')">
              <button class=" btn bg-primary p-2 text-white rounded ml-2">Tambah User</button>
            </Link>

        </div>

        <div class="relative">
            <input
            v-model="searchInput"
            type="text"
            placeholder="Cari nama, email, atau ID..."
            class="border border-gray-300 rounded-lg pl-9 pr-8 py-2 text-sm w-72 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
            />
            <!-- search icon -->
            <svg
                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 opacity-60"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                >
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <button
              v-if="search"
              @click="clearSearch"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              aria-label="Clear search"
            >
            ×
            </button>
        </div>
        </div>

        <!-- Table -->
        <div class="overflow-auto rounded border border-gray-200 shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 sticky top-0 z-10">
            <tr class="text-gray-700">
                <th class="px-4 py-3 border-b border-gray-200 text-left">ID</th>
                <th class="px-4 py-3 border-b border-gray-200 text-left">Nama</th>
                <th class="px-4 py-3 border-b border-gray-200 text-left">Jabatan</th>
                <th class="px-4 py-3 border-b border-gray-200 text-left">Role</th>
                <th class="px-4 py-3 border-b border-gray-200 text-left">Email</th>
                <!-- <th class="px-4 py-3 border-b border-gray-200 text-left">Terverifikasi</th> -->
                <th class="px-4 py-3 border-b border-gray-200 text-left w-[100px]">Action</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="(u,i) in pageItems"
                :key="u.id"
                class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition-colors"
            >
                <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap">{{ startIndex + i + 1 }}</td>
                <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap font-medium text-gray-800">{{ u.name }}</td>
                <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap">{{ u.jabatan ?? '-' }}</td>
                <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap">{{ u.role_name ?? '-' }}</td>
                <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap">{{ u.email }}</td>
                <!-- <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap">{{ moment(u.email_verified_at).format('D MMMM YYYY') }}</td> -->
                <td class="px-4 py-3 border-b border-gray-200 whitespace-nowrap">
                  <div class="flex gap-1">
                        <Link :href="route('users.edit', {id: u.id})">
                          <Button class="bg-yellow-500 text-white">
                              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/></svg>
                          </Button>
                        </Link>
                        <Button class="bg-red-600 text-white" @click="handleUserDeletion(u.id)">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M6 7h12v2H6zm2 3h8v9H8zm3-6h2v2h-2z"/></svg>
                        
                        </Button>
                    </div>
                </td>
            </tr>
            <tr v-if="pageItems.length === 0">
                <td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada data yang cocok.</td>
            </tr>
            </tbody>
        </table>
        </div>

        <!-- Footer / Pagination -->
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
        <div class="text-gray-500">
            Menampilkan {{ startIndex + 1 }}–{{ endIndex }} dari {{ filtered.length }} data
        </div>
        <div class="inline-flex overflow-hidden rounded-xl border border-gray-200">
            <button
            class="px-3 py-1 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none"
            :disabled="page === 1"
            @click="page--"
            >
            « Prev
            </button>

            <button
            v-for="p in totalPages"
            :key="p"
            class="px-3 py-1 hover:bg-gray-50"
            :class="p === page ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'"
            @click="page = p"
            >
            {{ p }}
            </button>

            <button
            class="px-3 py-1 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none"
            :disabled="page === totalPages"
            @click="page++"
            >
            Next »
            </button>
        </div>
        </div>
    </div>
    </AppLayout>
</template>
