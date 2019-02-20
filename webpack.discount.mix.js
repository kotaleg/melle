let path = require('path')
let mix = require('laravel-mix')

const CODENAME = 'pro_discount'
const ADMIN_PATH = path.join('admin/view/javascript', CODENAME)

mix.js(path.join(ADMIN_PATH, 'src/main.js'), path.join(ADMIN_PATH, 'dist/'+CODENAME+'.js'))

mix.setPublicPath(path.join(ADMIN_PATH, 'dist/'))

mix.options({
    postCss: [require('autoprefixer')]
})