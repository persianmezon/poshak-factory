<template>
  <div class="min-h-screen bg-gray-50">

    <!-- نمایش محتوای مسیرهای router -->
    <main class="px-4 sm:px-8 pb-10">
      <router-view @edit-batch="setBatchToEdit" />
    </main>
  </div>
</template>

<script>
import { useRoute } from 'vue-router'

export default {
  setup() {
    const route = useRoute()

    const setBatchToEdit = (batch) => {
      console.log('Batch to edit:', batch)
    }

    return { route, setBatchToEdit }
  }
}
</script>
