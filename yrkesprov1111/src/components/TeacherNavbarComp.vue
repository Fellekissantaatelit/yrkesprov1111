<template>
  <nav class="user-navbar">
    <div class="container d-flex justify-content-between align-items-center">
      <!-- LOGO -->
      <router-link class="navbar-brand" to="/teacher-dashboard">
        Dashboard
      </router-link>


      <!-- LOGIN / LOGOUT -->
      <div>
        <router-link 
          v-if="!isLoggedIn" 
          class="btn btn-primary btn-sm" 
          to="/">
          Login
        </router-link>

        <button 
          v-if="isLoggedIn" 
          class="btn btn-danger btn-sm" 
          @click="logout">
          Logout
        </button>
      </div>

    </div>
  </nav>
</template>


<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import axios from "axios"

const router = useRouter()

const isLoggedIn = ref(false)

// ================================
// CHECK LOGIN + LOAD USER DATA
// ================================
const checkSession = async () => {
  try {
    const res = await axios.get("http://localhost/fragesport/api/auth.php", { withCredentials: true })
    isLoggedIn.value = res.data.loggedIn

    if (isLoggedIn.value) loadLevel()
  } catch (err) {
    console.error(err)
  }
}
onMounted(checkSession)

// ================================
// LOGOUT
// ================================
const logout = async () => {
  try {
    const res = await axios.post("http://localhost/fragesport/api/logout.php", {}, { withCredentials: true })
    if (res.data.success) {
      isLoggedIn.value = false
      router.push("/")
    }
  } catch (err) {
    console.error(err)
    alert("Fel vid kontakt med servern")
  }
}
</script>