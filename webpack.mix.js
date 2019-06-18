let path = require('path')
let mix = require('laravel-mix')
// const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')

const CODENAME = 'melle'
const CATALOG_JS_PATH = path.join('catalog/view/javascript', CODENAME)
// const CATALOG_CSS_PATH = path.join('catalog/view/theme/', CODENAME, 'stylesheet/')

mix.js(path.join(CATALOG_JS_PATH, 'src/main.js'), path.join(CATALOG_JS_PATH, 'dist/'+CODENAME+'.js'))
// mix.sass(path.join(CATALOG_CSS_PATH, 'main.scss'), CODENAME+'.css');
.disableNotifications()

mix.setPublicPath(path.join(CATALOG_JS_PATH, 'dist/'))

mix.extract(['vue', 'vuex', 'axios', 'vue-router',
    'vue-recaptcha', 'v-tooltip', 'v-click-outside',
    'vue-notification', 'vue-js-modal', 'vue-select',
    'vue-carousel', 'vue-slider-component', 'vue-rate-it'
])

// mix.options({
    // extractVueStyles: true,
    // purifyCss: true,
    // postCss: [require('autoprefixer')],
// })

if (mix.inProduction()) {
    mix.version();
}

mix.webpackConfig({
    plugins: [
        // new BundleAnalyzerPlugin()
    ],
})