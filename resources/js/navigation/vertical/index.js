import amministrazione from './amministrazione'
import configurazione from './configurazione'
import general from './general'
import reports from './reports'
import workflow from './workflow'
// import marketing from './marketing'


export default [
    ...general,
    ...workflow,
    ...configurazione,
    // ...comunicazioni,
    ...amministrazione,
    ...reports,
    // ...business,
    // ...marketing,
    // ...dashboard,
]
