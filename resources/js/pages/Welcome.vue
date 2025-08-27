
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
  banners: { type: Array, default: () => [] }
})

const slides = computed(() => props.banners)


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
    <header class="fixed top-0 left-0 w-full bg-gradient-to-r from-[rgba(125,211,252,0.75)] to-[rgba(129,140,248,0.75)] shadow-md z-50 backdrop-blur-sm">
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
          <a href="#" class="hover:underline">Home</a>
          <a href="#" class="hover:underline">Layanan Kami</a>
          <!-- <div class="relative group">
            <button class="hover:underline">Product</button>
            <div
              class="absolute left-0 mt-2 hidden group-hover:block bg-white text-gray-700 rounded shadow-md w-40"
            >
              <a href="#" class="block px-4 py-2 hover:bg-gray-100">Kitchen Furniture</a>
              <a href="#" class="block px-4 py-2 hover:bg-gray-100">Chairs</a>
              <a href="#" class="block px-4 py-2 hover:bg-gray-100">Tables</a>
              <a href="#" class="block px-4 py-2 hover:bg-gray-100">Workspace</a>
            </div>
          </div> -->
          <a href="#" class="hover:underline">Tentang Kami</a>
          <a href="#" class="hover:underline">Hubungi Kami</a>
        </nav>
      </div>
    </header>

    <!-- Hero Section / Image Slider -->
    <section class="relative w-full h-[80vh] mt-0"> 
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
    <section class="py-12 px-6 text-center">
      <h3 class="text-2xl font-semibold mb-2">Visi & Misi Kami</h3>
      <p class="text-gray-600 mb-10">
        Our mission is to provide good quality furniture and eco-friendly products
      </p>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
        <div>
          <div class="w-12 h-12 mx-auto bg-sky-200 rounded-full flex items-center justify-center">
            🌍
          </div>
          <h4 class="mt-3 font-semibold">Save Planet</h4>
          <p class="text-gray-500 text-sm">Eco friendly products</p>
        </div>
        <div>
          <div class="w-12 h-12 mx-auto bg-sky-200 rounded-full flex items-center justify-center">
            📈
          </div>
          <h4 class="mt-3 font-semibold">Strategy</h4>
          <p class="text-gray-500 text-sm">Sustainable business</p>
        </div>
        <div>
          <div class="w-12 h-12 mx-auto bg-sky-200 rounded-full flex items-center justify-center">
            💬
          </div>
          <h4 class="mt-3 font-semibold">Help & Support</h4>
          <p class="text-gray-500 text-sm">24/7 assistance</p>
        </div>
        <div>
          <div class="w-12 h-12 mx-auto bg-sky-200 rounded-full flex items-center justify-center">
            🌱
          </div>
          <h4 class="mt-3 font-semibold">Grow Up</h4>
          <p class="text-gray-500 text-sm">Grow with innovation</p>
        </div>
      </div>
    </section>

    <!-- Product Section -->
    <section class="px-6 py-12 grid md:grid-cols-2 gap-8 container mx-auto">
      <div class="bg-white shadow rounded overflow-hidden">
        <img src="/images/product1.jpg" alt="Product" class="w-full" />
        <div class="p-6">
          <h4 class="font-bold text-lg mb-2">Product</h4>
          <p class="text-gray-600 mb-4">
            As a humanistic company, we believe that gender equality is good for our
            company and our customers...
          </p>
          <button class="px-4 py-2 bg-sky-200 rounded hover:bg-sky-300">
            View Product
          </button>
        </div>
      </div>
      <div class="bg-white shadow rounded overflow-hidden">
        <img src="/images/product2.jpg" alt="Product" class="w-full" />
        <div class="p-6">
          <h4 class="font-bold text-lg mb-2">Product</h4>
          <p class="text-gray-600 mb-4">
            As a humanistic company, we believe that gender equality is good for our
            company and our customers...
          </p>
          <button class="px-4 py-2 bg-sky-200 rounded hover:bg-sky-300">
            View Product
          </button>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-sky-200 py-8 mt-auto">
      <div class="container mx-auto grid md:grid-cols-3 gap-8 px-6">
        <div>
          <h5 class="font-bold text-lg mb-3">Wood</h5>
          <p class="text-gray-700">
            We love thinking differently and coming up with new ideas that make our
            products.
          </p>
        </div>
        <div>
          <h5 class="font-bold text-lg mb-3">Quick Links</h5>
          <ul class="space-y-2">
            <li><a href="#" class="hover:underline">Home</a></li>
            <li><a href="#" class="hover:underline">About</a></li>
            <li><a href="#" class="hover:underline">Contact</a></li>
            <li><a href="#" class="hover:underline">Services</a></li>
          </ul>
        </div>
        <div>
          <h5 class="font-bold text-lg mb-3">Contact Information</h5>
          <p class="text-gray-700">xxx building, xxx street</p>
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
