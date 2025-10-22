
<script setup lang="ts">
import {  computed } from "vue";
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  blogs: { type: Array, default: () => [] },
  settingweb: { type: Array, default: () => [] }
})

const blogs = computed(() => props.blogs)
const settingweb = computed(() => props.settingweb)



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
          <a href="/#home" class="hover:underline mt-2">Home</a>
          <a href="/#layanan" class="hover:underline mt-2">Layanan Kami</a>
          <a href="/blog" class="hover:underline mt-2">Blog</a>
          <a href="/allprojects" class="hover:underline mt-2">Projects</a>
          <a href="/#kontak" class="hover:underline mt-2">Hubungi Kami</a>

          <a href="/supplier" class="hover:underline btn bg-yellow-600 p-2 rounded-lg ">Supplier</a>
        </nav>
      </div>
    </header>   

    <!-- Product Section -->
     
    <section class="px-6 py-12  container mx-auto mt-20 ">
      <h3 class="mb-5 text-xl">Blogs</h3>
      <div class="grid md:grid-cols-4 gap-8">

        <div v-for="(blog, index) in blogs" :key="index" class="bg-warning border-1 shadow rounded overflow-hidden ">
        <div
            class="bg-white shadow rounded overflow-hidden h-[200px] bg-cover bg-center"
            :style="{
              backgroundImage: `url('/images/${blog.image_blog}')`
            }"
          >
          </div>
        <!-- <img :src="`/images/${blog.image_blog}`" alt="Product" class="w-full" /> -->
        <div class="p-6">
          <h4 class="font-bold text-lg mb-2">{{ blog.title_blog }}</h4>
          <p class="text-gray-600 mb-4">
            {{ blog.content_blog }}
          </p>
        </div>
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
