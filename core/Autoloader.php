<?php

class Autoloader
{
    public static function register(string $basePath): void
    {
        spl_autoload_register(function ($class) use ($basePath) {
            $relativeClass = str_replace('\\', '/', $class);
            $candidates = [
                $basePath . '/core/' . $relativeClass . '.php',
                $basePath . '/controllers/' . $relativeClass . '.php',
                $basePath . '/Controller/' . $relativeClass . '.php',
                $basePath . '/models/' . $relativeClass . '.php',
                $basePath . '/Model/' . $relativeClass . '.php',
                $basePath . '/repositories/' . $relativeClass . '.php',
                $basePath . '/config/' . $relativeClass . '.php',
            ];

            foreach ($candidates as $file) {
                if (is_file($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }
}
