<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, defineProps, computed } from 'vue';

interface Product {
  id: number;
  name: string;
  price: number;
}

interface Gambar {
  id: number;
  path: string;
}

interface Annotation {
  id: number;
  x: number;
  y: number;
  label: string;
}

interface MasterMaterial {
  id: number;
  name: string;
  satuan: string;
}

interface Bom {
  id: number;
  product_id: number;
  material_id: number;
  mark_id: number;
  jumlah: number;
  panjang: number;
  lebar: number;
  tinggi: number;
  satuan: string;
  estimasi_price: number;
  keterangan: string;
}

const page = usePage();

// props dari inertia
const props = defineProps<{
  product: Product;
  annotations?: Annotation[];
  imagePath?: string | null;
  image?: Gambar;
  ms_material?: MasterMaterial[];
  bom?: Bom[];
}>();

// refs
const canvasRef = ref<HTMLCanvasElement | null>(null);
const marks = ref<Annotation[]>(props.annotations ?? []);
const msmat = ref<MasterMaterial[]>(props.ms_material ?? []);
const imagePath = ref<string | null>(props.imagePath ?? null);
const imageId = ref<number | null>(props.image?.id ?? null);
const bommaterial = ref<Bom[]>(props.bom ?? []);

const selectedFile = ref<File | null>(null);

// fungsi draw ulang canvas
const draw = () => {
  const canvas = canvasRef.value;
  if (!canvas || !imagePath.value) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const img = new Image();
  img.src = `${imagePath.value}`;
  img.onload = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

    marks.value.forEach((m) => {
      const paddingX = 8;
      const paddingY = 4;

      ctx.font = '12px Arial';
      const text = m.label;
      const textWidth = ctx.measureText(text).width;
      const rectWidth = textWidth + paddingX * 2;
      const rectHeight = 20;

      const rectX = m.x - rectWidth / 2;
      const rectY = m.y - rectHeight / 2;

      const radius = rectHeight / 2;
      ctx.beginPath();
      ctx.moveTo(rectX + radius, rectY);
      ctx.lineTo(rectX + rectWidth - radius, rectY);
      ctx.quadraticCurveTo(rectX + rectWidth, rectY, rectX + rectWidth, rectY + radius);
      ctx.lineTo(rectX + rectWidth, rectY + rectHeight - radius);
      ctx.quadraticCurveTo(rectX + rectWidth, rectY + rectHeight, rectX + rectWidth - radius, rectY + rectHeight);
      ctx.lineTo(rectX + radius, rectY + rectHeight);
      ctx.quadraticCurveTo(rectX, rectY + rectHeight, rectX, rectY + rectHeight - radius);
      ctx.lineTo(rectX, rectY + radius);
      ctx.quadraticCurveTo(rectX, rectY, rectX + radius, rectY);
      ctx.closePath();

      ctx.fillStyle = 'red';
      ctx.fill();

      ctx.fillStyle = 'white';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(text, m.x, m.y);
    });
  };
};

// klik untuk kasih penanda
const handleClick = (e: MouseEvent) => {
  if (!imagePath.value) return;

  const canvas = canvasRef.value;
  if (!canvas) return;
  const rect = canvas.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;

  const label = prompt('Masukkan label penandaan:');
  if (!label) return;

  // kasih id unik
  const newMark: Annotation = { id: Date.now(), x, y, label };
  marks.value.push(newMark);

  // simpan ke backend
  router.post('/annotations', {
    image_id: imageId.value,
    product_id: props.product.id,
    mark_id: newMark.id,
    x,
    y,
    label,
  }, {
    onSuccess: () => {
      // reload props dari server tanpa reload browser penuh
      router.visit(window.location.pathname, { replace: true });
      
    }
  });

  

};

// pilih file
const handleFileSelect = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files?.length) {
    selectedFile.value = target.files[0];
  }
};

// gabungkan marks dengan bommaterial
const marksWithBOM = computed(() => {
  return marks.value.map((m) => {
    const bom = bommaterial.value.find(b => b.mark_id === m.id) || {};
    return {
      ...m,
      product_id: props.product.id,
      mark_id: m.id,
      materialId: bom.material_id ?? "",
      satuan: bom.satuan ?? "",
      jumlah: bom.jumlah ?? 0,
      panjang: bom.panjang ?? 0,
      lebar: bom.lebar ?? 0,
      tinggi: bom.tinggi ?? 0,
      estimasi_price: bom.estimasi_price ?? 0,
    };
  });
});

const form = useForm({
  image: null,
  product_id: props.product.id,
});

const handleSubmit = () => {
  form.post(route('upload.image'), {
    forceFormData: true,
    onSuccess: (page) => {
      if (page.props.image.path) {
        imagePath.value = page.props.image.path;
        imageId.value = page.props.image.id;
        setTimeout(() => {
          draw();
        }, 500);
      }
    },
    onError: (errors) => {
      console.error("Upload gagal:", errors);
    }
  });
};

onMounted(() => {
  if (props.image) {
    imageId.value = props.image.id;
    imagePath.value = props.image.path;

    setTimeout(() => {
      draw();
    }, 500);
  }
});

const rows = ref<any[]>([]);

onMounted(() => {
  rows.value = marks.value.map((m, idx) => {
    const bom = bommaterial.value.find(b => b.mark_id === m.id) || {};
    return {
      ...m,
      product_id: props.product.id,
      mark_id: bom.mark_id ?? m.id,
      materialId: bom.material_id ?? "",
      satuan: bom.satuan ?? "",
      jumlah: bom.jumlah ?? 0,
      panjang: bom.panjang ?? 0,
      lebar: bom.lebar ?? 0,
      tinggi: bom.tinggi ?? 0,
      estimasi_price: bom.estimasi_price ?? 0,
      keterangan: bom.keterangan ?? "",
    };
  });
});

const getMaterialById = (id: string | number) => {
  // const material = msmat.value.find(m => m.id === Number(id));

  // console.log(material.satuan)
  return msmat.value.find(m => m.id === Number(id));
};



const saveRow = (row: any) => {
  router.post('/bommaterial/create', {
    product_id: props.product.id,
    material_id: row.materialId,
    mark_id: row.mark_id,
    jumlah: row.jumlah,
    panjang: row.panjang,
    lebar: row.lebar,
    tinggi: row.tinggi,
    satuan: row.satuan,
    estimasi_price: 0,
    keterangan: row.keterangan,
  });

  router.visit(window.location.pathname, { replace: true });


};



const deleteRow = (row: any) => {
  router.post(`/bommaterialdelete`,{
     product_id: props.product.id,
     material_id: row.materialId,
     mark_id: row.mark_id,
  });
  marks.value.splice(rows.value.indexOf(row), 1);
};
</script>

<template>
  <Head title="Edit a product" />

  <AppLayout :breadcrumbs="[{ title: 'Edit a product', href: `/products` }]">
    <div class="mb-6">
      <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
        Detail Bill of Material
        <p class="text-white mt-1 text-sm font-normal">
          Perhitungan estimasi pekerjaan
          <b>{{ props.product.name }}</b>
          <h5 class="float-right font-bold">Rp.{{ props.product.price }}</h5>
        </p>
      </h1>
    </div>

    <div class="p-4 mb-3">
      <div class="grid grid-cols-5 gap-4 mb-4">
        <!-- Upload -->
        <div class="col-span-4 mb-4" v-if="!imageId">
          <form @submit.prevent="handleSubmit" class="flex gap-2 items-center">
            <input
              id="image"
              type="file"
              accept="image/*"
              @change="(e) => form.image = e.target.files[0]"
              class="border p-2 rounded"
            />
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
              Upload
            </button>
          </form>
        </div>

        <!-- Canvas -->
        <div class="col-span-4 max-w-[810px]" v-if="imageId && imagePath">
          <canvas
            ref="canvasRef"
            width="800"
            height="600"
            class="border border-black"
            @click="handleClick"
          ></canvas>
        </div>

        <!-- Sidebar -->
        <div class="col-start-5 w-full text-right">
          <h2 class="font-semibold mb-2">Daftar Penandaan</h2>
          <ul>
            <li v-for="(m, idx) in marks" :key="idx" class="text-sm mb-1 flex items-center gap-2">
              <span class="w-3 h-3 rounded-full bg-red-500"></span>
              {{ m.label }} (x: {{ m.x }}, y: {{ m.y }})
            </li>
          </ul>

          <hr class="my-4 border-dotted" />

          <p class="text-red-600 text-lg w-full">
            Pastikan anda mengisi data informasi pada tabel di bawah ini. guna sebagai bill of material produksi.
          </p>
        </div>
      </div>
    </div>

    <!-- Tabel BOM -->
    <div class="overflow-auto rounded border border-gray-200 shadow-sm w-full p-10">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 sticky top-0 z-10">
          <tr class="text-gray-700">
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white">Kode</th>
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white">Material</th>
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white w-[100px]">Satuan</th>
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white w-[100px]">Jumlah</th>
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white w-[100px]">Panjang</th>
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white w-[100px]">Lebar</th>
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white w-[100px]">Tinggi</th>
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white w-[200px]">Keterangan</th>
            <th class="px-4 py-3 border-b border-gray-200 text-left bg-gray-500 text-white w-[100px]">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(m, idx) in rows" :key="idx">
            <td class="border-b border-gray-200">
              <input :value="m.label" class="border border-gray-200 p-2 w-full" disabled />
            </td>

            <td class="border-b border-gray-200">
              <select 
                v-model="m.materialId"
                class="border border-gray-200 p-2 w-full"
                @change="m.satuan = getMaterialById(m.materialId)?.satuan"
              >
                <option disabled value="">-- Pilih Material --</option>
                <option v-for="(materials, idm) in msmat" :key="idm" :value="materials.id">
                  {{ materials.name }}
                </option>
              </select>
            </td>

            <td class=" border-b border-gray-200">
              <input class="border border-gray-200 p-2 w-[100px]" type="text" v-model="m.satuan" disabled />
            </td>

            <td class="border-b border-gray-200">
              <input v-model="m.jumlah" type="number" class="border border-gray-200 p-2 w-[100px]" />
            </td>
            <td class=" border-b border-gray-200">
              <input v-model="m.panjang" type="number" class="border border-gray-200 p-2 w-[100px]" />
            </td>
            <td class="border-b border-gray-200">
              <input v-model="m.lebar" type="number" class="border border-gray-200 p-2 w-[100px]" />
            </td>
            <td class="border-b border-gray-200">
              <input v-model="m.tinggi" type="number" class="border border-gray-200 p-2 w-[100px]" />
            </td>

            <td class="border-b border-gray-200">
              <input v-model="m.keterangan" type="text" class="border border-gray-200 p-2 w-[200px]" />
            </td>

            <td class="border-b border-gray-200 w-[150px]">
              <button class="bg-primary text-white  p-2 rounded cursor-pointer" @click="saveRow(m)">
                Simpan
              </button>
              <button class="bg-red-600 text-white p-2 rounded ml-2 cursor-pointer" @click="deleteRow(m)">
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
