<script setup>
import { ref } from 'vue'
import { auth } from '../services/api'

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

      <h1>catlendar</h1>

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
    url('https://i.pinimg.com/1200x/ea/7c/b8/ea7cb8c31237edfee3db1f58f382c500.jpg') center / cover no-repeat;

  font-family: Arial, sans-serif;
}

.auth-card {
  width: 340px;
  text-align: center;
}

h1 {
  margin: 0;
  font-size: 42px;
  color: #1e293b;
}

.cat-quote {
  color: #9f1239;
  font-style: italic;
  margin-bottom: 20px;
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
  color: #334155;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.18s ease, color 0.18s ease;
}

.tabs .active {
  background: #2f6f73;
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
  background: #2f6f73;
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
