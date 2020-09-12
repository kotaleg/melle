const path = require('path')
const mix = require('laravel-mix')
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')

const CODENAME = 'melle'
const CATALOG_JS_PATH = path.join('catalog/view/javascript', CODENAME)

mix.js(path.join(CATALOG_JS_PATH, 'src/main.js'), path.join(CATALOG_JS_PATH, 'dist/'+CODENAME+'.js'))

mix.disableNotifications()

mix.setPublicPath(path.join(CATALOG_JS_PATH, 'dist/'))

mix.extract([
  'vue',
  'vuex',
  'vue-router',
  'axios',
  'vue-recaptcha',
  'vue-the-mask',
  'vue-notification',
  'vue-js-modal',
  'vue-select',
  'vue-carousel',
  'vue-rate-it',
  'algoliasearch',
  'vue-instantsearch',
])

if (mix.inProduction()) {
  mix.version();
}

mix.webpackConfig({
  plugins: [
    // new BundleAnalyzerPlugin()
  ],
})
