import { createRouter, createWebHistory } from 'vue-router';
import HomePage from './views/HomePage.vue';
import RegisterPage from './views/RegisterPage.vue';
import LoginPage from './views/LoginPage.vue';

const routes = [
  { path: '/', component: HomePage },
  { path: '/register', component: RegisterPage },
  { path: '/login', component: LoginPage },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;