/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1976D2',   // آبی مدیریتی برند
        accent: '#4CAF50',    // سبز تولید
        warning: '#FF5722',   // رنگ هشدارها
        background: '#F9FAFB' // پس‌زمینه نرم و سازمانی
      }
    },
  },
  plugins: [],
}
