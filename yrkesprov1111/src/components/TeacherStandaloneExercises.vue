<template>
  <div class="container mt-4">

    <h2 class="mb-3">Alla uppgifter</h2>

    <button class="btn btn-success mb-3" @click="createExercise">
      Skapa Ny Uppgift
    </button>

    <ul class="list-group">
      <li 
        v-for="ex in exercises" 
        :key="ex.Exercise_Id"
        class="list-group-item d-flex justify-content-between align-items-center"
      >
        <span>
          <strong>{{ ex.Title }}</strong>
          <small class="text-muted ms-2">({{ ex.Type }})</small>
        </span>

        <div>
          <button class="btn btn-primary btn-sm me-2" @click="editExercise(ex)">
            Redigera
          </button>

          <button class="btn btn-danger btn-sm" @click="openDeleteModal(ex)">
            Ta bort
          </button>
        </div>
      </li>
    </ul>

    <!-- =============================== -->
    <!-- DELETE CONFIRM MODAL            -->
    <!-- =============================== -->
    <div 
      class="modal fade show d-block" 
      v-if="showDelete"
      tabindex="-1"
      style="background: rgba(0,0,0,0.5);"
    >
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title text-danger">Ta bort uppgift</h5>
            <button type="button" class="btn-close" @click="showDelete=false"></button>
          </div>

          <div class="modal-body">
            Vill du verkligen ta bort uppgiften  
            <strong>{{ deleteData.Title }}</strong>?
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDelete=false">Avbryt</button>
            <button class="btn btn-danger" @click="confirmDelete">Ta bort</button>
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
