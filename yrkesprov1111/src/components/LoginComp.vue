<template>
  <div class="d-flex justify-content-center align-items-center vh-100 bg-dark">
    <div class="card p-4 shadow" style="width: 350px;">
      <h3 class="card-title text-center mb-4">Logga in</h3>
      <form @submit.prevent="handleLogin">
        <div class="mb-3">
          <label for="username" class="form-label">Användarnamn</label>
          <input type="text" v-model="username" class="form-control" id="username" required>
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
    const res = await axios.post('http://localhost/fragesport/api/login.php', {
      username: username.value,
      password: password.value
    }, { withCredentials: true })
      console.log(res.data);
    if (res.data.success) {
      const role = res.data.user.role

      if (role === 3) {
        router.push('/admin-dashboard') 
      } else if (role === 2) {
        router.push('/teacher-dashboard') 
      } else {
        router.push('/user-dashboard')    
      }
      
    } else {
      alert(res.data.message)
    }

  } catch (err) {
    console.error(err)
    alert('Fel vid kontakt med servern')
  }
}


</script>
