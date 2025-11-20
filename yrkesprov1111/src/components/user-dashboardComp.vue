<template>
  <div class="container mt-4">
    <h2>Övningar för min klass</h2>
    <ul class="list-group">
      <li v-for="ex in exercises" :key="ex.id" class="list-group-item d-flex justify-content-between align-items-center">
        {{ ex.Title }}
        <router-link :to="`/play-exercise/${ex.Exercise_Id}`" class="btn btn-sm btn-primary">Spela</router-link>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const exercises = ref([])

const loadExercises = async () => {
  const res = await axios.get('http://localhost/fragesport/api/get_class_exercises.php', { withCredentials: true })
  if(res.data.success) exercises.value = res.data.exercises
}

onMounted(loadExercises)
</script>
