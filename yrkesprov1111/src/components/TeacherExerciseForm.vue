<template>
  <div class="teacher-dark-page">

    <div class="teacher-card container mt-4">

      <!-- HEADER -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ isEdit ? "Redigera Övning" : "Skapa Ny Övning" }}</h2>

        <button class="btn btn-outline-light" @click="router.push('/teacher-dashboard')">
          ← Tillbaka
        </button>
      </div>

      <form @submit.prevent="saveExercise">

        <!-- TITLE -->
        <div class="mb-3">
          <label class="form-label">Titel</label>
          <input v-model="exercise.title" class="form-control" required />
        </div>

        <!-- DESCRIPTION -->
        <div class="mb-3">
          <label class="form-label">Beskrivning</label>
          <textarea v-model="exercise.description" class="form-control"></textarea>
        </div>

        <!-- TYPE -->
        <div class="mb-3">
          <label class="form-label">Typ av övning</label>
          <select v-model="exercise.type" class="form-select" required>
            <option value="true_false">Sant/Falskt</option>
            <option value="mcq">Flervalsfrågor</option>
            <option value="ordering">Ordna meningar</option>
          </select>
        </div>

        <!-- XP -->
        <div class="mb-3">
          <label class="form-label">Max XP</label>
          <input v-model.number="exercise.max_xp" type="number" min="1" class="form-control" required />
        </div>

        <!-- QUESTIONS -->
        <div class="mb-3">
          <label class="form-label">Frågor / Meningar</label>

          <div 
            v-for="(q, idx) in exercise.questions"
            :key="q.Question_Id ?? idx"
            class="question-block"
          >

            <!-- Question text -->
            <input 
              v-model="q.statement"
              class="form-control mb-2"
              placeholder="Fråga / Mening"
              required
            />

            <!-- TRUE/FALSE -->
            <div v-if="exercise.type === 'true_false'" class="mb-2">
              <select v-model="q.correct" class="form-select">
                <option disabled value="">Välj korrekt</option>
                <option :value="1">Sant</option>
                <option :value="0">Falskt</option>
              </select>
            </div>

            <!-- MCQ ANSWERS -->
            <div v-if="exercise.type === 'mcq'">
              <label class="form-label">Alternativ</label>

              <div 
                v-for="(opt, i) in q.options"
                :key="opt.Option_Id ?? i"
                class="d-flex align-items-center mb-2"
              >

                <button
                  type="button"
                  class="btn me-2"
                  :class="q.correct === i ? 'btn-success' : 'btn-outline-secondary'"
                  @click="q.correct = i"
                >✔</button>

                <input
                  v-model="opt.text"
                  class="form-control me-2"
                  placeholder="Alternativ"
                  required
                />

                <button 
                  type="button"
                  class="btn btn-danger btn-sm"
                  @click="removeOption(q, i)"
                >X</button>
              </div>

              <button 
                type="button"
                class="btn btn-secondary btn-sm"
                @click="addOption(q)"
              >Lägg till alternativ</button>
            </div>

            <!-- ORDERING -->
            <div v-if="exercise.type === 'ordering'" class="mt-2">
              <span class="badge bg-primary me-2">{{ idx + 1 }}</span>
              <span class="text-light">Meningens ordning sparas automatiskt</span>
            </div>

            <button 
              type="button"
              class="btn btn-warning btn-sm mt-3"
              @click="removeQuestion(idx)"
            >Ta bort fråga</button>

          </div>

          <button 
            type="button"
            class="btn btn-primary add-question-btn"
            @click="addQuestion"
          >+ Lägg till fråga</button>
        </div>

        <!-- CLASSES -->
        <div class="mb-3">
          <label class="form-label">Tilldela klasser</label>

          <div class="class-select-list">
            <div
              v-for="cls in classes"
              :key="cls.class_id"
              class="class-select-item"
              :class="{ selected: selectedClasses.includes(cls.class_id) }"
              @click="toggleClass(cls.class_id)"
            >
              <div>
                <strong>{{ cls.class_name }}</strong>
                <span v-if="selectedClasses.includes(cls.class_id)" class="assigned-text">✔ Tilldelad</span>
              </div>

              <div class="checkmark-box">
                <span v-if="selectedClasses.includes(cls.class_id)">✔</span>
              </div>
            </div>
          </div>
        </div>

        <!-- BUTTONS -->
        <div class="d-flex mt-4">
          <button class="btn btn-success px-4">
            {{ isEdit ? "Spara ändringar" : "Skapa övning" }}
          </button>

          <button 
            v-if="isEdit"
            type="button"
            class="btn btn-danger px-4 delete-bottom-btn"
            @click="deleteExercise"
          >
            🗑 Radera övning
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const route = useRoute();

const exercise = ref({
  exercise_id: null,
  title: "",
  description: "",
  type: "true_false",
  max_xp: 25,
  questions: []
});

const classes = ref([]);
const selectedClasses = ref([]);
const isEdit = ref(false);

/* LOAD CLASSES */
const loadClasses = async () => {
  const res = await axios.get("http://localhost/fragesport/api/get_classes.php", { withCredentials: true });
  classes.value = res.data.classes;
};

const toggleClass = id => {
  if (selectedClasses.value.includes(id))
    selectedClasses.value = selectedClasses.value.filter(c => c !== id);
  else
    selectedClasses.value.push(id);
};

/* ADD / REMOVE QUESTIONS */
const addQuestion = () => {
  if (exercise.value.type === "mcq") {
    exercise.value.questions.push({
      Question_Id: null,
      statement: "",
      correct: null,
      options: [{ Option_Id: null, text: "", correct: 0 }]
    });
  } else if (exercise.value.type === "ordering") {
    exercise.value.questions.push({
      Question_Id: null,
      statement: "",
      correct: exercise.value.questions.length + 1
    });
  } else {
    exercise.value.questions.push({
      Question_Id: null,
      statement: "",
      correct: ""
    });
  }
};

const removeQuestion = i => exercise.value.questions.splice(i, 1);
const addOption = q => q.options.push({ Option_Id: null, text: "", correct: 0 });
const removeOption = (q, i) => q.options.splice(i, 1);

/* LOAD EXERCISE */
const loadExercise = async (id) => {
  const res = await axios.get(`http://localhost/fragesport/api/get_exercise.php?id=${id}`, { withCredentials: true });

  const data = res.data.exercise;

  // ordering = one question → multiple statements
  if (data.Type === "ordering") {
    exercise.value = {
      exercise_id: data.Exercise_Id,
      title: data.Title,
      description: data.Description,
      type: data.Type,
      max_xp: data.Max_XP,
      questions: data.questions[0].options.map(o => ({
        Question_Id: o.Option_Id,
        statement: o.text,
        correct: Number(o.correct)
      }))
    };
  } 
  else {
    exercise.value = {
      exercise_id: data.Exercise_Id,
      title: data.Title,
      description: data.Description,
      type: data.Type,
      max_xp: data.Max_XP,
      questions: data.questions.map(q => ({
        Question_Id: q.Question_Id,
        statement: q.Statement,
        correct: Number(q.Correct),
        options:
          data.Type === "mcq"
            ? q.options.map(o => ({
                Option_Id: o.Option_Id,
                text: o.Option_Text ?? o.text,
                correct: Number(o.Is_Correct ?? o.correct)
              }))
            : []
      }))
    };
  }

  selectedClasses.value = data.assigned_classes || [];
  isEdit.value = true;
};

/* SAVE */
const saveExercise = async () => {
  if (exercise.value.type === "mcq") {
    exercise.value.questions.forEach(q => {
      q.options = q.options.map((opt, i) => ({
        Option_Id: opt.Option_Id ?? null,
        text: opt.text,
        correct: q.correct === i ? 1 : 0
      }));
    });
  }

  if (exercise.value.type === "ordering") {
    exercise.value.questions.forEach((q, i) => q.correct = i + 1);
  }

  const res = await axios.post(
    "http://localhost/fragesport/api/create_edit_exercise.php",
    {
      exercise: exercise.value,
      classes: selectedClasses.value
    },
    { withCredentials: true }
  );

  if (res.data.success) {
    alert("Övningen sparades!");
    router.push("/teacher-dashboard");
  } else {
    alert(res.data.message);
  }
};

/* DELETE */
const deleteExercise = async () => {
  if (!confirm("Vill du radera övningen?")) return;

  const res = await axios.post(
    "http://localhost/fragesport/api/delete_exercise.php",
    { exercise_id: exercise.value.exercise_id },
    { withCredentials: true }
  );

  if (res.data.success) {
    alert("Övning raderad!");
    router.push("/teacher-dashboard");
  }
};

onMounted(() => {
  loadClasses();

  if (route.query.id)
    loadExercise(route.query.id);
});
</script>
