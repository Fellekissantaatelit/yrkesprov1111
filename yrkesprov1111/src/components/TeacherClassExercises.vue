<template>
  <div class="teacher-dark-page">

    <div class="teacher-card container mt-4">

      <!-- Header + Buttons -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Övningar i {{ className }}</h2>

        <button class="btn btn-outline-light" @click="backtodash">
          ← Tillbaka
        </button>
      </div>

      <!-- Action buttons -->
      <div class="d-flex mb-3">
        <button class="btn btn-success" @click="createExercise">
          Skapa ny övning
        </button>
      </div>

      <!-- Exercises list -->
      <div class="exercise-list">

        <div
          v-for="ex in exercises"
          :key="ex.Exercise_Id"
          class="exercise-item"
        >

          <div class="exercise-title">
            {{ ex.Title }}
          </div>

          <div class="exercise-actions">
            <button class="btn btn-sm btn-primary me-2" @click="editExercise(ex)">
              Redigera
            </button>

            <button class="btn btn-sm btn-danger" @click="openDeleteModal(ex)">
              🗑 Ta bort
            </button>
          </div>

        </div>
      </div>

      <!-- =============================== -->
      <!-- DELETE MODAL (Dark UI)          -->
      <!-- =============================== -->
      <div
        class="modal-backdrop-custom"
        v-if="showDelete"
      >
        <div class="modal-dialog-centered-custom">
          <div class="modal-dark-card">

            <h4 class="text-danger mb-3">Ta bort övning</h4>

            <p class="mb-4">
              Är du säker på att du vill ta bort  
              <strong class="text-warning">{{ deleteData.Title }}</strong>?
            </p>

            <div class="d-flex justify-content-end gap-2">
              <button 
                class="btn btn-secondary"
                @click="showDelete = false"
              >
                Avbryt
              </button>

              <button 
                class="btn btn-danger"
                @click="confirmDelete"
              >
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
import { useRouter, useRoute } from "vue-router"
import axios from "axios"

const router = useRouter()
const route = useRoute()

const exercises = ref([])
const classId = route.query.class_id
const className = route.query.class_name

const showDelete = ref(false)
const deleteData = ref({})

const backtodash = () => {
    router.push({
    name: "TeacherDashboard",
  })
};

const loadExercises = async () => {
  const res = await axios.get(
    `http://localhost/fragesport/api/get_class_exercises.php?class_id=${classId}`,
    { withCredentials: true }
  )
  exercises.value = res.data.exercises
}

const createExercise = () => {
  router.push({
    name: "TeacherExerciseForm",
    query: { class_id: classId }
  })
}

const editExercise = (ex) => {
  router.push({
    name: "TeacherExerciseForm",
    query: { id: ex.Exercise_Id, class_id: classId }
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

const playExercise = (ex) => {
  router.push({
    name: "PlayExercise",
    query: { exercise_id: ex.Exercise_Id }
  })
}

onMounted(loadExercises)
</script>
