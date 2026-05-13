<script setup>
import { ref } from 'vue'
import { auth } from '../services/api'
import logo from '../assets/catlendar-logo.png'

const mode = ref('login')
const username = ref('')
const email = ref('')
const password = ref('')
const loading = ref(false)

async function submit() {
  if (mode.value === 'register' && username.value.trim().length < 3) {
    alert('Lietotājvārdam jābūt vismaz 3 simboli!')
    return
  }

  if (!email.value || !password.value) {
    alert('Ievadi e-pastu un paroli!')
    return
  }

  if (password.value.length < 4) {
    alert('Parolei jābūt vismaz 4 simboli!')
    return
  }

  try {
    loading.value = true

    if (mode.value === 'login') {
      await auth.login({
        email: email.value,
        password: password.value
      })
    } else {
      await auth.register({
        username: username.value.trim(),
        email: email.value,
        password: password.value
      })
    }

    window.location.href = '/calendar'
  } catch (error) {
    alert(error.message || 'Neizdevās pieslēgties serverim!')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <form class="auth-card" @submit.prevent="submit">

      <img class="auth-logo" :src="logo" alt="catlendar logo" />

      <div class="tabs">
        <button type="button"
                :class="{ active: mode === 'login' }"
                @click="mode = 'login'">
          Pieslēgties
        </button>

        <button type="button"
                :class="{ active: mode === 'register' }"
                @click="mode = 'register'">
          Reģistrēties
        </button>
      </div>

      <input v-if="mode === 'register'" v-model="username" type="text" placeholder="Lietotājvārds" />
      <input v-model="email" type="email" placeholder="E-pasts" />
      <input v-model="password" type="password" placeholder="Parole" />

      <button class="main-btn" type="submit">
        {{ loading ? 'Lūdzu, uzgaidi...' : mode === 'login' ? 'Ienākt' : 'Reģistrēties' }}
      </button>

    </form>
  </main>
</template>

<style scoped>
:global(html),
:global(body),
:global(#app) {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
}

.auth-page {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 32px 16px;

  display: flex;
  justify-content: center;
  align-items: center;

  background:
    linear-gradient(rgba(255,255,255,0.35), rgba(255,255,255,0.35)),
    url('https://i.pinimg.com/1200x/d2/cf/0a/d2cf0af6fbab5d95c4f15d46b5904973.jpg') center / cover no-repeat;

  font-family: Arial, sans-serif;
}

.auth-card {
  width: 340px;
  text-align: center;
}

.auth-logo {
  max-width: 220px;
  width: 100%;
  height: auto;
  margin: 0 auto 20px;
  display: block;
}

.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 16px;
}

.tabs button {
  flex: 1;
  padding: 13px;
  border: none;
  border-radius: 999px;
  background: rgba(248, 250, 252, 0.92);
  color: #e197cb;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.18s ease, color 0.18s ease;
}

.tabs .active {
  background: #e197cb;
  color: white;
}

.tabs button:hover,
.main-btn:hover {
  opacity: 0.92;
}

input,
select {
  width: 100%;
  box-sizing: border-box;
  padding: 14px;
  margin-bottom: 12px;
  border-radius: 14px;
  border: 1px solid #cbd5e1;
  font-size: 15px;
  background: rgba(255,255,255,0.9);
}

.main-btn {
  width: 100%;
  padding: 15px;
  border: none;
  border-radius: 999px;
  background: #e197cb;
  color: white;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: opacity 0.18s ease;
}

.hint {
  margin-top: 20px;
  color: #475569;
  font-size: 14px;
}
</style>
