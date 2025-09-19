<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/app.layout.vue';

const props = defineProps({
  gallery: Object,
  parents: { type: Array, default: () => [] },
  photos: { type: Object, default: () => ({ data: [] }) },
});

const form = useForm({
  title: props.gallery.title || '',
  description: props.gallery.description || '',
  date: props.gallery.date || '',
  public: !!props.gallery.public,
  access_code: props.gallery.access_code || '',
  thumbnail: props.gallery.thumbnail || '',
  parent_id: props.gallery.parent_id || '',
});

function updateGallery() { form.put(`/galleries/${props.gallery.id}`, { preserveScroll: true }); }

function normalizeSrc(path) {
  if (!path) return '';
  const p = String(path);
  if (p.startsWith('http://') || p.startsWith('https://') || p.startsWith('data:')) return p;
  if (p.startsWith('/')) return p;
  return '/' + p.replace(/^\/+/, '');
}

// Photo edit modal
const showPhotoEdit = ref(false);
const editing = ref(false);
const photoForm = useForm({ title: '', description: '' });
let currentPhoto = ref(null);

function openPhotoEdit(photo) {
  currentPhoto.value = photo;
  photoForm.title = photo.title || '';
  photoForm.description = photo.description || '';
  showPhotoEdit.value = true;
}

function savePhoto() {
  if (!currentPhoto.value) return;
  editing.value = true;
  photoForm.put(`/galleries/${props.gallery.id}/photos/${currentPhoto.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { router.reload({ only: ['photos'] }); },
    onFinish: () => { editing.value = false; showPhotoEdit.value = false; },
  });
}

// Photo delete modal
const showDelete = ref(false);
const deleting = ref(false);
const toDelete = ref(null);
function askDelete(photo) { toDelete.value = photo; showDelete.value = true; }
function confirmDelete() {
  if (!toDelete.value) return;
  deleting.value = true;
  const formDelete = useForm({});
  formDelete.delete(`/galleries/${props.gallery.id}/photos/${toDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { router.reload({ only: ['photos'] }); },
    onFinish: () => { deleting.value = false; showDelete.value = false; toDelete.value = null; },
  });
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: Photos -->
      <section class="lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold">Photos</h2>
          <div class="text-sm text-gray-600">Total: {{ photos.total || photos.data.length }}</div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
          <div v-for="p in photos.data" :key="p.id" class="group relative bg-white rounded-md overflow-hidden shadow">
            <img :src="normalizeSrc(p.path_thumb || p.path_web)" class="w-full h-40 object-cover" alt="thumb" />
            <div class="p-2">
              <div class="text-sm font-medium truncate" :title="p.title">{{ p.title || 'Untitled' }}</div>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button @click="openPhotoEdit(p)" class="px-2 py-1 text-xs bg-white/90 rounded shadow">Edit</button>
              <button @click="askDelete(p)" class="px-2 py-1 text-xs bg-red-600 text-white rounded shadow">Delete</button>
            </div>
          </div>
        </div>

        <!-- Pagination controls -->
        <div v-if="photos && photos.last_page && photos.last_page > 1" class="mt-4 flex items-center justify-between">
          <div class="text-sm text-gray-600">Page {{ photos.current_page }} of {{ photos.last_page }} • {{ photos.total }} items</div>
          <nav class="flex items-center gap-1">
            <button
              v-if="photos.prev_page_url"
              @click="router.visit(photos.prev_page_url, { preserveScroll: true, preserveState: true })"
              class="px-3 py-1 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50"
            >Prev</button>

            <template v-for="l in photos.links" :key="l.url + '-' + l.label">
              <button
                v-if="l.url && l.label.match(/^\d+$/)"
                @click="router.visit(l.url, { preserveScroll: true, preserveState: true })"
                :class="[
                  'px-3 py-1 text-sm rounded border',
                  l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 bg-white hover:bg-gray-50'
                ]"
              >{{ l.label }}</button>
            </template>

            <button
              v-if="photos.next_page_url"
              @click="router.visit(photos.next_page_url, { preserveScroll: true, preserveState: true })"
              class="px-3 py-1 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50"
            >Next</button>
          </nav>
        </div>
      </section>

      <!-- Right: Gallery details -->
      <aside>
        <div class="bg-white shadow rounded-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium">Gallery Details</h2>
            <a href="/admin/galleries" class="text-sm text-accent hover:underline">Back</a>
          </div>

          <form @submit.prevent="updateGallery" class="grid grid-cols-1 gap-4">
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

            <div>
              <label class="block text-sm font-medium text-gray-700">Parent Gallery</label>
              <select v-model="form.parent_id" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">None</option>
                <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.title }}</option>
              </select>
              <p v-if="form.errors.parent_id" class="text-sm text-red-600 mt-1">{{ form.errors.parent_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
              <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Thumbnail URL</label>
              <input v-model="form.thumbnail" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
              <p v-if="form.errors.thumbnail" class="text-sm text-red-600 mt-1">{{ form.errors.thumbnail }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Public</label>
              <div class="mt-1 flex items-center">
                <input id="public" v-model="form.public" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                <label for="public" class="ml-2 text-sm text-gray-700">Visible to everyone</label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Access Code (optional)</label>
              <input v-model="form.access_code" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
              <p v-if="form.errors.access_code" class="text-sm text-red-600 mt-1">{{ form.errors.access_code }}</p>
            </div>

            <div class="flex items-center justify-end gap-3">
              <a href="/admin/galleries" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700">Cancel</a>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                {{ form.processing ? 'Saving…' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </aside>
    </div>
  </AdminLayout>

  <!-- Photo Edit modal -->
  <div v-if="showPhotoEdit" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="showPhotoEdit = false"></div>
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
      <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-medium">Edit Photo</h2>
        <button @click="showPhotoEdit = false" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <div class="p-6 space-y-4">
        <img :src="normalizeSrc(currentPhoto?.path_web || currentPhoto?.path_thumb)" class="w-full h-48 object-cover rounded" alt="preview" />
        <div>
          <label class="block text-sm font-medium text-gray-700">Title</label>
          <input v-model="photoForm.title" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea v-model="photoForm.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
        </div>
      </div>
      <div class="px-6 py-4 border-t flex items-center justify-end gap-3">
        <button @click="showPhotoEdit = false" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700">Cancel</button>
        <button @click="savePhoto" :disabled="editing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
          {{ editing ? 'Saving…' : 'Save' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Photo Delete modal -->
  <div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="showDelete = false"></div>
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
      <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-medium">Delete Photo</h2>
        <button @click="showDelete = false" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <div class="p-6 space-y-3">
        <p>Are you sure you want to delete this photo?</p>
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
