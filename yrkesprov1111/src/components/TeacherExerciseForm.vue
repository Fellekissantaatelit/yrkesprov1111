<template>
  <div class="container mt-4">
    <h2>{{ isEdit ? "Redigera Övning" : "Skapa Ny Övning" }}</h2>

    <form @submit.prevent="saveExercise">
      <!-- Titel -->
      <div class="mb-3">
        <label class="form-label">Titel</label>
        <input v-model="exercise.title" type="text" class="form-control" required />
      </div>

      <!-- Beskrivning -->
      <div class="mb-3">
        <label class="form-label">Beskrivning</label>
        <textarea v-model="exercise.description" class="form-control"></textarea>
      </div>

      <!-- Typ -->
      <div class="mb-3">
        <label class="form-label">Typ av övning</label>
        <select v-model="exercise.type" class="form-select" required>
          <option value="true_false">Sant/Falskt</option>
          <option value="mcq">Flervalsfrågor</option>
          <option value="match">Para ihop</option>
          <option value="ordering">Ordna meningar</option>
          <option value="fill_blank">Fyll i luckor</option>
        </select>
      </div>

      <!-- Frågor -->
      <div class="mb-3">
        <label class="form-label">Frågor</label>
        <div v-for="(q, idx) in exercise.questions" :key="idx" class="mb-2 p-2 border rounded">
          <input v-model="q.statement" type="text" class="form-control mb-1" placeholder="Fråga" />
          <input v-model="q.correct" type="text" class="form-control" placeholder="Rätt svar" />
          <button type="button" class="btn btn-sm btn-danger mt-1" @click="removeQuestion(idx)">Ta bort</button>
        </div>
        <button type="button" class="btn btn-sm btn-secondary mt-2" @click="addQuestion">Lägg till fråga</button>
      </div>

      <!-- Tilldelade klasser -->
      <div class="mb-3">
        <label class="form-label">Tilldela klasser</label>
        <select v-model="selectedClasses" multiple class="form-select">
          <option v-for="cls in classes" :key="cls.class_id" :value="cls.class_id">
            {{ cls.class_name }}
          </option>
        </select>
      </div>

      <!-- Spara -->
      <button type="submit" class="btn btn-primary">{{ isEdit ? "Spara Ändringar" : "Skapa Övning" }}</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import axios from "axios"

const router = useRouter()
const route = useRoute()

const exercise = ref({
  exercise_id: null,
  title: "",
  description: "",
  type: "true_false",
  questions: []
})

const classes = ref([])
const selectedClasses = ref([])
const isEdit = ref(false)

// Ladda alla klasser
const loadClasses = async () => {
  try {
    const res = await axios.get("http://localhost/fragesport/api/get_classes.php", { withCredentials: true })
    classes.value = res.data.classes
  } catch (err) {
    console.error(err)
    alert("Fel vid hämtning av klasser")
  }
}

// Ladda övning för edit
const loadExercise = async (id) => {
  try {
    const res = await axios.get(`http://localhost/fragesport/api/get_exercise.php?id=${id}`, { withCredentials: true })
    const data = res.data.exercise

    exercise.value = {
      exercise_id: data.Exercise_Id,
      title: data.Title,
      description: data.Description,
      type: data.Type,
      questions: data.questions.map(q => ({ statement: q.Statement, correct: q.Correct }))
    }

    selectedClasses.value = data.classes.map(c => c.class_id)
    isEdit.value = true
  } catch (err) {
    console.error(err)
    alert("Fel vid hämtning av övningen")
  }
}

// Frågehantering
const addQuestion = () => exercise.value.questions.push({ statement: "", correct: "" })
const removeQuestion = (idx) => exercise.value.questions.splice(idx, 1)

// Spara övning
const saveExercise = async () => {
  if (!exercise.value.title) { alert("Titel krävs"); return }
  if (!exercise.value.questions.length) { alert("Minst en fråga krävs"); return }

  try {
    const payload = {
      exercise: exercise.value,  // exercise_id är redan korrekt null eller ID
      classes: selectedClasses.value
    }

    const res = await axios.post(
      "http://localhost/fragesport/api/create_edit_exercise.php",
      payload,
      { withCredentials: true }
    )

    if (res.data.success) {
      alert("Övning sparad!")
      router.push("/teacher-dashboard")
    } else {
      alert("Fel vid sparande: " + res.data.message)
    }
  } catch (err) {
    console.error(err)
    alert("Fel vid kontakt med servern")
  }
}

onMounted(() => {
  loadClasses()
  if (route.query.id) loadExercise(route.query.id)
})
</script>
