<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

Bitrix\Main\Loader::includeModule('iblock');

// проверка прав на модуль
$rights = $APPLICATION->GetGroupRight("redcollar.helpers");

if ($rights == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

// подготовка фильтра
$listID = 'company_catalogs_list';

$gridOptions = new GridOptions($listID);

$sort = $gridOptions->GetSorting([
    'sort' => ['NAME' => 'ASC'],
    'vars' => ['by' => 'by', 'order' => 'order']
]);

$navParams = $gridOptions->GetNavParams();

$nav = new PageNavigation($listID);

$nav->allowAllRecords(true)
    ->setPageSize($navParams['nPageSize'])
    ->initFromUri();

if ($nav->allRecordsShown()) {
    $navParams = false;
} else {
    $navParams['iNumPage'] = $nav->getCurrentPage();
}

$uiFilter = [
    ['id' => 'NAME', 'name' => 'Название', 'type'=>'text', 'default' => true],
];

// подготовка таблицы
$filterOption = new Bitrix\Main\UI\Filter\Options($listID);

$filterData = $filterOption->getFilter([]);

$filter = [
    'IBLOCK_TYPE_ID' => 'company_catalogs'
];

foreach ($filterData as $k => $v) {
    $filter['NAME'] = "%".$filterData['FIND']."%";
}

$nav = new PageNavigation("grid-catalogs-company");

$nav->allowAllRecords(true)
    ->setPageSize(10)
    ->initFromUri();

$columns = [];

$columns[] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true];
$columns[] = ['id' => 'NAME', 'name' => 'Название', 'sort' => 'NAME', 'default' => true];

$dbIblocks = Bitrix\Iblock\IblockTable::getList([
    'filter' => $filter,
    'select' => [
        "*",
    ],
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
    'order' => $sort['sort'],
    "count_total" => true,
]);

$nav->setRecordCount($dbIblocks->getCount());

while ($iblock = $dbIblocks->fetch()) {
    $list[] = [
        'data' => [
            "ID" => $iblock['ID'],
            "NAME" => $iblock['NAME'],
        ],
        'actions' => [
            [
                'text'    => 'Просмотр контента',
                'default' => true,
                'onclick' => 'location.href="/bitrix/admin/iblock_list_admin.php?IBLOCK_ID='.$iblock['ID'].'&type=company_catalogs&lang=ru&find_section_section=0"'
            ],
            [
                'text'    => 'Просмотр настроек',
                'default' => true,
                'onclick' => 'location.href="/bitrix/admin/iblock_edit.php?ID='.$iblock['ID'].'&type=company_catalogs&lang=ru&admin=Y"'
            ]
        ]
    ];
}

// вывод данных
$APPLICATION->SetTitle(GetMessage("page_title"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<div class="show-iblock">
    <h2>Поиск</h2>
    <div>
        <?$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
            'FILTER_ID' => $listID,
            'GRID_ID' => $listID,
            'FILTER' => $uiFilter,
            'ENABLE_LIVE_SEARCH' => true,
            'ENABLE_LABEL' => true
        ]);?>
    </div>
    <div style="clear: both;"></div>
    <h2>Список компаний</h2>
    <? $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
        'GRID_ID' => $listID,
        'COLUMNS' => $columns,
        'ROWS' => $list,
        'SHOW_ROW_CHECKBOXES' => false,
        'NAV_OBJECT' => $nav,
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'PAGE_SIZES' =>  [
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100']
        ],
        'AJAX_OPTION_JUMP' => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU' => true,
        'SHOW_GRID_SETTINGS_MENU' => true,
        'SHOW_NAVIGATION_PANEL' => true,
        'SHOW_PAGINATION' => true,
        'SHOW_SELECTED_COUNTER'  => true,
        'SHOW_TOTAL_COUNTER' => true,
        'SHOW_PAGESIZE' => true,
        'SHOW_ACTION_PANEL' => true,
        'ALLOW_COLUMNS_SORT' => true,
        'ALLOW_COLUMNS_RESIZE' => true,
        'ALLOW_HORIZONTAL_SCROLL' => true,
        'ALLOW_SORT' => true,
        'ALLOW_PIN_HEADER' => true,
        'AJAX_OPTION_HISTORY' => 'N'
    ]);
    ?>
</div>

<!-- Думаю, нет ничего страшного в таком описании CSS, могу сделать красиво, но таким образом все в одном php скрипте-->
<style>
    .show-iblock .main-ui-filter-search.main-ui-filter-theme-default {
        height: unset;
        margin: 0;
    }

    .show-iblock .main-ui-filter-search.main-ui-filter-theme-default input[type="text"] {
        height: 35px;
        border: unset;
        -webkit-box-shadow: unset;
        box-shadow: unset;
    }

    .show-iblock .main-ui-item-icon.main-ui-search {
        top: 0px;
        height: 35px;
    }
</style>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");