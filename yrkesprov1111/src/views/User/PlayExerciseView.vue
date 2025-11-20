<template>
  <div class="container mt-4">
    <h2>{{ exercise.Title }}</h2>
    <p v-if="exercise.Description">{{ exercise.Description }}</p>

    <div v-if="loading">Laddar övning...</div>
    <div v-else>
      <form @submit.prevent="submitAnswers">
        <div v-for="(q, index) in questions" :key="q.Question_Id" class="mb-3">
          <p>{{ index + 1 }}. {{ q.Statement }}</p>

          <!-- Flervals -->
          <div v-if="exercise.Type === 'mcq'">
            <div v-for="opt in q.options" :key="opt.Option_Id" class="form-check">
              <input class="form-check-input" type="radio" :name="'q' + q.Question_Id" :value="opt.Option_Id" v-model="answers[q.Question_Id]" />
              <label class="form-check-label">{{ opt.Option_Text }}</label>
            </div>
          </div>

          <!-- Sant/Falskt -->
          <div v-if="exercise.Type === 'true_false'">
            <div class="form-check">
              <input class="form-check-input" type="radio" :name="'q' + q.Question_Id" value="1" v-model="answers[q.Question_Id]" />
              <label class="form-check-label">Sant</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" :name="'q' + q.Question_Id" value="0" v-model="answers[q.Question_Id]" />
              <label class="form-check-label">Falskt</label>
            </div>
          </div>

          <!-- Ordering / Fill-in kan utökas senare -->
        </div>

        <button class="btn btn-primary" type="submit">Skicka svar</button>
      </form>

      <div v-if="score !== null" class="mt-3">
        <p>Du fick {{ score }} poäng!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const exercise = ref({})
const questions = ref([])
const answers = ref({})
const score = ref(null)
const loading = ref(true)

const fetchExercise = async () => {
  try {
    const res = await axios.get(`http://localhost/fragesport/api/get_exercise.php?id=${route.params.id}`, { withCredentials: true })
    exercise.value = res.data.exercise
    questions.value = res.data.questions
    // Initiera svar-objekt
    questions.value.forEach(q => { answers.value[q.Question_Id] = null })
  } catch (err) {
    console.error(err)
    alert('Fel vid hämtning av övning')
    router.push('/user-dashboard')
  } finally {
    loading.value = false
  }
}

const submitAnswers = async () => {
  try {
    const res = await axios.post('http://localhost/fragesport/api/submit_result.php', {
      exercise_id: exercise.value.Exercise_Id,
      answers: answers.value
    }, { withCredentials: true })

    if (res.data.success) {
      score.value = res.data.score
    } else {
      alert('Fel vid skickning av svar')
    }
  } catch (err) {
    console.error(err)
    alert('Fel vid kontakt med servern')
  }
}

onMounted(fetchExercise)
</script>
