<template>
  <div class="teacher-dark-page">

    <div class="teacher-card container mt-4">

      <!-- Header + Create button -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Alla uppgifter</h2>

        <button class="btn btn-success" @click="createExercise">
          Skapa Ny Uppgift
        </button>
      </div>

      <!-- Exercise list -->
      <div class="exercise-list">

        <div
          v-for="ex in exercises"
          :key="ex.Exercise_Id"
          class="exercise-item"
        >
          <div class="exercise-info">
            <strong class="exercise-title">{{ ex.Title }}</strong>
            <span class="exercise-type">({{ ex.Type }})</span>
          </div>

          <div class="exercise-actions">
            <button class="btn btn-primary btn-sm me-2" @click="editExercise(ex)">
              Redigera
            </button>

            <button class="btn btn-danger btn-sm" @click="openDeleteModal(ex)">
              🗑 Ta bort
            </button>
          </div>
        </div>

      </div>

      <!-- ====================================================== -->
      <!-- DELETE MODAL                                           -->
      <!-- ====================================================== -->

      <div class="modal-backdrop-custom" v-if="showDelete">
        <div class="modal-dialog-centered-custom">
          <div class="modal-dark-card">

            <h4 class="text-danger mb-3">Ta bort uppgift</h4>

            <p>
              Vill du verkligen radera uppgiften  
              <strong class="text-warning">{{ deleteData.Title }}</strong>?
            </p>

            <div class="d-flex justify-content-end gap-2 mt-3">
              <button class="btn btn-secondary" @click="showDelete=false">
                Avbryt
              </button>

              <button class="btn btn-danger" @click="confirmDelete">
                Ta bort
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>

  </div>
</template>


<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import { useRouter } from "vue-router"

const router = useRouter()

const exercises = ref([])

// Delete modal
const showDelete = ref(false)
const deleteData = ref({})

// Hämta mallar + egna
const loadExercises = async () => {
  const res = await axios.get("http://localhost/fragesport/api/get_exercises.php", {
    withCredentials: true
  })

  if (res.data.type === "teacher") {
    exercises.value = [
      ...res.data.templates,
      ...res.data.own_exercises
    ]
  }
}

const createExercise = () => {
  router.push({ name: "TeacherExerciseForm" })
}

const editExercise = (ex) => {
  router.push({
    name: "TeacherExerciseForm",
    query: { id: ex.Exercise_Id }
  })
}

// ===============================
// DELETE MODAL FLOW
// ===============================
const openDeleteModal = (ex) => {
  deleteData.value = { ...ex }
  showDelete.value = true
}

const confirmDelete = async () => {
  const res = await axios.post(
    "http://localhost/fragesport/api/delete_exercise.php",
    { exercise_id: deleteData.value.Exercise_Id },
    { withCredentials: true }
  )

  if (res.data.success) {
    showDelete.value = false
    loadExercises()
  }
}

onMounted(loadExercises)
</script>
