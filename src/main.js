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

let appInstance = null

onAuthStateChanged(auth, () => {
  if (!appInstance) {
    appInstance = createApp(App)
    
    appInstance.use(router)
    appInstance.use(VueLazyload, {
      loading: '', // آدرس عکس لودینگ در صورت نیاز
      error: ''    // آدرس عکس خطا در صورت نیاز
    })
    appInstance.component('DatePicker', DatePicker)

    appInstance.mount('#app')
  }
})

// حذف سرویس‌ورکرهای قبلی
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.getRegistrations().then(registrations => {
    for (let reg of registrations) {
      reg.unregister()
    }
  })
}
