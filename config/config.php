<?php

/** load the parameters configuration */
$parameterFile = __DIR__ . '/parameters.json';
if (!file_exists($parameterFile))
{
    // allows you to customize parameter file
    $parameterFile = $parameterFile . '.dist';
}

if (!$parameters = json_decode(file_get_contents($parameterFile), true))
{
    exit('unable to parse parameters file: ' . $parameterFile);
}

$app['parameters'] = $parameters;
