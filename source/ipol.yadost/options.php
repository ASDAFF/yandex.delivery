<?
/**
 * Copyright (c) 25/10/2019 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */
?>
<?
IncludeModuleLangFile(__FILE__);

CJSCore::Init(array("jquery"));
CModule::includeModule('sale');

$module_id = "ipol.yadost";
global $APPLICATION;

$curlFind = true;
if (!function_exists('curl_init'))
{
    $curlFind = false;
	echo "<br>" . GetMessage("IPOLyadost_NOCURL_TEXT") . "<br><br>";
}

if ($curlFind)
{
	if (CModule::includeModule($module_id))
	{
		$unique_num = COption::GetOptionString($module_id, "unique_num");
		if (empty($unique_num))
		{
			$unique_num = md5($_SERVER["HTTP_HOST"] . time());
			COption::SetOptionString($module_id, "unique_num", $unique_num);
		}
		
		CAgent::AddAgent("CIPOLYadostDriver::agentOrderStates();", $module_id, "N", 1800);//обновление статусов заказов
		
		$converted = CIPOLYadostHelper::isConverted();
		
		$arCityFrom = CIPOLYadostHelper::getCityFromNames();
		
		$siteIds = CIPOLYadostHelper::getSiteIds();
		
		$selectSites = CIPOLYadostHelper::selectSite();
		
		CIPOLYadostDriver::getConfigFileName();
		$configFilePath = CIPOLYadostDriver::$configFileName;
		if (file_exists($configFilePath))
		{
			CIPOLYadostDriver::getRequestConfig();
			$arRequestConfig = CIPOLYadostDriver::$requestConfig;
		}
		
		$arAllOptions = array(
			"msOptions" => Array(//Настройки обмена
				Array("basketWidget", GetMessage("IPOLyadost_OPT_basketWidget"), '', Array("textarea")),
				Array("cityFrom", GetMessage("IPOLyadost_OPT_cityFrom"), "MOSCOW", Array("selectbox", $arCityFrom)),
				Array("to_yd_warehouse", GetMessage("IPOLyadost_OPT_to_yd_warehouse"), '', Array("checkbox")),
				Array("delivery_type_import_widthdraw", GetMessage("IPOLyadost_OPT_delivery_type_import_widthdraw"), 'import',
					Array("selectbox", array(
						"import" => GetMessage("IPOLyadost_INPUTS_delivery_type_import"),
						"withdraw" => GetMessage("IPOLyadost_INPUTS_delivery_type_withdraw")
					))
				),
				Array("site_selection", GetMessage("IPOLyadost_OPT_site_selection"), '', Array("multiselectbox", $siteIds))
			),
			"defaultSenderOptions" => array(// значения из конфига по умолчанию
				Array("defaultSender", GetMessage("IPOLyadost_OPT_default_sender"), '0', Array("text")),
				Array("defaultWarehouse", GetMessage("IPOLyadost_OPT_default_warehouse"), '0', Array("text")),
				Array("assessedCostPercent", GetMessage("IPOLyadost_OPT_assessedCostPercent"), '100', Array("text")),
			),
			"dimensions" => Array(//Габариты товаров
				Array("sideMode", GetMessage("IPOLyadost_OPT_sideMode"), 'sep', Array("text")),//одним свойством / раздельными свойствами (unit / sep)
				Array("sidesUnit", GetMessage("IPOLyadost_OPT_sides"), 'DIMESIONS', Array("text")),//стороны одним
				Array("sidesUnitSprtr", GetMessage("IPOLyadost_OPT_sidesUnitSprtr"), 'x', Array("text")),//символ-разделитель в одной стороне
				Array("sidesSep", GetMessage("IPOLyadost_OPT_sides"), 'a:3:{s:1:"L";s:6:"LENGTH";s:1:"W";s:5:"WIDTH";s:1:"H";s:6:"HEIGHT";}', Array("text")),//стороны раздельными
				Array("weightPr", GetMessage("IPOLyadost_OPT_weight"), 'CATALOG_WEIGHT', Array("text")),//откуда брать вес
				Array("sidesMeas", GetMessage("IPOLyadost_OPT_sidesMeas"), 'mm', Array("text")),//единицы измерения размеров
				Array("weightMeas", GetMessage("IPOLyadost_OPT_sidesMeas"), 'g', Array("text")),//единицы измерения веса
			),
			"sidesDefaut" => Array(//Габариты товаров (дефолтные)
				Array("lengthD", GetMessage("IPOLyadost_OPT_lengthD"), '400', Array("text")),
				Array("widthD", GetMessage("IPOLyadost_OPT_widthD"), '300', Array("text")),
				Array("heightD", GetMessage("IPOLyadost_OPT_heightD"), '200', Array("text")),
				
				// Array("assessedCost", GetMessage("IPOLyadost_OPT_assessedCost"), '0', Array("text")),
			),
			"weightDefault" => Array(//Габариты товаров (дефолтный вес)
				Array("weightD", GetMessage("IPOLyadost_OPT_weightD"), '1', Array("text")),
			),
			"propsDefault" => Array(//Свойства товаров (артикул и т.д.)
				Array("artnumber", GetMessage("IPOLyadost_OPT_artnumber"), 'ARTNUMBER', Array("text")),
			),
			"orderProps" => Array(//свойства заказа откуда брать
				Array("fname", GetMessage("IPOLyadost_OPT_fname"), 'FIO', Array("text")),
				Array("lname", GetMessage("IPOLyadost_OPT_lname"), 'FIO', Array("text")),
				Array("mname", GetMessage("IPOLyadost_OPT_mname"), 'FIO', Array("text")),
				Array("email", GetMessage("IPOLyadost_OPT_email"), 'EMAIL', Array("text")),
				Array("phone", GetMessage("IPOLyadost_OPT_phone"), 'PHONE', Array("text")),
				Array("index", GetMessage("IPOLyadost_OPT_index"), 'ZIP', Array("text")),
				Array("addressMode", GetMessage("IPOLyadost_OPT_addressMode"), 'one', Array("text")),
				Array("address", GetMessage("IPOLyadost_OPT_address"), 'ADDRESS', Array("text")),
				Array("street", GetMessage("IPOLyadost_OPT_street"), 'STREET', Array("text")),
				Array("house", GetMessage("IPOLyadost_OPT_house"), 'HOUSE', Array("text")),
				Array("build", GetMessage("IPOLyadost_OPT_build"), 'BUILD', Array("text")),
				Array("flat", GetMessage("IPOLyadost_OPT_flat"), 'FLAT', Array("text")),
			),
			"templateOptions" => array(
				Array("oldTemplate", GetMessage("IPOLyadost_OPT_oldTemplate"), 'Y', Array("checkbox")),
			),
			"delivSigns" => Array(//Службы доставки до двери подписи
				Array("showWidgetOnProfile", GetMessage("IPOLyadost_OPT_showWidgetOnProfile"), 'N', array('checkbox')),//открывать виджет при клике на профиль доставки
				// Array("idOfPVZ",GetMessage("IPOLyadost_OPT_idOfPVZ"),'',array('text')),//id элемента, куда выводить пвз
			),
			/*"courier" => array(// настройки курьера магазина, доставляющего заказ
				Array("courier_name", GetMessage("delivery_OPT_courier_name"), '', Array("text")),
				Array("car_number", GetMessage("delivery_OPT_car_number"), 'XX100X199', Array("text")),
				Array("car_model", GetMessage("delivery_OPT_car_model"), 'Ford', Array("text")),
			),*/
			"service" => array(
				Array("active", "active", 'N', Array('text')),
				Array("unique_num", "unique_num", false, Array('text')),
			)
		);
		
		// статусы заказов. выбираем статусы типа "Заказ"
		$dbStatus = CSaleStatus::GetList(
			array(),
			array("LID" => LANGUAGE_ID, "TYPE" => "O"),
            false,
            false,
            array('ID', 'SORT', 'TYPE', 'NOTIFY', 'LID', 'COLOR' ,'NAME', 'DESCRIPTION')
		);
		
		$arStatuses = array();
		$arStatuses[] = "";
		while ($arStatus = $dbStatus->Fetch())
		{
			$arStatuses[$arStatus["ID"]] = $arStatus["NAME"];
		}
		
		$arDeliveryStatus = CIPOLYadostHelper::getDeliveryStatuses();
		
		foreach ($arDeliveryStatus as $statusID => $statusName)
			$arAllOptions["arStatus"][] = Array($statusID, $statusName, '', Array("selectbox", $arStatuses));
		// конец статусов заказа
		
		$sideMode = COption::GetOptionString($module_id, 'sideMode', 'sep');
		if ($_REQUEST["sideMode"])
		{
			$arAllowSideMode = array(
				"unit",
				"sep",
				"def",
			);
			if (in_array($_REQUEST["sideMode"], $arAllowSideMode))
				$sideMode = $_REQUEST["sideMode"];
		}
		
		$addressMode = COption::GetOptionString($module_id, 'addressMode', 'one');
		if ($_REQUEST["addressMode"])
		{
			$arAllowAddressMode = array(
				"one",
				"sep"
			);
			if (in_array($_REQUEST["addressMode"], $arAllowAddressMode))
				$addressMode = $_REQUEST["addressMode"];
		}
		
		$_REQUEST['sidesSep'] = serialize(array('L' => $_POST['IPOLyadost_MEA_SEP_L'], 'W' => $_POST['IPOLyadost_MEA_SEP_W'], 'H' => $_POST['IPOLyadost_MEA_SEP_H']));
		
		$sides = unserialize(COption::GetOptionString($module_id, 'sidesSep', 'a:3:{s:1:"L";s:6:"LENGTH";s:1:"W";s:5:"WIDTH";s:1:"H";s:6:"HEIGHT";}'));
		if (
			isset($_POST['IPOLyadost_MEA_SEP_L']) &&
			isset($_POST['IPOLyadost_MEA_SEP_W']) &&
			isset($_POST['IPOLyadost_MEA_SEP_H'])
		)
			$sides = unserialize($_REQUEST['sidesSep']);
		
		$IPOLyadost_weight = COption::getOptionString($module_id, 'weightPr', 'CATALOG_WEIGHT');
		if ($_REQUEST["weiMode"] == "cat")
			$IPOLyadost_weight = $_REQUEST["weightPr"] = "CATALOG_WEIGHT";
        elseif ($_REQUEST["weightPr"])
			$IPOLyadost_weight = $_REQUEST["weightPr"];
		
		$aTabs = array(
			array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
			array("DIV" => "edit2", "TAB" => GetMessage("IPOLyadost_FAQ_TAB_SETUP"), "TITLE" => GetMessage("IPOLyadost_FAQ_TAB_SETUP")),
			array("DIV" => "edit3", "TAB" => GetMessage("IPOLyadost_FAQ_TAB_RIGHTS"), "TITLE" => GetMessage("IPOLyadost_FAQ_TAB_RIGHTS"))
		);
		
		$tabControl = new CAdminTabControl("tabControl", $aTabs);
		
		/** @var $REQUEST_METHOD string */
		/** @var $Update string */
		/** @var $Apply string */
		/** @var $RestoreDefaults string */
		if ($REQUEST_METHOD == "POST" && strlen($Update . $Apply . $RestoreDefaults) > 0 && check_bitrix_sessid() && CIPOLYadostHelper::isAdmin())
		{
			if (strlen($RestoreDefaults) > 0)
				COption::RemoveOption("ipol.yadost");
			else
			{
				foreach ($arAllOptions as $aOptGroup)
					foreach ($aOptGroup as $option)
						__AdmSettingsSaveOption($module_id, $option);
				
				//тут как-то все по-мски сделано, но лучше я не вижу пути
				$arSetups = array();
				foreach ($arAllOptions as $arOpts)
					foreach ($arOpts as $option)
					{
						$option = $option[0];
						if ($option == 'sidesSep' || $option == 'payers' || $option == 'paySysDeps')
							$arSetups[$option] = unserialize(COption::GetOptionString($module_id, $option, 'a:0:{}'));
						else
							$arSetups[$option] = COption::GetOptionString($module_id, $option, false);
					}
				
				CIPOLYadostDriver::sendStatistic(array("type" => "settings"));
			}
			if ($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == "")
				echo '<script type="text/javascript">window.location="' . CUtil::addslashes($_REQUEST["back_url_settings"]) . '";</script>';
		}
		
		
		function ShowParamsHTMLByArray($arParams)
		{
			foreach ($arParams as $Option)
				__AdmSettingsDrawRow($GLOBALS['module_id'], $Option);
		}
		
		?>
        <style>
            .PropHint {
                background: url("/bitrix/images/<?=$module_id?>/hint.gif") no-repeat transparent;
                display: inline-block;
                height: 12px;
                position: relative;
                width: 12px;
            }

            .b-popup {
                background-color: #FEFEFE;
                border: 1px solid #9A9B9B;
                box-shadow: 0px 0px 10px #B9B9B9;
                display: none;
                font-size: 12px;
                padding: 19px 13px 15px;
                position: absolute;
                top: 38px;
                width: 300px;
                z-index: 12;
            }

            .b-popup .pop-text {
                margin-bottom: 10px;
                color: #000;
            }

            .pop-text i {
                color: #AC12B1;
            }

            .moduleInst i {
                color: #AC12B1;
            }

            .b-popup .close {
                background: url("/bitrix/images/<?=$module_id?>/popup_close.gif") no-repeat transparent;
                cursor: pointer;
                height: 10px;
                position: absolute;
                right: 4px;
                top: 4px;
                width: 10px;
            }

            .moduleHeader {
                font-size: 16px;
                cursor: pointer;
                display: block;
                color: #2E569C;
                background-image: url("/bitrix/images/<?=$module_id?>/icon_info.gif");
                padding-left: 20px;
                background-repeat: no-repeat;
            }

            .moduleInst {
                /*display:none; */
                margin-left: 10px;
                margin-top: 10px;
            }

            textarea[name="basketWidget"] {
                width: 300px;
                height: 100px;
            }

            /
            /
            .measHide {
                display: none;
            }

            select {
                max-width: 400px;
            }

            table.assessed_limits td {
                white-space: nowrap;
                border: 1px solid black;
            }
        </style>

        <form method="post"
              action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialchars($mid) ?>&amp;lang=<? echo LANG ?>">
			<?
			$tabControl->Begin();
			
			$tabControl->BeginNextTab();
			include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $module_id . "/optionsInclude/setups.php");
			
			$tabControl->BeginNextTab();
			include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $module_id . "/optionsInclude/faq.php");
			
			$tabControl->BeginNextTab();
			require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");
			
			$tabControl->Buttons();
			?>
            <div id="ipol_buttons_block" align="left" style='position: relative;'>
				<? if (!CIPOLYadostHelper::isAdmin())
				{
					echo GetMessage("IPOLyadost_HELPER_RightsNotAllow_SaveOption");
				} ?>
                <input type="hidden" name="Update" value="Y">
                <input type="submit" <? if (!CIPOLYadostHelper::isAdmin())
					echo " disabled "; ?> name="Update" value="<? echo GetMessage("MAIN_SAVE") ?>">
            </div>
			
			<? $tabControl->End(); ?>
			<?= bitrix_sessid_post(); ?>
        </form>


        <div id="pop-assessed_limits" class="b-popup" style="display: none; width: 420px;">
            <div class="pop-text">
				<?= GetMessage("IPOLyadost_HELPER_assessed_limits") ?>
				<? echo $limitsHtml; ?>
            </div>
            <div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
        </div>
	
	<? }
	else
	{
		echo GetMessage("IPOLyadost_DEMO_MODE_EXPIRED");
	}
}?>
