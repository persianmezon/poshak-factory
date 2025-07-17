const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: '/mahdi4354/',
  pwa: {
    name: 'پوشاک فکتوری',
    themeColor: '#2563eb',
    manifestOptions: {
      short_name: 'Factory',
      start_url: '.',
      display: 'standalone',
      background_color: '#ffffff'
    }
  }
})


