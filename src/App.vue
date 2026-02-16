<script setup>
import { ref } from 'vue'

const drawer = ref(false)
const navigationItems = [
  { title: 'Home', icon: 'mdi-home', to: '/' },
  { title: 'About', icon: 'mdi-information', to: '/about' },
  { title: 'Services', icon: 'mdi-briefcase', to: '/services' },
  { title: 'Contact', icon: 'mdi-email', to: '/contact' },
]
</script>

<template>
  <v-app>
    <v-app-bar color="primary" dark>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-app-bar-title>Catlendar</v-app-bar-title>
      <v-spacer></v-spacer>
      <router-link to="/" class="nav-link">
        <v-btn text>Home</v-btn>
      </router-link>
      <router-link to="/about" class="nav-link">
        <v-btn text>About</v-btn>
      </router-link>
      <router-link to="/services" class="nav-link">
        <v-btn text>Services</v-btn>
      </router-link>
      <router-link to="/contact" class="nav-link">
        <v-btn text>Contact</v-btn>
      </router-link>
      <v-btn variant="elevated" class="ml-4">Sign Up</v-btn>
    </v-app-bar>

    <v-navigation-drawer v-model="drawer">
      <v-list>
        <v-list-item
          v-for="item in navigationItems"
          :key="item.title"
          :to="item.to"
          :prepend-icon="item.icon"
          :title="item.title"
          @click="drawer = false"
        ></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <router-view></router-view>
    </v-main>

    <v-footer app>
      <v-col class="text-center">
        <strong>Catlendar 2024-2025</strong> | Event Management Platform
      </v-col>
    </v-footer>
  </v-app>
</template>

<style scoped>
  .nav-link {
    text-decoration: none;
    color: inherit;
  }

  .nav-link a {
    text-decoration: none;
  }
</style>
</style>
