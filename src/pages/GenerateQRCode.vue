 <!-- eslint-disable -->
<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">ساخت QRCode</h1>

    <!-- انتخاب بخش اصلی -->
    <div class="mb-4">
      <label class="block mb-1">بخش اصلی:</label>
      <select v-model="mainSection" class="border p-2 rounded w-full">
        <option value="">انتخاب کنید...</option>
        <option value="cut">برش</option>
        <option value="sewing">سالن دوخت</option>
        <option value="final">نهایی‌کار</option>
      </select>
    </div>

    <!-- جزئیات برای بخش برش -->
    <div v-if="mainSection === 'cut'" class="mb-4">
      <label class="block mb-1">بخش برش:</label>
      <select v-model="cutPart" class="border p-2 rounded w-full">
        <option value="">انتخاب کنید...</option>
        <option value="آستین">آستین</option>
        <option value="جلوی مانتو">جلوی مانتو</option>
      </select>
      <input v-model="code" placeholder="کد مانتو" class="border p-2 mt-2 rounded w-full" />
      <input v-model.number="count" type="number" placeholder="تعداد" class="border p-2 mt-2 rounded w-full" />
    </div>

    <!-- جزئیات برای بخش دوخت -->
    <div v-if="mainSection === 'sewing'" class="mb-4">
      <label class="block mb-1">بخش دوخت:</label>
      <select v-model="sewingPart" class="border p-2 rounded w-full">
        <option value="">انتخاب کنید...</option>
        <option value="زیگزاگ">زیگزاگ</option>
        <option value="اتو">اتو</option>
      </select>
      <input v-model="code" placeholder="کد مانتو" class="border p-2 mt-2 rounded w-full" />
      <input v-model.number="count" type="number" placeholder="تعداد" class="border p-2 mt-2 rounded w-full" />
    </div>

    <!-- جزئیات برای نهایی‌کار -->
    <div v-if="mainSection === 'final'" class="mb-4">
      <input v-model="finalCode" placeholder="کد کار" class="border p-2 rounded w-full" />
      <input v-model.number="count" type="number" placeholder="تعداد" class="border p-2 mt-2 rounded w-full" />
    </div>

    <!-- دکمه ساخت QR -->
    <button @click="generateQR" class="bg-blue-600 text-white px-4 py-2 rounded mt-4">
      ساخت QRCode
    </button>

<!-- نمایش QRCode -->
<div v-if="qrData" class="mt-6 flex flex-col items-center">
  <p class="mb-2 font-medium">QR تولید شده:</p>
<qrcode-vue ref="qrCanvas" :value="qrData" :size="200" />
  <p class="mt-2 text-sm text-gray-700">{{ qrLabel }}</p> <!-- 👈 اضافه شده -->
  <button @click="printQRCode" class="bg-green-600 text-white px-4 py-2 rounded mt-4">
    🖨️ چاپ لیبل
  </button>
</div>

  </div>
</template>

<script>
import QrcodeVue from 'qrcode.vue'

export default {
  components: {
    QrcodeVue
  },
  data() {
    return {
      mainSection: '',
      cutPart: '',
      sewingPart: '',
      finalCode: '',
      code: '',
      count: null,
      qrLabel: '',
      qrData: ''
    }
  },
  methods: {
generateQR() {
  let content = ''
  let label = ''
  if (this.mainSection === 'cut') {
    content = `بخش: برش - قسمت: ${this.cutPart} - کد: ${this.code} - تعداد: ${this.count}`
    label = `برش - ${this.cutPart} - ${this.code} - تعداد: ${this.count}`
  } else if (this.mainSection === 'sewing') {
    content = `بخش: دوخت - قسمت: ${this.sewingPart} - کد: ${this.code} - تعداد: ${this.count}`
    label = `دوخت - ${this.sewingPart} - ${this.code} - تعداد: ${this.count}`
  } else if (this.mainSection === 'final') {
    content = `بخش: نهایی‌کار - کد کار: ${this.finalCode} - تعداد: ${this.count}`
    label = `نهایی‌کار - ${this.finalCode} - تعداد: ${this.count}`
  }
  this.qrData = content
  this.qrLabel = label
}
,
printQRCode() {
  const canvas = this.$refs.qrCanvas // ❗ مستقیم به DOM اشاره داره
  if (!canvas || typeof canvas.toDataURL !== 'function') {
    return alert('QRCode هنوز آماده نیست.')
  }

  const imgData = canvas.toDataURL()
  const label = this.qrLabel

  const printWindow = window.open('', '_blank', 'width=300,height=400')
  printWindow.document.open()
  printWindow.document.write('<!DOCTYPE html>')
  printWindow.document.write('<html dir="rtl"><head><title>چاپ QRCode</title>')
  printWindow.document.write('<style>')
  printWindow.document.write(`
    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-family: sans-serif;
      padding: 20px;
    }
    img {
      width: 180px;
      height: 180px;
      margin-bottom: 10px;
    }
    p {
      font-size: 14px;
      text-align: center;
      margin: 0;
    }
  `)
  printWindow.document.write('</style></head><body>')
  printWindow.document.write('<img src="' + imgData + '" />')
  printWindow.document.write('<p>' + label + '</p>')
  printWindow.document.write('<scr' + 'ipt>')
  printWindow.document.write(`
    window.onload = function () {
      window.print();
      window.onafterprint = function () {
        window.close();
      };
    };
  `)
  printWindow.document.write('</scr' + 'ipt></body></html>')
  printWindow.document.close()
}
  }
}
</script>


