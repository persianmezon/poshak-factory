<template>
  <AppLayout>
    <div class="p-4">
      <h2 class="text-2xl font-bold mb-4">مدیریت کاربران</h2>

      <!-- فرم افزودن کاربر جدید -->
<div class="mb-8 p-4 bg-white rounded shadow">
  <h3 class="text-lg font-semibold mb-2">افزودن کاربر جدید</h3>

  <input v-model="email" type="email" placeholder="ایمیل کاربر" class="input mb-2" />
  <input v-model="password" type="password" placeholder="رمز عبور" class="input mb-2" />

  <select v-model="role" class="input mb-2">
    <option value="">انتخاب نقش</option>
    <option value="supervisor">سرپرست</option>
    <option value="manager">مدیر تولید</option>
    <option value="admin">ادمین</option>
  </select>

  <button @click="addUser" class="button">ایجاد کاربر</button>
</div>

      <!-- لیست کاربران -->
      <div class="p-4 bg-white rounded shadow">
        <h3 class="text-lg font-semibold mb-4">لیست کاربران</h3>
        <table class="w-full border">
          <thead>
            <tr class="bg-gray-200">
              <th class="p-2 border">شناسه کاربر (UID)</th>
              <th class="p-2 border">نقش</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.uid">
              <td class="p-2 border">{{ user.uid }}</td>
              <td class="p-2 border">{{ user.role }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import { createUserWithEmailAndPassword } from 'firebase/auth';
import { auth, db } from '@/firebase';
import { doc, setDoc, collection, getDocs } from 'firebase/firestore';
import AppLayout from '@/components/AppLayout.vue';

export default {
  name: 'UsersManagement',
  components: { AppLayout },
  setup() {
    const email = ref('');
    const password = ref('');
    const role = ref('');
    const users = ref([]);

    const addUser = async () => {
      if (!email.value || !password.value || !role.value) {
        alert('لطفاً همه فیلدها را پر کنید');
        return;
      }

      try {
        const userCredential = await createUserWithEmailAndPassword(auth, email.value, password.value);
        const uid = userCredential.user.uid;

await setDoc(doc(db, 'users', uid), {
  email: email.value,
  role: role.value
});


        alert('کاربر با موفقیت ایجاد شد!');
        email.value = '';
        password.value = '';
        role.value = '';

        loadUsers();
      } catch (error) {
        console.error(error);
        alert('خطا در ایجاد کاربر');
      }
    };

    const loadUsers = async () => {
      const usersSnapshot = await getDocs(collection(db, 'users'));
      const userList = [];

      usersSnapshot.forEach(docSnap => {
        userList.push({
          uid: docSnap.id,
          role: docSnap.data().role
        });
      });

      users.value = userList;
    };

    onMounted(() => {
      loadUsers();
    });

    return {
      email, password, role, users, addUser
    };
  }
};
</script>

