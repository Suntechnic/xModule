<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7c3c0c9c823fbe4d674247031e0cf639
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'cebe\\markdown\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'cebe\\markdown\\' => 
        array (
            0 => __DIR__ . '/..' . '/cebe/markdown',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7c3c0c9c823fbe4d674247031e0cf639::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7c3c0c9c823fbe4d674247031e0cf639::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7c3c0c9c823fbe4d674247031e0cf639::$classMap;

        }, null, ClassLoader::class);
    }
}