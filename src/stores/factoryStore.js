import { ref, onMounted } from 'vue'
import { db } from '@/firebase'
import { doc, getDoc } from 'firebase/firestore'

const factorySettings = ref({ name: '', themeColor: '' })

export function useFactorySettings() {
  onMounted(async () => {
    const snap = await getDoc(doc(db, 'config', 'factory'))
    if (snap.exists()) factorySettings.value = snap.data()
  })

  return { factorySettings }
}
