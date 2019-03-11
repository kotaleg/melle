var pvzList = [];
var myMap, myPlacemark;    
//var customerId = $('#tokenDostavim').val(); //сюда вставить полученный токен
var variableOrderCost = 200;

var iconLayoutVal = "default#image";
var iconImageHrefVal = "img/pin.svg";
var iconImageSizeVal = [60, 35];
var widgetHost = "https://api.dostav.im";
window.customerVarSity = false;
window.customerVarPhone = false;




function main(){
	
	//===================================deliveryCity===================================================
function sityApiDostavim(sityDostavim, regionDostavim){
	$(sityDostavim).after('<div id="search_advice_wrapper"></div>');
$(sityDostavim).keyup(function(I){
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
                url: "https://api.dostav.im/Address/CityByRegion/",
                dataType: "json",
                minLength: 3,
				headers: {
				authorization: customerId
				},
                data: {
					region: $(regionDostavim).find('option:selected').text().trim() , //'', // //  jQuery(regionDostavim).val("value"), 
					city: $(this).val().trim(),
					//fillRegion: true
                },
                success: function (data) { 
	
						var list = data;
						suggest_count = list.length;
						//var options2 = [];
						if(suggest_count > 0){
							// перед показом слоя подсказки, его обнуляем
							$("#search_advice_wrapper").html("").show();
							for(var i in list){							
								if(list[i].path != " "){
									// добавляем слою позиции
									$('#search_advice_wrapper').append('<div index="'+list[i].guid+'" class="advice_variant">'+list[i].name+'</div>');
									//options2.push({ id: list[i].guid, value: list[i].type + ". " + list[i].name + " - " + list[i].path });
								}
								else
								{	
									$('#search_advice_wrapper').append('<div index="'+list[i].guid+'" class="advice_variant">'+list[i].name+'</div>');
									//options2.push({ id: list[i].guid, value: list[i].type + ". " + list[i].name });
								}																
							}
							
						}
                }                
            });				
					//============================================================================================
					
					
				}
			break;
		}
	});

	//  
	//считываем нажатие клавишь, уже после вывода подсказки
	$(sityDostavim).keydown(function(I){
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
		    $(sityDostavim).val($(this).text());
			$(sityDostavim).attr('value', $(this).text());							
		    // прячем слой подсказки
		    $('#search_advice_wrapper').fadeOut(350).html('');
		    $('#search_advice_wrapper').remove();

	});	

	
	// если кликаем в любом месте сайта, нужно спрятать подсказку
	$('html').click(function(){
		$('#search_advice_wrapper').hide();
	});
	// если кликаем на поле input и есть пункты подсказки, то показываем скрытый слой
	//$(sityDostavim).click(function(event){
	//	//alert(suggest_count);
	//	if(suggest_count)
	//		$('#search_advice_wrapper').show();
	//	event.stopPropagation();
	//});


	function key_activate(n){
		$('#search_advice_wrapper div').eq(suggest_selected-1).removeClass('active');
	
		if(n == 1 && suggest_selected < suggest_count){
			suggest_selected++;
		}else if(n == -1 && suggest_selected > 0){
			suggest_selected--;
		}
	
		if( suggest_selected > 0){
			$('#search_advice_wrapper div').eq(suggest_selected-1).addClass('active');
			$(sityDostavim).val( $('#search_advice_wrapper div').eq(suggest_selected-1).text() );
		} else {
			$(sityDostavim).val( input_initial_value );
		}
	}		
	
	
}	
	
//25.02.2019
    // Сортировка списка ПВЗ по первым буквам фильтра
    jQuery('input.address-filter').keyup(function(e){
        if (e.keyCode === 40) {            
            if (!jQuery('.pvz-item.selected').length) {
				
                jQuery('.pvz-item').not('.hidden').first().addClass('selected').attr('tabindex', '0').focus();
                jQuery('.address-filter').val(jQuery('.pvz-item.selected').text());
            } else {
                jQuery('.pvz-item.selected').focus();                
            };            
        } else if (e.keyCode === 38) {
            jQuery('.pvz-item.selected').focus(); 
        } else {
            jQuery('.pvz-item.selected').removeClass('selected');
            var sortVal = jQuery.trim(jQuery(this).val().toLowerCase().split(',')[0]);
            console.log(sortVal);
            jQuery('.pvz-item').map(function(){ 
                if (jQuery(this).text().split(',')[0].toLowerCase().indexOf(sortVal) + 1){
                    jQuery(this).removeClass('hidden');
                } else {
                    jQuery(this).addClass('hidden');
                };
            });        
        }
    });
//25.02.2019

function phoneDostavim(phone){
					//начало функции mask (Маска для телефона)
					document.addEventListener("mouseup", function() {
					function setCursorPosition(pos, elem) {
					    elem.focus();
					    if (elem.setSelectionRange) elem.setSelectionRange(pos, pos);
					    else if (elem.createTextRange) {
					        var range = elem.createTextRange();
					        range.collapse(true);
					        range.moveEnd("character", pos);
					        range.moveStart("character", pos);
					        range.select()
					    }
					}
					
					function mask(event) {
					    var matrix = "+7 (___) ___-__-__",
					        i = 0,
					        def = matrix.replace(/\D/g, ""),
					        val = this.value.replace(/\D/g, "");
							//console.log(val);
							var updatePhone = val.substring(0,1);
							if(updatePhone==8){
								val = val.substr(1);
							}
							//console.log(updatePhone);
                                
					    if (def.length >= val.length) val = def;
					    this.value = matrix.replace(/./g, function(a) {
					        return /[_\d]/.test(a) && i < val.length ? val.charAt(i++) : i >= val.length ? "" : a
					    });
					    if (event.type == "blur") {
					        if (this.value.length == 2) this.value = ""
					    } else setCursorPosition(this.value.length, this)
					};
					    var updatePhone = document.querySelector(phone);//.substring(0,1);
						//console.log(updatePhone);
					    var input = document.querySelector(phone);
						//console.log(input);
					    input.addEventListener("input", mask, false);
					    input.addEventListener("focus", mask, false);
					    input.addEventListener("blur", mask, false);
					});
					//конец функции mask (Маска для телефона)	

}

//для оригинального opencart 
document.addEventListener("mouseup", function() {
	console.log(typeof  $('input[name="telephone"]').val());

	if(window.customerVarPhone == 'true'){	
		if (typeof $('input[name="telephone"]').val()  !== "undefined"){
		phoneDostavim('input[name="telephone"]');
		}
	}
	
	if(window.customerVarSity == 'true'){	
		if ((typeof $('input[name="city"]').val()  != 'undefined') && (typeof $('input[name="city"]').val()  != 'undefined')){
		sDostavim = 'input[name="city"]';
		rDostavim = 'select[name="zone_id"]';
		sityApiDostavim(sDostavim,rDostavim);  //нужно проверить ещё включено или нет
	    }	
	}


	
})
//для оригинального opencart 

function reloadDostavim(){
	
									if (typeof (reloadAll) === "function") {
										reloadAll()
									}
									else
									{
										simplecheckout_reload('cart_changed');
									}
}

	//здесь проверяем сколько раз перезагружалась симпла	
	
setTimeout(function() { 
var customerId = $('span#tokenDostavim').text(); //сюда вставить полученный токен
																	

 //здесь мы вычисляем какая симпла установлена  // telDostavim, addressDostavim
if (typeof (reloadAll) === "function") 
	{	
		var nameDostavim = '#customer_firstname';
		var telDostavim = '#customer_telephone';
		var emailDostavim = '#customer_email';
		//var addressDostavim = '#payment_address_address_2';//#shipping_address_address_2';
		window.addressDostavim = '#shipping_address_address_2';//#shipping_address_address_2';
		window.addressDostavim2 = '#shipping_address_address_1';//#shipping_address_address_2';
		var labeladress='label[for="shipping_address_address_2"]';
		var labeladress2='label[for="shipping_address_address_1"]';
		var CommentDostavim = '#comment';
		var sityDostavim = '#shipping_address_city';//#shipping_address_city';
		var regionDostavim = '#shipping_address_zone_id';
		var indexDostavim = '#shipping_address_postcode';
	}
	else
	{
		var nameDostavim = '#checkout_customer_main_firstname';
		var telDostavim = '#checkout_customer_main_telephone';
		var emailDostavim = '#checkout_customer_main_email';
		//var addressDostavim = '#checkout_customer_main_address_1';
		window.addressDostavim = '#checkout_customer_main_address_2';
		window.addressDostavim2 = '#checkout_customer_main_address_1';//#shipping_address_address_2';
		var CommentDostavim = '#checkout_customer_main_comment';
		var labeladress='label[for="shipping_address_address_1"]';
		var labeladress2='label[for="shipping_address_address_2"]';
		var sityDostavim = '#checkout_customer_main_city';
		var regionDostavim = '#checkout_customer_main_zone_id';
		var indexDostavim = '#checkout_customer_main_postcode';
	}

		//alert(sityDostavim);
	//запоминание чекбокса home

           //проверяем какой чекбокс нажат и скрываем пвз
        //if($('input:radio[value="dostavimchekaut.dostavimchekaut1"]').prop("checked")){
		//		$('span.hidepvz').css('display','none');
        //}	
        //if($('input:radio[value="dostavimchekaut.dostavimchekaut2"]').prop("checked")){
		//         $('span.hidepvz').css('display','none');
        //}	
		
		
		$('input[value="dostavimchekaut.dostavimchekaut3"]').change(function() {
							$(addressDostavim).val('');
				$(addressDostavim).attr('value','');
				$(addressDostavim2).val('');
				$(addressDostavim2).attr('value','');	
		});
		
		
        if($('input:radio[value="dostavimchekaut.dostavimchekaut3"]').prop("checked")){	
	

		
		        $('span.hidepvz').css('display','block');
				$(addressDostavim).attr('readonly','readonly');
				$(addressDostavim).css('display','none');
				$(addressDostavim2).css('display','none');
				$(labeladress).attr('style','display:none !important');	
				$(labeladress2).attr('style','display:none !important');				
				var $parents = $(addressDostavim).parents('tr');  //21.12.2018 
				$parents.css('display','none'); //21.12.2018
				
        }	
		else
		{
			//$(addressDostavim).val('');
			//$(addressDostavim).attr('value','');
				$(addressDostavim).attr('readonly','readonly');
				$(addressDostavim).css('display','none');
				$(labeladress).attr('style','display:none !important');
			$('span.hidepvz').css('display','none');
		}

//здесь мы берем стандартную функцию live и адаптируем под все версии Jquery
jQuery.fn.extend({
    live: function (event, callback) {
       if (this.selector) {
            jQuery(document).on(event, this.selector, callback);
        }
        return this;
    }
});
	
//настройки	
var suggest_count = 0;
var input_initial_value = '';
var suggest_selected = 0;
var options2 = [];	
	
	
	
 //Получение данных для заполнения id
    function getOrdersSetting() {
	jQuery.ajax({

		url: "index.php?route=checkout/dostavimajaxquote",

		type: 'post',

		data: {name:'dostavim', quotedostavim:'0'},

		dataType: 'json',

		success: function (data) { 

		window.customerId = data;

	
        $.ajax({                    
            url: widgetHost + "/StoreWidget/IntegrationSettings",            
            type: 'GET',
            contentType: 'application/json',
            dataType: "json", 
            headers: {
              authorization: window.customerId
            },
            data: {},//JSON.stringify(formData),
            success: function (got)
            {
				if (got.customerVar7 == 'true'){
					window.customerVarPhone = 'true';
					if (typeof $('input[name="telephone"]').val()  !== 'undefined'){
					 phoneDostavim('input[name="telephone"]');
					 
					}
					else
					{
						if (typeof $(telDostavim).val()  !== 'undefined'){
					      phoneDostavim(telDostavim);	
						}
					}
				}
				else
				window.customerVarPhone = false;
				
				if (got.customerVar6 == 'true'){
						window.customerVarSity = 'true';
				}
				else
				window.customerVarSity = false;

				
            },
            error : function (msg) {                
                return console.log(msg);
            },
            timeout: 3000
        });  

					}, 

		error: function (msg) {

			console.log(msg);

						}

	}); 
		
		
    };	



		jQuery('#pvz-dostavim').click( function(event){ // лoвим клик пo ссылки с id="pvz-dostavim"
			jQuery('#overlayDostavim').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
				$('#exampleModal') .css('display', 'block').animate({opacity: 1, top: '20%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
			});
		
		});
		$('.close, #overlayDostavim').click( function(){ // ловим клик по крестику или подложке
		$('#exampleModal')
			.animate({opacity: 0, top: '20%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
				function(){ // после анимации
					$(this).css('display', 'none'); // делаем ему display: none;
					$('#overlayDostavim').fadeOut(400); // скрываем подложку
				}
			);
	});
	
	
		$('.close').click( function(){ // // ловим клик по крестику
		$('#modal_form')
			.animate({opacity: 0, top: '20%'}, 200, 
				function(){ 
					$(this).css('display', 'none');
					$('#overlayDostavim').fadeOut(400); 
				}
			);
							});
	
	/* Закрытие модального окна */
	$('#modal_close, #overlay').click( function(){ // ловим клик по крестику или подложке
		$('#modal_form')
			.animate({opacity: 0, top: '45%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
				function(){ // после анимации
					$(this).css('display', 'none'); // делаем ему display: none;
					$('#overlay').fadeOut(400); // скрываем подложку
				}
			);
	});
	
	/* Закрытие модального окна */
	$('#modal_close, #overlayDostavim').click( function(){ // ловим клик по крестику или подложке
		$('#modal_form')
			.animate({opacity: 0, top: '45%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
				function(){ // после анимации
					$(this).css('display', 'none'); // делаем ему display: none;
					$('#overlay').fadeOut(400); // скрываем подложку
				}
			);
	});


//////////////////////////////////////////////////////////////////////////здесь начало функций калькулятора упаковки товара




  
                      // Обработчик клика по пункту списка ПВЗ
                    jQuery('.pvz-item').click(function(){
						jQuery(addressDostavim).val(jQuery(this).text()+' ::'+jQuery(this).attr("serviceid")+'::'+jQuery(this).attr("pvzId")+'::3::');
						jQuery(addressDostavim2).val(jQuery(this).text());
						
						jQuery(addressDostavim).attr('pvzId',jQuery(this).attr('pvzId'));
						
                        jQuery('.pvz-item.selected').removeClass('selected').attr('tabindex', '-1');
                        jQuery(this).addClass('selected').attr('tabindex', '0').focus();        
                        jQuery('.address-filter').val(jQuery(this).text());
                        jQuery('.address-filter').attr('serviceId',jQuery(this).attr('serviceId'));
                        jQuery('.address-filter').attr('pvzId',jQuery(this).attr('pvzId'));
                        jQuery('.address-filter').attr('pvzCode', jQuery(this).attr('pvzCode'));
                        jQuery('.address-filter').attr('pvzGuid', jQuery(this).attr('pvzGuid'));
                        jQuery('.pvz-list-link').html(jQuery(this).html());
						
						jQuery('.address-filter').attr('serviceid', jQuery(this).children('img').attr("pvzId"));
						jQuery('.address-filter').attr('pvzguid', jQuery(this).attr("pvzguid"));
						jQuery('.address-filter').attr('pvzcode', jQuery(this).attr("pvzcode"));
						jQuery('.address-filter').attr('serviceid', jQuery(this).attr("serviceid"));
						
						
                        jQuery('.pvz-div').hide();
						reloadDostavim();
                    });
				   
  
					//клик по кластеру
					jQuery('body').on("click", '.pvz-cluster-balloon', function(e){
						e.preventDefault();
						
						jQuery(addressDostavim).val(jQuery(this).text()+' ::'+jQuery(this).attr("serviceid")+'::'+jQuery(this).attr("pvzcode")+'::3::');
						jQuery(addressDostavim2).val(jQuery(this).text());
						jQuery(addressDostavim).attr('pvzId',jQuery(this).attr('pvzId'));
						
						jQuery('.pvz-list-link').html(jQuery(this).html());
						//jQuery(addressDostavim).val(jQuery(this).html().split('>')[1]);
						jQuery('.address-filter').attr('pvzId', jQuery(this).children('img').attr("pvzId"));
						jQuery('.address-filter').attr('serviceid', jQuery(this).children('img').attr("pvzId"));		
						jQuery('.address-filter').attr('pvzguid', jQuery(this).attr("pvzguid"));
						jQuery('.address-filter').attr('pvzcode', jQuery(this).attr("pvzcode"));
						jQuery('.address-filter').attr('serviceid', jQuery(this).attr("serviceid"));

						jQuery('.pvz-div').hide();
                        jQuery('#exampleModal').css('display','none');	
                        jQuery('#overlayDostavim').css('display','none');						
						reloadDostavim();
					});
    
    // Проверка заполнения полей
	
//25.02.2019					
                    jQuery('.pvz-item').keyup(function(e){
                        e.preventDefault();               
                        if (e.keyCode === 40) {
                            jQuery(this).removeClass('selected').attr('tabindex', '-1');
                            var currentPvz = jQuery(this).nextAll('.pvz-item:not(.hidden)').first();
                            currentPvz.addClass('selected').attr('tabindex', '0').focus();
                            jQuery('.address-filter').val(currentPvz.text());
                        } else if (e.keyCode === 38) {
                            jQuery(this).removeClass('selected').attr('tabindex', '-1');
                            var currentPvz = jQuery(this).prevAll('.pvz-item:not(.hidden)').first();
                            currentPvz.addClass('selected').attr('tabindex', '0').focus();
                            jQuery('.address-filter').val(currentPvz.text());
                        } else if (e.keyCode === 13) {                            
                            if (jQuery(this).hasClass('selected')) {
                                jQuery('#contactChoice3').prop('checked', true);
                                jQuery('.pvz-list-link').html(jQuery(this).html());
                                jQuery('.pvz-div').hide();       
                            }                            
                        } else {
                            
                        };        
                        jQuery(addressDostavim).val(jQuery(this).text());
                    });
//25.02.2019	


	//var checkFormPhone = function(formVal) {
	//	var errorMessage = "";
	//	var form_phone = /^[0-9+()-\s]/;
	//	if (formVal == "" || formVal.length < 10 || !form_phone.test(formVal)) {
	//		errorMessage = "Неправильный формат номера";
	//		jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
	//	 	function(){ // после выполнения предъидущей анимации
	//		jQuery('#errorModalDostavimTitle').text('Сообщение об ошибке');			
	//		jQuery('#errorModalDostavim').text(errorMessage);
	//			$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
	//		});
    //        return false;
	//	};
	//	return true;
	//};
    //
    //function checkFormInputs() {
    //    var formFillCheck = 'Ваш заказ создан!';
    //    if ( (!jQuery(sityDostavim).val()) || (!jQuery(nameDostavim).val()) || (!jQuery(addressDostavim).val()) ) {          
    //        formFillCheck = "Пожалуйста, заполните поля формы!";
    //        jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
	//	
	//	 	function(){ // после выполнения предъидущей анимации
	//		jQuery('#errorModalDostavimTitle').text('Сообщение об ошибке');			
	//		jQuery('#errorModalDostavim').text(formFillCheck);
	//			$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз					
	//	});
    //        return false;
    //    }
    //
    //
	//	if(jQuery(telDostavim).val().length!=18) {  //если телефон несоответствует маски
	//		var errorMessageTel = '';
	//		errorMessageTel = "Вы забыли указать ваш телефон";
	//		jQuery('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
	//	 	function(){ // после выполнения предъидущей анимации
	//		jQuery('#errorModalDostavimTitle').text('Сообщение об ошибке');			
	//		jQuery('#errorModalDostavim').text(errorMessageTel);
	//			$('#modal_form') .css('display', 'block').animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
	//		});	
	//		            return false;
    //    } 
	//		
	//		
	//	
    //    return true;
    //};
    



	function HackForPvz(){
								setTimeout(function() {   //вообщето здесь надо делать флаг
									if (typeof (reloadAll) === "function") {
									    reloadAll();
									}
									else
									{
										simplecheckout_reload('cart_changed');
									}
								}, 5000);
	
	}
	
	

	//==================================deliveryCity====================================================
    
    //  Открытие списка ПВЗ
    jQuery('.pvz-list-link').click(function(e){
        e.preventDefault();
        jQuery('.pvz-div').show();
        jQuery('.address-filter').focus().select();
    });
        
    // Открытие списка ПВЗ на карте
    jQuery('.pvz-map-link').click(function(e){
        e.preventDefault();
		
    });

    // Закрытие списка ПВЗ при клике вне его
    jQuery(document).mouseup(function (e) {
        var container = jQuery(".pvz-div");
        if (container.has(e.target).length === 0){
            container.hide();
        }
    });
 
	
	
    //// Базовая загрузка виджета  если теелфон включене
    jQuery(function(){
        getOrdersSetting();  //ЕСЛИ симпла то лучше вообще отключить
		$(sityDostavim).after('<div id="search_advice_wrapper"></div>');
		$(sityDostavim).attr("autocomplete", "off");
	});
	
	
//начало функции переноса данных для оригинального opencart
}, 20)


};
	main();