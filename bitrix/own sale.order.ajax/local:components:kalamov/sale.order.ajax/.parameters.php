<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = [
    'PARAMETERS' => [
        'PERSON_TYPE_1' => [
            'NAME' => 'ID физического лица',
            'DEFAULT' => '1',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'PERSON_TYPE_2' => [
            'NAME' => 'ID юридического лица',
            'DEFAULT' => '2',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'PATH_TO_REGISTRATION' => [
            'NAME' => 'Путь к оформлению заказа',
            'DEFAULT' => '/personal/order/make/',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
        'PATH_TO_BASKET' => [
            'NAME' => 'Путь к корзине',
            'DEFAULT' => '/personal/cart/',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N'
        ],
    ]
];