<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';

interface Blog {
    id: number;
    title_blog: string;
    date_blog: Date;
    image_blog: string;
    content_blog: string;
    user_id: number;
    status: string;
}

// const form = useForm({
//   title_blog: '',
//   date_blog: dayjs().format('YYYY-MM-DDTHH:mm'), // default to now (HTML datetime-local format)
//   image_blog: null,     // File
//   content_blog: '',
//   user_id: 1,
//   status: 'Draft',
// });


const props = defineProps<{
  blog: Blog;
}>()


const form = useForm({
    title_blog: props.blog.title_blog,
    date_blog: props.blog.date_blog,
    image_blog: props.blog.image_blog,
    content_blog: props.blog.content_blog,
    user_id: props.blog.user_id,
    status: props.blog.status,
});

// this function is going to handle the user information submit.
const handleInput = () => {
    form.put(route('blogs.update', { blog: props.blog }));
};
</script>

<template>
    <Head title="Edit a blog" />

    <AppLayout :breadcrumbs="[{ title: 'Edit a blog', href: `/blogs/${props.blog.id}` }]">
        <div class="mb-6 ">
            <h1 class="text-lg font-semibold text-white bg-primary px-5 py-3 rounded-se-xl shadow border-b-2 border-secondary">
                Edit Blog
                <p class="text-white mt-1 text-sm font-normal">Kelola daftar blog Anda.</p>
            </h1>
        
        </div>
        <div class="p-4">
            <form class="w-8/12 space-y-4" @submit.prevent="handleInput">
                
                 <div class="space-y-2">
          <Label for="title_blog">Title</Label>
          <Input
            v-model="form.title_blog"
            id="title_blog"
            type="text"
            placeholder="Enter blog title..."
          />
          <div class="text-sm text-red-600" v-if="form.errors.title_blog">{{ form.errors.title_blog }}</div>
        </div>

        <!-- Date -->
        <div class="space-y-2">
          <Label for="date_blog">Date</Label>
          <Input
            v-model="form.date_blog"
            id="date_blog"
            type="datetime-local"
          />
          <div class="text-sm text-red-600" v-if="form.errors.date_blog">{{ form.errors.date_blog }}</div>
        </div>

        <!-- Image -->
        <div class="space-y-2">
          <Label for="image_blog">Blog Image</Label>
          <Input
            id="image_blog"
            type="file"
            accept="image/*"
            @change="(e) => form.image_blog = e.target.files[0]"
          />
          <div class="text-sm text-red-600" v-if="form.errors.image_blog">{{ form.errors.image_blog }}</div>
        </div>

        <!-- Content -->
        <div class="space-y-2">
          <Label for="content_blog">Content</Label>
          <Textarea
            v-model="form.content_blog"
            id="content_blog"
            rows="6"
            placeholder="Write your blog content here..."
          />
          <div class="text-sm text-red-600" v-if="form.errors.content_blog">{{ form.errors.content_blog }}</div>
        </div>

        <!-- Status -->
      <div class="space-y-2">
        <label for="status">Status</label>
        <select
          id="status"
          v-model="form.status"
          class="border border-gray-300 rounded px-3 py-2 w-full"
        >
          <option disabled value="">Select status</option>
          <option value="Draft">Draft</option>
          <option value="Publish">Publish</option>
        </select>
        <div class="text-sm text-red-600" v-if="form.errors.status">
          {{ form.errors.status }}
        </div>
      </div>
                

                <Button type="submit" :disabled="form.processing"> Edit Blog  </Button>
            </form>
        </div>
    </AppLayout>
</template>
