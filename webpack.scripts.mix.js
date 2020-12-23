const path = require('path')
const mix = require('laravel-mix')
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')

const CODENAME = 'melle'
const CATALOG_THEME_PATH = path.join('catalog/view/theme', CODENAME)
const CATALOG_JS_PATH = path.join(CATALOG_THEME_PATH, 'javascript')

mix.js(path.join(CATALOG_JS_PATH, 'src/main.js'), path.join(CATALOG_JS_PATH, 'dist/'+CODENAME+'.js'))

mix.disableNotifications()

mix.setPublicPath(path.join(CATALOG_JS_PATH, 'dist'))

mix.extract()

if (mix.inProduction()) {
  mix.version();
}

mix.webpackConfig({
  resolve: {
    alias: {
      '@': path.resolve(path.join(CATALOG_JS_PATH, 'src')),
    },
  },
  plugins: [
    // new BundleAnalyzerPlugin()
  ],
})

