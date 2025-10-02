
<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

// const slides = [
//   "/images/slider1.jpg",
//   "/images/slider2.jpg",
//   "/images/slider3.jpg",
// ];

const props = defineProps({
  banners: { type: Array, default: () => [] },
  services: { type: Array, default: () => [] },
  kontens: { type: Array, default: () => [] },
  settingweb: { type: Array, default: () => [] }
})

const slides = computed(() => props.banners)
const services = computed(() => props.services)
const kontens = computed(() => props.kontens)
const settingweb = computed(() => props.settingweb)
const currentSlide = ref(0);

onMounted(() => {
  console.log('Slides:', slides.value);
  setInterval(() => {
    if (slides.value.length > 0) {
      currentSlide.value = (currentSlide.value + 1) % slides.value.length;
    }
  }, 5000);
});

const activeSlide = computed(() => slides.value[currentSlide.value] || {})


</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.8s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar (Fixed) -->
    <header  class="fixed top-0 left-0 w-full bg-gradient-to-r from-[rgba(125,211,252,0.75)] to-[rgba(129,140,248,0.75)] shadow-md z-50 backdrop-blur-sm">
      <div class="container mx-auto flex justify-between items-center py-4 px-6">
 
          <div class="flex flex-col items-center gap-2">
              <Link :href="route('home')" class="flex flex-col items-center gap-2 grid-cols-2 font-medium">
                <div class="mb-1 flex w-auto items-center justify-center rounded-md">
                    <div class="w-[50px] ">
                      <AppLogoIcon class=" fill-current text-[var(--foreground)] dark:text-white" />
                    </div>
                    
                    <h2 class="text-white mt-1 text-lg font-bold ml-2 space-x-2">CV. NUSANTARA HYDRO GATE</h2>
                </div>
                        
                        
              </Link>
          </div>
          
      
        <nav class="flex space-x-6 text-white">
          <a href="#home" class="hover:underline">Home</a>
          <a href="#layanan" class="hover:underline">Layanan Kami</a>
          <a href="/blog" class="hover:underline">Blog</a>
          <a href="#kontak" class="hover:underline">Hubungi Kami</a>
        </nav>
      </div>
    </header>

    <!-- Hero Section / Image Slider -->
    <section id="home" class="relative w-full h-[80vh] mt-0"> 
      <!-- Images -->
      <div class="absolute inset-0 overflow-hidden">
        <transition-group name="fade" tag="div">
          <img
            v-for="(slide, index) in slides"
            v-show="currentSlide === index"
            :key="slide.id"
            :src="`/images/${slide.img_banner}`"
            :alt="slide.title_banner"
            class="absolute inset-0 w-full h-full object-cover"
          />
        </transition-group>
      </div>

      <!-- Overlay content -->
      <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center text-center text-white">
        <h2 class="text-3xl font-light drop-shadow-md">
          {{ activeSlide.title_banner }}
        </h2>
        <button
          class="mt-6 px-6 py-2 bg-white text-indigo-500 rounded-full shadow hover:bg-gray-100"
        >
          Our Services →
        </button>
      </div>

      <!-- Slider Controls -->
      <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-2">
        <button
          v-for="(slide, index) in slides"
          :key="index"
          @click="currentSlide = index"
          :class="[
            'w-3 h-3 rounded-full',
            currentSlide === index ? 'bg-white' : 'bg-gray-400'
          ]"
        ></button>
      </div>
    </section>

    <!-- Vision Section -->
    <section id="layanan" class="py-12 px-6 text-center">
      <h3 class="text-2xl font-semibold mb-2">Layanan Kami</h3>
      <p class="text-gray-600 mb-10">
        Layanan kami sebagai pengabdian kami untuk negara dan masyarakat
      </p>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto" 
       >
        
        <div  v-for="(service, index) in services"
        :key="index">
          <div class="w-12 h-12 mx-auto bg-sky-200 rounded-full flex items-center justify-center">
            <div v-html="service.icon"></div>
          </div>
          <h4 class="mt-3 font-semibold">{{service.name}}</h4>
          <p class="text-gray-500 text-sm">{{service.description}}</p>
        </div>
        
      </div>
    </section>

    <!-- Product Section -->
    <section class="px-6 py-12 grid md:grid-cols-2 gap-8 container mx-auto">
      <div class="bg-white shadow rounded overflow-hidden h-[580px]">
        <img :src="kontens[0].img_konten" alt="Product" class="w-full" />
        <div class="p-6">
          <h4 class="font-bold text-lg mb-2">{{ kontens[0].judul_konten   }}</h4>
          <p class="text-gray-600 mb-4">
            {{ kontens[0].konten   }}
          </p>
        </div>
      </div>
      <div class="bg-white shadow rounded overflow-hidden mt-[100px]">
        <img :src="kontens[1].img_konten" alt="Product" class="w-full" />
        <div class="p-6">
          <h4 class="font-bold text-lg mb-2">{{ kontens[1].judul_konten   }}</h4>
          <p class="text-gray-600 mb-4">
            {{ kontens[1].konten   }}
          </p>
        </div>
      </div>
    </section>


    <!-- Section: Kontak & Maps -->
    <section id="kontak" class="bg-gray-100 py-16 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <h3 class="text-2xl font-semibold mb-2">Layanan Kami</h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
          
          <!-- Kontak Info -->
          <div class="space-y-6">
            <div class="h-[300px]">
              <h3 class="text-xl font-semibold text-gray-700 mb-2">Alamat</h3>
              <p class="text-gray-600">Jl. Contoh No. 123, Jakarta, Indonesia</p>
              <p class="text-gray-600">(+62) 812-3456-7890</p>
              <p class="text-gray-600">info@contoh.com</p>
              <p class="text-gray-600">Senin - Jumat: 08.00 - 17.00 WIB</p>
            </div>

          </div>

          <!-- Google Maps -->
          <div>
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126917.54981249191!2d106.68943168830583!3d-6.229728010823376!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f157b67e2e05%3A0x401e8f1fc28d360!2sJakarta!5e0!3m2!1sen!2sid!4v1693385944321!5m2!1sen!2sid" 
              width="100%" 
              height="400" 
              style="border:0;" 
              allowfullscreen="" 
              loading="lazy" 
              referrerpolicy="no-referrer-when-downgrade"
              class="rounded-lg shadow-md w-full h-full">
            </iframe>
          </div>

        </div>
      </div>
    </section>


    <!-- Footer -->
    <footer class="bg-sky-200 py-8 mt-auto">
      <div class="container mx-auto grid md:grid-cols-3 gap-8 px-6">
        <div>
          <div class="w-[50px] mb-3">
                      <AppLogoIcon class=" fill-current text-[var(--foreground)] dark:text-white" />
                    </div>
          <h5 class="font-bold text-lg mb-3">CV. Nusantara Hydro Gate</h5>
          
          <p class="text-gray-700">
            {{ settingweb[2].value }}
          </p>
        </div>
        <div>
          <h5 class="font-bold text-lg mb-3">Quick Links</h5>
          <ul class="space-y-2">
            <li><a href="#home" class="hover:underline">Home</a></li>
            <li><a href="#layanan" class="hover:underline">Layanan Kami</a></li>
            <li><a href="/blog" class="hover:underline">Blog</a></li>
            <li><a href="#kontak" class="hover:underline">Hubungi Kami</a></li>
          </ul>
        </div>
        <div>
          <h5 class="font-bold text-lg mb-3">Contact Information</h5>
          <p class="text-gray-700"></p>
          <p class="text-gray-700">+62 812 xxx xxxx</p>
          <div class="flex space-x-4 mt-3 text-xl">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-twitter"></i>
            <i class="fab fa-instagram"></i>
          </div>
        </div>
      </div>
      <p class="text-center text-gray-600 mt-6">© 2025 All rights reserved</p>
    </footer>
  </div>
</template>
