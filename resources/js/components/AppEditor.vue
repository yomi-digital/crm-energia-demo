<script setup>
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  rules: {
    type: Array,
    default: () => [],
  },
  editorOptions: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="app-editor">
    <label v-if="label" class="text-body-2 text-disabled mb-1">{{ label }}</label>
    <QuillEditor
      :content="modelValue"
      content-type="html"
      @update:content="emit('update:modelValue', $event)"
      :options="editorOptions"
      :placeholder="placeholder"
      theme="snow"
    />
    <div v-if="rules.length" class="text-caption text-error mt-1">
      {{ rules.map(rule => rule(modelValue)).find(result => result !== true) }}
    </div>
  </div>
</template>

<style>
.app-editor {
  margin-bottom: 1rem;
}
.ql-editor {
  min-height: 200px;
}
</style> 
