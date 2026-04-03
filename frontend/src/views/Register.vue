<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Create account</h1>
      <p>Start organizing your events today.</p>

      <form class="auth-form">
        <input type="text" placeholder="Full name" />
        <input type="email" placeholder="Email" />
        <input type="password" placeholder="Password" />
        <button type="submit">Register</button>
      </form>

      <p class="auth-footer">
        Already have an account?
        <router-link to="/login">Login</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: calc(100vh - 64px);
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8f7fc;
  padding: 24px;
}

.auth-card {
  width: 100%;
  max-width: 430px;
  background: white;
  border-radius: 24px;
  padding: 36px;
  box-shadow: 0 12px 40px rgba(124, 58, 237, 0.08);
}

.auth-card h1 {
  margin-bottom: 8px;
}

.auth-card p {
  color: #6b7280;
  margin-bottom: 22px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.auth-form input {
  padding: 14px 16px;
  border-radius: 12px;
  border: 1px solid #d1d5db;
  font-size: 15px;
}

.auth-form button {
  padding: 14px 16px;
  border: none;
  border-radius: 12px;
  background: #7c3aed;
  color: white;
  font-weight: 700;
  cursor: pointer;
}

.auth-footer {
  margin-top: 18px;
}
</style>