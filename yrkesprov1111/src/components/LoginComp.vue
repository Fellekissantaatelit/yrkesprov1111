<template>
  <div class="d-flex justify-content-center align-items-center vh-100 bg-dark">
    <div class="card p-4 shadow" style="width: 350px;">
      <h3 class="card-title text-center mb-4">Logga in</h3>
      <form @submit.prevent="handleLogin">
        <div class="mb-3">
          <label for="email" class="form-label">Användarnamn</label>
          <input type="text" v-model="username" class="form-control" id="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Lösenord</label>
          <input type="password" v-model="password" class="form-control" id="password" required>
        </div>
        <button type="submit" class="btn btn-secondary w-100">Logga in</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const username = ref('')
const password = ref('')

const handleLogin = async () => {
  try {
  const response = await axios.post('http://localhost/frågesport/api/login.php', {
  action: 'login',
  username: username.value,
  password: password.value
});

if (response.data.success) {
  localStorage.setItem('user', username.value)
  router.push('/dashboard')
} else {
  alert(response.data.message)
  router.push('/')
}
  } catch (error) {
    console.error(error)
    alert('Fel vid kontakt med servern')
  }
}


</script>