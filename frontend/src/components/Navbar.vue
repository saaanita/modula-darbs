<template>
  <v-app-bar flat class="navbar">
    <div class="nav-inner">
      <router-link to="/" class="brand">Catlendar</router-link>

      <div class="nav-links">
        <router-link to="/" class="nav-link">Sākums</router-link>
        <a href="#features" class="nav-link">Funkcijas</a>
        <a href="#about" class="nav-link">Par projektu</a>
        <router-link to="/login" class="nav-btn login-btn">Pieslēgties</router-link>
        <router-link to="/register" class="nav-btn register-btn">Reģistrēties</router-link>
      </div>
    </div>
  </v-app-bar>
</template>

<style scoped>
.navbar {
  background: #ffffff;
  border-bottom: 1px solid #eee;
}

.nav-inner {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  font-size: 28px;
  font-weight: 800;
  color: #7c3aed;
  text-decoration: none;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 18px;
}

.nav-link {
  color: #374151;
  text-decoration: none;
  font-weight: 500;
}

.nav-link:hover {
  color: #7c3aed;
}

.nav-btn {
  text-decoration: none;
  padding: 10px 18px;
  border-radius: 12px;
  font-weight: 600;
}

.login-btn {
  border: 1px solid #d1d5db;
  color: #111827;
  background: white;
}

.register-btn {
  background: #7c3aed;
  color: white;
}

@media (max-width: 768px) {
  .nav-inner {
    flex-direction: column;
    gap: 12px;
    padding: 12px 16px;
  }

  .nav-links {
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>