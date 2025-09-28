<script setup>
import SiteTopNav from '../Components/SiteTopNav.vue';
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
.bg-background { background-color: #ffffff; }
.text-foreground { color: #1f2937; }
.bg-primary { background-color: #1f2937; }
.text-primary-foreground { color: #ffffff; }
.bg-accent { background-color: #8b5cf6; }
.text-accent { color: #8b5cf6; }
.text-accent-foreground { color: #ffffff; }
.bg-muted { background-color: #f1f5f9; }
.text-muted-foreground { color: #6b7280; }
.bg-card { background-color: #f1f5f9; }
.text-card-foreground { color: #1f2937; }
</style>
