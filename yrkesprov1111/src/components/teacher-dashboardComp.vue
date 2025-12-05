<template>
  <div class="teacher-dark-page">

    <div class="teacher-card container mt-4">

      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dina klasser</h2>
      </div>

      <!-- Class grid -->
      <div class="class-grid">

        <div
          v-for="cls in classes"
          :key="cls.class_id"
          class="class-item"
        >
          <div class="class-title">{{ cls.class_name }}</div>

          <div class="class-actions">
            <button class="btn btn-primary btn-sm" @click="gotoClass(cls)">
              Visa övningar
            </button>

            <button class="btn btn-warning btn-sm" @click="openClassModal(cls)">
              Redigera
            </button>
          </div>
        </div>

      </div>

      <!-- ====================================================== -->
      <!-- EDIT CLASS MODAL (Glassy dark modal)                  -->
      <!-- ====================================================== -->

      <div class="modal-backdrop-custom" v-if="showModal">
        <div class="modal-dialog-centered-custom">
          <div class="modal-dark-card">

            <h4 class="mb-3">Redigera klass</h4>

            <label class="form-label">Klassnamn</label>
            <input
              type="text"
              class="form-control dark-input mb-3"
              v-model="modalClass.class_name"
            />

            <div class="d-flex justify-content-end gap-2 mt-3">
              <button class="btn btn-danger" @click="showDeleteConfirm = true">
                🗑 Radera
              </button>

              <button class="btn btn-success" @click="saveClass">
                Spara
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- ====================================================== -->
      <!-- DELETE CONFIRM MODAL (Dark style)                     -->
      <!-- ====================================================== -->

      <div class="modal-backdrop-custom" v-if="showDeleteConfirm">
        <div class="modal-dialog-centered-custom">
          <div class="modal-dark-card">

            <h4 class="text-danger mb-3">Bekräfta radering</h4>

            <p>
              Vill du verkligen radera klassen
              <strong class="text-warning">{{ modalClass.class_name }}</strong>?
            </p>

            <div class="d-flex justify-content-end gap-2 mt-3">
              <button class="btn btn-secondary" @click="showDeleteConfirm = false">
                Avbryt
              </button>

              <button class="btn btn-danger" @click="deleteClass">
                Radera
              </button>
            </div>

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
