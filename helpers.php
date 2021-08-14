<?php

function dd($res)
{
    echo '<pre>';
    print_r($res);
    echo '</pre>';
}

function getConfigs()
{
    $configs = include 'config.php';

    return $configs;
}

function isLocal()
{
    $configs = getConfigs();

    if (isset($configs['is_local']) && $configs['is_local']) {
        return true;
    }

    return false;
}

function serverUri($withQueryParams = true)
{
    $uri = $_SERVER['REQUEST_URI'];

    return $uri;
}


function getPathTo($path)
{
    $path = ltrim($path, '/');

    $uri = serverUri(false);

    $path = $uri . $path;

    return $path;
}

