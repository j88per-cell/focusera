<script setup>
import SiteTopNav from '../Components/SiteTopNav.vue'
</script>

<template>
  <div class="min-h-screen bg-background text-foreground">
    <SiteTopNav />

    <main>
      <slot />
    </main>
  </div>
</template>

<style scoped>
/* Twilight palette overrides for token-like utility classes */
.bg-background { background-color: #0f172a; } /* slate-900 */
.text-foreground { color: #e5e7eb; }          /* gray-200 */
.bg-primary { background-color: #1e293b; }    /* slate-800 */
.text-primary-foreground { color: #f8fafc; }  /* slate-50 */
.bg-accent { background-color: #3b82f6; }     /* blue-500 */
.text-accent { color: #60a5fa; }              /* blue-400 */
.text-accent-foreground { color: #ffffff; }
.bg-muted { background-color: #111827; }
.text-muted-foreground { color: #94a3b8; }    /* slate-400 */
.bg-card { background-color: #0f172a; }
.text-card-foreground { color: #e5e7eb; }
</style>

