<template>
  <div class="container mt-4" v-if="exercise">
    <h3>{{ exercise.Title }}</h3>
    <p>{{ exercise.Description }}</p>

    <!-- Frågeindikator -->
    <p class="text-muted">Fråga {{ currentIndex + 1 }} av {{ exercise.questions.length }}</p>

    <!-- Nuvarande fråga -->
    <div v-if="currentQuestion" class="mb-3 p-3 border rounded bg-light">
      <component
        :is="questionComponent(currentQuestion.Type)"
        :question="currentQuestion"
        :model-value="answers[currentQuestion.Question_Id]"
        @update:modelValue="val => saveAnswer(currentQuestion.Question_Id, val)"
      />
    </div>

    <!-- Navigering -->
    <div class="d-flex gap-2 mb-3">
      <button class="btn btn-secondary" @click="prevQuestion" :disabled="currentIndex === 0">
        Föregående
      </button>

      <button class="btn btn-primary" @click="nextQuestion"
        :disabled="currentIndex === exercise.questions.length - 1">
        Nästa
      </button>

      <button class="btn btn-success ms-auto" 
        v-if="currentIndex === exercise.questions.length - 1"
        @click="submitExercise">
        Slutför
      </button>
    </div>

    <!-- DEBUG-PANEL -->
    <div class="p-3 border rounded bg-light">
      <h5>Debug-panel</h5>
      <p><strong>Nuvarande svar:</strong></p>
      <pre>{{ answers }}</pre>
      <p v-if="backendResponse"><strong>Backend-respons:</strong></p>
      <pre v-if="backendResponse">{{ backendResponse }}</pre>
    </div>
  </div>

  <div v-else class="text-center mt-4">
    <p>Loading...</p>
  </div>

  <!-- ====================================================== -->
  <!--                      ENDGAME SCREEN                    -->
  <!-- ====================================================== -->
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

      <p class="fs-5 mb-4">
        + {{ resultData.xp_earned }} XP
      </p>

      <div class="d-flex justify-content-between mt-3">
        <button class="btn btn-secondary w-45" @click="goBack">Till Menyn</button>
        <button class="btn btn-primary w-45" @click="goNext">Nästa Övning</button>
      </div>

    </div>
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

// Frågetyper
import TrueFalseQuestion from '@/components/GameTypes/TrueFalseQuestion.vue'
import MCQQuestion from '@/components/GameTypes/MCQQuestion.vue'
import MatchQuestion from '@/components/GameTypes/MatchQuestion.vue'
import OrderingQuestion from '@/components/GameTypes/OrderingQuestion.vue'
import FillBlankQuestion from '@/components/GameTypes/FillBlankQuestion.vue'

const route = useRoute();
const router = useRouter();

const exercise = ref(null);
const currentIndex = ref(0);
const answers = ref({});
const backendResponse = ref(null);

// Endgame screen
const showResult = ref(false);
const resultData = ref(null);

// Dynamisk komp-val
const questionComponent = (type) => {
  switch (type) {
    case "true_false": return TrueFalseQuestion;
    case "mcq": return MCQQuestion;
    case "match": return MatchQuestion;
    case "ordering": return OrderingQuestion;
    case "fill_blank": return FillBlankQuestion;
    default: return null;
  }
};

const currentQuestion = computed(() =>
  exercise.value?.questions[currentIndex.value] ?? null
);

// Ladda övning
const loadExercise = async () => {
  try {
    const exerciseId = route.query.exercise_id || route.query.id;
    if (!exerciseId) return alert("Exercise ID saknas!");

    const res = await axios.get(
      `http://localhost/fragesport/api/get_exercise.php?id=${exerciseId}`,
      { withCredentials: true }
    );

    if (res.data.success) {
      exercise.value = res.data.exercise;

      exercise.value.questions = exercise.value.questions.map((q) => ({
        ...q,
        Type: q.Type || exercise.value.Type,
        options: q.options || [],
        Correct: q.Correct ?? null,
      }));
    }
  } catch (err) {
    console.error(err);
    alert("Fel vid hämtning av övning");
  }
};

const saveAnswer = (questionId, answer) => {
  answers.value[questionId] = answer;
};

// Navigation
const nextQuestion = () => {
  if (currentIndex.value < exercise.value.questions.length - 1)
    currentIndex.value++;
};
const prevQuestion = () => {
  if (currentIndex.value > 0) currentIndex.value--;
};

// Skicka in resultat
const submitExercise = async () => {
  try {
    const res = await axios.post(
      "http://localhost/fragesport/api/submit_result.php",
      {
        exercise_id: exercise.value.Exercise_Id,
        answers: answers.value,
      },
      { withCredentials: true }
    );

    backendResponse.value = res.data;

    if (res.data.success) {
      resultData.value = res.data;
      showResult.value = true;
    }
  } catch (err) {
    console.error(err);
    alert("Fel vid skickande av svar");
  }
};

// Gå tillbaka till dashboard
const goBack = () => {
  router.push("/user-dashboard");
};

// Nästa random övning med samma typ
const goNext = async () => {
  const type = exercise.value.Type;

  const res = await axios.get(
    "http://localhost/fragesport/api/get_exercises.php",
    { withCredentials: true }
  );

  const sameType = res.data.exercises.filter((e) => e.Type === type);

  if (sameType.length === 0) return goBack();

  const next = sameType[Math.floor(Math.random() * sameType.length)];

  router.push({
    name: "PlayExercise",
    query: { exercise_id: next.Exercise_Id },
  });

  showResult.value = false;
};

onMounted(loadExercise);
</script>


<style scoped>
.container {
  max-width: 800px;
}
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.w-45 {
  width: 45%;
}
</style>
