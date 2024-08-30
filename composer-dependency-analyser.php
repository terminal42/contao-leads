<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->ignoreUnknownClasses(['Haste\Util\StringUtil'])

    // Optional integrations
    ->ignoreErrorsOnPackage('dompdf/dompdf', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('mpdf/mpdf', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('tecnickcom/tcpdf', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
