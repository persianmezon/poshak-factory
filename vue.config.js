const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: '/mahdi5989/',

  // فعال‌سازی هش برای فایل‌های خروجی (اجباری برای جلوگیری از conflict)
  filenameHashing: true,

  configureWebpack: {
    output: {
      filename: 'js/[name].[contenthash].js',
      chunkFilename: 'js/[name].[contenthash].js'
    }
  },

})

