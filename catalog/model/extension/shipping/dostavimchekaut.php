<?php
header('Content-Type: text/html; charset=utf-8');
	class AreaCalcLenghtWidthHeight  {
		function __construct($layer, $countLayer, $countHeight, $countWidth, $countLenght) {
			$this->layer  = $layer;
			$this->countLayer = $countLayer;
			$this->height = $countHeight;
			$this->width  = $countWidth;
			$this->lenght = $countLenght;
		}
   }



	class CalcLenghtWidthHeight {

		function __construct($lenght, $width, $height) {
			$this->width  = $width;
			$this->height = $height;
			$this->lenght = $lenght;
		}

		function sumWidthHeight() {
			return $this->width*$this->height;
		}
		function sumWidthLenght() {
			return $this->width*$this->lenght;
		}
		function sumLenghtHeight() {
			return $this->lenght*$this->height;
		}
		function funcMinHeight() {
			//далее находим высоту наименьшую
			return min($this->width, $this->height, $this->lenght);
		}

		function funcMaxWidthAnd() {
			//далее находим ширину наименьшую
			$min1 =  min($this->width, $this->height);
			$min2 =  min($this->width, $this->lenght);
			$min3 =  min($this->height, $this->lenght);
			return max($min1, $min2, $min3);

		}

		function funcMaxLenghtAnd() {
			//далее находим длину наименьшую
			return max($this->width, $this->height, $this->lenght);
		}

		function leastValue() {
			$max1 = max($this->sumWidthHeight(), $this->sumWidthLenght()); // находим наибольшее значение сумм высоты,ширины и длины в разных комбинациях
			$max2 = max($this->sumWidthHeight(), $this->sumLenghtHeight()); //находим наибольшее значение сумм высоты,ширины и длины в разных комбинациях
			$max3 = max($this->sumWidthLenght(), $this->sumLenghtHeight()); //находим наибольшее значение сумм высоты,ширины и длины в разных комбинациях
			$maxmin1 = min($max1, $max2, $max3); // находим наименьшее значение сумм высоты,ширины и длины в разных комбинациях
			$maxmax2 = max($max1, $max2, $max3); // находим наибольшее значение сумм высоты,ширины и длины в разных комбинациях
			return $maxmax2;//arr.filter(e => e != min);
		}


	}

class ModelExtensionShippingDostavimchekaut extends Model {
//////////////////////////////////////////////////////////////////////////здесь начало функций калькулятора упаковки товара

function sign( $number ) {
    return ( $number > 0 ) ? 1 : ( ( $number < 0 ) ? -1 : 0 );
}
	//function count($obj) {
    //      $count = 0;
	//
	//	  foreach ($obj as $prs ) {
    //               $count++;
    //      }
    //      return $count;
	//}

	function recursionArea($product,$area,$arrayCount) {




	  $maxObject = []; // пока пустой



	  for ($i = 0; $i < $arrayCount; $i++) {
		if (isset($product[$i])) {
			$min = $product[$i]->leastValue();
			$max = $min;
		}
	  }


	  for ($i = 0; $i < $arrayCount; $i++) {
	   if (isset($product[$i])) {
		if ($product[$i]->leastValue() >= $max) {$max = $product[$i]->leastValue(); $maxObject = $i; };
	   }
		//if (product[i].leastValue() < min) min = product[i].leastValue(); //может пригодиться
	  }






	  //var area = new AreaCalcLenghtWidthHeight(max,0);

	  $areaLast = $area->layer;
	  $height = 0;


	  //снова ищем товар с максимальной площадью и минимальной высотой
	  for ($i = 0; $i < $arrayCount; $i++) {

		if (isset($product[$i])) {  //здесь высоту вычисляем
			if (($this->sign($areaLast - $product[$i]->leastValue()) == 1) || ($this->sign($areaLast - $product[$i]->leastValue()) == 0))  {  //может юыть ошибка перепроверить

				$areaLast = $areaLast - $product[$i]->leastValue();
					if ($product[$i]->funcMinHeight() > $height) { $height = $product[$i]->funcMinHeight();}
				unset($product[$i]); //если продукт добавлен до удаляем его объект
			}
			else
			{}//нет функционала
		}
	 }

	 unset($product[$maxObject]);

	 $area->height = $area->height+$height;
	 $area->countLayer = $area->countLayer+1;

		if (count($product) == 0)
		{
			return;
		}
		else
			$this->recursionArea($product,$area, $arrayCount);

	}

  function goCalcWHL($objFromOpencart) {
	//CalcLenghtWidthHeight

	$product = []; // пока пустой
	$maxObject = 0; // пока пустой

	for($i=0;$i< count($objFromOpencart);$i++){
		$product[$i] = new CalcLenghtWidthHeight($objFromOpencart[$i]["Lenght"],$objFromOpencart[$i]["Width"],$objFromOpencart[$i]["Height"]);
	}


	for ($i = 0; $i < count($product); $i++) {
		$min = $product[0]->leastValue();
		$max = $min;
	}

	for ($i = 0; $i < count($product); $i++) {
		if ($product[$i]->leastValue() > $max) {$max = $product[$i]->leastValue();$maxObject = $i; } else {$maxObject = 0; };  //потестить
	}

	 //console.log(maxObject);

	$area = new AreaCalcLenghtWidthHeight($max,0,0,0,0);
	$area->width = $product[$maxObject]->funcMaxWidthAnd();
	$area->lenght = $product[$maxObject]->funcMaxLenghtAnd();

	$count = count($product);

    $this->recursionArea($product, $area, $count);
    return 	$area;
  }
//////////////////////////////////////////////////////////////////////////здесь конец функций калькулятора упаковки товара

//здесь мы получаем guid города и guid региона
//function GetGuidCityAndRegion($partName, $regionName, $fillRegion)
function GetGuidCityAndRegion($key, $city, $region, $fillRegion)
	{
		$url = "https://api.dostav.im/Address/CityByRegion/?";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		//CURLOPT_URL => $url."partName=".urlencode($partName)."&regionName=".urlencode($regionName)."&fillRegion=".urlencode($fillRegion),
		CURLOPT_URL => $url."city=".urlencode($city)."&region=".urlencode($region)."&fillRegion=".urlencode($fillRegion),
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
		    "authorization: ".$key
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "Error 31425#:" . $err;
		} else {
			return $response;
		}

    }
//здесь мы получаем список СД конец


//здесь мы получаем список СД начало
    function GetListProvider()
    {
		$url = "https://api.dostav.im/DeliveryService/dslist";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "Error 31425#:" . $err;
		} else {
			return $response;
		}

    }
//здесь мы получаем список СД конец

//здесь мы получаем название описание и  СД цену   начало

	function GetNameAndPriceAndDescription($key,$townGuidTo,$orderCost,$deliveryService,$length,$width,$height,$weight, $UseOrderCostAsCodAndAssessed)
	{
		$url = "https://api.dostav.im/StoreWidget/DeliveryMethodsFront?";
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url."townGuidTo=".$townGuidTo."&orderCost=".$orderCost."&deliveryService=".$deliveryService."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight."&UseOrderCostAsCodAndAssessed=".$UseOrderCostAsCodAndAssessed,
		  CURLOPT_SSL_VERIFYHOST => false,
		  CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "authorization: ".$key
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return  "Error 213123#:" . $err;
		} else {
		  return  $response;
		}
	}

//здесь мы получаем название описание и  СД цену   конец

//здесь мы получаем все филиалы ПВЗ   начало
    function GetFilialsPVZ($key,$townGuidTo,$orderCost,$length,$width,$height,$weight)
    {
		$url = "https://api.dostav.im/StoreWidget/ServicesAndFilials?";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url."townGuid=".$townGuidTo."&orderCost=".$orderCost."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 30,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
		    "authorization: ".$key
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return  "Error 5747547#:" . $err;
		} else {
		  return  $response;
		}
    }

//здесь мы получаем все филиалы ПВЗ    конец
    function GenerateListPvz($arrayFillials, $listprovider)
    {

	$newData  =	$listprovider;
	//var copyNewData = Object.assign({}, newData); ниже аналог на пхп
	//$copyNewData = clone json_decode($newData);
	$pvzList = array();
	//$copyNewData = array();

	$pvzListPhp = array();
	$pvzListFinal = '';
	$newData = json_decode($newData);

    foreach ($newData as $k => $v) {  //тут побыстрее надо сделать
		$copyNewData[$k] = clone $v;
	}



	$arrayFillials = json_decode($arrayFillials);
	$pvzItem = [];
		            for ($i = 0; $i < count($arrayFillials); $i++){
                        for ($j = 0; $j < count($arrayFillials[$i]->deliveryFilials); $j++) {

                            $pvzCoordinates = [$arrayFillials[$i]->deliveryFilials[$j]->lat, $arrayFillials[$i]->deliveryFilials[$j]->lng];
							for ($k = 0; $k < count($newData); $k++){


								if(mb_strtoupper($arrayFillials[$i]->id) == mb_strtoupper($newData[$k]->key))
									{
												$pvzItem['serviceName'] = $newData[$k]->key;
												$pvzItem['serviceImg'] = "https://dostav.im/img/".$newData[$k]->logo.".png";
												//26.10.2018
												//delete $copyNewData[$k];
												unset($copyNewData[$k]);
												//26.10.2018
									}
							}

                           //	print_r($pvzItem);

                            $pvzItem['serviceId'] = $arrayFillials[$i]->id;
                            $pvzItem['hintContent'] = $arrayFillials[$i]->deliveryFilials[$j]->name;
							$razbivka = explode(":", $arrayFillials[$i]->deliveryFilials[$j]->address);
                            $contentForBalloon = '<img src="'. $pvzItem['serviceImg'] .'" style="width: 40px; margin-right: 10px;" pvzId="' . $arrayFillials[$i]->deliveryFilials[$j]->apiShipId . '">' . $razbivka[1];   //непонятка  доделать
							$pvzItem['balloonContent'] = $contentForBalloon;
                            $pvzItem['balloonContentHeader']= $pvzItem['serviceName'];
                            //$pvzItem['balloonContentBody'] = '<a href="#" pvzguid="'.$arrayFillials[$i]->deliveryFilials[$j]->addressGuid.'" pvzcode="'.$arrayFillials[$i]->deliveryFilials[$j]->id.'" serviceid="'.$pvzItem['serviceName'].'" class="pvz-cluster-balloon" >' .$contentForBalloon. '</a>';	//10.12.2018
                            $pvzItem['balloonContentBody'] = '<a href="#" pvzguid="" pvzcode="'.$arrayFillials[$i]->deliveryFilials[$j]->id.'" serviceid="'.$pvzItem['serviceName'].'" class="pvz-cluster-balloon" >' .$contentForBalloon. '</a>';
                            $pvzItem['type'] = $arrayFillials[$i]->deliveryFilials[$j]->type;
                            $pvzItem['id'] = $arrayFillials[$i]->deliveryFilials[$j]->apiShipId;
                            $pvzItem['code'] = $arrayFillials[$i]->deliveryFilials[$j]->id;
                            //$pvzItem['guid'] = $arrayFillials[$i]->deliveryFilials[$j]->addressGuid;	//10.12.2018
                            $pvzItem['guid'] = '';
                            $pvzItem['pvzId'] = $arrayFillials[$i]->deliveryFilials[$j]->id;

                            $pvzArrayItem = [$pvzCoordinates, $pvzItem];  //непонятка  доделать

						    $pvzList =  $pvzArrayItem;
                            //array_push($pvzList, $pvzArrayItem);


							////////////////////////////////////////////////////////

							foreach ($pvzList as $t => $arrDost) {
								if(isset($arrDost["type"])) {
									if ($arrDost["type"] == 0 || $arrDost["type"] == 1) {
										$pvzListPhp[$t] = '<li class="pvz-item" tabindex="-1" serviceId="' . $arrDost["serviceId"] . '" pvzId="' . $arrDost["id"] . '" pvzCode="' . $arrDost["code"] . '" pvzGuid="' . $arrDost["guid"] . '">' . $arrDost["balloonContent"] . '</li>';
								    }
								 }
							}
							if(isset($pvzListPhp[1])) {
								$pvzListFinal .=  $pvzListPhp[1];
							}
							////////////////////////////////////////////////////////

                        }
                    };
				//print_r($pvzListFinal);
			return $pvzListFinal;
	}

//здесь мы получаем все филиалы ПВЗ    конец
    function GenerateListPvzForYandex($arrayFillials, $listprovider)
    {

	$newData  =	$listprovider;
	$pvzList = array();
	$c=0;

	$pvzListPhp = array();
	$pvzListFinal = '';
	$newData = json_decode($newData);

    foreach ($newData as $k => $v) {  //тут побыстрее надо сделать  И ВООБЩЕ ЭТО УБРАТЬ НАДО
		$copyNewData[$k] = clone $v;
	}



	$arrayFillials = json_decode($arrayFillials);


		            for ($i = 0; $i < count($arrayFillials); $i++){
                        for ($j = 0; $j < count($arrayFillials[$i]->deliveryFilials); $j++) {
                                 $pvzItem = [];
                            $pvzCoordinates = [$arrayFillials[$i]->deliveryFilials[$j]->lat, $arrayFillials[$i]->deliveryFilials[$j]->lng];

							for ($k = 0; $k < count($newData); $k++){


								if(mb_strtoupper($arrayFillials[$i]->id) == mb_strtoupper($newData[$k]->key))
									{
												$pvzItem['serviceName'] = mb_strtolower($newData[$k]->key);
												$pvzItem['serviceImg'] = "https://dostav.im/img/".mb_strtolower($newData[$k]->logo).".png";
												//delete $copyNewData[$k];
												unset($copyNewData[$k]);
									}
							}

                            $pvzItem['serviceId'] = mb_strtolower($arrayFillials[$i]->id);
                            $pvzItem['hintContent'] = $arrayFillials[$i]->deliveryFilials[$j]->name;
							$razbivka = explode(":", $arrayFillials[$i]->deliveryFilials[$j]->address);
                            $contentForBalloon = '<img src=\''. $pvzItem['serviceImg'] .'\' style=\'width: 40px; margin-right: 10px;\' pvzId=\'' . $arrayFillials[$i]->deliveryFilials[$j]->apiShipId . '\'>' . $razbivka[1];   //непонятка  доделать
							$pvzItem['balloonContent'] = $contentForBalloon;
                            $pvzItem['balloonContentHeader ']= $pvzItem['serviceName'];
                            $pvzItem['balloonContentBody'] = '<a href=\'#\' pvzguid=\'\' pvzcode=\''.$arrayFillials[$i]->deliveryFilials[$j]->id.'\' serviceid=\''.$pvzItem['serviceName'].'\' class=\'pvz-cluster-balloon\' >' .$contentForBalloon. '</a>';
                            $pvzItem['type'] = $arrayFillials[$i]->deliveryFilials[$j]->type;
                            $pvzItem['id'] = $arrayFillials[$i]->deliveryFilials[$j]->apiShipId;
                            $pvzItem['code'] = $arrayFillials[$i]->deliveryFilials[$j]->id;
                            //$pvzItem['guid'] = $arrayFillials[$i]->deliveryFilials[$j]->addressGuid;	//10.12.2018
                            $pvzItem['guid'] = '';
                            $pvzItem['pvzId'] = $arrayFillials[$i]->deliveryFilials[$j]->id;

                            $pvzArrayItem = [$pvzCoordinates, $pvzItem];  //непонятка  доделать
							$pvzList[$c] = $pvzArrayItem;
							$c++;
                        }

                    };

			return $pvzList;
	}

	function GenerateCheckbox($listprovider)
    {   $bodyDostavim='';
		$data = json_decode($listprovider);

		$beginningDostavim  = 'jQuery("ul.ds-list").replaceWith(\'<ul class="ds-list">';
				for ($i = 0; $i < count($data); $i++){
					$bodyDostavim .= '<li class="dos'.$data[$i]->key.'" class="ds-item"><label><input id="'.$data[$i]->key.'" style="list-style-type:none" type="checkbox" name="checkbox-test" checked="true"><span class="checkbox-custom"></span><span>'.$data[$i]->name.'</span></label></li> ';
				//
				}
			$endingDostavim  = '</ul>\');';

			return $beginningDostavim.$bodyDostavim.$endingDostavim;
	}


	//рисуем точки самовывоза
    function initDostavim($coordinates, $zoomVal, $dslist, $pvzYandex){
	$dslist = json_decode($dslist);
	$pvzlist = $pvzYandex;

	$part1 = '';
	$part2 = '';
	$part3 = '';
	$part4 = '';
	$part5 = '';
	$part6 = '';
	$part7 = '';
	$part7_5 = '';


	$part1 = '
	    var iconLayoutVal = "default#image";
		var iconImageHrefVal = "img/pin.svg";
		var iconImageSizeVal = [60, 35];
		ymaps.ready(init); function init(coordinates, zoomVal){  if (typeof(myMap) !== "undefined") {
		myMap.destroy();
	}
	';



    $part2 = 'myMap = new ymaps.Map("mapdostavim", {
		center: '.$coordinates.', // Москва
		zoom: '.$zoomVal.',
        behaviors: ["default", "scrollZoom"]
    });


         clusterer = new ymaps.Clusterer({
         groupByCoordinates: false,
         clusterDisableClickZoom: false,
         clusterHideIconOnBalloonOpen: false,
         geoObjectHideIconOnBalloonOpen: false,
         clusterOpenBalloonOnClick: true,
         propagateEvents: true,
         gridSize: 100,
         clusterBalloonPanelMaxMapArea: 0,
		clusterBalloonMaxHeight: 150,
     });

     function checkState () {
         var shownObjects,
             byDeliveryService = new ymaps.GeoQueryResult();

      ';

			//for ($i = 0; $i < count($dslist); $i++){
			//	$part3 .= 'if (jQuery("#'.$dslist[$i]->key.'").prop("checked")) {
			//			byDeliveryService = result.search(\'properties.balloonContentHeader = "'.$dslist[$i]->key.'"\').add(byDeliveryService);
			//		}';
			//}
			for ($i = 0; $i < count($dslist); $i++){
				$part3 .= 'if (jQuery("#'.$dslist[$i]->key.'").prop("checked")) {
						byDeliveryService = result.search(\'properties.serviceId = "'.mb_strtolower($dslist[$i]->key).'"\').add(byDeliveryService);
					}';
			}


    $part4 = '
        clusterer.removeAll();

        shownObjects = byDeliveryService.addToMap(myMap);
        clusterer.add(shownObjects._objects);
        result.remove(shownObjects).removeFromMap(myMap);
        myMap.geoObjects.add(clusterer);

    }

    geoObjects = [];
    // Создание массива меток
	';


    for ($i = 0; $i < count($pvzlist); $i++) {
        $part5 .='geoObjects['.$i.'] = new ymaps.Placemark('.json_encode($pvzlist[$i][0]).','.json_encode($pvzlist[$i][1]).',{iconLayout: iconLayoutVal, iconImageHref: "'.$pvzlist[$i][1]['serviceImg'].'", iconImageSize: iconImageSizeVal});';
    }


    // Чекбоксы фильтра по службам доставки
	for ($i = 0; $i < count($dslist); $i++){
		$part6 .= 'jQuery("#'.$dslist[$i]->key.'").click(checkState);';
	}



    $part7 = ' var result = ymaps.geoQuery(geoObjects);
    clusterer.add(geoObjects);
    myMap.geoObjects.add(clusterer);

    // Создаем собственный макет с информацией о выбранном геообъекте.
    var customBalloonContentLayout = ymaps.templateLayoutFactory.createClass([
            "<ul class=list>",
            "</ul>"
        ].join(""), {
            build: function(){
                customBalloonContentLayout.superclass.build.call(this);
                jQuery(".pvz-cluster-balloon").bind("click", this.onBalloonClick);
            },
            clear: function(){
                jQuery(".pvz-cluster-balloon").unbind("click", this.onBalloonClick);
                customBalloonContentLayout.superclass.clear.call(this);
            },
            onBalloonClick: function() {
                alert("the balloon was clicked!");
            }
        }
        );

    var block = jQuery(".pvz-cluster-balloon");
    clusterer.events.add("click", function(e)
        {
            e.preventDefault();
            console.log("Кликнут кластер") ;
            return clusterer;
        });

    // Обработка клика по метке на карте
    myMap.geoObjects.events.add("click", function (e) {
        // Получение ссылки на дочерний объект, на котором произошло событие.
        var object = e.get("target");
        if (!object.properties._data.iconContent) {
            jQuery(".pvz-list-link").html(object.properties._data.balloonContent);
			jQuery(window.addressDostavim).val(object.properties._data.balloonContent.split(">")[1]+"::"+object.properties._data.serviceId+"::"+object.properties._data.id+"::3::");
			//jQuery(addressDostavim).val(jQuery(this).attr("serviceid")+": "+jQuery(this).text());
            jQuery(".address-filter").attr("pvzId", object.properties._data.id);
            jQuery(".address-filter").attr("serviceId", object.properties._data.serviceId);

		jQuery("#errorModalDostavim").html(object.properties._data.balloonContent);

					$("#exampleModal").animate({opacity: 0, top: "20%"}, 200, function(){
					$(this).css("display", "none");
					$("#overlayDostavim").fadeOut(400);
					});
        reloadDostavim();

	}}); }  window.onload = function() {init()};
      ';




$uniq_arr = array();

foreach ($pvzlist as $i => $item) {
    if (!in_array($item[1]["serviceName"], $uniq_arr)) {
        $uniq_arr[$i] = $item[1]["serviceName"];
        //print_r($uniq_arr);
    }
}

for ($i = 0; $i < count($dslist); $i++) {
	if (!in_array($dslist[$i]->key, $uniq_arr )) {
	$part7_5 .= 'jQuery("#'.$dslist[$i]->key.'").attr("disabled", "disabled");	jQuery(".dos'.$dslist[$i]->key.'").css("opacity", "0.4");';
	}
}

	  return $part1.$part2.$part3.$part4.$part5.$part6.$part7_5.$part7;
    }



//print_r($pvzYandex);
//$checkboxs  = GenerateCheckbox();
//print_r($checkboxs);
//////////////////////////////////////////////////////////////////////////////////////////////////новый функционал на PHP
   function createJsDsoatavim($contentDostavim){

	   $file = "get_url.js";

	   $fd = fopen($file,"w");

	   if(!$fd) {
		exit("Не возможно открыть файл");
	   }

	   if(!flock($fd,LOCK_EX)) {
		exit("Блокировка файла не удалась");
	   }

	   fwrite($fd,$contentDostavim."\n");

	   if(!flock($fd,LOCK_UN)) {
		exit("Не возможно разблокировать файл");
	   }
	   fclose($fd);

	   $path = substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],"/"));

	   return '<script type="text/javascript" src="/'.$file.'"></script>';
   }
   function createjsPvz($contentDostavim){

	   $file = "get_url_pvz.js";

	   $fd = fopen($file,"w");

	   if(!$fd) {
		exit("Не возможно открыть файл");
	   }

	   if(!flock($fd,LOCK_EX)) {
		exit("Блокировка файла не удалась");
	   }

	   fwrite($fd,$contentDostavim."\n");

	   if(!flock($fd,LOCK_UN)) {
		exit("Не возможно разблокировать файл");
	   }
	   fclose($fd);

	   $path = substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],"/"));

	   return '<script type="text/javascript" src="/'.$file.'"></script>';
   }


//здесь мы получаем список вес из ЛК
    function GetListParametrs($key)
	{
		$url = "https://api.dostav.im/StoreWidget/DeliveryMethodsBack/?";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
		    "authorization: ".$key
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "Error 31425#:" . $err;
		} else {
			return json_decode($response);
		}

    }
//здесь мы получаем список вес из ЛК

//асинхронно пытаемся получить два метода сразу  php конечно не асинхронный но что делать приходиться извращаться



	//function GetNameAndPriceAndDescriptionANDGetFilialsPVZ($key,$townGuidTo,$orderCost,$deliveryService,$length,$width,$height,$weight, $UseOrderCostAsCodAndAssessed)
	//{
	//
	//	// устанавливаем URL и другие соответствующие опции
	//	$url = "https://api.dostav.im/StoreWidget/ServicesAndFilials?";
	//	$curl = curl_init();
	//	curl_setopt_array($curl, array(
	//	CURLOPT_URL => $url."townGuid=".$townGuidTo."&orderCost=".$orderCost."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight,
	//	CURLOPT_SSL_VERIFYHOST => false,
	//	CURLOPT_SSL_VERIFYPEER => false,
	//	CURLOPT_RETURNTRANSFER => true,
	//	CURLOPT_ENCODING => "",
	//	CURLOPT_MAXREDIRS => 30,
	//	CURLOPT_TIMEOUT => 30,
	//	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	//	CURLOPT_CUSTOMREQUEST => "GET",
	//	CURLOPT_HTTPHEADER => array(
	//	    "authorization: ".$key
	//	  ),
	//	));
    //
	//	$url2 = "https://api.dostav.im/StoreWidget/DeliveryMethodsFront?";
	//	$cur2 = curl_init();
	//	curl_setopt_array($cur2, array(
	//	  CURLOPT_URL => $url2."townGuidTo=".$townGuidTo."&orderCost=".$orderCost."&deliveryService=".$deliveryService."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight."&UseOrderCostAsCodAndAssessed=".$UseOrderCostAsCodAndAssessed,
	//	  CURLOPT_SSL_VERIFYHOST => false,
	//	  CURLOPT_SSL_VERIFYPEER => false,
	//	  CURLOPT_RETURNTRANSFER => true,
	//	  CURLOPT_ENCODING => "",
	//	  CURLOPT_MAXREDIRS => 10,
	//	  CURLOPT_TIMEOUT => 30,
	//	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	//	  CURLOPT_CUSTOMREQUEST => "GET",
	//	  CURLOPT_HTTPHEADER => array(
	//	    "authorization: ".$key
	//	  ),
	//	));
    //
	//
	//	//создаем набор дескрипторов cURL
	//	$mh = curl_multi_init();
	//
	//	//добавляем два дескриптора
	//	curl_multi_add_handle($mh,$curl);
	//	curl_multi_add_handle($mh,$cur2);
	//
	//	$active = null;
	//	//запускаем дескрипторы
	//	do {
	//		$mrc = curl_multi_exec($mh, $active);
	//	} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	//
	//	while ($active && $mrc == CURLM_OK) {
	//		if (curl_multi_select($mh) != -1) {
	//			do {
	//				$mrc = curl_multi_exec($mh, $active);
	//			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	//		}
	//	}
    //
    //    $response_1 = curl_multi_getcontent($curl);
    //    $response_2 = curl_multi_getcontent($cur2);
	//
	//	//закрываем все дескрипторы
	//	curl_multi_remove_handle($mh, $curl);
	//	curl_multi_remove_handle($mh, $cur2);
	//	curl_multi_close($mh);
	//
	//
	//	$url = "https://api.dostav.im/StoreWidget/DeliveryMethodsFront?";
	//	$curl = curl_init();
	//
	//	curl_close($curl);
	//
	//	return  array_merge(json_decode($response_1, true),json_decode($response_2, true));
	//
    //
	//}
//асинхронно пытаемся получить два метода сразу  php конечно не асинхронный но что делать приходиться извращаться
	function GetNameAndPriceAndDescriptionANDGetFilialsPVZ($key,$townGuidTo,$orderCost,$deliveryService,$length,$width,$height,$weight, $UseOrderCostAsCodAndAssessed)
	{

			$ch1 = curl_init();
			$ch2 = curl_init();
			$url = "https://api.dostav.im/StoreWidget/ServicesAndFilials?"."townGuid=".$townGuidTo."&orderCost=".$orderCost."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight;


			$url2 = "https://api.dostav.im/StoreWidget/DeliveryMethodsFront?"."townGuidTo=".$townGuidTo."&orderCost=".$orderCost."&deliveryService=".$deliveryService."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight."&UseOrderCostAsCodAndAssessed=".$UseOrderCostAsCodAndAssessed;

			curl_setopt($ch1, CURLOPT_URL, $url);
			//curl_setopt($ch1, CURLOPT_HEADER, 0);
			curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
				"authorization: ".$key
			));
			curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch1, CURLOPT_ENCODING, true);
			curl_setopt($ch1, CURLOPT_TIMEOUT, 0);
			curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 0);


			curl_setopt($ch2, CURLOPT_URL, $url2);
			//curl_setopt($ch2, CURLOPT_HTTPHEADER, 0);
			curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
				"authorization: ".$key
			));
			curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch2, CURLOPT_ENCODING, true);
			curl_setopt($ch2, CURLOPT_TIMEOUT, 0);
			curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 0);


			//create the multiple cURL handle
			$mh = curl_multi_init();

			// добавляем обработчики
			curl_multi_add_handle($mh,$ch1);
			curl_multi_add_handle($mh,$ch2);

			$running = null;
			// выполняем запросы
			do {
			curl_multi_exec($mh, $running);
			} while ($running > 0);

			// освободим ресурсы
			curl_multi_remove_handle($mh, $ch1);
			curl_multi_remove_handle($mh, $ch2);
			curl_multi_close($mh);


			$response_1 = curl_multi_getcontent($ch1);
			$response_2 = curl_multi_getcontent($ch2);


   		    //echo $response_1.$response_2;

			//return array_merge(json_decode($response_1, true),json_decode($response_2, true));


			if (isset($response_1) and !empty($response_1) and isset($response_2) and !empty($response_2)) {
			return array_merge(json_decode($response_1, true),json_decode($response_2, true));
			} else {
			return  '';
			}
	}
	function getQuote($address) {
		$this->language->load('checkout/cart');
        $products = $this->cart->getProducts();
		$this->load->model('setting/setting');
		$this->load->model('setting/settingdostavim');
		$param1 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_token');
	    $tokenDostavim = $param1["value"];
		$UseOrderCostAsCodAndAssessedDostavim = '';
		//получаем guid Города и региона
		//получаем флаг
		//если флаг неполучен ждем получения
		$sd = $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_sd');
		$city = $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_city');
		$flag = $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_flag');
		$oldTotalPrice = $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_price1');
		$price_dostavim0 = '';
		$price_dostavim1 = '';
		$price_dostavim2 = '';
		$price_dostavim3 = '';
		$individualPrice = '';
        $deliveryMethodsFrontName0 = '';
		$deliveryMethodsFrontDescription0 = '';
        $deliveryMethodsFrontDeliveryCostMin0 = '';
        $deliveryMethodsFrontName1 = '';
		$deliveryMethodsFrontDescription1 = '';
        $deliveryMethodsFrontDeliveryCostMin1 = '';
        $deliveryMethodsFrontName2 = '';
		$deliveryMethodsFrontDescription2 = '';
        $deliveryMethodsFrontDeliveryCostMin2 = '';
        $deliveryMethodsFrontCountDay2 = '';
		$pvzListLi = '';
        $deliveryMethodsFrontName3 = '';
		$deliveryMethodsFrontDescription3 = '';
        $deliveryMethodsFrontDeliveryCostMin3 = '';
        $deliveryMethodsFrontCountDay3 = '';
		$innerJs = '';
		$nogrid = true;
		$key = $param1["value"];
		$parametrs = $this->GetListParametrs($key);
        $parametrsLength = $parametrs->deliveryMethods[3]->length;
        $parametrsWidth = $parametrs->deliveryMethods[3]->width;
        $parametrsHeight = $parametrs->deliveryMethods[3]->height;
        $parametrsWeigh = $parametrs->deliveryMethods[3]->weight;

		foreach ($products as $i => $item) {
				$priceProduct =	$item['total'];
		}

		if(stristr($address['address_1'], '::', true)){
			$deliveryService1 = explode("::", $address['address_1']);
			$deliveryService = $deliveryService1[1];
			$deliveryServiceForReplace = $deliveryService1[1];
			$deliveryAddressForReplace = $deliveryService1[0];
		}
		else
			$deliveryService = '';


        $deliveryServiceSD = '';  //может понадобиться в 1.5

		//17.01.2019
		$limit = explode("::", $address['address_1']);
		if (isset($limit[1])){
			$deliveryAddressGet = explode("::", $address['address_1']);
			$deliveryService = $deliveryAddressGet[1];
			$deliveryServiceAddress = $deliveryAddressGet[0];
			$deliveryServiceSD = $deliveryAddressGet[2];
		}
		else
			$deliveryService = '';
		//17.01.2019

		$guidCityAndRegHide = $this->GetGuidCityAndRegion($key, $address['city'],$address['zone'],true);  //тут отключен регион, пока можно оставить, но потом надо это исправить

		//$this->log->write(print_r($guidCityAndRegHide,true));

		$guidCityAndRegHide = json_decode($guidCityAndRegHide);

		if (isset($guidCityAndRegHide[0]->name)){
			if ($guidCityAndRegHide[0]->name!=$address['city']) //если получин результат   важно надо доделать
			$nogrid = false;
		}
		else
		$nogrid = false;

//новое
		$weightDostavim = 0;
		$variableOrderCost = 0;
		$productDostavim = '';
		$arrayProductHeighWidthlength = [];
		$k=0;



		foreach ($products as $i => $item) {


			if($item['weight_class_id']=='1'){
				if($item['weight']==0){
				$weightDostavim	= floatval($parametrsWeigh)+floatval($weightDostavim);
				}
				else
				$weightDostavim	= floatval($item['weight']*1000)+floatval($weightDostavim);
			}
			else
			{
				if($item['weight']==0){
				$weightDostavim	= floatval($parametrsWeigh)+floatval($weightDostavim);
				}
				else
			    $weightDostavim	= floatval($item['weight'])+floatval($weightDostavim);
			}

				$variableOrderCost = floatval($item['total'])+floatval($variableOrderCost);
				$productDostavim	= $item['name'].' '.$item['quantity'].'шт , '.$productDostavim;


			for($j=0;$j < $item['quantity'];$j++){
						if($item['length']==0.00000000) $item['length'] = $parametrsLength;
						if($item['width']==0.00000000) $item['width'] = $parametrsWidth;
						if($item['height']==0.00000000) $item['height'] = $parametrsHeight;
					$arrayProductHeighWidthlength[$k] = [
						'Lenght'=> floatval($item['length']),
						'Width'=> floatval($item['width']),
						'Height'=> floatval($item['height'])
					];
					$k++;

			}
		}
		$ves = $weightDostavim;

//новое

//для наложка
		$nalojka_dostavim =  $this->model_setting_settingdostavim->getPriceDostavim('dostavimchekaut_nalojka');



		if (!empty($nalojka_dostavim['value'])){
			if ($variableOrderCost >= $nalojka_dostavim['value'])
			{
				$UseOrderCostAsCodAndAssessedDostavim = 'false';
			}
		}
//для наложка



       if(($city['value']!=$address["city"])or($deliveryService!=$sd['value'])or($oldTotalPrice['value']!=$variableOrderCost)){ //если город поменялся или пункт сомовывоза или цена





		$objectHeighWidthlength = $this->goCalcWHL($arrayProductHeighWidthlength);

		$guidCityAndReg = $guidCityAndRegHide;//json_decode($guidCityAndReg);  2019

		if (isset($guidCityAndReg[0]->guid)){ //если получин результат   важно надо доделать

		$townGuidTo = $guidCityAndReg[0]->guid;


		$key = $param1["value"];

		$orderCost = $variableOrderCost;
        $deliveryServiceSD = '';  //может понадобиться в 1.5

		$limit = explode("::", $address['address_1']);
		if (isset($limit[1])){
			$deliveryAddressGet = explode("::", $address['address_1']);
			$deliveryService = $deliveryAddressGet[1];
			$deliveryServiceAddress = $deliveryAddressGet[0];
			$deliveryServiceSD = $deliveryAddressGet[2];
		}
		else
			$deliveryService = '';


		if($weightDostavim<100)
			$weightDostavim=100;

		if (isset($objectHeighWidthlength)){
			$length = ceil($objectHeighWidthlength->lenght);
			$width = ceil($objectHeighWidthlength->width);
			$height = ceil($objectHeighWidthlength->height);
			$weight = $weightDostavim;
		}
		else   //тут нужна галочка расчитывать не из личного кабинета
		{
			$length = '';
			$width = '';
			$height = '';
			$weight = '';
		}

		 $url = "";
		//$this->log->write(print_r($length,true));


		//обнуляем  город и сд
		$setting_city_id = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_city', 0);
		$this->model_setting_settingdostavim->setCityDostavim($address["city"], $setting_city_id['setting_id']);

		$setting_sd_id = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_sd', 0);
		$this->model_setting_settingdostavim->setCityDostavim($deliveryService, $setting_sd_id['setting_id']);
		//обнуляем город и сд

		$ves = $weight;

		$deliveryMethodsFront2 = $this->GetNameAndPriceAndDescriptionANDGetFilialsPVZ($key,$townGuidTo,$orderCost,$deliveryService,$length,$width,$height,$weight,$UseOrderCostAsCodAndAssessedDostavim);//7.47

		$deliveryMethodsFront = array_splice($deliveryMethodsFront2, -2);
		$deliveryMethodsFront = json_decode(json_encode($deliveryMethodsFront));


       if(($city['value']!=$address["city"])or($oldTotalPrice['value']!=$variableOrderCost)){ //если город поменялся или пункт сомовывоза или цена	//2019 11.01.2019

       $getfilialsPVZ = json_encode($deliveryMethodsFront2, true);

		if ((!is_array(json_decode($getfilialsPVZ))) or (empty(json_decode($getfilialsPVZ)))) {
			//$length = '';
			//$width = '';
			//$height = '';
			//$weight = '';
			//$orderCost=0;
		    //getfilialsPVZ = $this->GetFilialsPVZ($key,$townGuidTo,$orderCost,$length,$width,$height,$weight);
		    $individualPrice  = ' (Размеры выходят за допустимые значения доставка будет расчитывать индивидуально)';
			$nogrid = false;
			//----------------ошибка------------------>
			$mapsYandex = '';
			$deliveryMethods = '';

		}
		else
		{

		 $listProv = $this->GetListProvider();  //- получил список служб доставки
		 $pvzListLi  = $this->GenerateListPvz($getfilialsPVZ, $listProv);


		 $innerJs2 = $this->createjsPvz('jQuery(function(){$("ul.pvz-list").append("'.addslashes($pvzListLi).'");});');

		 $pvzYandex  = $this->GenerateListPvzForYandex($getfilialsPVZ, $listProv);


		 $mapsYandex  = $this->initDostavim(json_encode($pvzYandex[0][0]), 10, $listProv, $pvzYandex);

		 $checkboxs  = $this->GenerateCheckbox($listProv);

	     $innerJs = $this->createJsDsoatavim($mapsYandex.$checkboxs);
		}

	   }//2019 11.01.2019


	   	if(isset($deliveryMethodsFront->deliveryMethods[3]->name)){

	    $deliveryMethodsFrontName0 = $deliveryMethodsFront->deliveryMethods[0]->name;
	    $deliveryMethodsFrontDescription0 = $deliveryMethodsFront->deliveryMethods[0]->description;

	    $deliveryMethodsFrontDeliveryCostMin0 = $deliveryMethodsFront->deliveryMethods[0]->deliveryCostMin;
	    $deliveryMethodsFrontUsed0 = $deliveryMethodsFront->deliveryMethods[0]->used;


	    $deliveryMethodsFrontName1 = $deliveryMethodsFront->deliveryMethods[1]->name;
	    $deliveryMethodsFrontDescription1 = $deliveryMethodsFront->deliveryMethods[1]->description;
	    $deliveryMethodsFrontDeliveryCostMin1 = $deliveryMethodsFront->deliveryMethods[1]->deliveryCostMin;
	    $deliveryMethodsFrontUsed1 = $deliveryMethodsFront->deliveryMethods[1]->used;


	    $deliveryMethodsFrontName2 = $deliveryMethodsFront->deliveryMethods[2]->name;
	    $deliveryMethodsFrontDescription2 = $deliveryMethodsFront->deliveryMethods[2]->description;
	    $deliveryMethodsFrontDeliveryCostMin2 = $deliveryMethodsFront->deliveryMethods[2]->deliveryCostMin;
	    $deliveryMethodsFrontUsed2 = $deliveryMethodsFront->deliveryMethods[2]->used;
	    $deliveryMethodsFrontDeliveryDateMax2 = $deliveryMethodsFront->deliveryMethods[2]->deliveryDateMax; // дата доставки
	    $deliveryMethodsFrontCountDay2 = date_diff(date_create($deliveryMethodsFront->deliveryMethods[2]->deliveryDateMax),date_create('now')); //количество дней до доставки
	    $deliveryMethodsFrontCountDay2 = $deliveryMethodsFrontCountDay2->d+1; //количество дней до доставки

	    $deliveryMethodsFrontName3 = $deliveryMethodsFront->deliveryMethods[3]->name;
	    $deliveryMethodsFrontDescription3 = $deliveryMethodsFront->deliveryMethods[3]->description;
	    $deliveryMethodsFrontDeliveryCostMin3 = $deliveryMethodsFront->deliveryMethods[3]->deliveryCostMin;
	    $deliveryMethodsFrontUsed3 = $deliveryMethodsFront->deliveryMethods[3]->used;
	    $deliveryMethodsFrontDeliveryDateMax3 = $deliveryMethodsFront->deliveryMethods[3]->deliveryDateMax; //дата доставки
	    $deliveryMethodsFrontCountDay3 = date_diff(date_create($deliveryMethodsFront->deliveryMethods[3]->deliveryDateMax),date_create('now'));;// - date("m"); //количество дней до доставки
	    $deliveryMethodsFrontCountDay3 = $deliveryMethodsFrontCountDay3->d+1;;// - date("m"); //количество дней до доставки

	    $deliveryMethodsFrontName4 = $deliveryMethodsFront->deliveryMethods[4]->name;
	    $deliveryMethodsFrontDescription4 = $deliveryMethodsFront->deliveryMethods[4]->description;
	    $deliveryMethodsFrontDeliveryCostMin4 = $deliveryMethodsFront->deliveryMethods[4]->deliveryCostMin;
	    $deliveryMethodsFrontUsed4 = $deliveryMethodsFront->deliveryMethods[4]->used;
	    $deliveryMethodsFrontDeliveryDateMax4 = $deliveryMethodsFront->deliveryMethods[4]->deliveryDateMax; //дата доставки
	    $deliveryMethodsFrontCountDay4 = date_diff(date_create($deliveryMethodsFront->deliveryMethods[4]->deliveryDateMax),date_create('now'));;// - date("m"); //количество дней до доставки
	    $deliveryMethodsFrontCountDay4 = $deliveryMethodsFrontCountDay4->d+1;;// - date("m"); //количество дней до доставки

		$price_dostavim0 = $deliveryMethodsFrontDeliveryCostMin0;
		$price_dostavim1 = $deliveryMethodsFrontDeliveryCostMin1;
		$price_dostavim2 = $deliveryMethodsFrontDeliveryCostMin2;
		$price_dostavim3 = $deliveryMethodsFrontDeliveryCostMin3;
		$price_dostavim4 = $deliveryMethodsFrontDeliveryCostMin4;


			//===========================================================================================================================
			$setting_price_id0 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_price1', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($price_dostavim0, $setting_price_id0["setting_id"]);

            $setting_name_id0 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_name1', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontName0, $setting_name_id0["setting_id"]);


            $setting_description_id0 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_description1', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontDescription0, $setting_description_id0["setting_id"]);
			//===========================================================================================================================
			//===========================================================================================================================
			$setting_price_id1 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_price2', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($price_dostavim1, $setting_price_id1["setting_id"]);

            $setting_name_id1 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_name2', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontName1, $setting_name_id1["setting_id"]);

            $setting_description_id1 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_description2', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontDescription1, $setting_description_id1["setting_id"]);
			//===========================================================================================================================
			$setting_price_id2 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_price3', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($price_dostavim2, $setting_price_id2["setting_id"]);

            $setting_name_id2 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_name3', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontName2, $setting_name_id2["setting_id"]);

            $setting_description_id2 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_description3', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontDescription2, $setting_description_id2["setting_id"]);

            $setting_day_id2 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_day3', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontCountDay2, $setting_day_id2['setting_id']);


			//$setting_pvz_id2 = $this->model_setting_settingdostavim->getSettingDostavim('dostavimchekaut', 'dostavimchekaut_pvz3[', 0);
			//$this->model_setting_settingdostavim->setSettingDostavim($pvzListLi, $setting_pvz_id2);

			//===========================================================================================================================

			$setting_price_id3 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_price4', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($price_dostavim3, $setting_price_id3["setting_id"]);

            $setting_name_id3 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_name4', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontName3, $setting_name_id3["setting_id"]);

            $setting_description_id3 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_description4', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontDescription3, $setting_description_id3["setting_id"]);

            $setting_day_id3 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_day4', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontCountDay3, $setting_day_id3['setting_id']);
			//===========================================================================================================================

			$setting_price_id4 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_price5', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($price_dostavim4, $setting_price_id4["setting_id"]);

            $setting_name_id4 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_name5', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontName4, $setting_name_id4["setting_id"]);

            $setting_description_id4 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_description5', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontDescription4, $setting_description_id4["setting_id"]);

            $setting_day_id4 = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_day5', 0);
			$this->model_setting_settingdostavim->setSettingDostavim($deliveryMethodsFrontCountDay4, $setting_day_id4['setting_id']);

	   }
	   else
		   $nogrid = false;

	}

		}

		$price_dostavim0 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_price1');
        $price_dostavim0 =  $price_dostavim0["value"];

		$name_dostavim0 =   $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_name1');;
        $deliveryMethodsFrontName0 =  $name_dostavim0["value"];

		$description_dostavim0 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_description1');
        $deliveryMethodsFrontDescription0 =  $description_dostavim0["value"];

		$price_dostavim1 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_price2');
        $price_dostavim1 =  $price_dostavim1["value"];
		$name_dostavim1 =   $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_name2');
        $deliveryMethodsFrontName1 =  $name_dostavim1["value"];
		$description_dostavim1 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_description2');
        $deliveryMethodsFrontDescription1 =  $description_dostavim1["value"];

		$price_dostavim2 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_price3');
        $price_dostavim2 =  $price_dostavim2["value"];
		$name_dostavim2 =   $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_name3');
        $deliveryMethodsFrontName2 =  $name_dostavim2["value"];
		$description_dostavim2 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_description3');
        $deliveryMethodsFrontDescription2 =  $description_dostavim2["value"];
		$day_dostavim2 =    $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_day3');
        $deliveryMethodsFrontCountDay2 =  $day_dostavim2["value"];

		$price_dostavim3 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_price4');
        $price_dostavim3 =  $price_dostavim3["value"];
		$name_dostavim3 =   $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_name4');
        $deliveryMethodsFrontName3 =  $name_dostavim3["value"];
		$description_dostavim3 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_description4');
        $deliveryMethodsFrontDescription3 =  $description_dostavim3["value"];
		$day_dostavim3 =    $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_day4');
        $deliveryMethodsFrontCountDay3 =  $day_dostavim3["value"];

		$price_dostavim4 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_price5');
        $price_dostavim4 =  $price_dostavim4["value"];
		$name_dostavim4 =   $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_name5');
        $deliveryMethodsFrontName4 =  $name_dostavim4["value"];
		$description_dostavim4 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_description5');
        $deliveryMethodsFrontDescription4 =  $description_dostavim4["value"];
		$day_dostavim4 =    $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_day5');
        $deliveryMethodsFrontCountDay4 =  $day_dostavim4["value"];



		//обнуляем флаг
		$setting_flag_id = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_flag', 0);
		$flag_dostavim =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_flag');
		$raschet=(int)$flag_dostavim["value"]+1;
		$this->model_setting_settingdostavim->setFlagDostavim($raschet, $setting_flag_id['setting_id']);
		//обнуляем флаг


		//записываем цену всего заказа
        $setting_total_price = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut', 'shipping_dostavimchekaut_price1', 0);
		$this->model_setting_settingdostavim->setSettingDostavim($variableOrderCost, $setting_total_price["setting_id"]);
		//записываем цену всего заказа

		//бесплатная доставка
		$rusha_dostavim =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_rusha');
		$moscow_dostavim =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_moscow');

		//бесплатная доставка
		if (!empty($rusha_dostavim["value"])){
			if ($address["city"]!='Москва'){
				if($priceProduct >= $rusha_dostavim["value"])
				{
					$price_dostavim2 = 0;
					$price_dostavim3 = 0;
					$price_dostavim4 = 0;
				}
			}
		}

		if (!empty($moscow_dostavim["value"])){
			if ($address["city"]=='Москва'){
				if($priceProduct >= $moscow_dostavim["value"])
				{
					$price_dostavim2 = 0;
					$price_dostavim3 = 0;
					$price_dostavim4 = 0;
				}
			}
		}

		if(isset($deliveryServiceForReplace))
		{
			if($city['value']!=$address["city"]){
				$deliveryServiceAddress = 'Выберите пункт самовывоза';
			}
			else
			if($deliveryServiceForReplace==$sd['value']){
				$deliveryServiceAddress = '<img src="https://dostav.im/img/'.$deliveryServiceForReplace.'.png" style="width: 40px; margin-right: 10px;" pvzid="'.$deliveryServiceSD.'">'.$deliveryAddressForReplace;
			}
			else
			$deliveryServiceAddress = '<img src="https://dostav.im/img/'.$deliveryServiceForReplace.'.png" style="width: 40px; margin-right: 10px;" pvzid="'.$deliveryServiceSD.'">'.$deliveryAddressForReplace;
		}
        else
	    $deliveryServiceAddress = 'Выберите пункт самовывоза';

		//- тут надо записывать цену
	/////////////////////////////////////////////////////////////////
	    $vidgetContent0 = '<main id="nameDostavimID" role="main" style="position: relative;width:100%" ><form action="" class="vidget-form"><div id="topLoader" style="position: absolute;left: 30%;display:none;z-index: 99999;"></div></form></main>';

	    $vidgetContent2 = '<span class="hidepvz"><a href="#" class="pvz-list-link"><img src="img/map-pin.svg" alt="">'.$deliveryServiceAddress.'</a><div class="pvz-div" style="display:none"><input type="text" class="address-filter" pvzId="" pvzGuid="" pvzCode="" serviceId=""><ul class="pvz-list" tabindex="0">';


		$vidgetContent3 = '<script type="text/javascript" src="/get_url.js"></script><script type="text/javascript" src="/get_url_pvz.js"></script>';  //16.01.2019



		//$vidgetContent4 = '	</ul></div><a href="#" class="pvz-map-link" style="margin-left: 10%;" id="pvz-dostavim" data-target="#exampleModal"><img src="img/map.svg" alt="">Карта</a><span>';
		$vidgetContent4 = '	</ul></div><a href="#" class="pvz-map-link" style="margin-left: 10%;" id="pvz-dostavim" data-target="#exampleModal"><img src="img/map.svg" alt="">Карта</a><span>';


		$vidgetContent5 = '<div  id="exampleModal" style="display:none" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"> <!--вывод идет с помощью дата таргет--> <div class="modal-dialog" role="document"><div class="modal-content"> <div class="modal-header"> <p class="modal-title" id="exampleModalLabel">Пункты самовывоза на карте</p><button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button> </div><div class="modal-body"> <div class="delivery-service-filter">  <div class="delivery-service-list"> <h5>Службы доставки</h5>  <ul class="ds-list"></ul> </div></div><div id="mapdostavim"></div></div>          </div></div></div><div id="overlayDostavim"></div> <div id="modal_form"><span id="modal_close">X</span><p id="errorModalDostavimTitle"></p><hr><p id="errorModalDostavim"></p><br><br><br><span style="    clear: both; float: none;text-align: center;padding: 5%;color: red;background: #333;margin-left: 40%;"  class="close" data-dismiss="modal" aria-label="Close" >ОК</span></div><div id="overlay"></div><script>main()</script>';//<script>main(1)</script>';

		$status = true; //fix

		if($ves==0){
			$weightDostavim = $ves;
		}


		$vidgetScript = '';

		$multideliverys = $this->config->get('shipping_dostavimchekaut');

		foreach($multideliverys as $i => $delivery) {

			if(!$delivery['status']) {
				continue;
			}
			if(!$delivery['geo_zone_id']) {
				$status = true;
			} else {


				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$delivery['geo_zone_id'] . "'" .
										  " AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
				if($query->num_rows) {
					$status = true;
				} else {
					$multideliverys[$i]['status'] = false;
				}
			}
		}

		$method_data = array();

		if ($status) {

			$quote_data = array();
			$sort_order = array();


			foreach($multideliverys as $i => $delivery) {
				if(!$delivery['status']) {
					continue;
				}


				if($i==1){

				$quote_data['dostavimchekaut1'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut1',
					'title'        => '<label><span id="nameChoice1">'.$deliveryMethodsFrontName0.'<span/><br></label>',
					'cost'         => $price_dostavim0,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $price_dostavim0.'р.'
				);

					continue;

				}

				if($i==2){
				$quote_data['dostavimchekaut2'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut2',
					'title'        => '<label><span id="nameChoice2">'.$deliveryMethodsFrontName1.'<span/><br></label>',
					'cost'         => $price_dostavim1,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $price_dostavim1.'р.'
				);

					continue;

				}


			if($i==3){
				if ($nogrid==true){
				$quote_data['dostavimchekaut3'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut3',
					'title'        => '<label><span id="nameChoice3">'.$deliveryMethodsFrontName2.'</span> - '.$deliveryMethodsFrontCountDay2.' дн <br></label>',
					'cost'         => $price_dostavim2,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $price_dostavim2.'р.<br>'.'</b></td></tr><tr> <td colspan="3">'.$vidgetContent2.$vidgetContent3.$vidgetContent4.'</td></tr>'
				);


				}
					continue;
			}

			if($i==4){
				if ($nogrid==true){    //если guid неполучен влияет напрямую и
				$quote_data['dostavimchekaut4'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut4',
					'title'        => '<label><span id="nameChoice4">'.$deliveryMethodsFrontName3.'</span> - '.$deliveryMethodsFrontCountDay3.' дн <br></label>',
					'cost'         => $price_dostavim3,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $price_dostavim3.'р.'.'</b></td></tr><tr> <td colspan="3">'.$vidgetContent0.'</td></tr>'.'<span id="tokenDostavim" style="display:none">'.$tokenDostavim.'</span>'.'</label>'.$vidgetContent5.'<label style="display:none">'.$vidgetContent3
				);
				}
					continue;
			}

			if($i==5){
			if($weightDostavim<=5000){
				if ($nogrid==true){    //если guid неполучен влияет напрямую и

				$quote_data['dostavimchekaut5'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut5',
					'title'        => '<label><span id="nameChoice5">'.$deliveryMethodsFrontName4.'</span> - '.$deliveryMethodsFrontCountDay4.' дн <br></label>',
					'cost'         => $price_dostavim4,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $price_dostavim4.'р.'.'</b></td></tr><tr> <td colspan="3">'.$vidgetContent0.'</td></tr>'.'<span id="tokenDostavim" style="display:none">'.$tokenDostavim.'</span>'.'</label>'.$vidgetContent5.'<hr id="forvidget" style="margin: 0px !important;"><label style="display:none">'.$vidgetContent3
				);
				}
					continue;
			   }
			     continue;
		    }

				$quote_data['dostavimchekaut' . $i] = array(
					'code' => 'dostavimchekaut.dostavimchekaut' . $i,
					'title' => $delivery['name'],
					'cost' => $delivery['cost'],
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $this->currency->format($this->tax->calculate($delivery['cost'], $delivery['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);





				$sort_order[$i] = $delivery['sort_order'];
			}

			//array_multisort($sort_order, SORT_ASC, $quote_data);

			$method_data = array(
				'code'       => 'dostavimchekaut',
				'title'      => 'Доставка от Dostav.im'.' '.$individualPrice,//.$,
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('dostavimchekaut_sort_order'),
				'error'      => false
			);


		}

		return $method_data;
	}
}
?>