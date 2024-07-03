import { createMongoAbility } from '@casl/ability'
import { abilitiesPlugin } from '@casl/vue'

export default function (app) {
  const entryUserAbilityRules = useLocalStorage('userAbilityRules')
  const userAbilityRules = ref([])
  if (entryUserAbilityRules.value) {
    try {
      userAbilityRules.value = JSON.parse(entryUserAbilityRules.value)
    } catch (err) {
      console.log(err)
    }
  }
  const initialAbility = createMongoAbility(userAbilityRules.value ?? [])

  app.use(abilitiesPlugin, initialAbility, {
    useGlobalProperties: true,
  })
}
