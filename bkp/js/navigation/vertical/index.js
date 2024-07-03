import general from './general'
import workflow from './workflow'
import configurazione from './configurazione'
import comunicazioni from './comunicazioni'
import amministrazione from './amministrazione'
import reports from './reports'
import business from './business'
// import marketing from './marketing'

import appsAndPages from './apps-and-pages'
import charts from './charts'
import dashboard from './dashboard'
import forms from './forms'
import others from './others'
import uiElements from './ui-elements'

export default [
    ...general,
    ...workflow,
    ...configurazione,
    ...comunicazioni,
    ...amministrazione,
    ...reports,
    ...business,
    // ...marketing,
    ...dashboard, ...appsAndPages, ...uiElements, ...forms, ...charts, ...others
]
