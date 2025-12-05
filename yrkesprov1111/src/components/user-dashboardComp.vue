<template>
  <div class="dashboard-container">

    <!-- SENASTE RESULTAT -->
    <div class="section-card">
      <h3 class="section-title">Senaste resultat</h3>

      <ul class="results-list">
        <li
          v-for="res in recentResults"
          :key="res.exercise_id"
          class="result-item"
          :class="{ failed: res.xp === 0 }"
        >
          <span>{{ res.title }}</span>
          <span class="xp-tag">{{ res.xp }} XP</span>
        </li>
      </ul>
    </div>

    <!-- ALLA ÖVNINGAR -->
    <div class="section-card">
      <h3 class="section-title">Dina övningar</h3>

      <div
        v-for="ex in exercises"
        :key="ex.Exercise_Id"
        class="exercise-card"
        :class="{ completed: Number(ex.Completed) === 1 }"
      >
        <div class="info">
          <h4>{{ ex.Title }}</h4>

          <p class="exercise-type">{{ ex.Type.toUpperCase() }}</p>

          <!-- Subtle Completed Badge -->
          <span v-if="Number(ex.Completed) === 1" class="completed-badge">
            ✔ Klar
          </span>
        </div>

        <router-link
          :to="`/play-exercise?exercise_id=${ex.Exercise_Id}&class_id=${ex.class_id}`"
          class="play-btn"
        >
          Spela →
        </router-link>
      </div>

      <p v-if="exercises.length === 0" class="no-ex">Inga övningar tillgängliga</p>
    </div>

  </div>
</template>



<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const recentResults = ref([]);
const exercises = ref([]);

const loadDashboard = async () => {
  try {
    const res = await axios.get(
      "http://localhost/fragesport/api/user_progress.php",
      { withCredentials: true }
    );

    if (res.data.success) {
      recentResults.value = res.data.recent_results;
      exercises.value = res.data.available_exercises;
    }
  } catch (err) {
    console.error(err);
    alert("Fel vid hämtning av dashboard");
  }
};

onMounted(loadDashboard);
</script>



<style scoped>
/* Subtle green background when completed */
.exercise-card.completed {
  border-left: 5px solid #4caf50;
}

/* The little badge */
.completed-badge {
  display: inline-block;
  background: #4caf50;
  color: white;
  padding: 2px 8px;
  border-radius: 8px;
  font-size: 0.8rem;
  margin-top: 4px;
}

.exercise-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff15;
  padding: 14px;
  border-radius: 10px;
  margin-bottom: 12px;
}

.exercise-type {
  opacity: 0.7;
  font-size: 0.9rem;
}
</style>
  