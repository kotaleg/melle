const path = require('path')
const mix = require('laravel-mix')
const autoprefixer = require('autoprefixer')
const purgeCssPlugin = require('laravel-mix-purgecss')

const CODENAME = 'melle'
const CATALOG_THEME_PATH = path.join('catalog/view/theme', CODENAME)
const CATALOG_JS_PATH = path.join(CATALOG_THEME_PATH, 'javascript')

mix.sass(
  path.join(CATALOG_THEME_PATH, 'stylesheet/sass/_bootstrap.scss'),
  path.join(CATALOG_THEME_PATH, 'stylesheet/compiled/bootstrap.css'),{
    sassOptions: {
    },
  })
  .options({
    processCssUrls: false,
  })
  .purgeCss({
    extend: {
      content: [
        'catalog/view/theme/default/template/**/*.twig',
        path.join(CATALOG_THEME_PATH, 'template/**/*.twig'),
        path.join(CATALOG_THEME_PATH, 'template/**/*.tpl'),
        path.join(CATALOG_JS_PATH, 'src/**/*.vue'),
        path.join(CATALOG_JS_PATH, 'query/**/*.js'),
      ],
      whitelistPatterns: [
        /mm-*/,
      ],
    },
  })

mix.disableNotifications()

mix.setPublicPath(path.join(CATALOG_THEME_PATH, 'stylesheet/compiled'))

if (mix.inProduction()) {
  mix.version()
}

mix.webpackConfig({
  plugins: [
  ],
})
