<template>
  <div class="card" style="padding:16px;">
    <p class="muted">{{ title }}</p>
    <p style="font-size:22px;font-weight:700;color:#1f2937;">
      {{ value }} <span style="font-size:16px;font-weight:600;color:#6b7280;">{{ unit }}</span>
    </p>
  </div>
  
</template>

<script setup lang="js">
import { defineProps } from 'vue';

defineProps({
  title: String,
  value: String,
  unit: String,
});
</script>
