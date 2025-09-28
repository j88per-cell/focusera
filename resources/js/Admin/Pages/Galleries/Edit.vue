<script setup>
import { ref, computed } from "vue";
import { useForm, Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

const props = defineProps({
    gallery: Object,
    parents: { type: Array, default: () => [] },
    photos: { type: Object, default: () => ({ data: [] }) },
    uploadMaxMb: { type: Number, default: 100 },
    uploadQueueClearSeconds: { type: Number, default: 15 },
    chunkBytes: { type: Number, default: 5 * 1024 * 1024 },
});

const form = useForm({
    title: props.gallery.title || "",
    description: props.gallery.description || "",
    attribution: props.gallery.attribution || "",
    notes: props.gallery.notes || "",
    date: props.gallery.date || "",
    public: !!props.gallery.public,
    allow_orders: !!props.gallery.allow_orders,
    markup_percent: props.gallery.markup_percent ?? "",
    thumbnail: props.gallery.thumbnail || "",
    parent_id: props.gallery.parent_id || "",
    exif_visibility: props.gallery.exif_visibility || "all",
    exif_fields: Array.isArray(props.gallery.exif_fields) ? props.gallery.exif_fields : [],
});

function updateGallery() {
    form.put(`/admin/galleries/${props.gallery.id}`, { preserveScroll: true });
}

const page = usePage();
const publicBaseUrl = computed(() => page.props?.site?.storage?.public_base_url || '/storage');

function normalizeSrc(path) {
    if (!path) return "";
    const p = String(path);
    if (p.startsWith("http://") || p.startsWith("https://") || p.startsWith("data:")) return p;
    if (p.startsWith("/")) return p;
    if (p.startsWith('storage/')) return '/' + p.replace(/^\/+/, '');
    return joinPublicBase(p);
}

// Thumbnail picker helpers
const showThumbPicker = ref(false);
function fileName(path) {
    if (!path) return "";
    const parts = String(path).split("/");
    return parts[parts.length - 1] || String(path);
}
function setThumbnailFromPhoto(photo) {
    if (!photo) return;
    form.thumbnail = photo.thumb_url || photo.path_thumb || photo.path_web || "";
    showThumbPicker.value = false;
}

// Photo edit modal
const showPhotoEdit = ref(false);
const editing = ref(false);
const photoForm = useForm({ title: "", description: "", attribution: '', notes: '', markup_percent: '' });
let currentPhoto = ref(null);

function openPhotoEdit(photo) {
    currentPhoto.value = photo;
    photoForm.title = photo.title || "";
    photoForm.description = photo.description || "";
    photoForm.attribution = photo.attribution || "";
    photoForm.notes = photo.notes || "";
    photoForm.markup_percent = photo.markup_percent ?? '';
    showPhotoEdit.value = true;
}

function savePhoto() {
    if (!currentPhoto.value) return;
    editing.value = true;
    photoForm.put(`/admin/galleries/${props.gallery.id}/photos/${currentPhoto.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ["photos"] });
        },
        onFinish: () => {
            editing.value = false;
            showPhotoEdit.value = false;
        },
    });
}

// Photo delete modal
const showDelete = ref(false);
const deleting = ref(false);
const toDelete = ref(null);
function askDelete(photo) {
    toDelete.value = photo;
    showDelete.value = true;
}
function confirmDelete() {
    if (!toDelete.value) return;
    deleting.value = true;
    const formDelete = useForm({});
    formDelete.delete(`/admin/galleries/${props.gallery.id}/photos/${toDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ["photos"] });
        },
        onFinish: () => {
            deleting.value = false;
            showDelete.value = false;
            toDelete.value = null;
        },
    });
}

function joinPublicBase(path) {
    const base = publicBaseUrl.value || '';
    if (!base) return '/' + path.replace(/^\/+/, '');
    return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
}

// Upload logic
const uploading = ref(false);
const queue = ref([]); // { file, name, status: 'pending'|'uploading'|'done'|'error', error?: string }
let clearTimer = null;
const fileInput = ref(null);
function openFilePicker() {
    fileInput.value?.click();
}
function onFilesSelected(e) {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;
    const maxBytes = props.uploadMaxMb * 1024 * 1024;
    const items = files.map((f) => {
        if (maxBytes && f.size > maxBytes) {
            return { file: f, name: f.name, status: "error", error: `File exceeds limit of ${props.uploadMaxMb}MB` };
        }
        return { file: f, name: f.name, status: "pending", error: null };
    });
    queue.value.push(...items);
    uploadNext();
    e.target.value = "";
}
async function uploadNext() {
    if (uploading.value) return;
    const next = queue.value.find((i) => i.status === "pending");
    if (!next) return;
    uploading.value = true;
    next.status = "uploading";
    try {
        if (next.file.size > props.chunkBytes) {
            await uploadChunked(next.file, next.name);
        } else {
            const fd = new FormData();
            fd.append("file", next.file);
            await axios.post(`/admin/galleries/${props.gallery.id}/photos/upload`, fd, {
                headers: { "Content-Type": "multipart/form-data" },
            });
        }
        next.status = "done";
        await router.reload({ only: ["photos"] });
    } catch (e) {
        next.status = "error";
        next.error = `${next.name}: ${extractError(e)}`;
    } finally {
        uploading.value = false;
        // kick off next
        const more = queue.value.find((i) => i.status === "pending");
        if (more) {
            uploadNext();
        } else {
            scheduleQueueClear();
        }
    }
}

async function uploadChunked(file, name) {
    // Start
    let start;
    try {
        start = await axios.post(`/admin/galleries/${props.gallery.id}/photos/upload/chunk/start`, { name });
    } catch (e) {
        throw new Error(`Start failed: ${extractError(e)}`);
    }
    const uploadId = start.data.upload_id;
    const size = file.size;
    const chunkSize = props.chunkBytes;
    const total = Math.ceil(size / chunkSize);
    for (let index = 0; index < total; index++) {
        const begin = index * chunkSize;
        const end = Math.min(begin + chunkSize, size);
        const blob = file.slice(begin, end);
        const fd = new FormData();
        fd.append("upload_id", uploadId);
        fd.append("index", index.toString());
        fd.append("total", total.toString());
        fd.append("name", name);
        fd.append("chunk", blob, name + `.part${index}`);
        try {
            await axios.post(`/admin/galleries/${props.gallery.id}/photos/upload/chunk`, fd, {
                headers: { "Content-Type": "multipart/form-data" },
            });
        } catch (e) {
            throw new Error(`Chunk ${index + 1}/${total} failed: ${extractError(e)}`);
        }
    }
    // Finish
    try {
        await axios.post(`/admin/galleries/${props.gallery.id}/photos/upload/chunk/finish`, { upload_id: uploadId });
    } catch (e) {
        throw new Error(`Finish failed: ${extractError(e)}`);
    }
}

function scheduleQueueClear() {
    // If there are active uploads, don't schedule
    const active = queue.value.some((i) => i.status === "pending" || i.status === "uploading");
    if (active || queue.value.length === 0) return;
    // Do not auto-clear if any errors remain
    const hasErrors = queue.value.some((i) => i.status === "error");
    if (hasErrors) return;
    // Reset previous timer
    if (clearTimer) {
        clearTimeout(clearTimer);
        clearTimer = null;
    }
    const delayMs = Math.max(0, (props.uploadQueueClearSeconds || 15) * 1000);
    clearTimer = setTimeout(() => {
        // Ensure still no active uploads before clearing
        const stillActive = queue.value.some((i) => i.status === "pending" || i.status === "uploading");
        if (!stillActive) {
            queue.value = [];
        }
        clearTimer = null;
    }, delayMs);
}

function clearQueueManual() {
    if (uploading.value) return;
    // Keep error items so user can review; clear successes
    queue.value = queue.value.filter((i) => i.status === "error");
}

function extractError(e) {
    try {
        if (e && e.response) {
            const data = e.response.data;
            if (typeof data === "string") return data;
            if (data && data.message) return data.message;
            // Laravel validation errors
            if (data && data.errors) {
                const first = Object.values(data.errors).flat()[0];
                if (first) return first;
            }
            return `HTTP ${e.response.status}`;
        }
        return e?.message || "Unknown error";
    } catch {
        return "Unknown error";
    }
}

// Transform controls (top-level so template can use them)
async function transformPhoto(photo, payload) {
    try {
        await axios.post(`/admin/galleries/${props.gallery.id}/photos/${photo.id}/transform`, payload);
        await router.reload({ only: ["photos"] });
    } catch (e) {
        /* noop */
    }
}
function rotateLeft(photo) {
    transformPhoto(photo, { rotate: -90 });
}
function rotateRight(photo) {
    transformPhoto(photo, { rotate: 90 });
}
function flipH(photo) {
    transformPhoto(photo, { flip: "h" });
}
function flipV(photo) {
    transformPhoto(photo, { flip: "v" });
}

// Generate access code and email it
const codeEmail = ref("");
const codeDuration = ref("14d"); // default duration
const codeLabel = ref("");
const codeBusy = ref(false);
const codeResult = ref(null); // { link, code, expires_at }
async function generateAndSendCode() {
    if (!codeEmail.value) return;
    codeBusy.value = true;
    codeResult.value = null;
    try {
        const { data } = await axios.post(`/admin/galleries/${props.gallery.id}/codes`, {
            email: codeEmail.value,
            duration: codeDuration.value,
            label: codeLabel.value || undefined,
        });
        codeResult.value = data;
    } catch (e) {
        alert(`Failed to generate code: ${extractError(e)}`);
    } finally {
        codeBusy.value = false;
    }
}
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Photos -->
            <section class="lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Photos</h2>
                    <div class="flex items-center gap-3">
                        <div class="text-sm text-gray-600">Total: {{ photos.total || photos.data.length }}</div>
                        <input
                            ref="fileInput"
                            type="file"
                            class="hidden"
                            multiple
                            accept="image/jpeg,image/png"
                            @change="onFilesSelected" />
                        <button
                            @click="openFilePicker"
                            class="px-3 py-1.5 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">
                            Upload
                        </button>
                    </div>
                </div>

                <div v-if="queue.length" class="mb-4 bg-white border rounded p-3 text-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-medium">Upload Queue</div>
                        <button type="button" class="text-xs text-gray-600 underline" @click="clearQueueManual" :disabled="uploading">
                            Clear
                        </button>
                    </div>
                    <ul class="space-y-1">
                        <li
                            v-for="(item, idx) in queue"
                            :key="idx"
                            class="flex items-center justify-between"
                            :class="item.status === 'error' ? 'text-red-700' : ''">
                            <span class="truncate max-w-[60%]" :title="item.name">{{ item.name }}</span>
                            <span
                                :title="item.error || ''"
                                :class="{
                                    'text-gray-600': item.status === 'pending',
                                    'text-indigo-600': item.status === 'uploading',
                                    'text-green-600': item.status === 'done',
                                    'text-red-600': item.status === 'error',
                                }"
                                >{{ item.status }}</span
                            >
                            <button
                                v-if="item.status === 'error' && item.error"
                                type="button"
                                class="ml-3 text-xs text-red-600 underline"
                                @click="
                                    $event.stopPropagation();
                                    alert(item.error);
                                ">
                                Details
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    <div v-for="p in photos.data" :key="p.id" class="group relative bg-white rounded-md overflow-hidden shadow">
                        <img :src="normalizeSrc(p.thumb_url || p.path_thumb || p.path_web)" class="w-full h-40 object-cover" alt="thumb" />
                        <div class="p-2">
                            <div class="text-sm font-medium truncate" :title="p.title">{{ p.title || "Untitled" }}</div>
                        </div>
                        <div
                            class="absolute inset-x-0 bottom-0 p-2 flex flex-wrap gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="rotateLeft(p)" class="px-2 py-1 text-xs bg-white/90 rounded shadow">⟲</button>
                            <button @click="rotateRight(p)" class="px-2 py-1 text-xs bg-white/90 rounded shadow">⟳</button>
                            <button @click="flipH(p)" class="px-2 py-1 text-xs bg-white/90 rounded shadow">⇋</button>
                            <button @click="flipV(p)" class="px-2 py-1 text-xs bg-white/90 rounded shadow">⇵</button>
                            <button @click="openPhotoEdit(p)" class="ml-auto px-2 py-1 text-xs bg-white/90 rounded shadow">Edit</button>
                            <button @click="askDelete(p)" class="px-2 py-1 text-xs bg-red-600 text-white rounded shadow">Delete</button>
                        </div>
                    </div>
                </div>

                <!-- Pagination controls -->
                <div v-if="photos && photos.last_page && photos.last_page > 1" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Page {{ photos.current_page }} of {{ photos.last_page }} • {{ photos.total }} items
                    </div>
                    <nav class="flex items-center gap-1">
                        <button
                            v-if="photos.prev_page_url"
                            @click="router.visit(photos.prev_page_url, { preserveScroll: true, preserveState: true })"
                            class="px-3 py-1 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">
                            Prev
                        </button>

                        <template v-for="l in photos.links" :key="l.url + '-' + l.label">
                            <button
                                v-if="l.url && l.label.match(/^\d+$/)"
                                @click="router.visit(l.url, { preserveScroll: true, preserveState: true })"
                                :class="[
                                    'px-3 py-1 text-sm rounded border',
                                    l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 bg-white hover:bg-gray-50',
                                ]">
                                {{ l.label }}
                            </button>
                        </template>

                        <button
                            v-if="photos.next_page_url"
                            @click="router.visit(photos.next_page_url, { preserveScroll: true, preserveState: true })"
                            class="px-3 py-1 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">
                            Next
                        </button>
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
                            <label class="block text-sm font-medium text-gray-700">Attribution</label>
                            <input v-model="form.attribution" type="text" class="mt-1 block w-full rounded-md border-gray-300" placeholder="e.g. Edited by Studio" />
                            <p v-if="form.errors.attribution" class="text-sm text-red-600 mt-1">{{ form.errors.attribution }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300" placeholder="Internal notes, credits, etc."></textarea>
                            <p v-if="form.errors.notes" class="text-sm text-red-600 mt-1">{{ form.errors.notes }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Thumbnail</label>
                            <div class="mt-1 flex items-center gap-3">
                                <input v-model="form.thumbnail" type="text" placeholder="/path/to/thumb.jpg" class="mt-0.5 block w-full rounded-md border-gray-300" />
                                <button type="button" class="px-3 py-2 text-sm rounded-md border hover:bg-gray-50" @click="showThumbPicker = !showThumbPicker">Pick</button>
                            </div>
                            <div v-if="form.thumbnail" class="mt-2 flex items-center gap-3">
                                <img :src="normalizeSrc(form.thumbnail)" alt="current thumbnail" class="w-16 h-16 object-cover rounded border" />
                                <button type="button" class="text-xs text-gray-600 hover:underline" @click="form.thumbnail = ''">Clear</button>
                            </div>
                            <p v-if="form.errors.thumbnail" class="text-sm text-red-600 mt-1">{{ form.errors.thumbnail }}</p>
                            <div v-if="showThumbPicker" class="mt-2 border rounded-md p-2">
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 max-h-64 overflow-auto">
                                    <button
                                      v-for="p in (props.photos?.data || [])"
                                      :key="p.id"
                                      type="button"
                                      class="group border rounded overflow-hidden hover:ring-2 hover:ring-indigo-500 text-left"
                                      @click="setThumbnailFromPhoto(p)"
                                    >
                                      <img :src="normalizeSrc(p.thumb_url || p.path_thumb || p.path_web)" alt="thumb" class="w-full h-20 object-cover block" />
                                      <div class="px-2 py-1 text-[11px] truncate text-gray-700">
                                        {{ fileName(p.thumb_url || p.path_thumb || p.path_web) }}
                                      </div>
                                    </button>
                                </div>
                                <div v-if="!(props.photos?.data || []).length" class="text-xs text-gray-500 px-1 py-2">No photos in this gallery yet.</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Public</label>
                            <div class="mt-1 flex items-center">
                                <input
                                    id="public"
                                    v-model="form.public"
                                    type="checkbox"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                                <label for="public" class="ml-2 text-sm text-gray-700">Visible to everyone</label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Enable Orders</label>
                            <div class="mt-1 flex items-center">
                                <input id="allow_orders" v-model="form.allow_orders" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                                <label for="allow_orders" class="ml-2 text-sm text-gray-700">Allow ordering prints from this gallery</label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Requires API key configured. Photos may have their own override.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Markup % (optional)</label>
                            <input v-model.number="form.markup_percent" type="number" step="0.01" min="0" class="mt-1 block w-40 rounded-md border-gray-300" />
                            <p class="text-xs text-gray-500 mt-1">Leave blank to use site default (e.g., 25%).</p>
                            <p v-if="form.errors.markup_percent" class="text-sm text-red-600 mt-1">{{ form.errors.markup_percent }}</p>
                        </div>

                        <!-- Access codes are generated, not typed. See the Share: Access Code section below. -->

                        <div>
                            <label class="block text-sm font-medium text-gray-700">EXIF Visibility</label>
                            <select v-model="form.exif_visibility" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="all">All</option>
                                <option value="none">None</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>

                        <div v-if="form.exif_visibility === 'custom'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">EXIF Fields to Show</label>
                            <div class="flex flex-wrap gap-3 text-sm">
                                <label class="inline-flex items-center gap-2"
                                    ><input type="checkbox" value="camera" v-model="form.exif_fields" /> Camera</label
                                >
                                <label class="inline-flex items-center gap-2"
                                    ><input type="checkbox" value="lens" v-model="form.exif_fields" /> Lens</label
                                >
                                <label class="inline-flex items-center gap-2"
                                    ><input type="checkbox" value="aperture" v-model="form.exif_fields" /> Aperture</label
                                >
                                <label class="inline-flex items-center gap-2"
                                    ><input type="checkbox" value="shutter" v-model="form.exif_fields" /> Shutter</label
                                >
                                <label class="inline-flex items-center gap-2"
                                    ><input type="checkbox" value="iso" v-model="form.exif_fields" /> ISO</label
                                >
                                <label class="inline-flex items-center gap-2"
                                    ><input type="checkbox" value="focal" v-model="form.exif_fields" /> Focal Length</label
                                >
                            </div>
                            <p v-if="form.errors.exif_fields" class="text-sm text-red-600 mt-1">{{ form.errors.exif_fields }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="/admin/galleries" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700">Cancel</a>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                {{ form.processing ? "Saving…" : "Save Changes" }}
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Access codes -->
                <div class="bg-white rounded-md shadow p-4">
                    <h3 class="text-lg font-medium mb-3">Share: Access Code</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recipient Email</label>
                            <input
                                v-model="codeEmail"
                                type="email"
                                class="mt-1 block w-full rounded-md border-gray-300"
                                placeholder="person@example.com" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Duration</label>
                            <select v-model="codeDuration" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="infinite">No expiration</option>
                                <option value="7d">7 days</option>
                                <option value="14d">14 days</option>
                                <option value="30d">30 days</option>
                                <option value="90d">90 days</option>
                                <option value="1y">1 year</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Label (optional)</label>
                            <input
                                v-model="codeLabel"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300"
                                placeholder="e.g. Client: ACME Review" />
                        </div>
                        <div class="flex justify-end">
                            <button
                                @click="generateAndSendCode"
                                :disabled="codeBusy || !codeEmail"
                                class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                {{ codeBusy ? "Sending…" : "Generate & Email" }}
                            </button>
                        </div>

                        <div v-if="codeResult" class="mt-2 text-sm">
                            <div class="text-green-700">
                                Code created{{
                                    codeResult.expires_at ? ` (expires ${new Date(codeResult.expires_at).toLocaleString()})` : ""
                                }}.
                            </div>
                            <div class="mt-1">
                                <span class="font-medium">Code:</span>
                                <code class="px-1 py-0.5 bg-gray-100 rounded">{{ codeResult.code }}</code>
                            </div>
                            <div class="mt-1 break-all">
                                <span class="font-medium">Link:</span>
                                <a :href="codeResult.link" target="_blank" class="text-indigo-700 underline">{{ codeResult.link }}</a>
                            </div>
                        </div>
                    </div>
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
                <img
                    :src="normalizeSrc(currentPhoto?.thumb_url || currentPhoto?.path_thumb || currentPhoto?.web_url || currentPhoto?.path_web)"
                    class="w-full h-48 object-cover rounded"
                    alt="preview" />
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input v-model="photoForm.title" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea v-model="photoForm.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attribution</label>
                    <input v-model="photoForm.attribution" type="text" class="mt-1 block w-full rounded-md border-gray-300" placeholder="e.g. Edited by Studio" />
                    <p v-if="photoForm.errors.attribution" class="text-sm text-red-600 mt-1">{{ photoForm.errors.attribution }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea v-model="photoForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300" placeholder="Internal notes"></textarea>
                    <p v-if="photoForm.errors.notes" class="text-sm text-red-600 mt-1">{{ photoForm.errors.notes }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Markup % (optional)</label>
                    <input v-model.number="photoForm.markup_percent" type="number" step="0.01" min="0" class="mt-1 w-40 rounded-md border-gray-300" />
                    <p class="text-xs text-gray-500">Leave blank to inherit from gallery/site.</p>
                </div>
                <div v-if="currentPhoto?.exif && Object.keys(currentPhoto.exif).length" class="text-sm text-gray-600">
                    <div class="font-medium mb-1">EXIF</div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                        <span v-if="currentPhoto.exif.camera">Camera: {{ currentPhoto.exif.camera }}</span>
                        <span v-if="currentPhoto.exif.lens">Lens: {{ currentPhoto.exif.lens }}</span>
                        <span v-if="currentPhoto.exif.aperture">Aperture: {{ currentPhoto.exif.aperture }}</span>
                        <span v-if="currentPhoto.exif.shutter">Shutter: {{ currentPhoto.exif.shutter }}</span>
                        <span v-if="currentPhoto.exif.iso">ISO: {{ currentPhoto.exif.iso }}</span>
                        <span v-if="currentPhoto.exif.focal">Focal: {{ currentPhoto.exif.focal }}</span>
                        <span v-if="currentPhoto.exif.datetime">Date: {{ currentPhoto.exif.datetime }}</span>
                        <span v-if="currentPhoto.exif.photographer">Photographer: {{ currentPhoto.exif.photographer }}</span>
                        <span v-if="currentPhoto.exif.latitude && currentPhoto.exif.longitude"
                            >Location: {{ Number(currentPhoto.exif.latitude).toFixed(6) }},
                            {{ Number(currentPhoto.exif.longitude).toFixed(6) }}</span
                        >
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t flex items-center justify-end gap-3">
                <button @click="showPhotoEdit = false" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700">Cancel</button>
                <button @click="savePhoto" :disabled="editing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    {{ editing ? "Saving…" : "Save" }}
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
                    {{ deleting ? "Deleting…" : "Delete" }}
                </button>
            </div>
        </div>
    </div>
</template>
