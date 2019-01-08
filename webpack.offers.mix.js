let path = require('path')
let mix = require('laravel-mix')

const CODENAME = 'super_offers'
const ADMIN_JS_PATH = path.join('admin/view/javascript', CODENAME)
const ADMIN_CSS_PATH = path.join('catalog/view/theme/', CODENAME, 'stylesheet/')

mix.js(path.join(ADMIN_JS_PATH, 'src/main.js'), path.join(ADMIN_JS_PATH, 'dist/'+CODENAME+'.js'))
// mix.sass(path.join(ADMIN_CSS_PATH, 'main.scss'), CODENAME+'.css');

mix.setPublicPath(path.join(ADMIN_CSS_PATH, 'dist/'))

mix.options({
    // extractVueStyles: true,
    // purifyCss: true,
    // postCss: [require('autoprefixer')]
})