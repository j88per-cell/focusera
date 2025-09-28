<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

const props = defineProps({
  settings: { type: Array, default: () => [] },
  provider_keys: { type: Array, default: () => [] },
  provider_defaults: { type: Object, default: () => ({}) },
  theme_keys: { type: Array, default: () => [] },
});

// Build grouped structure: groups -> subgroups -> items
const grouped = computed(() => {
  const map = {};
  for (const s of props.settings) {
    const g = s.group || 'app';
    const sg = s.sub_group || '(general)';
    map[g] ||= {};
    map[g][sg] ||= [];
    map[g][sg].push(s);
  }
  // sort keys
  const result = [];
  for (const g of Object.keys(map).sort()) {
    const sub = map[g];
    const subEntries = [];
    for (const sg of Object.keys(sub).sort()) {
      const items = [...sub[sg]].sort((a,b) => a.key.localeCompare(b.key));
      subEntries.push({ name: sg, items });
    }
    result.push({ name: g, subgroups: subEntries });
  }
  return result;
});

// UI state for accordions
const openGroups = ref({});
const openSubs = ref({});
function toggleGroup(name) { openGroups.value[name] = !openGroups.value[name]; }
function toggleSub(group, sub) {
  const key = group + '::' + sub;
  openSubs.value[key] = !openSubs.value[key];
}

// Inline edit state
const editingId = ref(null);
const editValue = ref('');
const saving = ref(false);

function startEdit(item) {
  editingId.value = item.id;
  editValue.value = isSecret(item) ? '' : (item.value ?? '');
}
function cancelEdit() {
  editingId.value = null;
  editValue.value = '';
}
function saveEdit(item) {
  if (saving.value) return;
  saving.value = true;
  router.visit(`/admin/settings/${item.id}`, {
    method: 'patch',
    data: { value: editValue.value },
    preserveScroll: true,
    onSuccess: () => {
      // Optimistic local update to avoid a flash
      item.value = editValue.value;
      cancelEdit();
    },
    onFinish: () => { saving.value = false; },
  });
}

function isBoolean(item) {
  const v = item.value;
  if (v === '1' || v === '0' || v === 1 || v === 0) return true;
  // Heuristic: features group often hold booleans
  const g = (item.group || '').toLowerCase();
  if (g === 'features') return true;
  return false;
}

function toggleBoolean(item) {
  if (saving.value) return;
  const newVal = (item.value === '1' || item.value === 1) ? '0' : '1';
  saving.value = true;
  router.visit(`/admin/settings/${item.id}`, {
    method: 'patch',
    data: { value: newVal },
    preserveScroll: true,
    onSuccess: () => { item.value = newVal; },
    onFinish: () => { saving.value = false; },
  });
}

function isSecret(item) {
  const g = (item.group || '').toLowerCase();
  const sg = (item.sub_group || '').toLowerCase();
  const k = (item.key || '').toLowerCase();
  if (g === 'sales') {
    if (sg.startsWith('providers.')) return k.includes('key') || k.includes('secret') || k.includes('token');
    return k.includes('key') || k.includes('secret') || k.includes('token');
  }
  return false;
}

function isSalesProvider(item) {
  return (item.group === 'sales' && item.key === 'provider');
}

function updateProvider(item, value) {
  if (saving.value) return;
  saving.value = true;
  router.visit(`/admin/settings/${item.id}`, {
    method: 'patch',
    data: { value },
    preserveScroll: true,
    onSuccess: () => { item.value = value; },
    onFinish: () => { saving.value = false; },
  });
}

function isSiteTheme(item) {
  return (item.group === 'site' && item.sub_group === 'theme' && item.key === 'active');
}

function updateTheme(item, value) {
  if (saving.value) return;
  saving.value = true;
  router.visit(`/admin/settings/${item.id}`, {
    method: 'patch',
    data: { value },
    preserveScroll: true,
    onSuccess: () => { item.value = value; },
    onFinish: () => { saving.value = false; },
  });
}

function parseProviderFromSubgroup(sub) {
  // expects 'providers.NAME'
  if (!sub) return null;
  const m = String(sub).match(/^providers\.(.+)$/);
  return m ? m[1] : null;
}

function setDefaultEndpoint(item) {
  const prov = parseProviderFromSubgroup(item.sub_group);
  if (!prov) return;
  const key = String(item.key || ''); // e.g., 'endpoint.sandbox'
  const suffix = key.split('.')[1] || '';
  const def = props.provider_defaults?.[prov]?.endpoint?.[suffix];
  if (!def) return;
  updateValue(item, def);
}

function updateValue(item, value) {
  if (saving.value) return;
  saving.value = true;
  router.visit(`/admin/settings/${item.id}`, {
    method: 'patch',
    data: { value },
    preserveScroll: true,
    onSuccess: () => { item.value = value; },
    onFinish: () => { saving.value = false; },
  });
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-5xl mx-auto p-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">
          Settings
        </h1>
        <a
          href="/admin"
          class="text-sm text-accent hover:underline"
        >Back to Admin</a>
      </div>

      <div
        v-if="!settings.length"
        class="text-sm text-gray-600 bg-white rounded border p-4"
      >
        No settings found yet. When settings are created, they will appear grouped here.
      </div>

      <div
        v-else
        class="space-y-3"
      >
        <div
          v-for="group in grouped"
          :key="group.name"
          class="bg-white rounded-md border"
        >
          <button
            type="button"
            class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50"
            @click="toggleGroup(group.name)"
          >
            <div class="font-medium text-gray-800">
              {{ group.name }}
            </div>
            <div class="text-xs text-gray-500">
              {{ group.subgroups.length }} subgroup(s)
              <span
                class="ml-2"
                aria-hidden="true"
              >{{ openGroups[group.name] ? '−' : '+' }}</span>
            </div>
          </button>
          <div
            v-show="openGroups[group.name]"
            class="border-t"
          >
            <div
              v-for="sub in group.subgroups"
              :key="group.name + '::' + sub.name"
              class="border-b last:border-b-0"
            >
              <button
                type="button"
                class="w-full flex items-center justify-between px-6 py-2 hover:bg-gray-50"
                @click="toggleSub(group.name, sub.name)"
              >
                <div class="text-sm text-gray-700">
                  {{ sub.name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ sub.items.length }} setting(s)
                  <span
                    class="ml-2"
                    aria-hidden="true"
                  >{{ openSubs[group.name + '::' + sub.name] ? '−' : '+' }}</span>
                </div>
              </button>
              <div
                v-show="openSubs[group.name + '::' + sub.name]"
                class="px-6 py-3"
              >
                <div class="overflow-x-auto">
                  <table class="min-w-full text-sm">
                    <thead>
                      <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 pr-4">
                          Key
                        </th>
                        <th class="py-2 pr-4">
                          Value
                        </th>
                        <th class="py-2">
                          Description
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="item in sub.items"
                        :key="item.id"
                        class="border-b last:border-b-0"
                      >
                        <td class="py-2 pr-4 font-mono text-xs">
                          {{ item.key }}
                        </td>
                        <td class="py-2 pr-4 align-top">
                          <div
                            v-if="editingId === item.id"
                            class="flex items-center gap-2"
                          >
                            <template v-if="isSecret(item)">
                              <input
                                v-model="editValue"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Enter new key"
                                class="w-64 max-w-full rounded border-gray-300"
                                @keyup.escape="cancelEdit"
                                @keyup.enter="saveEdit(item)"
                              >
                            </template>
                            <template v-else>
                              <input
                                v-model="editValue"
                                type="text"
                                class="w-64 max-w-full rounded border-gray-300"
                                @keyup.escape="cancelEdit"
                                @keyup.enter="saveEdit(item)"
                              >
                            </template>
                            <button
                              class="px-2 py-1 text-xs rounded bg-indigo-600 text-white disabled:opacity-50"
                              :disabled="saving"
                              @click="saveEdit(item)"
                            >
                              Save
                            </button>
                            <button
                              class="px-2 py-1 text-xs rounded border"
                              @click="cancelEdit"
                            >
                              Cancel
                            </button>
                          </div>
                          <template v-else>
                            <span
                              v-if="isSecret(item)"
                              class="inline-flex items-center gap-2"
                            >
                              <span
                                v-if="item.value"
                                class="px-1 py-0.5 rounded bg-gray-100 text-gray-500"
                              >••••••</span>
                              <span
                                v-else
                                class="px-1 py-0.5 rounded bg-gray-50 text-gray-400 border"
                              >Not set</span>
                              <button
                                class="text-xs text-indigo-600 hover:underline"
                                @click="startEdit(item)"
                              >{{ item.value ? 'Change' : 'Set' }}</button>
                            </span>
                            <div
                              v-else
                              class="flex items-center gap-2"
                            >
                              <template v-if="isSiteTheme(item)">
                                <select
                                  :value="item.value || ''"
                                  class="rounded border-gray-300 text-sm"
                                  @change="e => updateTheme(item, e.target.value)"
                                >
                                  <option
                                    v-for="k in theme_keys"
                                    :key="k"
                                    :value="k"
                                  >
                                    {{ k }}
                                  </option>
                                </select>
                              </template>
                              <template v-else-if="isSalesProvider(item)">
                                <select
                                  :value="item.value || ''"
                                  class="rounded border-gray-300 text-sm"
                                  @change="e => updateProvider(item, e.target.value)"
                                >
                                  <option
                                    v-for="k in provider_keys"
                                    :key="k"
                                    :value="k"
                                  >
                                    {{ k }}
                                  </option>
                                </select>
                              </template>
                              <template v-else-if="isBoolean(item)">
                                <span class="inline-flex items-center gap-2">
                                  <span :class="[ (item.value === '1' || item.value === 1) ? 'text-green-600' : 'text-gray-500']">
                                    {{ (item.value === '1' || item.value === 1) ? 'Enabled' : 'Disabled' }}
                                  </span>
                                  <button
                                    class="px-2 py-1 text-xs rounded border"
                                    :disabled="saving"
                                    @click="toggleBoolean(item)"
                                  >Toggle</button>
                                </span>
                              </template>
                              <template v-else>
                                <div class="flex items-center gap-2">
                                  <button
                                    class="text-left hover:bg-gray-50 rounded px-1 py-0.5"
                                    @click="startEdit(item)"
                                  >
                                    <span>{{ item.value }}</span>
                                  </button>
                                  <button
                                    v-if="item.group === 'sales' && String(item.key || '').startsWith('endpoint.') && parseProviderFromSubgroup(item.sub_group) && (provider_defaults?.[parseProviderFromSubgroup(item.sub_group)]?.endpoint?.[String(item.key).split('.')[1]] )"
                                    class="px-2 py-1 text-xs rounded border"
                                    :disabled="saving"
                                    @click="setDefaultEndpoint(item)"
                                  >
                                    Use default
                                  </button>
                                </div>
                              </template>
                            </div>
                          </template>
                        </td>
                        <td class="py-2 text-gray-500">
                          {{ item.description }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
