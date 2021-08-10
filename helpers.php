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
    if (!$withQueryParams) {
        $uri = substr($uri, 0, strpos($uri, '?'));
    }

    return $uri;
}


function getPathTo($path)
{
    $path = ltrim($path, '/');

    $uri = dirname(serverUri(false));

    $path = $uri . '/' . $path;

    if (!isLocal()){
        $path = '/S1' . $path;
    }

    return $path;
}

