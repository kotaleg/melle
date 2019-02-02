    //var customerId = 'a5210296-58ad-4620-9e1d-b5e638217080';    
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

localStorage.setItem('customerId', customerId);

var imgArray = {
    'dpd' : "DPD.png",
    'boxberry' : "Boxberry.png",
    'DelLin' : "dl.png",
    'cdek' : "CDEK.png",
    'maxipost' : "MaxiPost.png",
    'b2cpl' : "B2CPL.png"    
};

function main(){
    
    // Удаление заказа из базы
    function deleteOrderFromBase(orderId, targetRow) {
        $.ajax({                    
            url: "https://api.dostav.im/StoreWidget/OrderDelete",            
            type: 'POST',
            contentType: 'application/json',
            dataType: "json", 
            headers: {
              authorization: customerId
            },
            data: JSON.stringify({
                orderId: orderId
            }),
            success: function (got)
            {        
                targetRow.remove();
                return console.log(got);
            },
            error : function (msg) {                
                return console.log(msg);
            },
            timeout: 30000
        });  
    }
    
    // Получение данных для заполнения таблицы
    function getOrders(customerId) {        
        $.ajax({                    
            url: "https://api.dostav.im/StoreWidget/OrderList?page=1&pageSize=20",            
//            type: 'POST',
//            contentType: 'application/json',
//            dataType: "json", 
            headers: {
              authorization: customerId
            },
            data: {},//JSON.stringify(formData),
            success: function (got)
            {
                tableOrdersFilling(got.orders);
                return console.log(got);
            },
            error : function (msg) {                
                return console.log(msg);
            },
            timeout: 30000
        });         
    };
    
    // Базовые вызовы функций при загрузке страницы
    getOrders(customerId);
    
};

$(document).ready(main);