<template>
    <div v-if="items.length > 0" class="summary-list">
         <span class="title">{{ title }}:</span>
         <div style="margin-top:4px;text-align:right;">
            <div v-for="(item, index) in items" :key="index" style="font-size:14px;color:#1f2937;">
                <span>{{ item.nome_voce || item.description || 'N/D' }}: </span>
                <span style="font-weight:700;">
                    <span v-if="item.tipo_valore === '%'">
                        {{ (item.valore_default || item.amount || 0).toFixed(2) }}%
                    </span>
                    <span v-else>
                        {{ (item.amount || item.valore_default || 0).toLocaleString('it-IT', { style: 'currency', currency: 'EUR' }) }}
                    </span>
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="js">
import { defineProps } from 'vue';

defineProps({
  title: String,
  items: Array,
});
</script>
