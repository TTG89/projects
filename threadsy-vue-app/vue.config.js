const { defineConfig } = require('@vue/cli-service');
const webpack = require('webpack');

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: { // DEV SETTING
    server: true, // DEV SETTING
    allowedHosts: ['localthreadsyfrontend.ngrok.app'], // DEV SETTING - THIS WOULD NEED TO BE UPDATED EVERY LOAD OF NGORK
    client: { // DEV SETTING
      webSocketURL: { // DEV SETTING
        protocol: 'wss', // DEV SETTING
        hostname: 'localthreadsyfrontend.ngrok.app', // DEV SETTING - THIS WOULD NEED TO BE UPDATED EVERY LOAD OF NGORK
        port: 443, // DEV SETTING
      }
    },
  },
  // The base URL your application bundle will be deployed at.
  // This is only needed if you plan on deploying your site under a subpath.
  publicPath: process.env.NODE_ENV === 'production'
    ? '/production-sub-path/' // Use a specific path for production
    : 'https://localthreadsyfrontend.ngrok.app', // Use your ngrok URL for development, // DEV SETTING - THIS WOULD NEED TO BE UPDATED EVERY LOAD OF NGORK
  filenameHashing: true,
  css: {
    extract: {
      filename: 'css/app.css'
    }
  },
  configureWebpack: {
    plugins: [
      new webpack.DefinePlugin({
        '__VUE_OPTIONS_API__': JSON.stringify(true),
        '__VUE_PROD_DEVTOOLS__': JSON.stringify(false),
        '__VUE_PROD_HYDRATION_MISMATCH_DETAILS__': JSON.stringify(false)
      })
    ]
  }
});
