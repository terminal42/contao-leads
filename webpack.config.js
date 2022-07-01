const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/terminal42leads')
    .setManifestKeyPrefix('')
    .cleanupOutputBeforeBuild()
    .disableSingleRuntimeChunk()

    .enableSassLoader()
    .enablePostCssLoader()
    .enableSourceMaps()
    .enableVersioning()

    .addEntry('leads', './assets/leads.js')

    .addLoader({
        test: /\.(gif|png|jpe?g|svg)$/i,
        use: [ 'image-webpack-loader' ],
    })
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
