<?php

/*
 * Set specific configuration variables here
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    | Avatar use Intervention Image library to process image.
    | Meanwhile, Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "Imagick" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */
    'driver' => env('IMAGE_DRIVER', 'gd'),

    // Initial generator class
    'generator' => \Laravolt\Avatar\Generator\DefaultGenerator::class,

    // Whether all characters supplied must be replaced with their closest ASCII counterparts
    'ascii' => false,

    // Image shape: circle or square
    'shape' => 'circle',

    // Image width, in pixel
    'width' => 100,

    // Image height, in pixel
    'height' => 100,

    // Number of characters used as initials. If name consists of single word, the first N character will be used
    'chars' => 2,

    // font size
    'fontSize' => 48,

    // convert initial letter in uppercase
    'uppercase' => false,

    // Right to Left (RTL)
    'rtl' => false,

    // Fonts used to render text.
    // If contains more than one fonts, randomly selected based on name supplied
    'fonts' => [__DIR__ . '/../fonts/OpenSans-Bold.ttf', __DIR__ . '/../fonts/rockwell.ttf'],

    // List of foreground colors to be used, randomly selected based on name supplied
    'foregrounds' => [
        '#FFFFFF',
    ],

    // List of background colors to be used, randomly selected based on name supplied
    'backgrounds' => [
        '#f44336',
        '#ad1347',
        '#9C27B0',
        '#673AB7',
        '#3F51B5',
        '#0c66ad',
        '#046d9b',
        '#046a77',
        '#009688',
        '#1d7a20',
        '#4f8511',
        '#808a0d',
        '#947001',
        '#be7403',
        '#cc4115',
        '#EF5350',
        '#b22217',
        '#C2185B',
        '#880E4F',
        '#AB47BC',
        '#9C27B0',
        '#8E24AA',
        '#7B1FA2',
        '#4A148C',
        '#6A1B9A',
        '#7E57C2',
        '#5E35B1',
        '#512DA8',
        '#4527A0',
        '#311B92',
        '#6200EA',
        '#651FFF',
        '#5C6BC0',
        '#3F51B5',
        '#3949AB',
        '#303F9F',
        '#283593',
        '#1A237E',
        '#304FFE',
        '#3D5AFE',
        '#1565C0',
        '#0D47A1',
        '#0277BD',
        '#01579B',
        '#0f5b8a',
        '#0d8493',
        '#0097A7',
        '#00838F',
        '#006064',
        '#26A69A',
        '#009688',
        '#00897B',
        '#00796B',
        '#00695C',
        '#004D40',
        '#66BB6A',
        '#4CAF50',
        '#43A047',
        '#388E3C',
        '#2E7D32',
        '#1B5E20',
        '#00a946',
        '#8BC34A',
        '#7CB342',
        '#689F38',
        '#558B2F',
        '#33691E',
        '#9fa628',
        '#AFB42B',
        '#9E9D24',
        '#827717',
        '#a66d11',
        '#bd9008',
        '#a27406',
        '#915b01',
        '#c45703',
        '#bd6b02',
        '#F57C00',
        '#FB8C00',
        '#FF9800',
        '#EF6C00',
        '#E65100',
        '#cb4116',
        '#de6b47',
        '#FF7043',
        '#F4511E',
        '#E64A19',
        '#D84315',
        '#BF360C',
        '#8D6E63',
        '#795548',
        '#6D4C41',
        '#5D4037',
        '#3E2723',
        '#4E342E',
        '#A1887F',
        '#9E9E9E',
        '#616161',
        '#424242',
        '#90A4AE',
        '#78909C',
        '#546E7A',
        '#607D8B',
        '#455A64',
        '#37474F',
    ],

    'border' => [
        'size' => 1,

        // border color, available value are:
        // 'foreground' (same as foreground color)
        // 'background' (same as background color)
        // or any valid hex ('#aabbcc')
        'color' => 'background',

        // border radius, currently only work for SVG
        'radius' => 0,
    ],

    // List of theme name to be used when rendering avatar
    // Possible values are:
    // 1. Theme name as string: 'colorful'
    // 2. Or array of string name: ['grayscale-light', 'grayscale-dark']
    // 3. Or wildcard "*" to use all defined themes
    'theme' => ['colorful'],

    // Predefined themes
    // Available theme attributes are:
    // shape, chars, backgrounds, foregrounds, fonts, fontSize, width, height, ascii, uppercase, and border.
    'themes' => [
        'grayscale-light' => [
            'backgrounds' => ['#edf2f7', '#e2e8f0', '#cbd5e0'],
            'foregrounds' => ['#a0aec0'],
        ],
        'grayscale-dark' => [
            'backgrounds' => ['#2d3748', '#4a5568', '#718096'],
            'foregrounds' => ['#e2e8f0'],
        ],
        'colorful' => [
            'backgrounds' => [
                '#f44336',
                '#E91E63',
                '#9C27B0',
                '#673AB7',
                '#3F51B5',
                '#2196F3',
                '#03A9F4',
                '#00BCD4',
                '#009688',
                '#4CAF50',
                '#8BC34A',
                '#CDDC39',
                '#FFC107',
                '#FF9800',
                '#FF5722',
            ],
            'foregrounds' => ['#FFFFFF'],
        ],
        'pastel' => [
            'backgrounds' => [
                '#ef9a9a',
                '#F48FB1',
                '#CE93D8',
                '#B39DDB',
                '#9FA8DA',
                '#90CAF9',
                '#81D4FA',
                '#80DEEA',
                '#80CBC4',
                '#A5D6A7',
                '#E6EE9C',
                '#FFAB91',
                '#FFCCBC',
                '#D7CCC8',
            ],
            'foregrounds' => [
                '#FFF',
            ],
        ],
    ],
];
