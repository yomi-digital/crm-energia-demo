<template>
  <div class="pv-stepper card">
    <div class="stepper">
      <div class="stepper__sidebar">
        <h3 class="section-title">Creazione Preventivo</h3>
        <nav class="steps-nav">
          <a
            v-for="(step, index) in steps"
            :key="index"
            @click="handleStepClick(index)"
            :class="['step-link', index === currentStep ? 'is-active' : (index < currentStep ? 'is-available' : 'is-locked')]"
          >
            <div :class="['step-number', index <= currentStep ? 'is-complete' : '']" aria-hidden="true">
              <Icon :name="stepIcons[index] || 'user'" />
            </div>
            <span>{{ step.title }}</span>
          </a>
        </nav>
      </div>

      <div class="stepper__content">
        <div class="stepper__slot">
          <slot></slot>
        </div>

        <div class="actions">
          <button
            @click="onBack"
            :disabled="currentStep === 0"
            class="btn btn-outline"
          >
            Indietro
          </button>
          <button
            v-if="currentStep < steps.length - 1"
            @click="onNext"
            :disabled="isNextDisabled"
            class="btn btn-primary"
          >
            Avanti
          </button>
        </div>
      </div>
    </div>
  </div>
  
</template>

<script setup lang="js">
import { defineEmits, defineProps } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
  steps: Array,
  currentStep: Number,
  isNextDisabled: Boolean,
});

const emit = defineEmits(['step-click', 'next', 'back']);

const handleStepClick = (index) => {
  if (index < props.currentStep) {
    emit('step-click', index);
  }
};

const onNext = () => {
  emit('next');
};

const onBack = () => {
  emit('back');
};

const stepIcons = ['user','calculator','sun','clipboard','calendar','document'];
</script>

<style src="./preventivi.css"></style>
