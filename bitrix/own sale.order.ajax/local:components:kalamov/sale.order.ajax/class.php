<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use getShipmentCollectionBitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Grid\Declension;

class CustomSaleOrderAjax extends CBitrixComponent
{
    /** @var \Bitrix\Sale\Order */
    public $order;

    private $siteId;
    private $arResponse = [
        'html' => ''
    ];

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->siteId = Context::getCurrent()->getSite();
    }

    public function onPrepareComponentParams($arParams)
    {
        // установка типа плательщика
        if (isset($this->request['person_type_id'])) {
            $arParams['PERSON_TYPE_ID'] = intval($this->request['person_type_id']);
        } else {
            $arParams['PERSON_TYPE_ID'] = $arParams['PERSON_TYPE_1'];
        }

        // ajax mode
        if (isset($arParams['IS_AJAX']) && $arParams['IS_AJAX'] == 'Y') {
            $arParams['IS_AJAX'] = true;
        } else {
            if (isset($this->request['is_ajax']) && $this->request['is_ajax'] == 'Y') {
                $arParams['IS_AJAX'] = true;
            } else {
                $arParams['IS_AJAX'] = false;
            }
        }

        // actions
        if (isset($arParams['ACTION']) && strlen($arParams['ACTION']) > 0) {
            $arParams['ACTION'] = strval($arParams['ACTION']);
        } else {
            if (isset($this->request['action']) && strlen($this->request['action']) > 0) {
                $arParams['ACTION'] = strval($this->request['action']);
            } else {
                $arParams['ACTION'] = '';
            }
        }
        
        return $arParams;
    }

    public function getPersonTypes()
    {
        $dbPersonTypes = CSalePersonType::GetList(
            ['ID' => 'ASC'],
            [
                'LID' => SITE_ID,
                'ACTIVE' => 'Y'
            ]
        );

        while ($person = $dbPersonTypes->Fetch())
        {
            $persons[$person['ID']] = [
                'ID' => $person['ID'],
                'NAME' => $person['NAME']
            ];

            if ($this->arParams['PERSON_TYPE_ID'] == $person['ID']) {
                $persons[$person['ID']]['SELECTED'] = 'Y';
            }
        }

        return $persons;
    }

    public function getBasketItems()
    {
        $basketItems = Bitrix\Sale\Basket::loadItemsForFUser(
            CSaleBasket::GetBasketUserID(),
            $this->siteId
        )->getOrderableItems();

        return $basketItems;
    }

    public function getAmountOfItems()
    {
        return count($this->getBasketItems());
    }

    public function getDeclension()
    {
        $declension = new Declension('товар', 'товара', 'товаров');

        return $declension->get($this->getAmountOfItems());
    }

    public function getFormatPrice()
    {
        return SaleFormatCurrency($this->order->getPrice(), $this->order->getCurrency());
    }

    public function getDeliveries()
    {
        $shipment = false;

        /** @var \Bitrix\Sale\Shipment $shipmentItem */
        foreach ($this->order->getShipmentCollection() as $shipmentItem) {
            if (!$shipmentItem->isSystem()) {
                $shipment = $shipmentItem;
                break;
            }
        }

        $result = [];

        if (!empty($shipment)) {
            $availableDeliveries = Delivery\Services\Manager::getRestrictedObjectsList($shipment);

            foreach ($availableDeliveries as $delivery) {
                $id = $delivery->getId();

                $result[$id]['ID'] = $id;
                $result[$id]['NAME'] = $delivery->getName();

                if ($delivery->isCalculatePriceImmediately()) {
//                    $shipment->setField('DELIVERY_ID', $delivery->getId());
                    $calcResult = $delivery->calculate();

                    if ($calcResult->isSuccess()) {
                        $result[$id]['PERIOD'] = $calcResult->getPeriodDescription();
                        $result[$id]['PRICE'] = SaleFormatCurrency(
                            $calcResult->getPrice(),
                            $this->order->getCurrency()
                        );
                    }
                }
            }

            if (isset($this->request['delivery_id']) && array_key_exists(intval($this->request['delivery_id']), $result)) {
                $result[intval($this->request['delivery_id'])]['CHECKED'] = 'Y';
            } else {
                reset($result);
                $firstKey = key($result);

                $result[$firstKey]['CHECKED'] = 'Y';
            }
        }

        return $result;
    }

    public function getPaySystems()
    {
        $payment = Payment::create($this->order->getPaymentCollection());
        $payment->setField('SUM', $this->order->getPrice());
        $payment->setField("CURRENCY", $this->order->getCurrency());
        $result = PaySystem\Manager::getListWithRestrictions($payment);

        if (isset($this->request['payment_id']) && array_key_exists(intval($this->request['payment_id']), $result)) {
            $result[intval($this->request['payment_id'])]['CHECKED'] = 'Y';
        } else {
            reset($result);
            $firstKey = key($result);

            $result[$firstKey]['CHECKED'] = 'Y';
        }

        return $result;
    }

    public function getOrderProperties()
    {
        foreach ($this->order->getPropertyCollection() as $prop) {
            /** @var \Bitrix\Sale\PropertyValue $prop */

            if ($prop->getField('CODE') != 'LOCATION') {
                $result[$prop->getField('CODE')] = [
                    'ID' => $prop->getField('ORDER_PROPS_ID'),
                    'CODE' => $prop->getField('CODE'),
                    'NAME' => $prop->getField('NAME'),
                ];
            }
        }

        return $result;
    }

    private function setOrderProps()
    {
        foreach ($this->order->getPropertyCollection() as $prop) {
            /** @var \Bitrix\Sale\PropertyValue $prop */
            foreach ($this->request as $key => $val) {
                if (strtolower($key) == strtolower($prop->getField('CODE'))) {
                    $prop->setValue($val);
                }
            }
        }
    }

    private function createVirtualOrder()
    {
        global $USER;

        // если нет товаров в корзине, уходим со страницы оформления
        if ($this->getAmountOfItems() == 0) {
            LocalRedirect($this->arParams['PATH_TO_BASKET']);
        }

        // создаем объект заказа
        $this->order = \Bitrix\Sale\Order::create($this->siteId, $USER->GetID());

        // установка типа плательщика, добавление товаров в корзину, заполнение свойств заказа
        $this->order->setPersonTypeId($this->arParams['PERSON_TYPE_ID']);
        $this->order->setBasket($this->getBasketItems());
        $this->setOrderProps();

        // создаем отгрузку, если указана id доставки, связываем ее со службой доставки, кладем все товары в данную отгрузку
        /* @var $shipmentCollection \Bitrix\Sale\ShipmentCollection */
        $shipmentCollection = $this->order->getShipmentCollection();

        if (intval($this->request['delivery_id']) > 0) {
            $shipment = $shipmentCollection->createItem(
                Bitrix\Sale\Delivery\Services\Manager::getObjectById(
                    intval($this->request['delivery_id'])
                )
            );
        } else {
            $shipment = $shipmentCollection->createItem();
        }

        /** @var $shipmentItemCollection \Bitrix\Sale\ShipmentItemCollection */
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        $shipment->setField('CURRENCY', $this->order->getCurrency());

        foreach ($this->order->getBasket()->getOrderableItems() as $item) {
            /**
             * @var $item \Bitrix\Sale\BasketItem
             * @var $shipmentItem \Bitrix\Sale\ShipmentItem
             * @var $item \Bitrix\Sale\BasketItem
             */
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }

        // добавляем платежную систему
        if (intval($this->request['payment_id']) > 0) {
            $paymentCollection = $this->order->getPaymentCollection();
            $payment = $paymentCollection->createItem(
                Bitrix\Sale\PaySystem\Manager::getObjectById(
                    intval($this->request['payment_id'])
                )
            );
            $payment->setField("SUM", $this->order->getPrice());
            $payment->setField("CURRENCY", $this->order->getCurrency());
        }
    }

    public function executeComponent()
    {
        global $APPLICATION;

        if ($this->arParams['IS_AJAX']) {
            $APPLICATION->RestartBuffer();
        }

        $this->createVirtualOrder();

        // choose action
        if (!empty($this->arParams['ACTION'])) {
            if (is_callable([$this, $this->arParams['ACTION'] . "Action"])) {
                call_user_func([$this, $this->arParams['ACTION'] . "Action"]);
            }
        }

        if ($this->arParams['IS_AJAX']) {
            if ($this->getTemplateName() != '') {
                ob_start();
                $this->includeComponentTemplate();
                $this->arResponse['html'] = ob_get_contents();
                ob_end_clean();
            }

            header('Content-Type: application/json');
            echo json_encode($this->arResponse);
            $APPLICATION->FinalActions();
            die();
        } else {
            $this->includeComponentTemplate();
        }
    }

    public function saveAction()
    {
        $this->order->save();
        $orderID = $this->order->getId();

        LocalRedirect($this->arParams['PATH_TO_REGISTRATION'] . '?ORDER_ID=' . $orderID);
    }

    public function calcAction()
    {
        $this->setTemplateName('');

        // собираем ID доставок
        $deliveryIDs = [];

        if (isset($this->request['delivery_id'])) {
            if (is_array($this->request['delivery_id'])) {
                foreach ($this->request['delivery_id'] as $val) {
                    if (intval($val) > 0) {
                        $deliveryIDs[intval($val)] = intval($val);
                    }
                }
            } elseif (intval($this->request['delivery_id']) > 0) {
                $deliveryIDs = [intval($this->request['delivery_id'])];
            } else {
                $deliveryIDs = [];
            }
        }

        // на выходе в любом случае будет массив
        sort($deliveryIDs);

        if (empty($deliveryIDs)) {
            throw new \Exception('Нет доставок для расчета');
        }

        $shipment = false;

        /** @var \Bitrix\Sale\Shipment $shipmentItem */
        foreach ($this->order->getShipmentCollection() as $shipmentItem) {
            if (!$shipmentItem->isSystem()) {
                $shipment = $shipmentItem;
                break;
            }
        }

        if (!$shipment) {
            throw new \Exception('Отгрузка не найдена');
        }

        // массив с доставками
        $availableDeliveries = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList(
            $shipment
        );

        foreach ($deliveryIDs as $deliveryId) {
            $obDelivery = false;

            if (isset($availableDeliveries[$deliveryId])) {
                $obDelivery = $availableDeliveries[$deliveryId];
            }
            
            if ($obDelivery) {
                $arDelivery = [
                    'id'                => $obDelivery->getId(),
                    'name'              => $obDelivery->getName(),
                    'logo_path'         => $obDelivery->getLogotipPath(),
                    'show'              => false,
                    'calculated'        => false,
                    'period'            => '',
                    'price'             => 0,
                    'price_formated'    => '',
                ];

                $shipment->setField('DELIVERY_ID', $obDelivery->getId());
                $calcResult = $obDelivery->calculate($shipment);

                if ($calcResult->isSuccess()) {
                    $arDelivery['calculated'] = true;
                    $arDelivery["price"] = $calcResult->getPrice();
                    $arDelivery["price_formated"] = \SaleFormatCurrency(
                        $calcResult->getPrice(),
                        $this->order->getCurrency()
                    );

                    if (strlen($calcResult->getPeriodDescription()) > 0) {
                        $arDelivery["period_text"] = $calcResult->getPeriodDescription();
                    }
                }

                if (floatval($arDelivery['price']) > 0) {
                    $arDelivery['show'] = true;
                }

                if (empty($arDelivery["period_text"])) {
                    $arDelivery["period_text"] = '';
                }

                $this->arResponse['deliveries'][$arDelivery['id']] = $arDelivery;
            } else {
                // в аякс ответе, даже недоступную доставку возвращаем
                $this->arResponse['deliveries'][$deliveryId] = [
                    'id'   => $deliveryId,
                    'show' => false
                ];
            }
        }
    }
}