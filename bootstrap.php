<?php
namespace GithubToJira;

spl_autoload_register(function ($class) {
    $class = str_replace(__NAMESPACE__ . '\\', '', $class);
    $class = str_replace(' ', '', ucwords(str_replace('_', ' ', str_replace('-', ' ', $class))));
    $class = str_replace(' ', '\\', ucwords(str_replace('\\', ' ', $class)));

    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    $file .= strtr($class, "\\_", DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR) . '.php';

    if (is_readable($file)) {
        require $file;
    }
});