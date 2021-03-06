<?
/**
 * Copyright (c) 13/11/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

$module_id = "kit.yadost";
//CModule::IncludeModule($module_id);
$moduleMode = CModule::IncludeModuleEx($module_id);

if ($moduleMode == 3)
{
	echo json_encode(array(
		'success' => false,
		'data' => array(
			'message' => "Can't include module. Check demo mode is expired."
		)
	));
	
	die();
}

if ($moduleMode == 0)
	die();

CKITYadostHelper::checkLogParams($_GET);

if (!check_bitrix_sessid())
	die();

// if (!$GLOBALS["USER"]->IsAdmin())
	// die("Access denied!");
if (!CKITYadostHelper::isAdmin("R"))
	die("Access denied");

try{
	if($_REQUEST['action'] == "propFixer")
		$data = CKITYadostProps::Start($_REQUEST);
	else
	{
		// классы модуля с доступными методами ajax-обработчиками
		$moduleClasses = array(
			'CKITYadostHelper' => array(
				"clearNoticeFile", // очистка файла с флагом изменения заказа
				"deleteOrderFromChange", // убирает признак изменения заказа
				"clearCache", // очистка кеша
			),
			'CKITYadostDriver' => array(
				"saveFormData", // сохранение данных формы
				"sendOrder", // отправка заказа
				"getOrderDocuments", // получение документов и ярлыков
				"getOrderDocs", // получение документов заказа
				"getOrderLabels", // получение ярлыков
				"getOrderStatus", // получение статуса заказа
				"sendOrderDraft", // отправка черновика, пока не используется отдельно, вызов в sendOrder
				"getOrderInfo", // получение данных заказа
				"getWarehouseInfo", // получение данных магазина
				"getSenderInfo", // получение данных отправителя
				"confirmOrder", // добавление заказа в отгрузку, пока не исп отдельно, вызов в sendOrder
				// "createDeliveryOrder", // создание забора или самопривоза, пока не включен в функционал
				// "confirmParcel", // подтверждение отгрузок, пока не включен в функционал
				"getFormIntervalWarehouse", // получение интервалов и складов на ФОЗ(форма отпр заявки)
				"getInterval", // получение интервалов на ФОЗ
				"getDeliveries", // получение доступных вариантов доставки и складов сортировки
				"cancelOrder", // отмена заказа
				"setConfig", // сохранение конфига
			),
			'CKITYadost' => array(
				"calculateOrder" // пересчет стоимости заказа на ФОЗ
			)
		);
		
		$methodFind = false;
		// foreach ($moduleClasses as $class)
			// if (!$methodFind)
				// if(method_exists($class, $_REQUEST['action']))
				// {
					// $methodFind = true;
					// $data = call_user_func($class."::".$_REQUEST['action'], $methodData = &$_REQUEST);
				// }
		foreach ($moduleClasses as $class => $methods)
			if (!$methodFind)
				if(in_array($_REQUEST['action'], $methods) && method_exists($class, $_REQUEST['action']))
				{
					$methodFind = true;
					$data = call_user_func($class."::".$_REQUEST['action'], $methodData = &$_REQUEST);
				}
		
		if (!$methodFind)
			CKITYadostHelper::throwException("Unknown action!", array(
				"request" => $_REQUEST
			));
	}
	
	$success = true;
}
catch (Exception $e)
{
	CKITYadostHelper::errorLog(CKITYadostDriver::$debug);
	$success = false;
	$data = CKITYadostHelper::convertFromUTF(CKITYadostHelper::$exceptionData);
}

CKITYadostHelper::errorLog(CKITYadostDriver::$debug);

if ("N" != $_POST["echoAJAX"])
{
	$GLOBALS["APPLICATION"]->RestartBuffer();
	echo json_encode(array("success" => $success, "data" => CKITYadostHelper::convertToUTF($data)));
	die();
}
?>