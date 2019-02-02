     var customerId = $('#tokendostavim').val(); //сюда вставить полученный токен

if (customerId != '')
{
	$('#divFormDostavim').css('display','none');
}
else
{
	$('main').css('display','none');
	$('#dostbox').css('display','none');
}






function main(){
	 
    function saveOrdersSetting() {
	 var phoneBoolDost = $('#settings-reloadphone').prop('checked');
	 var cityBoolDost = $('#settings-reloadcity').prop('checked');
        $.ajax({                    
            url: "https://api.dostav.im/StoreWidget/IntegrationSettings",            
            type: 'POST',
            contentType: 'application/json',
            dataType: "json", 
            headers: {
              authorization: customerId
            },
            data: JSON.stringify({
				customerVar6: cityBoolDost,
				customerVar7: phoneBoolDost
            }),
            success: function (got)
            {        
                console.log(got);
            },
            error : function (msg) {                
                return console.log(msg);
            },
            timeout: 30000
        });  
    }	 
	 
    function getOrdersSetting() {
        $.ajax({                    
            url: "https://api.dostav.im/StoreWidget/IntegrationSettings",            
            type: 'GET',
            contentType: 'application/json',
            dataType: "json", 
            headers: {
              authorization: customerId
            },
            //data: JSON.stringify({
			//	customerVar6: cityBoolDost,
			//	customerVar7: phoneBoolDost
            //}),
            success: function (got)
            {   
                    jQuery('#settings-reloadcity').prop('checked', JSON.parse(got.customerVar6));
                    jQuery('#settings-reloadphone').prop('checked',  JSON.parse(got.customerVar7));		
                    //jQuery('#settings-reloadcity').prop('checked',  false);
                    //jQuery('#settings-reloadphone').prop('checked',  false);			
                console.log(got);

            },
            error : function (msg) {                
                return console.log(msg);
            },
            timeout: 30000
        });  
    }	 


//if (($('#tokendostavim').val() != '')or ($('#tokendostavim').val() != 0))



	
	//получение токена
   
		
	
    function sendFormDataToken(formData) {
        console.log();
	$.ajax({
	url: 'https://api.dostav.im/StoreWidget/GetAuthorizationToken',
	type: 'post',
	//data: {'Email':Email,'companyName':companyName,'Phone':Phone,'password':mypassword,'domain':domain},
	dataType: 'json',
	contentType: 'application/json',
    data: JSON.stringify(formData),
	success: function(jsondata) {
	if (jsondata['success']) {
            var key = jsondata['success'];
			//key = '555'; //это потом убрать 4634
			jQuery('#tokendostavim').val(key);
            jQuery('#form').submit();	
	}
	}})};      
	
	    // Создание объекта данных формы
    jQuery('#registr-token').click(function(){
		var token = $('#dostavimToken').val();
        geTrueToken(token);        
    });  	
	
	    // Создание объекта данных формы
    jQuery('#get-form-token').click(function(){
		var formData = {};
		formData.companyName = $('#CompanyName').val();
		formData.Email = $('#DostavimEmail').val();
		formData.Phone = $('#DostavimPhone').val();
		formData.domain = location.hostname;
		formData.password = $('#DostavimPassword').val();   
       sendFormDataToken(formData);        
    });  

//	
    
    var checkFormCost = function(formVal) {
		var errorMessage = "";
		var form_cost = /\d*/;        
		if (formVal == "" || !form_cost.test(formVal)) {
			errorMessage = "Некорректно указана наценка";
		};
		return errorMessage;
	};
    
    var checkFormSize = function(formVal) {
		var errorMessage = "";
		var form_size = /\d*/;
		if (formVal == "" || !form_size.test(formVal)) {
			errorMessage = "Некорректно указан один из размеров";
		};
		return errorMessage;
	};
    
    var checkFormWeight = function(formVal) {
		var errorMessage = "";
		var form_weight = /\d+[.]*\d*/;
		if (formVal == "" || !form_weight.test(formVal)) {
			errorMessage = "Некорректно указан вес";
		};
		return errorMessage;
	};
    
    function getSettings() {
        jQuery.ajax({
            url: "https://api.dostav.im/StoreWidget/DeliveryMethodsBack",
            type: 'GET',
            contentType: 'application/json',
            dataType: "json",
            headers: {
                authorization: customerId
            },
            data: {},
            success: function (data)            
            {
                getOrdersSetting();
                var inputsArray = jQuery('.settings-table tr').map(function ()
                {
                    return jQuery(this);
                }).get();
                for (var i = 0; i < data.deliveryMethods.length; i++)
                {
                    //                    console.log(data.deliveryMethods[i]);
                    jQuery('#settings-delivery-type' + (i + 1)).prop('checked', data.deliveryMethods[i].used);
                    inputsArray[i + 1].find('#name' + (i + 1)).val(data.deliveryMethods[i].name);
                    jQuery('#settings-fromDoor' + (i + 1)).prop('checked', data.deliveryMethods[i].fromDoor);
                    inputsArray[i + 1].find('td:nth-child(4) textarea').val(data.deliveryMethods[i].description);
                    inputsArray[i + 1].find('td:nth-child(5) input').val(data.deliveryMethods[i].margin);
                    jQuery('#length' + (i + 1)).val(data.deliveryMethods[i].length);
                    jQuery('#width' + (i + 1)).val(data.deliveryMethods[i].width);
                    jQuery('#height' + (i + 1)).val(data.deliveryMethods[i].height);
                    inputsArray[i + 1].find('.settings-weight').val(data.deliveryMethods[i].weight / 1000);
					
                    jQuery('#settings-cod' + (i + 1)).prop('checked', data.deliveryMethods[i].costAsCodAndAssessed);
                    jQuery('#settings-ac' + (i + 1)).prop('checked', data.deliveryMethods[i].costAsAssessed);
                }
            },
            error: function (msg)
            {
                return console.log(msg);
            },
            timeout: 30000
        });        
    }
    
    function sendSettings(formData) {
        jQuery.ajax({                    
            url: "https://api.dostav.im/StoreWidget/DeliveryMethodsBack",            
            type: 'POST',
            contentType: 'application/json',
            dataType: "json",
            headers: {
              authorization: customerId
            },
            data: JSON.stringify({
                deliveryMethods: formData
            }),
            success: function (got)
            {
				saveOrdersSetting();
                return console.log(got);
            },
            error : function (msg) {                
                return console.log(msg);
            },
            timeout: 30000
        });        
    }
    
    jQuery('#settings-submit-btn').click(function(e){        
        e.preventDefault();
		
		//alert(jQuery('#settings-cod3').prop("checked"));

		
        var formData = [];        
        var inputsArray = jQuery('.settings-table tr').map(function(){
            return jQuery(this);
        }).get();        
        for (var i = 1; i < inputsArray.length; i++) {
            var item = {};
            //item.id = i;
            item.number = i;
            item.used = jQuery('#settings-delivery-type'+i).prop('checked');            
            item.name = inputsArray[i].find('#name'+i).val();
            item.fromDoor = jQuery('#settings-fromDoor'+i).prop('checked');  
            item.description = inputsArray[i].find('td:nth-child(4) textarea').val();       
            item.CostAsCodAndAssessed = jQuery('#settings-cod'+i).prop('checked');           
            item.CostAsAssessed = jQuery('#settings-ac'+i).prop('checked');
            
            if (!checkFormCost(inputsArray[i].find('td:nth-child(5) input').val())) {
                item.margin = inputsArray[i].find('td:nth-child(5) input').val() * 1; 
            } else {
                console.log(checkFormCost(inputsArray[i].find('td:nth-child(5) input').val()));
                return;
            }             
            if (!checkFormSize(jQuery('#length'+i).val())) {
                item['length'] = jQuery('#length'+i).val() * 1;  
            } else {
                console.log(checkFormSize(jQuery('#length'+i).val()));
                return;
            }  
            if (!checkFormSize(jQuery('#width'+i).val())) {
                item['width'] = jQuery('#width'+i).val() * 1;                  
            } else {
                console.log(checkFormSize(jQuery('#width'+i).val()));
                return;
            }  
            if (!checkFormSize(jQuery('#height'+i).val())) {
                item['height'] = jQuery('#height'+i).val() * 1;  
            } else {
                console.log(checkFormSize(jQuery('#height'+i).val()));
                return;
            }  
            if (!checkFormWeight(inputsArray[i].find('.settings-weight').val())) {
                item.weight = inputsArray[i].find('.settings-weight').val() * 1000;
            } else {
                console.log(inputsArray[i].find('.settings-weight').val())
                console.log(checkFormWeight(inputsArray[i].find('.settings-weight').val()));
                return;
            }  
            
            formData.push(item);
        }        
        
        console.log(formData);
        sendSettings(formData);
        
    });
    
    // Базовая загрузка виджета
    jQuery(function(){              
        getSettings();
//        jQuery('.preloader').show();                        
    });
}
