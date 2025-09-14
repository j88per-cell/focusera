<script setup>
</script>

<template>
  <div class="min-h-screen bg-background text-foreground">
    <header class="bg-primary text-primary-foreground shadow-sm">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex-shrink-0">
            <h1 class="text-xl font-bold">PhotoStudio</h1>
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
              <slot name="nav">
                <!-- default nav (can be overridden by pages) -->
                <a class="hover:text-accent px-3 py-2 rounded-md text-sm font-medium" href="#">Home</a>
                <a class="hover:text-accent px-3 py-2 rounded-md text-sm font-medium" href="#">Galleries</a>
                <a class="hover:text-accent px-3 py-2 rounded-md text-sm font-medium" href="#">News</a>
                <a class="hover:text-accent px-3 py-2 rounded-md text-sm font-medium" href="#">Contact</a>
              </slot>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <slot />
    </main>

    <footer class="bg-primary text-primary-foreground py-12 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <slot name="footer">
          <!-- default footer content -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
              <h5 class="text-2xl font-bold mb-4">PhotoStudio</h5>
              <p class="text-primary-foreground/80 mb-4 max-w-md">
                Professional photography services capturing life's most precious moments with artistic vision and technical excellence.
              </p>
            </div>
            <div>
              <h6 class="font-semibold mb-4">Services</h6>
              <ul class="space-y-2 text-primary-foreground/80">
                <li>Wedding Photography</li>
                <li>Portrait Sessions</li>
                <li>Event Photography</li>
                <li>Commercial Work</li>
              </ul>
            </div>
            <div>
              <h6 class="font-semibold mb-4">Contact</h6>
              <ul class="space-y-2 text-primary-foreground/80">
                <li>hello@photostudio.com</li>
                <li>+1 (555) 123-4567</li>
                <li>123 Photography Lane</li>
                <li>Creative City, CC 12345</li>
              </ul>
            </div>
          </div>
          <div class="border-t border-primary-foreground/20 mt-8 pt-8 text-center text-primary-foreground/60">
            <p>&copy; 2025 PhotoStudio. All rights reserved.</p>
          </div>
        </slot>
      </div>
    </footer>
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
