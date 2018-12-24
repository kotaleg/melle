let path = require('path')
let mix = require('laravel-mix')

const CODENAME = 'melle'
const CATALOG_JS_PATH = path.join('catalog/view/javascript', CODENAME)
const CATALOG_CSS_PATH = path.join('catalog/view/theme/', CODENAME, 'stylesheet/')

// mix.js(path.join(CATALOG_JS_PATH, 'src/main.js'), path.join(CATALOG_JS_PATH, 'dist/'+CODENAME+'.js'))
mix.sass(path.join(CATALOG_CSS_PATH, 'main.scss'), CODENAME+'.css');

mix.setPublicPath(path.join(CATALOG_CSS_PATH, 'dist/'))

mix.options({
    // extractVueStyles: true,
    // purifyCss: true,
    // postCss: [require('autoprefixer')]
})