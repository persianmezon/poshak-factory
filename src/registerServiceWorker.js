/* eslint-disable no-console */

import { register } from 'register-service-worker'

if (process.env.NODE_ENV === 'production') {
  register(`${process.env.BASE_URL}service-worker.js`, {
    ready () {
      console.log(
        '✅ App is being served from cache by a service worker.\n' +
        'More info: https://goo.gl/AFskqB'
      )
    },
    registered () {
      console.log('✅ Service worker has been registered.')
    },
    cached () {
      console.log('💾 Content has been cached for offline use.')
    },
    updatefound () {
      console.log('🔄 New content is downloading...')
    },
    updated (registration) {
      console.log('🚀 New content is available; refreshing...')

      // کش قبلی رو پاک کن و نسخه جدید رو فعال کن
      if (registration && registration.waiting) {
        registration.waiting.postMessage({ type: 'SKIP_WAITING' })

        // کمی تاخیر برای اطمینان، بعدش رفرش
        setTimeout(() => {
          window.location.reload()
        }, 1000)
      }
    },
    offline () {
      console.log('⚠️ No internet connection found. Running in offline mode.')
    },
    error (error) {
      console.error('❌ Error during service worker registration:', error)
    }
  })
}

if ('serviceWorker' in navigator) {
  navigator.serviceWorker.getRegistrations().then(function(registrations) {
    for(let registration of registrations) {
      registration.unregister();
    }
  });
}
