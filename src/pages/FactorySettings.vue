<template>
  <div class="p-6 max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">⚙️ تنظیمات کارخانه</h1>
    <input v-model="settings.name" placeholder="🧵 نام کارخانه" class="input mb-3" />
    <input v-model="settings.themeColor" placeholder="🎨 رنگ اصلی (hex)" class="input mb-3" />
    <button @click="saveSettings" class="button">💾 ذخیره</button>
  </div>
</template>

<script>
import { db } from '@/firebase'
import { doc, getDoc, setDoc } from 'firebase/firestore'

export default {
  name: 'FactorySettings',
  data() {
    return {
      settings: {
        name: '',
        themeColor: ''
      }
    }
  },
  async mounted() {
    const ref = doc(db, 'config', 'factory')
    const snap = await getDoc(ref)
    if (snap.exists()) this.settings = snap.data()
  },
  methods: {
    async saveSettings() {
      await setDoc(doc(db, 'config', 'factory'), this.settings)
      alert('✅ تنظیمات ذخیره شد')
    }
  }
}
</script>
