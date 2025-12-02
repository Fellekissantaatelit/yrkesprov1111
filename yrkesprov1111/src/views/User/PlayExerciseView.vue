<template>

  <!-- LOADING -->
  <div v-if="!exercise" class="center-screen text-white">
    <div class="loading-card">Laddar övning…</div>
  </div>

  <!-- INTRO SCREEN -->
  <div v-else-if="showIntro" class="center-screen">
    <div class="intro-card glass-card text-center">
      <h2 class="fw-bold mb-3">{{ exercise.Title }}</h2>
      <p class="mb-4 opacity-75">{{ exercise.Description }}</p>

      <button class="btn btn-primary btn-lg glow-btn" @click="startQuestions">
        Börja svara på frågorna
      </button>
    </div>
  </div>

  <!-- GAME SCREEN -->
  <div v-else class="game-container glass-card p-4 text-white">

    <h3 class="fw-bold mb-1">{{ exercise.Title }}</h3>
    <p class="opacity-50 mb-3">Fråga {{ currentIndex + 1 }} / {{ exercise.questions.length }}</p>

    <div v-if="currentQuestion" class="question-card p-3 mb-4">
      <component
        :is="questionComponent(currentQuestion.Type)"
        :question="currentQuestion"
        :model-value="answers[currentQuestion.Question_Id]"
        @update:modelValue="val => saveAnswer(currentQuestion.Question_Id, val)"
      />
    </div>

    <!-- Buttons -->
    <div class="d-flex gap-2">
      <button class="btn btn-secondary" @click="prevQuestion" :disabled="currentIndex === 0">
        Föregående
      </button>

      <button class="btn btn-primary" @click="nextQuestion" :disabled="currentIndex === exercise.questions.length - 1">
        Nästa
      </button>

      <button v-if="currentIndex === exercise.questions.length - 1"class="btn btn-success ms-auto"@click="submitExercise">Slutför</button>
    </div>

  </div>

  <!-- ENDGAME -->
  <div 
    v-if="showResult"
    class="end-overlay d-flex justify-content-center align-items-center"
  >
    <div class="result-card glass-card text-center">

      <h2 class="mb-2 fw-bold">
        {{ resultData.completed ? ' Level Klarad!' : ' Level Misslyckad' }}
      </h2>

      <h4 class="mb-1">{{ resultData.percent_correct }}% korrekt</h4>

      <p class="fs-5 opacity-75">+ {{ resultData.xp_earned }} XP</p>

      <div class="d-flex justify-content-between mt-3">
        <button class="btn btn-outline-light w-45" @click="goBack">Till Menyn</button>
        <button class="btn btn-primary glow-btn w-45" @click="goNext">Nästa Övning</button>
      </div>

    </div>
  </div>

</template>


<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

// COMPONENTS
import TrueFalseQuestion from '@/components/GameTypes/TrueFalseQuestion.vue'
import MCQQuestion from '@/components/GameTypes/MCQQuestion.vue'
import OrderingQuestion from '@/components/GameTypes/OrderingQuestion.vue'

const route = useRoute();
const router = useRouter();

const exercise = ref(null);
const currentIndex = ref(0);
const answers = ref({});
const backendResponse = ref(null);
const showIntro = ref(true);
const showResult = ref(false);
const resultData = ref(null);

//------------------------------------------------------------------
// HELPER: Shuffle
//------------------------------------------------------------------
const shuffle = arr => [...arr].sort(() => Math.random() - 0.5);

//------------------------------------------------------------------
// FRÅGEKOMPONENT VÄLJARE
//------------------------------------------------------------------
const questionComponent = type => {
  switch (type) {
    case "true_false": return TrueFalseQuestion;
    case "mcq": return MCQQuestion;
    case "ordering": return OrderingQuestion;
    default: return null;
  }
};

const currentQuestion = computed(() =>
  exercise.value?.questions[currentIndex.value] ?? null
);

//------------------------------------------------------------------
// INTRO → STARTA SPELET
//------------------------------------------------------------------
const startQuestions = () => {
  showIntro.value = false;
};

//------------------------------------------------------------------
// LADDA ÖVNING
//------------------------------------------------------------------
const loadExercise = async () => {
  try {
    const exerciseId = route.query.exercise_id || route.query.id;
    if (!exerciseId) return alert("Exercise ID saknas!");

    const res = await axios.get(
      `http://localhost/fragesport/api/get_exercise.php?id=${exerciseId}`,
      { withCredentials: true }
    );

    if (!res.data.success) {
      alert(res.data.message);
      return;
    }

    exercise.value = res.data.exercise;

    // =============================
    // ORDERING — endast options shufflas
    // =============================
    if (exercise.value.Type === "ordering") {
      exercise.value.questions[0].options = shuffle(exercise.value.questions[0].options);
    }

    // =============================
    // TRUE/FALSE & MCQ — shuffla frågorna
    // =============================
    else {
      exercise.value.questions = shuffle(exercise.value.questions);
    }

  } catch (err) {
    console.error(err);
    alert("Fel vid hämtning av övning");
  }
};

//------------------------------------------------------------------
// NAVIGATION
//------------------------------------------------------------------
const saveAnswer = (questionId, answer) => {
  answers.value[questionId] = answer;
};

const nextQuestion = () => {
  if (currentIndex.value < exercise.value.questions.length - 1)
    currentIndex.value++;
};

const prevQuestion = () => {
  if (currentIndex.value > 0) currentIndex.value--;
};

//------------------------------------------------------------------
// SKICKA RESULTAT
//------------------------------------------------------------------
const submitExercise = async () => {
  try {
    const payload = {
      exercise_id: exercise.value.Exercise_Id,   // ← FIX
      answers: answers.value
    };

    console.log("Sending:", payload);

    const res = await axios.post(
      "http://localhost/fragesport/api/submit_result.php",
      payload,
      { withCredentials: true }
    );

    console.log("Response:", res.data);

    if (!res.data.success) {
      alert("Backend error: " + res.data.message);
      return;
    }

    resultData.value = res.data;
    showResult.value = true;

  } catch (err) {
    console.error(err);
    alert("Fel vid skickande av svar");
  }
};


//------------------------------------------------------------------
// END SCREEN KNAPPAR
//------------------------------------------------------------------
const goBack = () => {
  router.push("/user-dashboard");
};

const goNext = async () => {
  const type = exercise.value.Type;
  const res = await axios.get("http://localhost/fragesport/api/get_exercises.php", { withCredentials: true });
  const sameType = res.data.exercises.filter(e => e.Type === type);

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
