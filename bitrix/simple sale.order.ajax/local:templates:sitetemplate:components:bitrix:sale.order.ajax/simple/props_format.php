<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

if (!function_exists("PrintPropsForm"))
{
    function PrintPropsForm($arSource = array(), $locationTemplate = ".default")
    {
        $counter = 1;

        foreach ($arSource as $arProperties) :?>
            <div data-property-id-row="<?= intval(intval($arProperties["ID"])) ?>">
                <? if ($arProperties["TYPE"] != "LOCATION") : ?>
                    <input class="cart__input"
                           type="<?= $arProperties['TYPE'] ?>"
                           id="<?= $arProperties['FIELD_NAME'] ?>"
                           name="<?= $arProperties['FIELD_NAME'] ?>"
                           placeholder="<?= $arProperties['NAME'] ?>"
                           value="<?= $arProperties["VALUE"] ?>"
                    >
                <? endif ?>

                <? if ($arProperties["TYPE"] == "LOCATION") : ?>
                    <?
                    $value = 0;
                    if (is_array($arProperties["VARIANTS"]) && count($arProperties["VARIANTS"]) > 0)
                    {
                        foreach ($arProperties["VARIANTS"] as $arVariant)
                        {
                            if ($arVariant["SELECTED"] == "Y")
                            {
                                $value = $arVariant["ID"];
                                break;
                            }
                        }
                    }

                    // here we can get '' or 'popup'
                    // map them, if needed
                    if(CSaleLocation::isLocationProMigrated())
                    {
//                            $locationTemplateP = $locationTemplate == 'popup' ? 'search' : 'steps';
//                            $locationTemplateP = $_REQUEST['PERMANENT_MODE_STEPS'] == 1 ? 'steps' : $locationTemplateP; // force to "steps"
                        $locationTemplateP = 'search';
                    }
                    ?>

                    <?if($locationTemplateP == 'steps'):?>
                        <input type="hidden" id="LOCATION_ALT_PROP_DISPLAY_MANUAL[<?=intval($arProperties["ID"])?>]" name="LOCATION_ALT_PROP_DISPLAY_MANUAL[<?=intval($arProperties["ID"])?>]" value="<?=($_REQUEST['LOCATION_ALT_PROP_DISPLAY_MANUAL'][intval($arProperties["ID"])] ? '1' : '0')?>" />
                    <?endif?>

                    <?CSaleLocation::proxySaleAjaxLocationsComponent(array(
                        "AJAX_CALL" => "N",
                        "COUNTRY_INPUT_NAME" => "COUNTRY",
                        "REGION_INPUT_NAME" => "REGION",
                        "CITY_INPUT_NAME" => $arProperties["FIELD_NAME"],
                        "CITY_OUT_LOCATION" => "Y",
                        "LOCATION_VALUE" => $value,
                        "ORDER_PROPS_ID" => $arProperties["ID"],
                        "ONCITYCHANGE" => ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "submitForm()" : "",
                        "SIZE1" => $arProperties["SIZE1"],
                    ),
                        array(
                            "ID" => $value,
                            "CODE" => "",
                            "SHOW_DEFAULT_LOCATIONS" => "Y",

                            // function called on each location change caused by user or by program
                            // it may be replaced with global component dispatch mechanism coming soon
                            "JS_CALLBACK" => "submitFormProxy",

                            // function window.BX.locationsDeferred['X'] will be created and lately called on each form re-draw.
                            // it may be removed when sale.order.ajax will use real ajax form posting with BX.ProcessHTML() and other stuff instead of just simple iframe transfer
                            "JS_CONTROL_DEFERRED_INIT" => intval($arProperties["ID"]),

                            // an instance of this control will be placed to window.BX.locationSelectors['X'] and lately will be available from everywhere
                            // it may be replaced with global component dispatch mechanism coming soon
                            "JS_CONTROL_GLOBAL_ID" => intval($arProperties["ID"]),

                            "DISABLE_KEYBOARD_INPUT" => "Y",
                            "PRECACHE_LAST_LEVEL" => "Y",
                            "PRESELECT_TREE_TRUNK" => "Y",
                            "SUPPRESS_ERRORS" => "Y"
                        ),
                        $locationTemplateP,
                        true,
                        'location-block-wrapper'
                    )?>
                <? endif ?>
            </div>

            <?if(CSaleLocation::isLocationProEnabled()):?>

                <?
                $propertyAttributes = array(
                    'type' => $arProperties["TYPE"],
                    'valueSource' => $arProperties['SOURCE'] == 'DEFAULT' ? 'default' : 'form' // value taken from property DEFAULT_VALUE or it`s a user-typed value?
                );

                if(intval($arProperties['IS_ALTERNATE_LOCATION_FOR']))
                    $propertyAttributes['isAltLocationFor'] = intval($arProperties['IS_ALTERNATE_LOCATION_FOR']);

                if(intval($arProperties['CAN_HAVE_ALTERNATE_LOCATION']))
                    $propertyAttributes['altLocationPropId'] = intval($arProperties['CAN_HAVE_ALTERNATE_LOCATION']);

                if($arProperties['IS_ZIP'] == 'Y')
                    $propertyAttributes['isZip'] = true;
                ?>

                <script>
                    <?// add property info to have client-side control on it?>
                    (window.top.BX || BX).saleOrderAjax.addPropertyDesc(<?=CUtil::PhpToJSObject(array(
                        'id' => intval($arProperties["ID"]),
                        'attributes' => $propertyAttributes
                    ))?>);

                </script>
            <?endif?>

            <? if ($counter == 3 || $counter == 6) :?>
                <div style="clear: both;"></div>
            <? endif ?>

            <? $counter++; ?>
        <? endforeach; ?>
    <?
    }
}