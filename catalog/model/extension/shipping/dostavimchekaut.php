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
function GetGuidCityAndRegion($token_dostavim, $city, $region, $fillRegion)
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
		    "authorization: ".$token_dostavim
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

	function GetNameAndPriceAndDescription($token_dostavim,$town_guid_to,$orderCost,$delivery_service,$length,$width,$height,$weight, $UseOrderCostAsCodAndAssessed)
	{
		$url = "https://api.dostav.im/StoreWidget/DeliveryMethodsFront?";
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url."townGuidTo=".$town_guid_to."&orderCost=".$orderCost."&deliveryService=".$delivery_service."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight."&UseOrderCostAsCodAndAssessed=".$UseOrderCostAsCodAndAssessed,
		  CURLOPT_SSL_VERIFYHOST => false,
		  CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "authorization: ".$token_dostavim
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
    function GetFilialsPVZ($token_dostavim,$town_guid_to,$orderCost,$length,$width,$height,$weight)
    {
		$url = "https://api.dostav.im/StoreWidget/ServicesAndFilials?";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url."townGuid=".$town_guid_to."&orderCost=".$orderCost."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 30,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
		    "authorization: ".$token_dostavim
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
                            $pvzItem['costSD'] = $arrayFillials[$i]->cost;
                            $pvzItem['hintContent'] = $arrayFillials[$i]->deliveryFilials[$j]->name;
							$razbivka = explode(":", $arrayFillials[$i]->deliveryFilials[$j]->address);
                            $contentForBalloon = '<img src="'. $pvzItem['serviceImg'] .'" style="width: 40px; margin-right: 10px;" pvzId="' . $arrayFillials[$i]->deliveryFilials[$j]->apiShipId . '">' . $razbivka[1];   //непонятка  доделать
							$pvzItem['balloonContent'] = $contentForBalloon;
                            $pvzItem['balloonContentHeader']= $pvzItem['serviceName'];
                            //$pvzItem['balloonContentBody'] = '<a href="#" pvzguid="'.$arrayFillials[$i]->deliveryFilials[$j]->addressGuid.'" pvzcode="'.$arrayFillials[$i]->deliveryFilials[$j]->id.'" serviceid="'.$pvzItem['serviceName'].'" class="pvz-cluster-balloon" >' .$contentForBalloon. '</a>';	//10.12.2018
                            $pvzItem['balloonContentBody'] = '<a href="#" pvzguid="" costSD="'.$arrayFillials[$i]->cost.'" pvzcode="'.$arrayFillials[$i]->deliveryFilials[$j]->id.'" serviceid="'.$pvzItem['serviceName'].'" class="pvz-cluster-balloon" >' .$contentForBalloon. '</a>';				
                            $pvzItem['type'] = $arrayFillials[$i]->deliveryFilials[$j]->type;
                            $pvzItem['id'] = $arrayFillials[$i]->deliveryFilials[$j]->apiShipId;
                            $pvzItem['phone'] = $arrayFillials[$i]->deliveryFilials[$j]->phone;
                            $pvzItem['timeTable'] = $arrayFillials[$i]->deliveryFilials[$j]->timeTable;
                            $pvzItem['day_min'] = $arrayFillials[$i]->deliveryDurationWorkingDaysMin;
                            $pvzItem['day_max'] = $arrayFillials[$i]->deliveryDurationWorkingDaysMax;							
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
									if ($arrDost["type"] == 0 || $arrDost["type"] == 1) {  //16.04.2019
										$pvzListPhp[$t] = '<li class="pvz-item" tabindex="-1" day_min="' . $arrDost["day_min"] . '" day_max="' . $arrDost["day_max"] . '"  timeTable="' . $arrDost["timeTable"] . '" phone="' . $arrDost["phone"] . '" costSD="' . $arrDost["costSD"] . '" serviceId="' . $arrDost["serviceId"] . '" pvzId="' . $arrDost["id"] . '" pvzCode="' . $arrDost["code"] . '" pvzGuid="' . $arrDost["guid"] . '">' . $arrDost["balloonContent"] . '</li>';
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
                            $pvzItem['costSD'] = $arrayFillials[$i]->cost;
                            $pvzItem['hintContent'] = $arrayFillials[$i]->deliveryFilials[$j]->name;
							$razbivka = explode(":", $arrayFillials[$i]->deliveryFilials[$j]->address);
                            $contentForBalloon = '<img src=\''. $pvzItem['serviceImg'] .'\' style=\'width: 40px; margin-right: 10px;\' pvzId=\'' . $arrayFillials[$i]->deliveryFilials[$j]->apiShipId . '\'>' . $razbivka[1];   //непонятка  доделать
							$pvzItem['balloonContent'] = $contentForBalloon;
                            $pvzItem['balloonContentHeader ']= $pvzItem['serviceName'];
                            $pvzItem['balloonContentBody'] = '<a href=\'#\' pvzguid=\'\' costSD=\''.$pvzItem['costSD'].'\' pvzcode=\''.$arrayFillials[$i]->deliveryFilials[$j]->id.'\' serviceid=\''.$pvzItem['serviceName'].'\' class=\'pvz-cluster-balloon\' >' .$contentForBalloon. '</a>';
                            $pvzItem['phone'] = $arrayFillials[$i]->deliveryFilials[$j]->phone;
                            $pvzItem['timeTable'] = $arrayFillials[$i]->deliveryFilials[$j]->timeTable;	
                            $pvzItem['day_min'] = $arrayFillials[$i]->deliveryDurationWorkingDaysMin;
                            $pvzItem['day_max'] = $arrayFillials[$i]->deliveryDurationWorkingDaysMax;							
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
		$part6 .= 'jQuery("#'.$dslist[$i]->key.'").click(checkState);';		//mb_strtolower
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
  			console.log(object);		
        if (!object.properties._data.iconContent) {

			
            jQuery(".pvz-list-link").html(object.properties._data.balloonContent); 	
			jQuery(window.addressDostavim).val(object.properties._data.balloonContent.split(">")[1]+"::"+object.properties._data.serviceId+"::"+object.properties._data.id+"::"+object.properties._data.costSD+"::3::");
			//jQuery(addressDostavim).val(jQuery(this).attr("serviceid")+": "+jQuery(this).text());
            jQuery(".address-filter").attr("pvzId", object.properties._data.id);
            jQuery(".address-filter").attr("serviceId", object.properties._data.serviceId);
            jQuery(".address-filter").attr("costSD", object.properties._data.costSD);

                        jQuery(".address-filter").attr("dostphone",object.properties._data.phone);
                        jQuery(".address-filter").attr("dosttime",object.properties._data.timeTable);			
                        jQuery("span#dostphone").text(object.properties._data.phone);
                        jQuery("span#dosttime").text(object.properties._data.timeTable);	
                        jQuery("span#dostadress").text(object.properties._data.balloonContent.split(">")[1]);
						jQuery("span#dostmysd").text(object.properties._data.serviceId);
                        jQuery("span#dostprice").text(object.properties._data.costSD);
                        jQuery("span#dostday_max").text(object.properties._data.day_max);
                        jQuery("span#dostday_min").text(object.properties._data.day_min);						

		jQuery("#errorModalDostavim").html(object.properties._data.balloonContent);	
		
					$("#exampleModal").animate({opacity: 0, top: "20%"}, 200, function(){
					$(this).css("display", "none");
					$("#overlayDostavim").fadeOut(400);
					});	
//////////////////////////////////////////////////////////////////////		reloadDostavim();
        $.ajax({                    
            url: "index.php?route=checkout/dostavimajaxquote",        
		    type: "post",
		    dataType: "json",
            data: {
				name:"dostavimCash",
				dostadress: $("span#dostadress").text(),
				dostphone: $("span#dostphone").text(),
				dosttime: $("span#dosttime").text(),
				dostmysd: $("span#dostmysd").text(),
                dostprice: $("span#dostprice").text(),
                dostday_max: $("span#dostday_max").text(),
                dostday_min:  $("span#dostday_min").text()				
			},//JSON.stringify(formData),
            success: function (got)
            {
						$("input[value=\'dostavimchekaut.dostavimchekaut3\']").click();	
						$("input[value=\'dostavimchekaut.dostavimchekaut3\']").trigger(\'change\');			
            },
            error : function (msg) {                
                return console.log(msg);
            },
            timeout: 3000
        });  	
//////////////////////////////////////////////////////////////////////	reloadDostavim();				
        						//reloadDostavim();	
	 
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

   

//здесь мы получаем список вес из ЛК	
    function GetListParametrs($token_dostavim)
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
		    "authorization: ".$token_dostavim
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
	function GetNameAndPriceAndDescriptionANDGetFilialsPVZ($token_dostavim,$town_guid_to,$orderCost, $length,$width,$height,$weight, $UseOrderCostAsCodAndAssessed)
	{
		    //print_r($token_dostavim);
		    //print_r($town_guid_to);
		    //print_r('$town_guid_to');
		    //print_r($orderCost);
		    //print_r('$orderCost');
		    //print_r($delivery_service);
		    //print_r('$delivery_service');
		    //print_r($length);
		    //print_r('$length');
		    //print_r($width);
		    //print_r('$width');
		    //print_r($height);
		    //print_r('$height');
		    //print_r($weight);
		    //print_r('$weight');
		    //print_r($UseOrderCostAsCodAndAssessed);
		    //print_r('$UseOrderCostAsCodAndAssessed');

		
			$ch1 = curl_init();
			$ch2 = curl_init();
			$url = "https://api.dostav.im/StoreWidget/ServicesAndFilials?"."townGuid=".$town_guid_to."&orderCost=".$orderCost."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight."&UseOrderCostAsCodAndAssessed=".$UseOrderCostAsCodAndAssessed;;
		
		
			$url2 = "https://api.dostav.im/StoreWidget/DeliveryMethodsFront?"."townGuidTo=".$town_guid_to."&orderCost=".$orderCost."&length=".$length."&width=".$width."&height=".$height."&weight=".$weight."&UseOrderCostAsCodAndAssessed=".$UseOrderCostAsCodAndAssessed;
		
			curl_setopt($ch1, CURLOPT_URL, $url);
			//curl_setopt($ch1, CURLOPT_HEADER, 0);
			curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
				"authorization: ".$token_dostavim
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
				"authorization: ".$token_dostavim
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
		
   		    //echo $response_1;	
   		    //echo '---------';
			//echo $response_2;
		
			//return array_merge(json_decode($response_1, true),json_decode($response_2, true));
		
		
			if (isset($response_1) or isset($response_1)) {
			return array_merge(json_decode($response_1, true),json_decode($response_2, true));
			} else {
			return  '';
			}
	}
//асинхронно пытаемся получить два метода сразу  php конечно не асинхронный но что делать приходиться извращаться
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
///////////////////////
   
	function getQuote($address) {
		//если курл неподключена то выкинуть ошибку
		if(!extension_loaded('curl')) {
						trigger_error('Не подключена библиотека CURL', E_USER_DEPRECATED);
		}	
		//если курл неподключена то выкинуть ошибку
	
		$this->language->load('checkout/cart');    
        $products = $this->cart->getProducts();  //получения списка продуктов с ценой и количеством
		//$this->language->load('shipping/dostavimchekaut');
		$this->load->model('setting/setting');
		$this->load->model('setting/settingdostavim');		
		//$setting_info = $this->model_setting_setting->getSetting('dostavim', 0);
		//получаем токен
		//if(isset($setting_info['dostavim_module'][0]['param1']))
		$param1 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_token');
	    $token_dostavim = $param1["value"];
		$use_order_cost_as_cod_and_assessed_dostavim = ''; //по умолчанию пустой
		//$price_dostavim2 = '';	
		//$price_dostavim3 = '';
		$individualPrice = '';	
        //$deliveryMethodsFrontName2 = '';
		//$deliveryMethodsFrontDescription2 = ''; 
        //$deliveryMethodsFrontDeliveryCostMin2 = '';
        //$deliveryMethodsFrontCountDay2 = '';
		//$pvzListLi = '';	
        //$deliveryMethodsFrontName3 = '';
		//$deliveryMethodsFrontDescription3 = ''; 
        //$deliveryMethodsFrontDeliveryCostMin3 = '';
        //$deliveryMethodsFrontCountDay3 = '';
		$innerJs = '';
		$nogrid = true;	
		$parametrs = $this->GetListParametrs($token_dostavim);
        $parametrsLength = $parametrs->deliveryMethods[3]->length;	
        $parametrsWidth = $parametrs->deliveryMethods[3]->width;	
        $parametrsHeight = $parametrs->deliveryMethods[3]->height;	
        $parametrsWeigh = $parametrs->deliveryMethods[3]->weight;
		$innerJs8 = '';
		$price_dostavim_rusha = false;
		$price_dostavim_moscow = false;
		//$min_сost_services = '';
		//$number_session = $this->customer->request->cookie["PHPSESSID"]; // если куки неподключены выкидовать ошибку
		$number_session = $this->customer->request->cookie["OCSESSID"]; // если куки неподключены выкидовать ошибку
		$time_replace = (time() + (3600 * 24 * 7));		
		$sd = $this->cache->get('dost.sd.'.$number_session); //надо ли этот вопрос	???
		$city = $this->cache->get('dost.city.'.$number_session);		
		$oldTotalPrice = $this->cache->get('dost.price1.'.$number_session);	
		
		//$this->customer->request->cookie["PHPSESSID"]; //номер сессии текущего пользователя
		//$this->session->data["shipping_methods"]["dostavimchekaut"]["quote"]["dostavimchekaut3"]["text"]; //так можно получить сессию			 
		//$this->log->write(print_r($length,true));	//так пишем логи	
		//$this->config->set('cdek_view_type', 'old'); изучитт надо
		
		
		//проверяем существует ли поле zone начало
		if(isset($address['zone'])){
			$region_dostavim = $address['zone'];
		}
		else
			trigger_error('Не включено обязательное поле регион - ошибка 6223', E_USER_DEPRECATED);	
		//проверяем существует ли поле zone конец
		
		//проверяем существует ли поле city начало
		if(isset($address['city'])){
			$city_dostavim = $address['city'];
		}
		else
			trigger_error('Не включено обязательное поле город - ошибка 2514', E_USER_DEPRECATED);
		//проверяем существует ли поле city конец		
		
		//проверяем существует ли поле address_2 начало
		if(isset($address['address_2'])){
			$address_2_pvz = $address['address_2'];
		}
		else
			trigger_error('Не включено обязательное поле Адрес (продолжение) - ошибка 2354', E_USER_DEPRECATED);
		//проверяем существует ли поле address_2 конец

	    //получение общей цены всех продуктов в корзине начало		
		$price_product = 0;
		foreach ($products as $i => $item) {
				$price_product =	$item['total']+$price_product;
		}
	    //получение общей цены всех продуктов в корзине	 конец			
		
		//проверяем есть ли данные о пвз начало
		if(stristr($address_2_pvz, '::', true)){
			$delivery_service1 = explode("::", $address_2_pvz);
			$delivery_service = $delivery_service1[1];
			$delivery_service_for_replace = $delivery_service1[1];
			$delivery_address_for_replace = '( '.$delivery_service1[0].' )';
			$delivery_service_cost = $delivery_service1[3];		
			$delivery_service_address = $delivery_service1[0];
			$delivery_service_sd = $delivery_service1[2];			
		}
		else
		{
			$delivery_service = '';
		    $delivery_service_for_replace = '';
			$delivery_address_for_replace = '';
			$delivery_service_cost = '';
			$delivery_service_address = '';
			$delivery_service_sd = '';
		}
		//проверяем есть ли данные о пвз конец			
			
		//фикс для разных версий  форматов городов начало, 
        if(isset($city_dostavim)){
			$result_replase = mb_substr($city_dostavim, 1, 2); 	
			if($result_replase == ". ")
			$city_dostavim = mb_substr($city_dostavim, 3); 
		}
		else
			trigger_error('Не включено обязательное поле город - ошибка 1324', E_USER_DEPRECATED);
		//фикс для разных версий  форматов городов конец, 
		
		
		//получаем guid начало
		$guid_сity_and_reg = $this->GetGuidCityAndRegion($token_dostavim, $city_dostavim, $region_dostavim, true);		
		$guid_сity_and_reg = json_decode($guid_сity_and_reg);
		//получаем guid	 конец		


		//если гуид получен то инициализируем его, а если нет данных то определяем это начало	
		if (isset($guid_сity_and_reg[0]->name)){
			$guid_сity_and_reg_name = $guid_сity_and_reg[0]->name;
			$town_guid_to = $guid_сity_and_reg[0]->guid;
		}
		else
		$nogrid = false;
		//если гуид получен то инициализируем его, а если нет данных то определяем  конец	

		//если город менялся то фиксируем это начало		
		if ((isset($guid_сity_and_reg_name)) and ($guid_сity_and_reg_name != $city)) //если город сменился
		{	
			$city_change = true;
		}
		else
			$city_change = false;
		//если город менялся то фиксируем это конец


		//инициаллизируем нужные переменные и считаем вес, ширину, высоту, длину и цену   начало
		$weight_dostavim = 0;
		$variable_order_cost = 0;
		$array_product_heigh_width_length = [];
		$k=0;
				
		foreach ($products as $i => $item) {
			if($item['weight_class_id']=='1'){
				if($item['weight']==0){
				$weight_dostavim	= floatval($parametrsWeigh)+floatval($weight_dostavim);	
				}
				else
				$weight_dostavim	= floatval($item['weight']*1000)+floatval($weight_dostavim);
			}
			else
			{
				if($item['weight']==0){
				$weight_dostavim	= floatval($parametrsWeigh)+floatval($weight_dostavim);
				}
				else
			    $weight_dostavim	= floatval($item['weight'])+floatval($weight_dostavim);
			}
						
				$variable_order_cost = floatval($item['total'])+floatval($variable_order_cost);
				//$productDostavim	= $item['name'].' '.$item['quantity'].'шт , '.$productDostavim; это можно записывать вкомментарий сразу
                      
			for($j=0;$j < $item['quantity'];$j++){	
						if($item['length']==0.00000000) $item['length'] = $parametrsLength;
						if($item['width']==0.00000000) $item['width'] = $parametrsWidth;
						if($item['height']==0.00000000) $item['height'] = $parametrsHeight;
					$array_product_heigh_width_length[$k] = [
						'Lenght'=> floatval($item['length']),
						'Width'=> floatval($item['width']),
						'Height'=> floatval($item['height'])
					];
					$k++;					
			}		
		}	
		//инициаллизируем нужные переменные и считаем вес, ширину, высоту, длину и цену   конец

		
		
		//если цена поменялась то фиксируем это начало		
		if ($oldTotalPrice!=$variable_order_cost) //если город сменился
		{	
			$price_change = true;
		}
		else
			$price_change = false;
		//если цена поменялась то фиксируем это конец
		
		//получаем наложку из базы начало
		$nalojka_dostavim =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_nalojka');
		//получаем наложку из базы конец
		
		//проверяем цена  товара больше чем наложка указанная в админке начало 		
		if (!empty($nalojka_dostavim['value'])){
			if ($variable_order_cost >= $nalojka_dostavim['value'])
			{				
				$use_order_cost_as_cod_and_assessed_dostavim = 'false';
			}
		}
		//проверяем цена  товара больше чем наложка указанная в админке конец 

       //if(($city_change==true)){ //если город поменялся 
	   //
	   //$this->cache->delete('dost.pvzgenerate.'.$number_session);
	   //
	   //}	   
			   


       if(($city_change==true)or($price_change==true)){ //если город поменялся или  цена 
	   
	   $this->cache->delete('dost.pvzgenerate.'.$number_session);
 
		$object_heigh_width_length = $this->goCalcWHL($array_product_heigh_width_length); //запакуем весь товар согласно алгаритму
		
		$delivery_address_for_replace = ''; //чистим загаловок если поменялась цена или город
		
	
		if (isset($town_guid_to)){ //если получин гуид города то делаем всё что ниже
		

		//если вес 0 то вес неуказан		
		if($weight_dostavim==0)
			$weight_dostavim='';
		
		//если вес меньше 1 кг то вес равен  1 кг
		if($weight_dostavim<1000)
			$weight_dostavim=1000;

		//получаем упакованные габариты	 начало		
		if (isset($object_heigh_width_length)){		
			$length = ceil($object_heigh_width_length->lenght);
			$width = ceil($object_heigh_width_length->width);
			$height = ceil($object_heigh_width_length->height);		
			$weight = $weight_dostavim;
		}
		else   //тут нужна галочка расчитывать не из личного кабинета
		{
			$length = '';
			$width = '';
			$height = '';		
			$weight = '';
		}
		//получаем упакованные габариты	 конец			
		 



        //здесь получаем все данные с сервера dostav.im асинхронно начало
		$delivery_methods_front2 = $this->GetNameAndPriceAndDescriptionANDGetFilialsPVZ($token_dostavim,$town_guid_to,$variable_order_cost,$length,$width,$height,$weight,$use_order_cost_as_cod_and_assessed_dostavim);
        //здесь получаем все данные с сервера dostav.im асинхронно	 конец
		
		if (!isset($delivery_methods_front2)){
			trigger_error('Небыло запрса к Dostav.im', E_USER_DEPRECATED);			
		}
		

        //здесь отделяем логику курьеской службы и ПВЗ (визуально чтобы незапутаться)
		$services_and_filials = $delivery_methods_front2;
        //здесь отделяем логику курьеской службы и ПВЗ (визуально чтобы незапутаться)


		
        //здесь отделяем логику курьеской службы и ПВЗ
		$deliveryMethodsFront = array_splice($delivery_methods_front2, -2);		
		$deliveryMethodsFront = json_decode(json_encode($deliveryMethodsFront));		
        //здесь отделяем логику курьеской службы и ПВЗ		
	   
         //логика форматирования
        $getfilialsPVZ = json_encode($delivery_methods_front2, true);	
		$genjson = json_decode($getfilialsPVZ);
         //логика форматирования		


        //если что-то пошло нетак то или если всё ок - начало 
		if ((!is_array($genjson)) or (empty($genjson))) {		
		    $individualPrice  = ' (Размеры выходят за допустимые значения доставка будет расчитывать индивидуально)';
			$nogrid = false;
			//----------------ошибка------------------>
			$mapsYandex = '';
		}
		else
		{

		//получение минимального значение стоиомти из всех точек ПВЗ				
		foreach ($services_and_filials as $key => $value){
				if(isset($value["cost"]))
				$min_сost_services[$key] = $value["cost"];
				if(isset($value["deliveryDurationWorkingDaysMin"]))
				$min_day_services[$key] = $value["deliveryDurationWorkingDaysMin"];
				if(isset($value["deliveryDurationWorkingDaysMax"]))
				$max_day_services[$key] = $value["deliveryDurationWorkingDaysMax"];
		}
	
			//var_dump($min_сost_services);
	
		if (isset($min_сost_services))
			$min_сost_services_sort = min($min_сost_services);
		else
			$min_сost_services_sort = '';
	
		if (isset($min_day_services))	
			$min_day_pvz = min($min_day_services);
		else
			$min_day_pvz = '';
	
		if (isset($max_day_services))		
			$max_day_pvz = max($max_day_services);
		else
			$max_day_pvz = '';			
		
		$listProv = $this->GetListProvider();  //- получил список служб доставки 

		
		$pvzListLi  = $this->GenerateListPvz($getfilialsPVZ, $listProv);  //сгенерировать список служб доставки
		 
		$innerJs2 = 'jQuery(function(){$("ul.pvz-list").append("'.addslashes($pvzListLi).'");});';  //jquery скрипт который вставляет список sd
		 
		$this->cache->delete('dost.pvz.'.$number_session);	//удалить кешь списка ПВЗ			
		$this->cache->set('dost.pvz.'.$number_session.'.'.$time_replace, $innerJs2);//запись в кешь	 списка ПВЗ	
		//file_put_contents(DIR_CACHE . 'cache.dost.pvz.'.$number_session.'.'.$time_replace, serialize($innerJs2)); //можно и так 
		 	 
		$pvzYandex  = $this->GenerateListPvzForYandex($getfilialsPVZ, $listProv); // генерируем список ПВЗ для яндекс карт

		$mapsYandex  = $this->initDostavim(json_encode($pvzYandex[0][0]), 10, $listProv, $pvzYandex); // генерируем яндекс карты
		 
		$checkboxs  = $this->GenerateCheckbox($listProv);  // генерируем чекбоксы для яндекс карты

		$innerJs3 = $mapsYandex.$checkboxs;  //собираем скрипт яндекс карт	для выбора ПВЗ 
		
		//var_dump($innerJs3);

		$this->cache->delete('dost.map.'.$number_session);		//удалить кешь генерации яндекс карт	 
		$this->cache->set('dost.map.'.$number_session.'.'.$time_replace, $innerJs3); //запись в кешь яндекс карту
		//file_put_contents(DIR_CACHE . 'cache.dost.shipping.mappvz.' . (time() + (3600 * 24 * 7)), serialize($innerJs3));//можно и так		
		}
        //если что-то пошло нетак то или если всё ок  - конец 
	   
		//проверяем существует ли поле address_2 начало
		if((isset($deliveryMethodsFront->deliveryMethods[3]->name))and(isset($deliveryMethodsFront->deliveryMethods[2]->name))){
			$data_dostavim = true;
		}
		else
			trigger_error('Данные из версиса Distav.im неполучены - ошибка 3242', E_USER_DEPRECATED);
		//проверяем существует ли поле address_2 конец
	
	   	if(isset($data_dostavim)){
		
	    $deliveryMethodsFrontName2 = $deliveryMethodsFront->deliveryMethods[2]->name;// получаем название доставки ПВЗ
	    $deliveryMethodsFrontDescription2 = $deliveryMethodsFront->deliveryMethods[2]->description; // получаем описание доставки ПВЗ
	    $deliveryMethodsFrontDeliveryCostMin2 = $min_сost_services_sort; // получаем мин. цену доставки ПВЗ
		$deliveryMethodsFrontCountDay2 = $max_day_pvz;  //максимальное количество дней ПВЗ
		$deliveryMethodsFrontCountDaymin2 = $min_day_pvz;  //минимальное количество дней ПВЗ
		
	    $deliveryMethodsFrontName3 = $deliveryMethodsFront->deliveryMethods[3]->name;// получаем название доставки курьерка
	    $deliveryMethodsFrontDescription3 = $deliveryMethodsFront->deliveryMethods[3]->description; // получаем описание доставки курьерка
	    $deliveryMethodsFrontDeliveryCostMin3 = $deliveryMethodsFront->deliveryMethods[3]->deliveryCostMin; // получаем мин. цену доставки курьерка 
		$deliveryMethodsFrontCountDay3 = $deliveryMethodsFront->deliveryMethods[3]->deliveryDurationWorkingDaysMax; //максимальное количество дней курьерка
		$deliveryMethodsFrontCountDaymin3 = $deliveryMethodsFront->deliveryMethods[3]->deliveryDurationWorkingDaysMin;	  //минимальное количество дней курьерка
		
	    $deliveryMethodsFrontName4 = $deliveryMethodsFront->deliveryMethods[4]->name;// получаем название доставки почта
	    $deliveryMethodsFrontDescription4 = $deliveryMethodsFront->deliveryMethods[4]->description; // получаем описание доставки почта
	    $deliveryMethodsFrontDeliveryCostMin4 = $deliveryMethodsFront->deliveryMethods[4]->deliveryCostMin; // получаем мин. цену доставки почта
		$deliveryMethodsFrontCountDay4 = $deliveryMethodsFront->deliveryMethods[4]->deliveryDurationWorkingDaysMax; //максимальное количество дней почта
		$deliveryMethodsFrontCountDaymin4 = $deliveryMethodsFront->deliveryMethods[4]->deliveryDurationWorkingDaysMin;	  //минимальное количество дней почта
		
		
	    //переопределяю только чтобы сократить длину перменной начало
		$price_dostavim2 = $deliveryMethodsFrontDeliveryCostMin2;	
		$price_dostavim3 = $deliveryMethodsFrontDeliveryCostMin3;		
		$price_dostavim4 = $deliveryMethodsFrontDeliveryCostMin4;	
	    //переопределяю только чтобы сократить длину перменной конец
		
		
		 
			//обнуляем  город
			$this->cache->delete('dost.city.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.city.'.$number_session.'.'.$time_replace, $city_dostavim); //запись в кешь яндекс карту	
			$this->cache->delete('dost.sd.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.sd.'.$number_session.'.'.$time_replace, $delivery_service); //запись в кешь яндекс карту		
			//обнуляем сд	
		

			//===========================================================================================================================			
			$this->cache->delete('dost.price3.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.price3.'.$number_session.'.'.$time_replace, $price_dostavim2); //запись в кешь яндекс карту	
			$this->cache->delete('dost.name3.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.name3.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontName2); //запись в кешь яндекс карту	
			$this->cache->delete('dost.description3.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.description3.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontDescription2); //запись в кешь яндекс карту	
			$this->cache->delete('dost.day3.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.day3.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontCountDay2); //запись в кешь яндекс карту				
			$this->cache->delete('dost.day_min3.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.day_min3.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontCountDaymin2); //запись в кешь яндекс карту				
			//===========================================================================================================================			
			$this->cache->delete('dost.price4.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.price4.'.$number_session.'.'.$time_replace, $price_dostavim3); //запись в кешь яндекс карту	
			$this->cache->delete('dost.name4.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.name4.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontName3); //запись в кешь яндекс карту	
			$this->cache->delete('dost.description4.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.description4.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontDescription3); //запись в кешь яндекс карту				
			$this->cache->delete('dost.day4.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.day4.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontCountDay3); //запись в кешь яндекс карту	
			$this->cache->delete('dost.day_min4.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.day_min4.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontCountDaymin3); //запись в кешь яндекс карту	
			//===========================================================================================================================			
			$this->cache->delete('dost.price5.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.price5.'.$number_session.'.'.$time_replace, $price_dostavim4); //запись в кешь яндекс карту	
			$this->cache->delete('dost.name5.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.name5.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontName4); //запись в кешь яндекс карту	
			$this->cache->delete('dost.description5.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.description5.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontDescription3); //запись в кешь яндекс карту				
			$this->cache->delete('dost.day5.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.day5.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontCountDay4); //запись в кешь яндекс карту	
			$this->cache->delete('dost.day_min5.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.day_min5.'.$number_session.'.'.$time_replace, $deliveryMethodsFrontCountDaymin4); //запись в кешь яндекс карту				
	//===========================================================================================================================				
	   }
	   else
		   $nogrid = false;
	}
		
		}
			
		

        $price_dostavim2 =  $this->cache->get('dost.price3.'.$number_session); 

        $deliveryMethodsFrontName2 =  $this->cache->get('dost.name3.'.$number_session);	
        $deliveryMethodsFrontDescription2 =  $this->cache->get('dost.description3.'.$number_session);	
        $deliveryMethodsFrontCountDay2 =  $this->cache->get('dost.day3.'.$number_session);
        $deliveryMethodsFrontCountDaymin2 =  $this->cache->get('dost.day_min3.'.$number_session);
        $price_dostavim3 =  $this->cache->get('dost.price4.'.$number_session);	
        $deliveryMethodsFrontName3 =  $this->cache->get('dost.name4.'.$number_session);
        $deliveryMethodsFrontDescription3 =  $this->cache->get('dost.description4.'.$number_session);
        $deliveryMethodsFrontCountDay3 =  $this->cache->get('dost.day4.'.$number_session);
        $deliveryMethodsFrontCountDaymin3 =  $this->cache->get('dost.day_min4.'.$number_session);
		
		//===========================================================================================================================			
        $price_dostavim4 =  $this->cache->get('dost.price5.'.$number_session);
        $deliveryMethodsFrontName4 =  $this->cache->get('dost.name5.'.$number_session);	
        $deliveryMethodsFrontDescription4 =  $this->cache->get('dost.description5.'.$number_session);
        $deliveryMethodsFrontCountDay4 =  $this->cache->get('dost.day5.'.$number_session);
        $deliveryMethodsFrontCountDaymin4 =  $this->cache->get('dost.day_min5.'.$number_session);
		//===========================================================================================================================			
	
		$this->cache->delete('dost.price1.'.$number_session);		//удалить кешь генерации яндекс карт	 
		$this->cache->set('dost.price1.'.$number_session.'.'.$time_replace, $variable_order_cost); //запись в кешь яндекс 
		//записываем цену всего заказа
		 
		//бесплатная доставка	
		$rusha_dostavim =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_rusha');
		$moscow_dostavim =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_moscow');
		$piter_dostavim =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_piter');	 //16.04.2018
		
		//наценка доставка	
		$rusha_dostavim_markup =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_rusha_markup');	 //16.04.2018
		$moscow_dostavim_markup =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_moscow_markup');	 //16.04.2018
		$piter_dostavim_markup =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_piter_markup');	 //16.04.2018


//16.04.2018
		if (!empty($rusha_dostavim_markup["value"])){
			if (($city_dostavim!='Москва')and($city_dostavim!='Санкт-Петербург')){
					$price_dostavim2 = $price_dostavim2+$rusha_dostavim_markup["value"];
					$price_dostavim3 = $price_dostavim3+$rusha_dostavim_markup["value"];
					$price_dostavim4 = $price_dostavim4+$rusha_dostavim_markup["value"];
						}					
		}
		
		
				
				
			
		//echo '<pre>';
		//echo 'отработало';
		//echo '</pre>';			
		
		
		
		if (!empty($moscow_dostavim_markup["value"])){
			if ($city_dostavim=='Москва'){
					$price_dostavim2 = $price_dostavim2+$moscow_dostavim_markup["value"];
					$price_dostavim3 = $price_dostavim3+$moscow_dostavim_markup["value"];
					$price_dostavim4 = $price_dostavim4+$moscow_dostavim_markup["value"];	
						}					
		}
		if (!empty($piter_dostavim_markup["value"])){
			if ($city_dostavim=='Санкт-Петербург'){
					$price_dostavim2 = $price_dostavim2+$piter_dostavim_markup["value"];
					$price_dostavim3 = $price_dostavim3+$piter_dostavim_markup["value"];
					$price_dostavim4 = $price_dostavim4+$piter_dostavim_markup["value"];	
			}					
		}
		//если не пустая бесплатная доставка по россии
		if (!empty($rusha_dostavim["value"])){
			if (($city_dostavim!='Москва')and($city_dostavim!='Санкт-Петербург')){
				if($price_product >= $rusha_dostavim["value"])
				{
					$price_dostavim2 = 0;
					$price_dostavim3 = 0;
					$price_dostavim4 = 0;
					$price_dostavim_rusha = true;
				}			
			}		
		}

		//если не пустая бесплатная доставка по Москве		
		if (!empty($moscow_dostavim["value"])){
			if ($city_dostavim=='Москва'){
				if($price_product >= $moscow_dostavim["value"])
				{
					$price_dostavim2 = 0;
					$price_dostavim3 = 0;
					$price_dostavim4 = 0;
					$price_dostavim_moscow = true;
				}			
			}		
		}
		
		//если не пустая бесплатная доставка по Питеру		/16.04.2018
		if (!empty($piter_dostavim["value"])){
			if ($city_dostavim=='Санкт-Петербург'){
				if($price_product >= $piter_dostavim["value"])
				{
					$price_dostavim2 = 0;
					$price_dostavim3 = 0;
					$price_dostavim4 = 0;
					$price_dostavim_piter = true;
				}			
			}		
		}
		
	
	    $delivery_service_address = 'Выберите пункт самовывоза';

	
	

		//- тут надо записывать цену		
	/////////////////////////////////////////////////////////////////
	    $vidgetContent0 = '<main id="nameDostavimID" role="main" style="position: relative;width:100%" ><form action="" class="vidget-form"><div id="topLoader" style="position: absolute;left: 30%;display:none;z-index: 99999;"></div></form></main>';
		
	    $vidgetContent2 = '<span><a href="#" class="pvz-list-link"><img src="img/map-pin.svg" alt="">'.$delivery_service_address.'</a><div class="pvz-div" style="display:none"><input type="text" class="address-filter" pvzId="" pvzGuid="" pvzCode="" costSD="" serviceId=""><ul class="pvz-list" tabindex="0">';

		$vidgetContent3 = '<script type="text/javascript" src="/get_url.js"></script><script type="text/javascript" src="/get_url_pvz.js"></script>';  //18.04.2019
	
		$vidgetContent4 = '	</ul></div><a href="#" class="pvz-map-link" style="margin-left: 10%;" id="pvz-dostavim" data-target="#exampleModal"><img src="img/map.svg" alt="">Карта</a><span>';

	
		$vidgetContent5 = '<div  id="exampleModal" style="display:none" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"> <!--вывод идет с помощью дата таргет--> <div class="modal-dialog" role="document"><div class="modal-content"> <div class="modal-header"> <p class="modal-title" id="exampleModalLabel">Пункты самовывоза на карте</p><button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button> </div><div class="modal-body"> <div class="delivery-service-filter">  <div class="delivery-service-list"> <h5>Службы доставки</h5>  <ul class="ds-list"></ul> </div></div><div id="mapdostavim"></div></div>          </div></div></div><div id="overlayDostavim"></div> <div id="modal_form"><span id="modal_close">X</span><p id="errorModalDostavimTitle"></p><hr><p id="errorModalDostavim"></p><br><br><br><span style="    clear: both; float: none;text-align: center;padding: 5%;color: red;background: #333;margin-left: 40%;"  class="close" data-dismiss="modal" aria-label="Close" >ОК</span></div><div id="overlay"></div><script>main()</script>';
		
		$vidgetScript = '';

 				
		$shipping_pvz = $this->cache->get('dost.pvz.'.$number_session);	
		$shipping_map = $this->cache->get('dost.map.'.$number_session);
		
		$this->createjsPvz($shipping_pvz);		
		$this->createJsDsoatavim($shipping_map);
		
		//var_dump($shipping_map);
		 	
		
		$getCasheDostavimchekaut0 = $this->cache->get('dost.pvzgenerate.'.$number_session);

		if(!empty($getCasheDostavimchekaut0)){
			$pvzgenerate = $getCasheDostavimchekaut0;
			}
		else 
		$pvzgenerate = '<hr class="hidepvz"><div  class="hidepvz" style="margin-right:10px;margin-left: 30px;color:#999"><span>Адрес: </span> <span id="dostadress"> не указан</span></br><hr class="hidepvz"><span>Служба доставки: </span> <span id="dostmysd"> не указана</span></br><hr class="hidepvz"><span>Телефон:</span> <span id="dostphone">не указан </span></br><hr class="hidepvz"><span>Режим работы:</span> <span id="dosttime"> не указан</span></br> <span style="display:none">Макс дн:</span> <span style="display:none" id="dostday_max">Нет данных по макс.  дн</span><hr class="hidepvz" style="display:none"><span style="display:none">Мин дн:</span> <span id="dostday_min" style="display:none">Нет данных по мин.  дн</span><hr class="hidepvz" style="display:none"><span style="display:none">Цена:</span> <span id="dostprice" style="display:none">Нет данных по цене</span><hr class="hidepvz" style="display:none"></div></br><hr class="hidepvz">';
	

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

		if (isset($status)) {
			$quote_data = array();
			$sort_order = array();				
			

			foreach($multideliverys as $i => $delivery) {
				if(!$delivery['status']) {
					continue;
				}			
				

			if($i==1){			
			$quote_data['dostavimchekaut1'] = array(
				'code'         => 'dostavimchekaut.dostavimchekaut1',
				'title'        => '<label><span id="nameChoice1"><span/><br><span style="color:#999"></span></label>',
				'cost'         => 0, 
				'tax_class_id' => $delivery['tax_class_id'],
				'text' => $this->currency->format($this->tax->calculate(0, $delivery['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
			);	
			$sort_order[1] = $delivery['sort_order'];
				continue;
			
			}	
				
			if($i==2){
			$quote_data['dostavimchekaut2'] = array(
				'code'         => 'dostavimchekaut.dostavimchekaut2',
				'title'        => '<label><span id="nameChoice2"><span/><br><span style="color:#999"></span></label>',
				'cost'         => 0,
				'tax_class_id' => $delivery['tax_class_id'],
				'text' => $this->currency->format($this->tax->calculate(0, $delivery['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
			);	
			$sort_order[2] = $delivery['sort_order'];
				continue;
			
			}		
			
					
						
			if($i==3){	
				if ($nogrid==true){	
				$quote_data['dostavimchekaut3'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut3',
					'title'        => $deliveryMethodsFrontName2.' '.$deliveryMethodsFrontDescription2.' от '.$deliveryMethodsFrontCountDaymin2.' до '.$deliveryMethodsFrontCountDay2.' дн  <b>'.$delivery_address_for_replace.' </b>',
					'cost'         => $price_dostavim2,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $this->currency->format($this->tax->calculate($price_dostavim2, $delivery['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
					);
				
				
				$sort_order[3] = $delivery['sort_order'];
				$fori6 = $delivery['sort_order'];	

				//.$vidgetContent3
				$quote_data['dostavimchekaut0'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut0',
					//'title'        => $vidgetContent2.$vidgetContent4.$vidgetContent0.'<br>'.$vidgetContent5.$vidgetContent3.'<span id="tokenDostavim" style="display:none">'.$token_dostavim.'</span>'.$shipping_pvz.$shipping_map.$pvzgenerate.'<script>jQuery(function(){$("input[value=\'dostavimchekaut.dostavimchekaut0\']").remove();});</script>',
					'title'        => $vidgetContent2.$vidgetContent4.$vidgetContent0.'<br>'.$vidgetContent5.'<span id="tokenDostavim" style="display:none">'.$token_dostavim.'</span>'.$pvzgenerate.'<script>jQuery(function(){$("input[value=\'dostavimchekaut.dostavimchekaut0\']").remove();});</script>'.$vidgetContent3,
					'cost'         => 0,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $this->currency->format($this->tax->calculate(0, $delivery['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
				
				
				////$sort_order[6] = $fori6+1;		
				$sort_order[100] = $delivery['sort_order']+1;
											
			
				
				}
					continue;			
			}			
			

			if($i==4){
				if ($nogrid==true){    //если guid неполучен влияет напрямую и 
				$quote_data['dostavimchekaut4'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut4',
					'title'        => $deliveryMethodsFrontName3.' '.$deliveryMethodsFrontDescription3.' '.$deliveryMethodsFrontCountDay3.' дн ',
					'cost'         => $price_dostavim3,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $this->currency->format($this->tax->calculate($price_dostavim3, $delivery['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
				
				$sort_order[4] = $delivery['sort_order'];	
				}			
					continue;
			}
						
			
			

			if($i==5){
			if($weight_dostavim<=5000){
				if ($nogrid==true){    //если guid неполучен влияет напрямую и 
				$quote_data['dostavimchekaut5'] = array(
					'code'         => 'dostavimchekaut.dostavimchekaut5',
					'title'        => $deliveryMethodsFrontName4.' '.$deliveryMethodsFrontDescription4.' '.$deliveryMethodsFrontCountDay4.' дн ',
					'cost'         => $price_dostavim4,
					'tax_class_id' => $delivery['tax_class_id'],
					'text' => $this->currency->format($this->tax->calculate($price_dostavim4, $delivery['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
				$sort_order[5] = $delivery['sort_order'];				
				}			
					continue;
			   }
			     continue;
		    }	

						

			
			if(($i!=1)or($i!=2)or($i!=3)or($i!=4)or($i!=5)){
				
				if (($price_dostavim_rusha == true) or ($price_dostavim_moscow == true) or ($price_dostavim_piter == true)){
					$delivery['cost'] = 0;
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
				
			
			}	

			if (isset($quote_data['dostavimchekaut3']['title'])){	
				$quote_data['dostavimchekaut3']['title'] = $quote_data['dostavimchekaut3']['title'];			
				$quote_data['dostavimchekaut3']['text'] = $quote_data['dostavimchekaut3']['text'];		
			}

			if (isset($quote_data['dostavimchekaut4']['title'])){			
				$quote_data['dostavimchekaut4']['text'] = $quote_data['dostavimchekaut4']['text'];					
			}	
			
			if (isset($quote_data['dostavimchekaut5']['title'])){							
				$quote_data['dostavimchekaut5']['text'] = $quote_data['dostavimchekaut5']['text'];
			}			

			
			array_multisort($sort_order, SORT_ASC, $quote_data);
			
			
			

			$method_data = array(
				'code'       => 'dostavimchekaut',
				'title'      => 'Доставка от Dostav.im'.' '.$individualPrice,//.$,
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_dostavimchekaut_sort_order'),
				'error'      => false
			);
	
			
		}
		
		
		return $method_data;
	}
}
?>