<template>
  <v-app-bar flat class="navbar">
    <v-container class="d-flex align-center justify-space-between">
      <div class="logo">🐱 KawaiiCalendar</div>

      <div>
        <v-btn variant="text" to="/">Home</v-btn>
        <v-btn variant="text">Features</v-btn>
        <v-btn variant="text">About</v-btn>
        <v-btn color="pink" class="ml-4" to="/login">Login</v-btn>
        <v-btn color="deep-purple-accent-1" to="/register">Register</v-btn>
      </div>
    </v-container>
  </v-app-bar>
</template>

<style scoped>
.navbar {
  background: white;
}
.logo {
  font-weight: bold;
  font-size: 20px;
  color: #ff69b4;
}
</style>
