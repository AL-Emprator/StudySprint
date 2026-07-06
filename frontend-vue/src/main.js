import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createPinia } from 'pinia' // neu
import { useAuthStore } from './services/auth' // neu

// App erstellen, Router einbinden und in den HTML-Container mounten
const app = createApp(App);
const pinia = createPinia()

app.use(pinia)
app.use(router);

const authStore = useAuthStore(pinia)

// Überprüfen, ob der Benutzer bereits eingeloggt ist, wenn die App geladen wird
await authStore.checkAuthentication


app.mount('#app');
