import { createRouter, createWebHistory } from 'vue-router'
import Auth from '../views/Auth.vue'
import Dashboard from '../views/Dashboard.vue'
import Calendar from '../views/Calendar.vue'

const routes = [
  { path: '/', component: Auth },
  { path: '/auth', component: Auth },
  { path: '/login', component: Auth },
  { path: '/dashboard', component: Dashboard },
  { path: '/calendar', component: Calendar }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  const user = localStorage.getItem('catlendar_user')

  if (to.path === '/dashboard' && !user) {
    return '/auth'
  }
})

export default router