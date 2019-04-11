<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CustomSaleOrderAjax $component */

$this->addExternalCss(SITE_TEMPLATE_PATH . '/components/kalamov/sale.order.ajax/.default/jquery.formstyler/jquery.formstyler.css');
$this->addExternalCss(SITE_TEMPLATE_PATH . '/components/kalamov/sale.order.ajax/.default/jquery.formstyler/jquery.formstyler.theme.css');
$this->addExternalJs(SITE_TEMPLATE_PATH . '/components/kalamov/sale.order.ajax/.default/jquery.formstyler/jquery.formstyler.min.js');

$c = $component;
//echo '<pre>'; print_r($c->getDeliveries()); echo '</pre>';
?>

<section class="cart__wrapper">
    <div class="cart">
        <p class="cart__title">Контакты и доставка</p>
        <p class="cart__info"><?= $c->getAmountOfItems()?> <?= $c->getDeclension()?> на <?= $c->getFormatPrice()?></p>
        <form action="" method="POST">

            <!-- Тип плательщика -->
            <div class="cart__blocks">
                <div class="cart__block">
                <div class="cart__blocks__icon">1</div>
                    <label class="cart__label" for="person-type">Выберите тип плательщика</label>
                    <select name="person_type_id" id="person-type">
                        <? foreach ($c->getPersonTypes() as $person) : ?>
                            <option value="<?= $person['ID']?>" id="PERSON_TYPE_<?= $person['ID']?>" <? if (isset($person['SELECTED'])) : ?>selected<? endif ?>><?= $person['NAME']?></option>
                        <? endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Свойства заказа -->
            <? if ($arParams['PERSON_TYPE_ID'] == $arParams['PERSON_TYPE_1']) :?>
                <div class="cart__blocks type__physical">
                    <div class="cart__block">
                        <div class="cart__blocks__icon">2</div>
                        <?
                        $counter = 0;

                        foreach ($c->getOrderProperties() as $property) {
                            $type = 'text';

                            if ($property['CODE'] == 'EMAIL') {
                                $type = 'email';
                            }

                            if ($counter == 3) {
                                ?><div class="location__wrapper"><?
                                $APPLICATION->IncludeComponent(
                                    "bitrix:sale.location.selector.search",
                                    "kalamov.saleorderajax",
                                    Array(
                                        "CACHE_TIME" => "36000000",
                                        "CACHE_TYPE" => "A",
                                        "CODE" => "",
                                        "FILTER_BY_SITE" => "N",
                                        "ID" => "",
                                        "INITIALIZE_BY_GLOBAL_EVENT" => "",
                                        "INPUT_NAME" => "LOCATION",
                                        "JS_CALLBACK" => "",
                                        "JS_CONTROL_GLOBAL_ID" => "",
                                        "PROVIDE_LINK_BY" => "code",
                                        "SHOW_DEFAULT_LOCATIONS" => "N",
                                        "SUPPRESS_ERRORS" => "N"
                                    )
                                );
                                ?>
                                </div>
                                <?
                            }
                            ?>
                            <input class="cart__input" type="<?= $type?>" id="<?= $property['CODE']?>" name="<?= $property['CODE']?>" placeholder="<?= $property['NAME']?>">
                            <?
                            $counter++;
                        };
                        ?>
                    </div>
                </div>
            <? elseif ($arParams['PERSON_TYPE_ID'] == $arParams['PERSON_TYPE_2']) :?>
                <div class="cart__blocks type__juridical">
                    <div class="cart__block">
                        <div class="cart__blocks__icon">2</div>
                        <?
                        $counter = 0;

                        foreach ($c->getOrderProperties() as $property) :
                            $type = 'text';

                            if ($property['CODE'] == 'EMAIL') {
                                $type = 'email';
                            }

                            if ($counter == 6) {
                                ?><div class="location__wrapper"><?
                                $APPLICATION->IncludeComponent(
                                    "bitrix:sale.location.selector.search",
                                    "kalamov.saleorderajax",
                                    Array(
                                        "CACHE_TIME" => "36000000",
                                        "CACHE_TYPE" => "A",
                                        "CODE" => "",
                                        "FILTER_BY_SITE" => "N",
                                        "ID" => "",
                                        "INITIALIZE_BY_GLOBAL_EVENT" => "",
                                        "INPUT_NAME" => "LOCATION",
                                        "JS_CALLBACK" => "",
                                        "JS_CONTROL_GLOBAL_ID" => "",
                                        "PROVIDE_LINK_BY" => "code",
                                        "SHOW_DEFAULT_LOCATIONS" => "N",
                                        "SUPPRESS_ERRORS" => "N"
                                    )
                                );
                                ?>
                                </div>
                                <?
                            }
                            ?>
                            <input class="cart__input" type="<?= $type?>" id="<?= $property['CODE']?>" name="<?= $property['CODE']?>" placeholder="<?= $property['NAME']?>">
                            <?
                            $counter++;
                        endforeach;
                        ?>
                    </div>
                </div>
            <? endif ?>

            <!-- Служба доставки -->
            <div id="delivery" class="cart__blocks">
                <div class="cart__block">
                    <div class="cart__blocks__icon">3</div>
                    <label class="cart__label">Служба доставки</label>
                    <div class="cart__radio-group">
                        <? foreach ($c->getDeliveries() as $delivery) : ?>
                            <input class="cart__radio" type="radio" id="DELIVERY_<?= $delivery['ID']?>" name="delivery_id" value="<?= $delivery['ID']?>" <? if ($delivery['CHECKED'] == 'Y') :?>checked="checked"<? endif ?>>
                            <label class="cart__label__radio" for="DELIVERY_<?= $delivery['ID']?>"><?= $delivery['NAME']?><? if (!empty($delivery['PRICE'])) :?> - <?= $delivery['PRICE']?><? endif ?><? if (!empty($delivery['PERIOD'])) :?> (<?= $delivery['PERIOD']?>)<? endif ?></label>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Служба оплаты -->
            <div id="payment" class="cart__blocks">
                <div class="cart__block">
                    <div class="cart__blocks__icon">4</div>
                    <label class="cart__label">Способ оплаты</label>
                    <div class="cart__radio-group">
                        <? foreach ($c->getPaySystems() as $payment) : ?>
                            <input class="cart__radio" type="radio" id="payment_<?= $payment['ID']?>" name="payment_id" value="<?= $payment['ID']?>" <? if ($payment['CHECKED'] == 'Y') :?>checked="checked"<? endif ?>>
                            <label class="cart__label__radio" for="payment_<?= $payment['ID']?>"><?= $payment['NAME']?></label>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="cart__footer">
                <div class="helper"></div>
                <input type="hidden" name="action" value="save">
                <div class="cart__footer__agreement">Нажимая кнопку «Перейти к оплате», я даю свое согласие на обработку моих персональных данных.</div>
                <button type="submit" class="cart__footer__button">Перейти к оплате</button>
            </div>
        </form>
    </div>
</section>
