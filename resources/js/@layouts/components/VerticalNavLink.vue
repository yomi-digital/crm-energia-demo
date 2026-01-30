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

// Controllo solar per menu Preventivi
const userData = useCookie('userData')

// Controlla se l'utente ha ruoli admin/gestione/amministrazione
const hasAdminRole = computed(() => {
  if (!userData.value?.roles) return false
  return userData.value.roles.some(
    role => ['gestione', 'amministrazione', 'admin'].includes(role.name)
  )
})

// Controlla se questo è un menu item che richiede il controllo solar
const requiresSolarCheck = computed(() => {
  return props.item.subject === 'preventivi' && 
         (props.item.to === 'workflow-preventivi' || props.item.to === 'workflow-archivio-preventivi')
})

// Controlla se il menu item può essere mostrato
const canShowMenuItem = computed(() => {
  const caslCheck = can(props.item.action, props.item.subject)
  
  // Controllo base dei permessi CASL
  if (!caslCheck) {
    return false
  }

  // Se non richiede il controllo solar, mostra normalmente
  if (!requiresSolarCheck.value) {
    return true
  }

  // Se ha ruoli admin/gestione/amministrazione, mostra sempre
  if (hasAdminRole.value) {
    return true
  }

  // Per gli altri utenti, controlla il campo solar
  const hasSolar = userData.value?.solar === true || userData.value?.solar === 1
  return hasSolar
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
