<template>
  <v-app :class="['app-shell', { dark: darkMode }]">
    <router-view />

    <footer class="site-footer">
      <p>© 2026 catlendar. Visas tiesības aizsargātas.</p> 
    </footer>
  </v-app>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const darkMode = ref(localStorage.getItem('dark') === 'true')

function syncDarkMode() {
  darkMode.value = localStorage.getItem('dark') === 'true'
}

onMounted(() => {
  window.addEventListener('storage', syncDarkMode)
  window.addEventListener('catlendar-dark-mode', syncDarkMode)
  window.addEventListener('focus', syncDarkMode)
})

onBeforeUnmount(() => {
  window.removeEventListener('storage', syncDarkMode)
  window.removeEventListener('catlendar-dark-mode', syncDarkMode)
  window.removeEventListener('focus', syncDarkMode)
})
</script>

<style scoped>
.app-shell {
  min-height: 100vh;
  background: #f4f7fb;
  color: #172033;
}

.app-shell.dark {
  background: #111827;
  color: #f9fafb;
}

.site-footer {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 6px;
  padding: 18px 32px;
  border-top: 1px solid #d9e1ee;
  background: #ffffff;
  color: #475467;
  font-family: Arial, sans-serif;
  font-size: 14px;
  text-align: center;
}

.site-footer div {
  display: flex;
  justify-content: center;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.site-footer strong {
  color: #172033;
  font-size: 16px;
}

.site-footer span {
  color: #667085;
}

.site-footer p {
  margin: 0;
}

.dark .site-footer {
  border-color: #374151;
  background: #1f2937;
  color: #cbd5e1;
}

.dark .site-footer strong {
  color: #f9fafb;
}

.dark .site-footer span {
  color: #9ca3af;
}

@media (max-width: 720px) {
  .site-footer {
    padding: 16px;
  }
}
</style>
