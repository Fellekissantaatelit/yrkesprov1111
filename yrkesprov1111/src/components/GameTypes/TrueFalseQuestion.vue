<template>
  <div>
    <p>{{ question.Statement }}</p>

    <button
      class="btn me-2"
      :class="modelValue === true ? 'btn-success' : 'btn-outline-success'"
      @click="select(true)"
    >
      Sant
    </button>

    <button
      class="btn"
      :class="modelValue === false ? 'btn-danger' : 'btn-outline-danger'"
      @click="select(false)"
    >
      Falskt
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  question: Object,
  modelValue: Boolean
})

const emit = defineEmits(["update:modelValue"])

const select = (val) => {
  emit("update:modelValue", val)
}
</script>
