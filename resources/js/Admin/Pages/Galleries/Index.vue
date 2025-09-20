<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

const props = defineProps({
  galleries: Object,
  parents: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  title: '',
  description: '',
  date: '',
  public: true,
  thumbnail: '',
  parent_id: '',
});

const saving = ref(false);
const showCreate = ref(false);
const showDelete = ref(false);
const deleting = ref(false);
const toDelete = ref(null);

function normalizeSrc(path) {
  if (!path) return '';
  const p = String(path);
  if (p.startsWith('http://') || p.startsWith('https://') || p.startsWith('data:')) return p;
  if (p.startsWith('/')) return p;
  return '/' + p.replace(/^\/+/, '');
}

function createGallery() {
  saving.value = true;
  form.post('/admin/galleries', {
    onFinish: () => {
      saving.value = false;
      form.reset('title', 'description', 'date', 'thumbnail');
      form.parent_id = '';
      showCreate.value = false;
    },
    preserveScroll: true,
  });
}

function askDelete(gallery) {
  toDelete.value = gallery;
  showDelete.value = true;
}

function confirmDelete() {
  if (!toDelete.value) return;
  deleting.value = true;
  const id = toDelete.value.id;
  const formDelete = useForm({});
  formDelete.delete(`/admin/galleries/${id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false;
      showDelete.value = false;
      toDelete.value = null;
    },
  });
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Galleries</h1>
        <div class="flex items-center gap-3">
          <a href="/galleries" class="text-sm text-accent hover:underline">View public</a>
          <button @click="showCreate = true" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">New Gallery</button>
        </div>
      </div>

      <!-- Create modal -->
      <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="showCreate = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
          <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-medium">Create Gallery</h2>
            <button @click="showCreate = false" class="text-gray-500 hover:text-gray-700">✕</button>
          </div>
          <form @submit.prevent="createGallery" novalidate class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Title</label>
              <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300" required />
              <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Date</label>
              <input v-model="form.date" type="date" class="mt-1 block w-full rounded-md border-gray-300" />
              <p v-if="form.errors.date" class="text-sm text-red-600 mt-1">{{ form.errors.date }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
              <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</p>
            </div>

            <div>
            <label class="block text-sm font-medium text-gray-700">Thumbnail URL (optional)</label>
            <input v-model="form.thumbnail" type="text" placeholder="/images/example.jpg" class="mt-1 block w-full rounded-md border-gray-300" />
            <p v-if="form.errors.thumbnail" class="text-sm text-red-600 mt-1">{{ form.errors.thumbnail }}</p>
          </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Parent Gallery</label>
              <select v-model="form.parent_id" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">None</option>
                <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.title }}</option>
              </select>
              <p v-if="form.errors.parent_id" class="text-sm text-red-600 mt-1">{{ form.errors.parent_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Public</label>
              <div class="mt-1 flex items-center">
                <input id="public" v-model="form.public" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                <label for="public" class="ml-2 text-sm text-gray-700">Visible to everyone</label>
              </div>
            </div>

            <!-- Access codes are generated from the gallery edit page, not typed here. -->

            <div class="md:col-span-2 flex items-center justify-end gap-3 pt-2">
              <button type="button" @click="showCreate = false" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700">Cancel</button>
              <button type="submit" :disabled="saving || form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                {{ form.processing ? 'Creating…' : 'Create Gallery' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- List -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-medium">Existing Galleries</h2>
        </div>
        <div class="divide-y">
          <div v-for="g in props.galleries.data" :key="g.id" class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
              <img v-if="g.thumbnail" :src="normalizeSrc(g.thumbnail)" alt="thumb" class="w-12 h-12 object-cover rounded" />
              <div>
                <div class="font-medium">{{ g.title }}</div>
                <div class="text-sm text-gray-500">{{ g.public ? 'Public' : 'Private' }} • {{ g.date || 'No date' }} • {{ g.photos_count || 0 }} photos</div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <a :href="`/galleries/${g.id}`" class="text-sm text-accent hover:underline">View</a>
              <a :href="`/admin/galleries/${g.id}/edit`" class="text-sm text-gray-600 hover:underline">Edit</a>
              <button @click="askDelete(g)" class="text-sm text-red-600 hover:underline">Delete</button>
            </div>
          </div>
        </div>
        <div class="px-6 py-3 flex justify-between text-sm text-gray-600">
          <span>Page {{ props.galleries.current_page }} of {{ props.galleries.last_page }}</span>
          <span>Total: {{ props.galleries.total }}</span>
        </div>
      </div>
    </div>
  </AdminLayout>

  <!-- Delete confirm modal -->
  <div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="showDelete = false"></div>
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
      <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-medium">Delete Gallery</h2>
        <button @click="showDelete = false" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <div class="p-6 space-y-3">
        <p>
          Are you sure you want to delete
          <strong>{{ toDelete?.title }}</strong>?
        </p>
        <p v-if="toDelete?.photos_count" class="text-red-600">
          Warning: This gallery contains {{ toDelete.photos_count }} photo(s). Deleting it will remove them.
        </p>
      </div>
      <div class="px-6 py-4 border-t flex items-center justify-end gap-3">
        <button @click="showDelete = false" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700">Cancel</button>
        <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
          {{ deleting ? 'Deleting…' : 'Delete' }}
        </button>
      </div>
    </div>
  </div>

</template>
