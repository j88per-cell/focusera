<script setup>
import AdminLayout from '@admin/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({ post: { type: Object, default: null } });
const isNew = !props.post;
const form = useForm({
  title: props.post?.title || '',
  excerpt: props.post?.excerpt || '',
  body: props.post?.body || '',
  published_at: props.post?.published_at || '',
});

function submit() {
  if (isNew) {
    form.post('/admin/news');
  } else {
    form.put(`/admin/news/${props.post.id}`);
  }
}

function destroyPost() {
  if (!props.post) return;
  if (!confirm('Delete this post?')) return;
  const f = useForm({});
  f.delete(`/admin/news/${props.post.id}`);
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-3xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">{{ isNew ? 'New Post' : 'Edit Post' }}</h1>
        <a href="/admin/news" class="text-sm text-accent hover:underline">Back</a>
      </div>

      <form @submit.prevent="submit" class="space-y-4 bg-white shadow rounded-lg p-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Title</label>
          <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
          <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Excerpt</label>
          <textarea v-model="form.excerpt" rows="2" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
          <p v-if="form.errors.excerpt" class="text-sm text-red-600 mt-1">{{ form.errors.excerpt }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Body (HTML allowed)</label>
          <textarea v-model="form.body" rows="10" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
          <p v-if="form.errors.body" class="text-sm text-red-600 mt-1">{{ form.errors.body }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Publish At (optional)</label>
          <input v-model="form.published_at" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300" />
          <p v-if="form.errors.published_at" class="text-sm text-red-600 mt-1">{{ form.errors.published_at }}</p>
        </div>

        <div class="flex items-center justify-end gap-3">
          <button type="button" v-if="!isNew" @click="destroyPost" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Delete</button>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            {{ form.processing ? 'Saving…' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
