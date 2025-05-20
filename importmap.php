<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'react' => [
        'version' => '19.1.0',
    ],
    'react-dom' => [
        'version' => '19.1.0',
    ],
    'react-router-dom' => [
        'version' => '6.20.0',
    ],
    './components/Home.js' => [
        'path' => './assets/react/components/Home.js',
    ],
    './components/Statistics.js' => [
        'path' => './assets/react/components/Statistics.js',
    ],
    './components/UploadCalls.js' => [
        'path' => './assets/react/components/UploadCalls.js',
    ],
];
