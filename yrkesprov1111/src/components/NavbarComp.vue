<template>
  <nav class="navbar navbar-light bg-light p-3 shadow">
    <div class="container">
      <router-link class="navbar-brand" to="/">Frågesport</router-link>
      <div>
        <router-link v-if="!isLoggedIn" class="btn btn-primary btn-sm" to="/">Login</router-link>
        <button v-if="isLoggedIn" class="btn btn-danger btn-sm" @click="logout">Logout</button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isLoggedIn = computed(() => !!localStorage.getItem('user'))

const logout = () => {
  localStorage.removeItem('user')
  router.push('/')
}
</script>
