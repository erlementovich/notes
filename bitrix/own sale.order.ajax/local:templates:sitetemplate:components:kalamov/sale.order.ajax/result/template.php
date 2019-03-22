<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

echo "Спасибо за покупку"; echo '<br>';

if ($_REQUEST['payment_id'] == '10') {
    echo "Оплата картой сбербанка"; echo '<br>';
}

echo '<pre>'; print_r($_REQUEST); echo '</pre>';