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
  const token = localStorage.getItem('catlendar_token')

  if ((to.path === '/dashboard' || to.path === '/calendar') && (!user || !token)) {
    return '/auth'
  }

  if ((to.path === '/' || to.path === '/auth' || to.path === '/login') && user && token) {
    return '/calendar'
  }
})

export default router
