<template>
  <div>
    <p>{{ question.Statement }}</p>

    <ul class="list-group">
      <li
        v-for="(item, index) in items"
        :key="item.Option_Id"
        class="list-group-item d-flex justify-content-between align-items-center"
      >
        <span>{{ item.text }}</span>

        <div>
          <button
            class="btn btn-sm btn-secondary me-1"
            @click="moveUp(index)"
            :disabled="index === 0"
          >↑</button>

          <button
            class="btn btn-sm btn-secondary"
            @click="moveDown(index)"
            :disabled="index === items.length - 1"
          >↓</button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { reactive, watch } from "vue"

const props = defineProps({
  question: Object,
  modelValue: Array
})

const emit = defineEmits(["update:modelValue"])

// Initiera items KORREKT → baserat på riktiga ID:n
const items = reactive(
  props.modelValue?.length
    ? props.question.options.filter(o => props.modelValue.includes(o.Option_Id))
    : [...props.question.options] // originalordning
)

// Watch och skicka tillbaka korrekt ordning (ID-array)
watch(
  items,
  () => {
    emit(
      "update:modelValue",
      items.map(i => i.Option_Id)
    )
  },
  { deep: true }
)

// Flytta upp/ner
const moveUp = (i) => {
  if (i === 0) return
  ;[items[i - 1], items[i]] = [items[i], items[i - 1]]
}

const moveDown = (i) => {
  if (i === items.length - 1) return
  ;[items[i], items[i + 1]] = [items[i + 1], items[i]]
}
</script>

<style scoped>
.list-group-item {
  cursor: grab;
}
</style>
