let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/themes/' + directory
const dist = 'public/themes/' + directory
const withoutWebpackBar = (plugin) => !['WebpackBar', 'WebpackBarPlugin'].includes(plugin.constructor?.name)

Mix.listen('configReady', (config) => {
    config.plugins = (config.plugins || []).filter(withoutWebpackBar)
})

mix
    .sass(source + '/assets/sass/style.scss', dist + '/css')
    .js(source + '/assets/js/script.js', dist + '/js')
    .js(source + '/assets/js/main.js', dist + '/js')
    .override((config) => {
        config.plugins = (config.plugins || []).filter(withoutWebpackBar)
    })

if (mix.inProduction()) {
    mix
        .copy(dist + '/css/style.css', source + '/public/css')
        .copy(dist + '/js/script.js', source + '/public/js')
        .copy(dist + '/js/main.js', source + '/public/js')
}
