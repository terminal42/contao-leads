const Encore = require('@terminal42/contao-build-tools');

module.exports = Encore('assets')
    .setOutputPath('public/')
    .setPublicPath('/bundles/terminal42leads')
    .getWebpackConfig()
;
