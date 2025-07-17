// src/firebase.js

import { initializeApp } from "firebase/app"
import { getFirestore } from "firebase/firestore"
import { getStorage } from 'firebase/storage'
import { getAuth } from "firebase/auth";

const firebaseConfig = {
  apiKey: "AIzaSyASe7763aembdzd_hJM2pGOiIF57EBTSpU",
  authDomain: "poshak-factory.firebaseapp.com",
  projectId: "poshak-factory",
  storageBucket: "poshak-factory.appspot.com", // ✅ اصلاح spelling
  messagingSenderId: "1024169748853",
  appId: "1:1024169748853:web:e5eedb7f4ea5e339210b3c",
  measurementId: "G-F4KQG6DZ2C"
}

// 👇 این خط باید قبل از همه باشه
const app = initializeApp(firebaseConfig)

const db = getFirestore(app)
const auth = getAuth(app)
const storage = getStorage(app) // ✅ اینجا app پاس بده

export { db, auth, storage }

