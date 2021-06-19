const path = require('path')
const mix = require('laravel-mix')

const CODENAME = 'melle'
const CATALOG_THEME_PATH = path.join('catalog/view/theme', CODENAME)
const CATALOG_JS_PATH = path.join(CATALOG_THEME_PATH, 'javascript')

mix.js(path.join(CATALOG_JS_PATH, 'src/main.js'), path.join(CATALOG_JS_PATH, 'dist/'+CODENAME+'.js'))
  .vue({version: 2})
  .alias({ '@': path.resolve(path.join(CATALOG_JS_PATH, 'src')) })

mix.disableNotifications()

mix.setPublicPath(path.join(CATALOG_JS_PATH, 'dist'))

mix.extract()

if (mix.inProduction()) {
  mix.version();
}
