<script setup>
import { ref } from 'vue'

const mode = ref('login')
const email = ref('')
const password = ref('')
const role = ref('user')

async function submit() {
  if (!email.value || !password.value) {
    alert('Ievadi e-pastu un paroli!')
    return
  }

  if (password.value.length < 4) {
    alert('Parolei jābūt vismaz 4 simboli!')
    return
  }

  const url =
    mode.value === 'login'
      ? 'http://127.0.0.1:8000/api/auth/login'
      : 'http://127.0.0.1:8000/api/auth/register'

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
        role: role.value
      })
    })

    const data = await res.json()

    if (!res.ok) {
      alert(data.message || 'Kļūda!')
      return
    }

    localStorage.setItem('catlendar_user', JSON.stringify(data))
    window.location.href = '/dashboard'

  } catch {
    alert('Serveris nestrādā!')
  }
}
</script>

<template>
  <main class="auth-page">
    <form class="auth-card" @submit.prevent="submit">

      <h1>🐱 CATlendar</h1>
      <p class="cat-quote">Meow your schedule into place.</p>

      <div class="tabs">
        <button type="button"
                :class="{ active: mode === 'login' }"
                @click="mode = 'login'">
          Log in
        </button>

        <button type="button"
                :class="{ active: mode === 'register' }"
                @click="mode = 'register'">
          Sign up
        </button>
      </div>

      <input v-model="email" type="email" placeholder="E-pasts" />
      <input v-model="password" type="password" placeholder="Parole" />

      <select v-if="mode === 'register'" v-model="role">
        <option value="user">Lietotājs</option>
        <option value="admin">Administrators</option>
      </select>

      <button class="main-btn" type="submit">
        {{ mode === 'login' ? 'Ienākt' : 'Reģistrēties' }}
      </button>

      <p class="hint">🐾 Kaķīgi sakārtots kalendārs</p>

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
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;

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
  border-radius: 14px;
  background: rgba(226, 232, 240, 0.9);
  color: #334155;
  cursor: pointer;
  font-weight: bold;
}

.tabs .active {
  background: #f472b6;
  color: white;
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
  border-radius: 14px;
  background: #1d4ed8;
  color: white;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
}

.hint {
  margin-top: 20px;
  color: #475569;
  font-size: 14px;
}
</style>