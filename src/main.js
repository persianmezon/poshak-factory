import { createApp } from 'vue'
import App from './App.vue'
import VueLazyload from 'vue3-lazy'
import router from './router'
import './assets/tailwind.css'
import './assets/styles.css'
import { onAuthStateChanged } from 'firebase/auth'
import { auth } from '@/firebase'
import DatePicker from 'vue3-persian-datetime-picker'
import './registerServiceWorker'

let app;

onAuthStateChanged(auth, () => {
  if (!app) {
    app = createApp(App)
    app.use(router)
    app.use(VueLazyload, {
      loading: '', // می‌تونی آدرس عکس لودینگ بذاری
      error: '',   // می‌تونی آدرس عکس خطا بذاری
    })
    app.component('DatePicker', DatePicker)
    app.mount('#app')
  }
})

if ('serviceWorker' in navigator) {
  navigator.serviceWorker.getRegistrations().then(function(registrations) {
    for (let registration of registrations) {
      registration.unregister();
    }
  });
}
