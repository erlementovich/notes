<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="cart__blocks">
    <div class="cart__block">
        <div class="cart__blocks__icon">1</div>
        <label class="cart__label" for="person-type">Выберите тип плательщика</label>

        <?foreach($arResult["PERSON_TYPE"] as $v):?>
            <input class="cart__radio" type="radio" id="PERSON_TYPE_<?=$v["ID"]?>" name="PERSON_TYPE" value="<?=$v["ID"]?>"<?if ($v["CHECKED"]=="Y") echo " checked=\"checked\"";?> onClick="submitForm()">
            <label class="cart__label__radio" for="PERSON_TYPE_<?=$v["ID"]?>"><?=$v["NAME"]?></label>
        <?endforeach;?>

        <input type="hidden" name="PERSON_TYPE_OLD" value="<?=$arResult["USER_VALS"]["PERSON_TYPE_ID"]?>" />
    </div>
</div>
