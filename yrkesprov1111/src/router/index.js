import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import DashboardView from '@/views/DashboardView.vue'

const routes = [
  { path: '/', component: LoginView },
  { 
    path: '/dashboard', 
    component: DashboardView, 
    meta: { requiresAuth: true } // ← viktigt! 
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Route guard
router.beforeEach((to, from, next) => {
  const username = localStorage.getItem('user')
  
  if (to.meta.requiresAuth && !username) {
    alert('Du måste logga in!')
    next('/') // redirect till login
  } else if (to.path === '/' && username) {
    next('/dashboard') // redan inloggad → dashboard
  } else {
    next()
  }
})

export default router
