<script setup>
import { useBrandCheck } from '@/composables/useBrandCheck'
import { layoutConfig } from '@layouts'
import { can } from '@layouts/plugins/casl'
import { useLayoutConfigStore } from '@layouts/stores/config'
import {
  getComputedNavLinkToProp,
  getDynamicI18nProps,
  isNavLinkActive,
} from '@layouts/utils'

const props = defineProps({
  item: {
    type: null,
    required: true,
  },
})

const configStore = useLayoutConfigStore()
const hideTitleAndBadge = configStore.isVerticalNavMini()

// Controllo brand per menu Preventivi
const { isAdmin, checkAlfacomSolarBrand } = useBrandCheck()
const hasAlfacomSolarBrand = ref(false) // Inizializza come false per non mostrare finché il check non è completato

// Controlla se questo è un menu item che richiede il brand ALFACOM SOLAR
const requiresAlfacomSolarBrand = computed(() => {
  return props.item.subject === 'preventivi' && 
         (props.item.to === 'workflow-preventivi' || props.item.to === 'workflow-archivio-preventivi')
})

// Controlla se il menu item può essere mostrato
const canShowMenuItem = computed(() => {
  const caslCheck = can(props.item.action, props.item.subject)
  console.log('canShowMenuItem computed:', {
    item: props.item.title,
    action: props.item.action,
    subject: props.item.subject,
    caslCheck,
    requiresAlfacomSolarBrand: requiresAlfacomSolarBrand.value,
    isAdmin: isAdmin.value,
    hasAlfacomSolarBrand: hasAlfacomSolarBrand.value,
  })
  
  // Controllo base dei permessi CASL
  if (!caslCheck && hasAlfacomSolarBrand.value !== true) {
    console.log('CASL check failed for:', props.item.title)
    return false
  }

  // Se non richiede il brand ALFACOM SOLAR, mostra normalmente
  if (!requiresAlfacomSolarBrand.value) {
    return true
  }

  // Se è admin, mostra sempre
  if (isAdmin.value) {
    return true
  }

  // Mostra solo se il check è completato e ha il brand abilitato
  const result = hasAlfacomSolarBrand.value === true
  console.log('Final result for', props.item.title, ':', result)
  return result
})

// Watch per vedere quando cambia hasAlfacomSolarBrand
watch(hasAlfacomSolarBrand, (newVal) => {
  console.log('hasAlfacomSolarBrand changed:', newVal, 'canShowMenuItem:', canShowMenuItem.value)
})

// Watch per vedere quando cambia canShowMenuItem
watch(canShowMenuItem, (newVal) => {
  console.log('canShowMenuItem changed:', newVal, 'item:', props.item.title)
})

// Controlla il brand quando il componente viene montato
onMounted(async () => {
  console.log('VerticalNavLink mounted:', {
    item: props.item,
    subject: props.item.subject,
    to: props.item.to,
    requiresAlfacomSolarBrand: requiresAlfacomSolarBrand.value,
    isAdmin: isAdmin.value,
    canShowMenuItem: canShowMenuItem.value,
    hasAlfacomSolarBrand: hasAlfacomSolarBrand.value,
  })
  
  if (requiresAlfacomSolarBrand.value && !isAdmin.value) {
    const result = await checkAlfacomSolarBrand()
    console.log('checkAlfacomSolarBrand result:', result)
    hasAlfacomSolarBrand.value = result
    console.log('hasAlfacomSolarBrand.value after update:', hasAlfacomSolarBrand.value)
    console.log('canShowMenuItem.value after update:', canShowMenuItem.value)
  }
})
</script>

<template>
  <li
    v-if="canShowMenuItem"
    class="nav-link"
    :class="{ disabled: item.disable }"
  >
    <Component
      :is="item.to ? 'RouterLink' : 'a'"
      v-bind="getComputedNavLinkToProp(item)"
      :class="{ 'router-link-active router-link-exact-active': isNavLinkActive(item, $router) }"
    >
      <Component
        :is="layoutConfig.app.iconRenderer || 'div'"
        v-bind="item.icon || layoutConfig.verticalNav.defaultNavItemIconProps"
        class="nav-item-icon"
      />
      <TransitionGroup name="transition-slide-x">
        <!-- 👉 Title -->
        <Component
          :is="layoutConfig.app.i18n.enable ? 'i18n-t' : 'span'"
          v-show="!hideTitleAndBadge"
          key="title"
          class="nav-item-title"
          v-bind="getDynamicI18nProps(item.title, 'span')"
        >
          {{ item.title }}
        </Component>

        <!-- 👉 Badge -->
        <Component
          :is="layoutConfig.app.i18n.enable ? 'i18n-t' : 'span'"
          v-if="item.badgeContent"
          v-show="!hideTitleAndBadge"
          key="badge"
          class="nav-item-badge"
          :class="item.badgeClass"
          v-bind="getDynamicI18nProps(item.badgeContent, 'span')"
        >
          {{ item.badgeContent }}
        </Component>
      </TransitionGroup>
    </Component>
  </li>
</template>

<style lang="scss">
.layout-vertical-nav {
  .nav-link a {
    display: flex;
    align-items: center;
  }
}
</style>
