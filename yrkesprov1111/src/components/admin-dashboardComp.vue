<template>
  <div class="container mt-4">
    <h2>Välkommen, {{ username }}</h2>
    <p>XP: {{ xp }}</p>

    <div v-if="latestResult">
      <h4>Senaste resultat:</h4>
      <p>Exercise ID: {{ latestResult.Exercise_Id }}</p>
      <p>Score: {{ latestResult.Score }}</p>
      <p>Completed: {{ latestResult.Completed ? 'Ja' : 'Nej' }}</p>
      <p>Datum: {{ latestResult.Completed_At }}</p>
    </div>
    <div v-else>
      <p>Inga resultat ännu.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const username = ref('')
const xp = ref(0)
const latestResult = ref(null)

const fetchDashboard = async () => {
  try {
    const response = await axios.post(
      'http://localhost/frågesport/api/Dashboard.php',
      {},
      { withCredentials: true } // viktigt, skickar session-cookie
    )

    if (response.data.success) {
      username.value = response.data.username
      xp.value = response.data.xp
      latestResult.value = response.data.latest_result
    } else {
      console.log(response.data.message)
    }
  } catch (error) {
    console.error(error)
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>
