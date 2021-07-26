<?php

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

function dd($res)
{
    echo '<pre>';
    print_r($res);
    echo '</pre>';
}
