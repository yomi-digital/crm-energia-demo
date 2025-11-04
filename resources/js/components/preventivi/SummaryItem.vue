<template>
    <div class="flex justify-between items-start py-2 border-b border-gray-100">
      <span class="text-sm font-medium text-gray-600">{{ label }}:</span>
      <span class="text-sm text-gray-800 font-semibold text-right">
        <slot>{{ value || 'Non specificato' }}</slot>
      </span>
    </div>
</template>

<script setup lang="js">
import { defineProps } from 'vue';

defineProps({
  label: String,
  value: [String, Number, Object],
});
</script>
