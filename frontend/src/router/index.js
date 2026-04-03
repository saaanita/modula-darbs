import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../pages/HomePage.vue'
import LoginPage from '../views/Login.vue'
import RegisterPage from '../views/Register.vue'
import DashboardPage from '../views/Dashboard.vue'

const routes = [
  { path: '/', component: HomePage },
  { path: '/login', component: LoginPage },
  { path: '/register', component: RegisterPage },
  { path: '/dashboard', component: DashboardPage },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router