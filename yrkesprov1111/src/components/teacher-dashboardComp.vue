<template>
  <div class="container mt-4">

    <!-- =============================== -->
    <!-- LÄRAR KLASSLISTA                 -->
    <!-- =============================== -->
    <h2 class="mb-3">Dina klasser</h2>

    <div class="row">
      <div 
        v-for="cls in classes" 
        :key="cls.class_id" 
        class="col-md-4 mb-3"
      >
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">{{ cls.class_name }}</h5>

            <button class="btn btn-primary btn-sm me-2" @click="gotoClass(cls)">
              Visa övningar
            </button>

            <button class="btn btn-warning btn-sm" @click="openClassModal(cls)">
              Redigera
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- =============================== -->
    <!-- EDIT CLASS MODAL                -->
    <!-- =============================== -->
    <div 
      class="modal fade show d-block" 
      tabindex="-1" 
      v-if="showModal"
      style="background: rgba(0,0,0,0.5);"
    >
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Redigera klass</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>

          <div class="modal-body">
            <label class="form-label">Klassnamn</label>
            <input type="text" class="form-control" v-model="modalClass.class_name">
          </div>

          <div class="modal-footer">
            <button class="btn btn-danger me-2" @click="showDeleteConfirm = true">Radera</button>
            <button class="btn btn-primary" @click="saveClass">Spara</button>
          </div>

        </div>
      </div>
    </div>

    <!-- =============================== -->
    <!-- DELETE CONFIRM MODAL            -->
    <!-- =============================== -->
    <div 
      class="modal fade show d-block" 
      tabindex="-1" 
      v-if="showDeleteConfirm"
      style="background: rgba(0,0,0,0.5);"
    >
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title text-danger">Bekräfta radering</h5>
            <button type="button" class="btn-close" @click="showDeleteConfirm = false"></button>
          </div>

          <div class="modal-body">
            Vill du verkligen radera klassen
            <strong>{{ modalClass.class_name }}</strong>?
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDeleteConfirm = false">Avbryt</button>
            <button class="btn btn-danger" @click="deleteClass">Radera</button>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

// ==============================
// DATA
// ==============================
const classes = ref([])
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const modalClass = ref({})

// ==============================
// LADDNING
// ==============================
const loadClasses = async () => {
  const res = await axios.get("http://localhost/fragesport/api/get_classes.php", { withCredentials: true })
  classes.value = res.data.classes || []
}

// ==============================
// NAVIGATION / MODALER
// ==============================
const gotoClass = cls => {
  router.push({
    name: "TeacherClassExercises",
    query: { class_id: cls.class_id, class_name: cls.class_name }
  })
}

const openClassModal = cls => {
  modalClass.value = { ...cls }
  showModal.value = true
}

const saveClass = async () => {
  const res = await axios.post(
    "http://localhost/fragesport/api/update_class.php",
    modalClass.value,
    { withCredentials: true }
  )
  if (res.data.success) {
    showModal.value = false
    loadClasses()
  }
}

const deleteClass = async () => {
  const res = await axios.post(
    "http://localhost/fragesport/api/delete_class.php",
    { class_id: modalClass.value.class_id },
    { withCredentials: true }
  )
  if (res.data.success) {
    showDeleteConfirm.value = false
    loadClasses()
  }
}

// ==============================
// INIT
// ==============================
onMounted(() => {
  loadClasses()
})
</script>
