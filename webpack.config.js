var Encore = require('@symfony/webpack-encore');

Encore
// directory where all compiled assets will be stored
    .setOutputPath('src/Resources/public/')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/bundles/terminal42leads')

    // removes the /layout prefix from assets paths
    .setManifestKeyPrefix('')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/layout/app.js
    .addEntry('leads', './assets/leads.js')

    // will output as web/layout/global.css
    // .addStyleEntry('global', './layout/styles/global.scss')

    // will require minified scripts without packing them
    .addLoader({
        test: /\.min\.js$/,
        use: [ 'script-loader' ]
    })

    //
    .addLoader({
        test: /\.(gif|png|jpe?g|svg)$/i,
        use: [ 'image-webpack-loader' ],
    })

    // allow sass/scss files to be processed
    .enableSassLoader()

    // optimize css files
    .enablePostCssLoader()

    // allow legacy applications to use $/jQuery as a global variable
    //.autoProvidejQuery()

    .enableSourceMaps(!Encore.isProduction())

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
