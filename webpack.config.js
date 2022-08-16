var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')
    .addEntry('autocomplete-select', './assets/js/utils/form/autocomplete-select.js')
    .addEntry('calendar', './assets/js/event/rdv/calendar-index.js')
    .addEntry('device', './assets/js/device/device.js')
    .addEntry('directory', './assets/js/directory/directory.js')
    .addEntry('document', './assets/js/document/document-index.js')
    .addEntry('evaluation', './assets/js/evaluation/evaluationIndex.js')
    .addEntry('export', './assets/js/export/export-index.js')
    .addEntry('home', './assets/js/home/home.js')
    .addEntry('listPeople', './assets/js/listPeople/listPeople.js')
    .addEntry('location-search', './assets/js/location-search.js')
    .addEntry('login', './assets/js/security/login.js')
    .addEntry('export-model', './assets/js/export/export-model-index.js')
    .addEntry('note', './assets/js/note/note-index.js')
    .addEntry('payment', './assets/js/payment/payment-index.js')
    .addEntry('peopleGroup', './assets/js/peopleGroup/peopleGroup.js')
    .addEntry('person', './assets/js/person/person.js')
    .addEntry('place', './assets/js/place/place.js')
    .addEntry('placeGroup', './assets/js/place/placeGroup.js')
    .addEntry('occupancy', './assets/js/app/occupancy.js')
    .addEntry('rdv', './assets/js/event/rdv/rdv-index.js')
    .addEntry('referent', './assets/js/referent/referent.js')
    .addEntry('search', './assets/js/searchIndex.js')
    .addEntry('securityUser', './assets/js/security/securityUser.js')
    .addEntry('service', './assets/js/service/service.js')
    .addEntry('support', './assets/js/support/support.js')
    .addEntry('tag', './assets/js/tag/tagIndex.js')
    .addEntry('task', './assets/js/event/task/taskIndex.js')
    .addEntry('user', './assets/js/user/user.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabel(() => {
    }, {
        useBuiltIns: 'usage',
        corejs: 3
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    // .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()

    .copyFiles([{
        from: './node_modules/ckeditor/',
        to: 'ckeditor/[path][name].[ext]',
        pattern: /\.(js|css)$/,
        includeSubdirectories: false
    },
        {
            from: './node_modules/ckeditor/adapters',
            to: 'ckeditor/adapters/[path][name].[ext]'
        },
        {
            from: './node_modules/ckeditor/lang',
            to: 'ckeditor/lang/[path][name].[ext]'
        },
        {
            from: './node_modules/ckeditor/plugins',
            to: 'ckeditor/plugins/[path][name].[ext]'
        },
        {
            from: './node_modules/ckeditor/skins',
            to: 'ckeditor/skins/[path][name].[ext]'
        }
    ])

module.exports = Encore.getWebpackConfig();