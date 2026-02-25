<template>
  <v-container class="hero-section text-center">
    <h1 class="hero-title">
      Organize your events in a cute way 🎀
    </h1>

    <p class="hero-subtitle">
      Kawaii Event Calendar helps you manage your life with style.
    </p>

    <div class="mt-6">
      <v-btn size="large" color="pink" to="/register">
        Get Started 💖
      </v-btn>

      <v-btn size="large" variant="outlined" class="ml-4" to="/login">
        Login
      </v-btn>

    
    </div>
  </v-container>
 
  <p v-if="isConnected" class="mt-4 text-green">
  Backend connected ✅
</p>

<p v-else class="mt-4 text-red">
  Backend not connected ❌
</p>
  
</template>



<script setup>
import { onMounted } from 'vue'
import { health } from '../services/api'

onMounted(() => {
  health()
})
</script>

<style scoped>
.hero-section {
  margin-top: 150px;
}

.hero-title {
  font-size: 48px;
  font-weight: bold;
  background: linear-gradient(90deg, #ff69b4, #ff1493);
  color: transparent;
}

.hero-subtitle {
  font-size: 20px;
  margin-top: 20px;
  color: #555;
}
</style>
