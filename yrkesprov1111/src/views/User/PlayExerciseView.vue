<template>

  <!-- LOADING -->
  <div v-if="!exercise" class="center-screen text-white">
    <div class="loading-card">Laddar övning…</div>
  </div>

  <!-- INTRO -->
  <div v-else-if="showIntro" class="center-screen">
    <div class="intro-card glass-card text-center">
      <h2 class="fw-bold mb-3">{{ exercise.Title }}</h2>
      <p class="mb-4 opacity-75">{{ exercise.Description }}</p>

      <button class="btn btn-primary btn-lg glow-btn" @click="startQuestions">
        Börja svara på frågorna
      </button>
    </div>
  </div>

  <!-- GAME -->
  <div v-else class="game-container glass-card p-4 text-white">

    <h3 class="fw-bold mb-1">{{ exercise.Title }}</h3>
    <p class="opacity-50 mb-3">Fråga {{ currentIndex + 1 }} / {{ exercise.questions.length }}</p>

    <div v-if="currentQuestion" class="question-card p-3 mb-4">
      <component
        :is="questionComponent(exercise.Type)"
        :question="currentQuestion"
        :model-value="answers[currentQuestion.Question_Id]"
        @update:modelValue="value => saveAnswer(currentQuestion.Question_Id, value)"
      />
    </div>

    <!-- Navigation buttons -->
    <div class="d-flex gap-2">
      <button 
        class="btn btn-secondary" 
        @click="prevQuestion"
        :disabled="currentIndex === 0"
      >
        Föregående
      </button>

      <button 
        class="btn btn-primary"
        @click="nextQuestion"
        :disabled="currentIndex === exercise.questions.length - 1"
      >
        Nästa
      </button>

      <button 
        v-if="currentIndex === exercise.questions.length - 1"
        class="btn btn-success ms-auto"
        @click="submitExercise"
      >
        Slutför
      </button>
    </div>

  </div>

  <!-- RESULT OVERLAY -->
  <div 
    v-if="showResult"
    class="end-overlay d-flex justify-content-center align-items-center"
  >
    <div class="result-card glass-card text-center">

      <h2 class="fw-bold mb-2">
        {{ resultData.completed ? "Level Klarad!" : "Level Misslyckad" }}
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

import TrueFalseQuestion from "@/components/GameTypes/TrueFalseQuestion.vue";
import MCQQuestion from "@/components/GameTypes/MCQQuestion.vue";
import OrderingQuestion from "@/components/GameTypes/OrderingQuestion.vue";

const route = useRoute();
const router = useRouter();

const exercise = ref(null);
const currentIndex = ref(0);
const answers = ref({});
const showIntro = ref(true);
const showResult = ref(false);
const resultData = ref(null);

/* HELPERS */
const shuffle = arr => [...arr].sort(() => Math.random() - 0.5);

const questionComponent = (type) => {
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

const startQuestions = () => showIntro.value = false;

/* LOAD EXERCISE */
const loadExercise = async () => {
  try {
    const id = route.query.exercise_id || route.query.id;
    if (!id) return alert("Ingen ID!");

    const res = await axios.get(
      `http://localhost/fragesport/api/get_exercise.php?id=${id}`,
      { withCredentials: true }
    );

    if (!res.data.success) return alert(res.data.message);

    const ex = res.data.exercise;

    /* ORDERING — Already merged into ONE question in backend */
    if (ex.Type === "ordering") {
      ex.questions[0].options = shuffle(ex.questions[0].options);
    }

    /* TRUE/FALSE */
    if (ex.Type === "true_false") {
      ex.questions = ex.questions.map(q => ({
        ...q,
        options: [
          { Option_Id: 1, text: "Sant", correct: q.Correct == 1 },
          { Option_Id: 0, text: "Falskt", correct: q.Correct == 0 }
        ]
      }));
    }

    /* MCQ */
    if (ex.Type === "mcq") {
      ex.questions = ex.questions.map(q => ({
        ...q,
        options: q.options.map(o => ({
          Option_Id: Number(o.Option_Id),
          text: o.Option_Text,
          correct: Number(o.Is_Correct)
        }))
      }));

      ex.questions = shuffle(ex.questions);
    }

    exercise.value = ex;

  } catch (e) {
    console.error(e);
    alert("Fel vid hämtning");
  }
};

/* SAVE ANSWER */
const saveAnswer = (qid, value) => {
  answers.value[qid] = value;
};

/* NAVIGATION */
const nextQuestion = () => {
  if (currentIndex.value < exercise.value.questions.length - 1)
    currentIndex.value++;
};

const prevQuestion = () => {
  if (currentIndex.value > 0) currentIndex.value--;
};

/* SUBMIT */
const submitExercise = async () => {
  try {
    const payload = {
      exercise_id: exercise.value.Exercise_Id,
      answers: answers.value
    };

    const res = await axios.post(
      "http://localhost/fragesport/api/submit_result.php",
      payload,
      { withCredentials: true }
    );

    if (!res.data.success)
      return alert("Fel: " + res.data.message);

    resultData.value = res.data;
    showResult.value = true;

  } catch (err) {
    console.error(err);
    alert("Fel vid skickning");
  }
};

const goBack = () => router.push("/user-dashboard");

/* NEXT EXERCISE SAME TYPE */
const goNext = async () => {
  const type = exercise.value.Type;

  const res = await axios.get(
    "http://localhost/fragesport/api/get_exercises.php",
    { withCredentials: true }
  );

  const sameType = res.data.exercises.filter(e => e.Type === type);

  if (sameType.length === 0) return goBack();

  const next = sameType[Math.floor(Math.random() * sameType.length)];

  router.push({
    name: "PlayExercise",
    query: { exercise_id: next.Exercise_Id }
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
