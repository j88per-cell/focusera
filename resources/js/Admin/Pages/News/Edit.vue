<script setup>
import AdminLayout from '@admin/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ post: { type: Object, default: null } });
const isNew = !props.post;
const form = useForm({
  title: props.post?.title || '',
  excerpt: props.post?.excerpt || '',
  body: props.post?.body || '',
  published_at: props.post?.published_at || '',
});

// Rich publish controls
// Status: 'draft' | 'published'
// If published: 'now' | 'schedule' with separate date/time pickers
const isExisting = !!props.post;
const initialStatus = props.post?.published_at ? 'published' : (isExisting ? 'draft' : 'published');
const status = ref(initialStatus); // 'draft' | 'published'
const publishMode = ref(props.post?.published_at ? 'schedule' : 'now'); // 'now' | 'schedule'

// Date/time pickers (local)
function toDateInputValue(d) {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
function toTimeInputValue(d) {
  const hh = String(d.getHours()).padStart(2, '0');
  const mm = String(d.getMinutes()).padStart(2, '0');
  return `${hh}:${mm}`;
}

const defaultDate = ref(props.post?.published_at ? toDateInputValue(new Date(props.post.published_at)) : toDateInputValue(new Date()));
const defaultTime = ref(props.post?.published_at ? toTimeInputValue(new Date(props.post.published_at)) : '09:00');
const publishDate = ref(defaultDate.value);
const publishTime = ref(defaultTime.value);

function setQuickSchedule(option) {
  const now = new Date();
  const d = new Date(now);
  if (option === 'today9') {
    // today at 09:00 (if past, use tomorrow)
    d.setHours(9, 0, 0, 0);
    if (d <= now) d.setDate(d.getDate() + 1);
  } else if (option === 'tomorrow9') {
    d.setDate(d.getDate() + 1);
    d.setHours(9, 0, 0, 0);
  } else if (option === 'nextMon9') {
    // next Monday 09:00
    const day = d.getDay(); // 0=Sun..6=Sat
    const diff = (8 - day) % 7 || 7; // days to next Monday
    d.setDate(d.getDate() + diff);
    d.setHours(9, 0, 0, 0);
  }
  publishDate.value = toDateInputValue(d);
  publishTime.value = toTimeInputValue(d);
  status.value = 'published';
  publishMode.value = 'schedule';
}

function composePublishedAtISO() {
  if (status.value !== 'published') return '';
  if (publishMode.value === 'now') return new Date().toISOString();
  // schedule: combine date + time as local and convert to ISO
  const date = publishDate.value || toDateInputValue(new Date());
  const time = publishTime.value || '09:00';
  const local = new Date(`${date}T${time}`);
  return isNaN(local.getTime()) ? new Date().toISOString() : local.toISOString();
}

function applyPublishBeforeSubmit() {
  form.published_at = composePublishedAtISO();
}

function submit() {
  applyPublishBeforeSubmit();
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
        <h1 class="text-2xl font-semibold">
          {{ isNew ? 'New Post' : 'Edit Post' }}
        </h1>
        <a
          href="/admin/news"
          class="text-sm text-accent hover:underline"
        >Back</a>
      </div>

      <form
        class="space-y-4 bg-white shadow rounded-lg p-6"
        @submit.prevent="submit"
      >
        <div>
          <label class="block text-sm font-medium text-gray-700">Title</label>
          <input
            v-model="form.title"
            type="text"
            class="mt-1 block w-full rounded-md border-gray-300"
          >
          <p
            v-if="form.errors.title"
            class="text-sm text-red-600 mt-1"
          >
            {{ form.errors.title }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Excerpt</label>
          <textarea
            v-model="form.excerpt"
            rows="2"
            class="mt-1 block w-full rounded-md border-gray-300"
          />
          <p
            v-if="form.errors.excerpt"
            class="text-sm text-red-600 mt-1"
          >
            {{ form.errors.excerpt }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Body (HTML allowed)</label>
          <textarea
            v-model="form.body"
            rows="10"
            class="mt-1 block w-full rounded-md border-gray-300"
          />
          <p
            v-if="form.errors.body"
            class="text-sm text-red-600 mt-1"
          >
            {{ form.errors.body }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Publish</label>
          <div class="space-y-2 mb-2">
            <div class="flex items-center gap-4">
              <label class="flex items-center gap-2 text-sm">
                <input
                  v-model="status"
                  type="radio"
                  name="status"
                  value="draft"
                >
                <span>Draft</span>
              </label>
              <label class="flex items-center gap-2 text-sm">
                <input
                  v-model="status"
                  type="radio"
                  name="status"
                  value="published"
                >
                <span>Published</span>
              </label>
            </div>
            <div
              v-if="status === 'published'"
              class="flex items-center gap-4"
            >
              <label class="flex items-center gap-2 text-sm">
                <input
                  v-model="publishMode"
                  type="radio"
                  name="publishMode"
                  value="now"
                >
                <span>Now</span>
              </label>
              <label class="flex items-center gap-2 text-sm">
                <input
                  v-model="publishMode"
                  type="radio"
                  name="publishMode"
                  value="schedule"
                >
                <span>Schedule</span>
              </label>
            </div>
          </div>
          <div
            v-if="status === 'published' && publishMode === 'schedule'"
            class="grid grid-cols-1 sm:grid-cols-2 gap-3"
          >
            <div>
              <label class="block text-xs text-gray-600">Date</label>
              <input
                v-model="publishDate"
                type="date"
                class="mt-1 block w-full rounded-md border-gray-300"
              >
            </div>
            <div>
              <label class="block text-xs text-gray-600">Time</label>
              <input
                v-model="publishTime"
                type="time"
                class="mt-1 block w-full rounded-md border-gray-300"
              >
            </div>
            <div class="sm:col-span-2 flex flex-wrap gap-2 mt-1">
              <button
                type="button"
                class="px-2 py-1 text-xs rounded border"
                @click="setQuickSchedule('today9')"
              >
                Today 9:00
              </button>
              <button
                type="button"
                class="px-2 py-1 text-xs rounded border"
                @click="setQuickSchedule('tomorrow9')"
              >
                Tomorrow 9:00
              </button>
              <button
                type="button"
                class="px-2 py-1 text-xs rounded border"
                @click="setQuickSchedule('nextMon9')"
              >
                Next Monday 9:00
              </button>
            </div>
            <p class="sm:col-span-2 text-xs text-gray-500">
              Times use your browser timezone; stored in UTC.
            </p>
            <p
              v-if="form.errors.published_at"
              class="sm:col-span-2 text-sm text-red-600 mt-1"
            >
              {{ form.errors.published_at }}
            </p>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <button
            v-if="!isNew"
            type="button"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
            @click="destroyPost"
          >
            Delete
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
          >
            {{ form.processing ? 'Saving…' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
