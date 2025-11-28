<template>
  <!-- ENDGAME SCREEN -->
<div 
  v-if="showResult"
  class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
  style="background: rgba(0,0,0,0.8); z-index: 9999;"
>
  <div class="bg-white text-center p-4 rounded shadow" style="width: 350px;">

    <h2 class="mb-3">
      {{ resultData.completed ? '🎉 Level Klarad!' : '❌ Level Misslyckad' }}
    </h2>

    <h4 class="mb-1">{{ resultData.percent_correct }}% korrekt</h4>

    <p class="fs-5 mb-4">+ {{ resultData.xp_earned }} XP</p>

    <div class="d-flex justify-content-between mt-3">
      <button class="btn btn-secondary w-45" @click="goBack">
        Till Menyn
      </button>

      <button class="btn btn-primary w-45" @click="goNext">
        Nästa Övning
      </button>
    </div>

  </div>
</div>

</template>

<script setup>
const showResult = ref(false)
const resultData = ref(null)

const goBack = () => {
  router.push('/user-dashboard')
}

// Ladda en random övning med samma typ
const goNext = async () => {
  const currentType = exercise.value.Type

  const res = await axios.get("http://localhost/fragesport/api/get_exercises.php", {
    withCredentials: true
  })

  if (!res.data.success) return

  // filtrera övningar med samma typ
  const sameType = res.data.exercises.filter(e => e.Type === currentType)

  if (sameType.length === 0) {
    return router.push('/user-dashboard')
  }

  // random exercise
  const random = sameType[Math.floor(Math.random() * sameType.length)]

  router.push({ 
    name: 'PlayExercise', 
    query: { exercise_id: random.Exercise_Id } 
  })

  showResult.value = false
}
</script>
