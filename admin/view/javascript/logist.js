     //var customerId = localStorage.getItem('customerId');
     var customerId = $('#token').val(); //сюда вставить полученный токен
    jQuery(function(){   		
	if (customerId=='')  //СМЕНИТЬ НА РАВНО
	{
     $('main.settings').hide();              
     $('#module').hide();  	
	}
	else
	{          
	 $('#divFormDostavim').hide();
	}	
    });
	
//var orderId = localStorage.getItem('orderId');	
var orderId = $('#dostdostavimorderid').text().substr(1);
var cityGuid = "";
//var widgetHost = "http://localhost:1888";
var widgetHost = "https://api.dostav.im";
var tariffsArray = [];
var chosenIndex = "";
var blockNumber = 0;
var options2 = [];
var suggest_count = 0;
var input_initial_value = '';
var suggest_selected = 0;
//var customerId = "";
//var orderId = "";
//if (!customerId) {    
//    customerId = 'a5210296-58ad-4620-9e1d-b5e638217080';
//};
//if (!orderId) {    
//   orderId = 7; 
//};

console.log('customerId : ' + customerId + '; orderId : ' + orderId);

	
	
//здесь мы берем стандартную функцию live и адаптируем под все версии Jquery
jQuery.fn.extend({
    live: function (event, callback) {
       if (this.selector) {
            jQuery(document).on(event, this.selector, callback);
        }
        return this;
    }
});

$(function(){
    
    // Ограничение ввода данных в числовые поля
    function digitInput(inputCount, multiplicity) {
        var cursorPosition = 0;
        var yesOneDot = false;
        inputCount.on('keyup', function () {
            cursorPosition = jQuery(this).getCursorPosition();

            var countCurrent = inputCount.val().replace(",",".");
            console.log(countCurrent.indexOf('.') + 1);            
            if ((countCurrent.indexOf('.')+1) == 0){
                inputCount.val(countCurrent.replace(/[^\d\.]{1}/g, ''));
            } else {                
                if (yesOneDot) {
                    inputCount.val(countCurrent.slice(0, -1));
                } else {
                    inputCount.val(countCurrent.replace(/[^\d+\.]{1}/g, ''));
                };
                yesOneDot = true;
            };
            if (/^\d+\.\d+$/.test(countCurrent.toString())) {
                var countUpdated = Math.round(countCurrent / multiplicity) * multiplicity;
                if (inputCount.attr('id') == "order-weight") {
                    inputCount.val(countUpdated.toFixed(1));
                } else {
                    inputCount.val(countUpdated);
                };
            }
            if (inputCount.attr('id') == "order-weight") {
                if (inputCount.val() > 2500) inputCount.val(2500);
            } else if (inputCount.attr('id') == "length4") {
                if (inputCount.val() > 1200) inputCount.val(1200);
            } else if (inputCount.attr('id') == "width4") {
                if (inputCount.val() > 240) inputCount.val(240);
            } else if (inputCount.attr('id') == "height4") {
                if (inputCount.val() > 220) inputCount.val(220);
            }
            jQuery(this).setCursorPosition(cursorPosition);
        });
    }

    jQuery(function () {
        var inputCount1 = jQuery('#order-weight');
        digitInput(inputCount1, 0.1);
        var inputCount2 = jQuery('#length4');
        digitInput(inputCount2, 1);
        var inputCount3 = jQuery('#width4');
        digitInput(inputCount3, 1);
        var inputCount4 = jQuery('#height4');
        digitInput(inputCount4, 1);
    });

    (function (jQuery) {
        jQuery.fn.getCursorPosition = function () {
            var input = this.get(0);
            if (!input) return;
            if ('selectionStart' in input) {
                return input.selectionStart;
            } else if (document.selection) {
                input.focus();
                var sel = document.selection.createRange();
                var selLen = document.selection.createRange().text.length;
                sel.moveStart('character', -input.value.length);
                return sel.text.length - selLen;
            }
        }
    })(jQuery);

    jQuery.fn.setCursorPosition = function (pos) {
        this.each(function (index, elem) {
            if (elem.setSelectionRange) {
                elem.setSelectionRange(pos, pos);
            } else if (elem.createTextRange) {
                var range = elem.createTextRange();
                range.collapse(true);
                range.moveEnd('character', pos);
                range.moveStart('character', pos);
                range.select();
            }
        });
        return this;
    };
    
    function errToString(msg, separator = "<br/>") {
       // Или так:
       var resp = JSON.parse(msg.responseText);
       // Или так:
       //var resp = msg.responseJSON;
       // В обоих случаях надо чтобы ЙСОН в ответ приходил.

       var errors = [];
       for (var key in resp) {
           if (resp.hasOwnProperty(key)) {
               var errs = resp[key];
               if (Array.isArray(errs)) {
                   errs.forEach(function (entry) {
                       errors.push(entry);
                   });
               } else {
                   //errors.push(errs);
                   errors.push(JSON.stringify(errs));
               };
           };
       };
       var resultMsg = errors.join(separator);
       return resultMsg;
    };
    
    function getOrder(customerId, orderId){
        jQuery.ajax({                    
            url: widgetHost + "/StoreWidget/OrderDetails?orderId="+orderId,   
			//timeout:30000,
            headers: {
              authorization: customerId
            },
            data: {},//JSON.stringify(formData),
            success: function (data)
            {
                console.log(data);
                var infoRow = jQuery('.info-row').children().map(function(){
                    return jQuery(this);
                }).get();
                //console.log(infoRow);
                cityGuid = data.order.clientCityGuid;
                blockNumber = data.deliveryMethodBlock.number;
				jQuery('#numberDost').attr('namber', blockNumber);  
                var timeContent = data.order.createdAt.split('.')[0];
                infoRow[0].text("");
                infoRow[1].text(timeContent.split('T')[0] + " " + timeContent.split('T')[1]);
                infoRow[2].text(data.order.deliveryCost);                
                infoRow[3].text(data.order.clientCity);
                infoRow[4].text(data.deliveryMethodBlock.name);                
                infoRow[5].text(data.order.clientAddress);
                infoRow[6].text(data.order.deliveryPointId);
                infoRow[7].text(data.order.clientFullName);
                infoRow[8].text(data.order.clientPhone);
                infoRow[9].text(data.order.statusStr);                
                jQuery('.comment').html('<strong class="comment-title">Комментарий:</strong> <br>'+data.order.clientCommentary);
                jQuery('#order-weight').val(data.order.weight/1000);
                jQuery('#length4').val(data.order.length);
                jQuery('#width4').val(data.order.width);
				//наложка и объявленная стоимость
				jQuery('#order-ac').val(data.order.assessedCost);  //объявленная стоимость
				jQuery('#order-cod').val(data.order.cod);  //наложенный платеж
				//наложка и объявленная стоимость
                jQuery('#height4').val(data.order.height);
                jQuery('#order-sum').val(data.order.orderCost);
                jQuery('.order-number').text(data.order.orderNumber);
                if ((data.order.status == 1) && (!data.order.length) && (!data.order.height) && (!data.order.width) && (!data.order.weight)) {
                    var tempObj = {};
                    tempObj.orderId =  orderId;
                    tempObj.length = data.deliveryMethodBlock.length;
                    tempObj.width = data.deliveryMethodBlock.width;
                    tempObj.height = data.deliveryMethodBlock.height;
                    tempObj.weight = data.deliveryMethodBlock.weight;
                    tempObj.cod = $('#order-cod').val();
                    tempObj.assessedCost = $('#order-ac').val();
                    tempObj.recipientHome = "4";
                    tempObj.recipientStreet = "test";
                    tempObj.recipientStreetCladr = "testCladr";
                    tempObj.recipientStreetGuid = "testStreetGuid";
                    tempObj.recipientCorpus = "2";
                    tempObj.recipientBuilding = "3";
                    tempObj.recipientApartment = "2";
                    getTariffs(customerId, tempObj);
                    $('#sizes-info span:first').text(' размеров: ' + tempObj.length + 'x' + tempObj.width + 'x' + tempObj.height + ' см и веса: ' + (tempObj.weight/1000) + ' кг');
                    $('#sizes-info').show();
                } else {
                    $('#sizes-info').hide();
                };                
            },
            error : function (msg) {                
                return console.log(msg);
            },
            timeout: 30000
        }); 
    };
        
    function getTariffs(customerId, data) {
        $('.main-div').append('<div id="preloader"><img src="img/preloader3.gif"></div>')
        console.log(data);    
        jQuery.ajax({                    
            url: widgetHost + "/StoreWidget/OrderCalculate",
            type: 'POST',
            contentType: 'application/json',
            dataType: "json",             
            headers: {
              authorization: customerId
            },
            data: JSON.stringify(data),
            success: function (got)
            {
                console.log(got);   
                tariffsArray = got;
                $('.delivery-services-table tbody').empty();
                var firstLine = '<tr><th></th><th>Служба доставки</th><th>Скорость доставки, дн</th><th>Тариф</th></tr>'
                $('.delivery-services-table tbody').append(firstLine);
                for (var i=0; i < got.length; i++) {
                    for (var j=0; j <got[i].tariffs.length; j++) {
                        var tempString = '<tr><td><label for="serviceChoice' + i + '.' + j + '" class="i-checks"><input type="radio" class="serviceChoice" id="serviceChoice' + i + '.' + j + '" name="order-service" index="' + i + '.' + j + '"><i></i></label></td><td>'+got[i].deliveryServiceName+'</td><td>'+got[i].tariffs[j].fullDuration+'</td><td>'+got[i].tariffs[j].cost+'</td></tr>';   
                        $('.delivery-services-table tbody').append(tempString);
                    };
                };
                
                $('#preloader').remove();
                
                $('.serviceChoice').change(function(){
                    var cost1 = +$('.order-cost').text();
                    var cost2 = +$(this).parent().parent().parent().children('td:nth-child(4)').text();
                    $('.order-delta-span').text(Math.ceil(cost2 - cost1) + ' руб.');       
                    chosenIndex = $(this).attr('index');
                });
            },
            error : function (msg) { 

			
                //$('#modal-error').modal({});			
                //$('#modal-error .modal-body p').html(errToString(msg));
                $('#preloader').remove();
                //$('#modal-error').show();                

			jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
			jQuery('#errorModalDostavimTitle').text('Сообщение об ошибке');			
			jQuery('#errorModalDostavim').text(errToString(msg));
				$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});				
                console.log(msg); 				
            },
            timeout: 30000
        });         
    };
 

 
    //jQuery("#order-street").autocomplete({
    //    source: function (request, response) {
    //        jQuery.ajax({
    //            url: widgetHost + "/Address/GetStreets/",
    //            dataType: "json",
    //            data: {
    //                name: request.term,
    //                guid: cityGuid
    //            },
    //            success: function (data) {
    //                var options = [];                    
    //                jQuery.each(data, function (i, item) {
    //                    options.push({ id: item.guid, code: item.code, value: item.typeName + " " + item.name });
    //                });
    //                response(options);
    //            }
    //        });
    //    },
    //    minLength: 2,
    //    select: function (event, selected) {
    //        jQuery(this).attr("cladr", selected.item.code.trim());
    //        jQuery(this).attr("guid", selected.item.id.trim());
    //    }
    //});
//===========================================================================================
//===================================order-street===================================================
$("#order-street").keyup(function(I){
		// определяем какие действия нужно делать при нажатии на клавиатуру
		switch(I.keyCode) {
			// игнорируем нажатия на эти клавишы
			case 13:  // enter
			case 27:  // escape
			case 38:  // стрелка вверх
			case 40:  // стрелка вниз
			break;

			default:
				// производим поиск только при вводе более 2х символов
				if($(this).val().length>2){

					input_initial_value = $(this).val();
					// производим AJAX запрос к /ajax/ajax.php, передаем ему GET query, в который мы помещаем наш запрос
					
					//============================================================================================				
            jQuery.ajax({
                url: widgetHost + "/Address/GetStreets/",
                dataType: "json",
                minLength: 3,
                data: {
                    name: $(this).val(),
                    guid: cityGuid
                },
                success: function (data) {   
						var list = data;
						suggest_count = list.length;
						//var options2 = [];
						if(suggest_count > 0){
							// перед показом слоя подсказки, его обнуляем
							$("#search_advice_wrapper").html("").show();
							console.log(list);
							for(var i in list){							
									// добавляем слою позиции
									$('#search_advice_wrapper').append('<div code="'+list[i].code+'" id="'+list[i].guid+'" class="advice_variant">'+list[i].name+' - '+list[i].typeName+'</div>');
									options2.push({ id: list[i].guid, code: list[i].code, value: list[i].typeName + " " + list[i].name});															
							}
							//console.log(options2);

							
						}
                }                
            });				
					//============================================================================================
					
					
				}
			break;
		}
	});

	//считываем нажатие клавишь, уже после вывода подсказки
	$("#order-street").keydown(function(I){
		switch(I.keyCode) {
			// по нажатию клавишь прячем подсказку
			case 13: // enter
			case 27: // escape
				$('#search_advice_wrapper').hide();
				return false;
			break;
			// делаем переход по подсказке стрелочками клавиатуры
			case 38: // стрелка вверх
			case 40: // стрелка вниз
				I.preventDefault();
				if(suggest_count){
					//делаем выделение пунктов в слое, переход по стрелочкам
					key_activate( I.keyCode-39 );
				}
			break;
		}
	});

	// делаем обработку клика по подсказке
	$('.advice_variant').live('click',function(){

		$('#order-street').val($(this).text());	
		var deliveryCodeDost = jQuery(this).attr("code");
		var deliveryIdDost = jQuery(this).attr("id");	
        jQuery('#order-street').attr("guid", deliveryIdDost); //сюда неприходит ИД
        jQuery("#order-street").attr("cladr", deliveryCodeDost.trim());
        //jQuery("#order-street").attr("guid", odeliveryIdDost);

		// прячем слой подсказки
		$('#search_advice_wrapper').fadeOut(350).html('');
	});

	// если кликаем в любом месте сайта, нужно спрятать подсказку
	$('html').click(function(){
		$('#search_advice_wrapper').hide();
	});
	// если кликаем на поле input и есть пункты подсказки, то показываем скрытый слой
	$('#order-street').click(function(event){
		//alert(suggest_count);
		if(suggest_count)
			$('#search_advice_wrapper').show();
		event.stopPropagation();
	});


function key_activate(n){
	$('#search_advice_wrapper div').eq(suggest_selected-1).removeClass('active');

	if(n == 1 && suggest_selected < suggest_count){
		suggest_selected++;
	}else if(n == -1 && suggest_selected > 0){
		suggest_selected--;
	}

	if( suggest_selected > 0){
		$('#search_advice_wrapper div').eq(suggest_selected-1).addClass('active');
		$("#order-street").val( $('#search_advice_wrapper div').eq(suggest_selected-1).text() );
		//чтобы отрабатвал  интер
		    var deliveryCodeDost = $('#search_advice_wrapper div').eq(suggest_selected-1).attr("code");
		    var deliveryIdDost = $('#search_advice_wrapper div').eq(suggest_selected-1).attr("id");	
            jQuery('#order-street').attr("guid", deliveryIdDost); //сюда неприходит ИД
            jQuery("#order-street").attr("cladr", deliveryCodeDost.trim());		
		//чтобы отрабатвал  интер
	} else {
		$("#order-street").val( input_initial_value );
	}
}	

//===========================================================================================	
    
    function getLocationYandex(lat, lon) {
        $.ajax({
            type: "GET",
            url: "https://geocode-maps.yandex.ru/1.x/?geocode="+lon+","+lat ,
            dataType: "xml",
            success: function(xml){
                var xmlDoc = $(xml);
                var kakodemon = $(xmlDoc).children().children().children('featureMember:nth-child(2)').children('GeoObject').children('metaDataProperty').children('GeocoderMetaData').children('AddressDetails').children('Country').children('AddressLine').text();
                console.log(kakodemon);
            },
            error: function() {
                alert("An error occurred while processing XML file.");
            }
        });
    }
    
    function getCountry() {
        $.getJSON("http://ip-api.com/json/?callback=?", function(data) {           
            console.log(data);
            console.log(data.country + ", " + data.city + ", IP-address: " + data.query); 
            getLocationYandex(data.lat,data.lon);
        });    
    };    
        
    function confirmOrderFinal(customerId, data) {
        jQuery.ajax({                    
            url: widgetHost + "/StoreWidget/OrderConfirm",
            type: 'POST',
            contentType: 'application/json',
            dataType: "json",             
            headers: {
              authorization: customerId
            },
            data: JSON.stringify(data),
            success: function (got)
            {
                //$('#modal-error').modal({});
                //$('#modal-error .modal-body p').text('Заказ успешно подтвержден!');
                $('#preloader').remove();
                //$('#modal-error').show(); 
			jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
			jQuery('#errorModalDostavimTitle').text('Отлично');			
			jQuery('#errorModalDostavim').text('Заказ успешно подтвержден!');
				$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});	
			$('#succesDostavim').css('display','block');
            },
            error : function (msg) { 
                //$('#modal-error').modal({});
                //$('#modal-error .modal-body p').html(errToString(msg));
                $('#preloader').remove();
                //$('#modal-error').show();                
			//тут надо разобраться
			//jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	//function(){ // после выполнения предъидущей анимации
			//jQuery('#errorModalDostavimTitle').text('Ошибка');			
			//jQuery('#errorModalDostavim').text(errToString(msg));
			//	$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			//});	
			//тут надо разобраться
			alert(msg['responseText']);
                console.log(msg); 		
				
            },
            timeout: 10000
        }); 
    };
    
    function checkFields() {        
        if ($('#order-weight').val() == 0) {
			
            //$('#modal-error').modal({});
            //$('#modal-error .modal-body p').text('Укажите вес отправления, пожалуйста!');   
            //$('#modal-error').show(); 

			jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
			jQuery('#errorModalDostavimTitle').text('Ошибка!');			
			jQuery('#errorModalDostavim').text('Укажите вес отправления, пожалуйста!');
				$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});			

            return false; 
        } else if(($('#height4').val()==0) || ($('#width4').val()==0) || ($('#length4').val()==0)) {
            //$('#modal-error').modal({});
            //$('#modal-error .modal-body p').text('Укажите габариты отправления, пожалуйста!');   
            //$('#modal-error').show(); 
			jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
			jQuery('#errorModalDostavimTitle').text('Ошибка!');			
			jQuery('#errorModalDostavim').text('Укажите габариты отправления, пожалуйста!');
				$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});					

            return false;  
        } else if (!$('#order-street').attr('cladr')) {
            //$('#modal-error').modal({});
            //$('#modal-error .modal-body p').text('Выберите улицу из выпадающего списка, пожалуйста!');
            //$('#modal-error').show(); 
			jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
			jQuery('#errorModalDostavimTitle').text('Ошибка!');			
			jQuery('#errorModalDostavim').text('Выберите улицу из выпадающего списка!');
				$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});	
			
            return false; 
        } else if ($('#order-building').val() == 0) {
            //$('#modal-error').modal({});
            //$('#modal-error .modal-body p').text('Укажите номер дома, пожалуйста!');
            //$('#modal-error').show(); 
			jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
			jQuery('#errorModalDostavimTitle').text('Ошибка!');			
			jQuery('#errorModalDostavim').text('Укажите номер дома, пожалуйста!');
				$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});	
            return false; 
        } else {
            console.log(true);
            return true;  
        };        
    };    
    
    $('#order-count').click(function(e){ 
        e.preventDefault(); 
		if($('#statusDostavim').html()!='Зарегистрирован'){
			if (checkFields()) {
				var tempObj = {};
				tempObj.orderId =  orderId;
				tempObj.length = $('#length4').val();
				tempObj.width = $('#width4').val();
				tempObj.height = $('#height4').val();
				tempObj.weight = ($('#order-weight').val())*1000;
				tempObj.cod = $('#order-cod').val();
				tempObj.assessedCost = $('#order-ac').val();
				tempObj.recipientHome = $('#order-building').val();
				tempObj.recipientStreet = $('#order-street').val();
				tempObj.recipientStreetCladr = $('#order-street').attr('cladr');
				tempObj.recipientStreetGuid = $('#order-street').attr('guid');
				tempObj.recipientCorpus = $('#order-pavilion').val();
				tempObj.recipientBuilding = $('#order-housing').val();
				tempObj.recipientApartment = $('#order-apartment').val();
				getTariffs(customerId, tempObj);
    //  	      getCountry();
			}
		}
		else{
			jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
			jQuery('#errorModalDostavimTitle').text('Ошибка!');			
			jQuery('#errorModalDostavim').text('Подтвержденный заказ нельзя расчитать!');
				$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});	
			
			}
    });
    
    $('#order-confirm').click(function(e){
        e.preventDefault();
       if (checkFields()) {
           var chooseTarif = false;
           $('.serviceChoice').each(function(){
                if ($(this).prop('checked')) 
                    chooseTarif = true;
           });
           if (chooseTarif) {
               console.log(chosenIndex);
               var i = chosenIndex.split('.')[0];
               var j = chosenIndex.split('.')[1];           
               var finalObject = {};   
               finalObject.tariffInfo = tariffsArray[i].tariffs[j];
               finalObject.orderId =  orderId;
               finalObject.length = $('#length4').val();
               finalObject.width = $('#width4').val();
               finalObject.height = $('#height4').val();
               finalObject.weight = ($('#order-weight').val())*1000;
               finalObject.cod = $('#order-cod').val();
               finalObject.assessedCost = $('#order-ac').val();
               finalObject.recipientHome = $('#order-building').val();
               finalObject.recipientStreet = $('#order-street').val();
               finalObject.recipientStreetCladr = $('#order-street').attr('cladr');
               finalObject.recipientStreetGuid = $('#order-street').attr('guid');
               finalObject.recipientCorpus = $('#order-pavilion').val();
               finalObject.recipientBuilding = $('#order-housing').val();
               finalObject.recipientApartment = $('#order-apartment').val();
               finalObject.orderNumber = $('.order-number').text();
			   finalObject.clientPostcode = $('#order-postcode').text();


				if ($('#dostdeliveryId').text()=='')
				{
					finalObject.pickUpDate = $('#courierDate').val();			   
					finalObject.courierCallTimeMin = $('#courierTime1 option:selected').attr('time1');			   
					finalObject.courierCallTimeMax = $('#courierTime1 option:selected').attr('time2');					
				}	
				else
					finalObject.pickUpDate = $('#pvzDate').val();
			   

//новый функционал		
	   
			   if ( typeof $('input[name=orderLico]:checked').val()!='undefined')
			   {
					finalObject.recipientPersonal = {
					    "recipientType" : $('input[name=orderLico]:checked').val(),
						"recipientJuridical": null,
						"recipientPhysical": {
							 "documentType": $('input[name=order-doc]:checked').val(),
							 "documentSeries": $('#orderSeria').val(),
							 "documentNumber": $('#orderNumber').val(),
							 "documentDate": $('#passportDate').val()
							}
						}
			   }
			   
//новый функционал				   
               console.log(finalObject);           
               confirmOrderFinal(customerId, finalObject);   
           } else {
			   
                //$('#modal-error').modal({});
                //$('#modal-error .modal-body p').text('Выберите тариф службы доставки!');   
                //$('#modal-error').show(); 
			jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
			jQuery('#errorModalDostavimTitle').text('Ошибка!');			
			jQuery('#errorModalDostavim').text('Выберите тариф службы доставки!');
				$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});	
				
                return false;
           };           
       } else {
           console.log('This is wrong!');
       };
    });
        
    $(document).ready(function(){
        getOrder(customerId, orderId);          
    });
    
});