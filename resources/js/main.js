import App from '@/App.vue';
import { registerPlugins } from '@core/utils/plugins';
import { PhoneInput } from '@lbgm/phone-number-input';
import { createApp } from 'vue';

// Styles
import '@core-scss/template/index.scss';
import '@styles/styles.scss';

// Create vue app
const app = createApp(App)

// Register phone input component globally
app.component('PhoneInput', PhoneInput)


// Register plugins
registerPlugins(app)

// Mount vue app
app.mount('#app')
