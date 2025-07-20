const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,

  // مسیر انتشار (بسته به محیط)
  publicPath: process.env.VUE_APP_BASE_PATH || '/',

  filenameHashing: true,

  configureWebpack: {
    output: {
      filename: 'js/[name].[contenthash].js',
      chunkFilename: 'js/[name].[contenthash].js'
    }
  }
})


