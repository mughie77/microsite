<?php

return [
    'background' => 'images/background.jpg',
    'title' => 'Microsite Keren Saya',
    'menu' => [
        [
            'type' => 'link',
            'text' => 'Beranda',
            'url' => '#',
            'icon' => 'fa-solid fa-house'
        ],
        [
            'type' => 'modal',
            'text' => 'Tentang',
            'content' => 'Ini adalah jendela modal dengan beberapa informasi tentang microsite.',
            'icon' => 'fa-solid fa-circle-info'
        ],
        [
            'type' => 'link',
            'text' => 'Kontak',
            'url' => '#',
            'icon' => 'fa-solid fa-envelope'
        ]
    ]
];
