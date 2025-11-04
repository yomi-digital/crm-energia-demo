<template>
  <div class="card" style="padding:16px;height:100%;">
    <p class="muted">{{ title }}</p>
    <p style="font-size:22px;font-weight:700;color:#059669;">{{ value }}</p>
    <p style="font-size:12px;color:#9ca3af;margin-top:4px;">{{ description }}</p>
  </div>
</template>

<script setup lang="js">
import { defineProps } from 'vue';

defineProps({
  title: String,
  value: String,
  description: String,
});
</script>
