<template>
  <div class="container mt-4">
    <h2>Dina klasser</h2>
    <div class="row">
      <div v-for="cls in classes" :key="cls.class_id" class="col-md-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ cls.class_name }}</h5>
            <button class="btn btn-sm btn-primary me-2" @click="viewExercises(cls)">Visa Övningar</button>
            <button class="btn btn-sm btn-warning me-2" @click="editClass(cls)">Redigera</button>
            <button class="btn btn-sm btn-danger" @click="deleteClass(cls.class_id)">Radera</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const classes = ref([])

// Ladda alla klasser
const loadClasses = async () => {
  const res = await axios.get('http://localhost/fragesport/api/get_classes.php', { withCredentials: true })
  classes.value = res.data.classes
}

// Navigera till övningar för vald klass
const viewExercises = (cls) => {
  router.push({ 
    name: 'TeacherClassExercises', 
    query: { class_id: cls.class_id, class_name: cls.class_name } 
  })
}



// Redigera klass
const editClass = async (cls) => {
  const newName = prompt("Nytt klassnamn:", cls.class_name)
  if (newName) {
    await axios.post('http://localhost/fragesport/api/edit_class.php', { class_id: cls.class_id, class_name: newName }, { withCredentials: true })
    loadClasses()
  }
}

// Ta bort klass
const deleteClass = async (classId) => {
  if (!confirm("Är du säker på att du vill radera klassen?")) return
  await axios.post('http://localhost/fragesport/api/delete_class.php', { class_id: classId }, { withCredentials: true })
  loadClasses()
}

onMounted(() => {
  loadClasses()
})
</script>
