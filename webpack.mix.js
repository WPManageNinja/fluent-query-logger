const mix = require('./resources/mix');

mix
    .js('resources/admin/start.js', 'assets/admin/js/start.js').vue({ version: 3 })
    .js('resources/admin/global_admin.js', 'assets/admin/js/global_admin.js')
    .sass('resources/scss/admin.scss', 'assets/admin/css/admin.css')
    .copy('resources/images', 'assets/images')
    .copy('resources/fonts', 'assets/admin/css/fonts');
