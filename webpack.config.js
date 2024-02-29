const { Encore } = require('@terminal42/contao-build-tools');

module.exports = Encore()
    .setOutputPath('public/')
    .setPublicPath('/bundles/terminal42leads')
    .addEntry('leads', './assets/leads.js')
    .getWebpackConfig()
;
