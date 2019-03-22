<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$bDefaultColumns = $arResult["GRID"]["DEFAULT_COLUMNS"];
$colspan = ($bDefaultColumns) ? count($arResult["GRID"]["HEADERS"]) : count($arResult["GRID"]["HEADERS"]) - 1;
$bPropsColumn = false;
$bUseDiscount = false;
$bPriceType = false;
$bShowNameWithPicture = ($bDefaultColumns) ? true : false; // flat to show name and picture column in one column
?>
<div class="cart__blocks">
    <div class="cart__block">
        <div class="cart__blocks__icon">5</div>

        <div class="cart__info">
            <p>
                Товаров на: <?=$arResult["ORDER_PRICE_FORMATED"]?>
            </p>
            <p>
                Доставка: <?=$arResult["DELIVERY_PRICE_FORMATED"]?>
            </p>
            <p>
                Итого: <?=$arResult["ORDER_TOTAL_PRICE_FORMATED"]?>
            </p>
        </div>
    </div>
</div>
