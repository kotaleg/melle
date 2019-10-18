$(document).ready(function() {
    $('#navigation').append('<button class="btn btn-default left-hide"><i class="fa fa-fast-backward"></i></button>');

    $('#filter-product').append('<button class="btn btn-default right-hide"><i class="fa fa-fast-forward"></i></button>');

    $('#filter-product>.panel>.panel-heading').attr('style', 'padding:3px;').find('h3').wrapAll('<button class="btn btn-default" onclick="$(\'#button-filter\').click();"></button>');

    $('#form-product table tr').each(function(i,e) {
        if (apf_settings.columns.product_id == 0)   $('td:eq(1)', this).toggle();
        if (apf_settings.columns.image == 0)        $('td:eq(2)', this).toggle();
        if (apf_settings.columns.name == 0)         $('td:eq(3)', this).toggle();
        if (apf_settings.columns.model == 0)        $('td:eq(4)', this).toggle();
        if (apf_settings.columns.sku == 0)          $('td:eq(5)', this).toggle();
        if (apf_settings.columns.manufacturer == 0) $('td:eq(6)', this).toggle();
        if (apf_settings.columns.category == 0)     $('td:eq(7)', this).toggle();
        if (apf_settings.columns.price == 0)        $('td:eq(8)', this).toggle();
        if (apf_settings.columns.quantity == 0)     $('td:eq(9)', this).toggle();
        if (apf_settings.columns.status == 0)       $('td:eq(10)', this).toggle();
        if (apf_settings.columns.action == 0)       $('td:eq(11)', this).toggle();
    });

    $('#filter-product').delegate('#button-filter-setting', 'click', function() {
        if ($('#panel-setting').css('display') == 'block') {
            if ($('#filter-product .btn-filter input').prop('checked')) {
                $('#filter-product .right-hide').click();
            }

            $('#panel-setting').hide();
        } else {
            $('#panel-setting').show();
        }
    });

    $('#filter-product').delegate('#button-filter-setting-close', 'click', function() {
        if ($('#filter-product .btn-filter input').prop('checked')) {
            $('#filter-product .right-hide').click();
        }

        $('#panel-setting').hide();
    });

    $('#filter-product').delegate('#panel-setting input', 'change', function() {
        ShowHide($(this).attr('data-key'));
    });

    $('#form-product table tr').each(function(i, e) {

        // Images
        if (i) $('td:eq(2)>img', this).after('&nbsp;&nbsp;<a class="btn btn-xs btn-default btn-images"></a>');

        // Name
        if (i) $('td:eq(3)', this).append('&nbsp;&nbsp;<a class="btn btn-xs btn-default btn-name"></a>');

        // Model
        if (i) { var model = $('td:eq(4)', this).text();
            if (model) {
                $('td:eq(4)', this).html('<a class="btn btn-xs btn-default btn-model">' + model + '</a>');
            } else {
                $('td:eq(4)', this).html('<a class="btn btn-xs btn-default btn-model-add"></a>');
            }
        }

        // Sku
        if (i) {
            var sku = $('td:eq(5)', this).text();

            if (sku) {
                $('td:eq(5)', this).html('<a class="btn btn-xs btn-default btn-sku">' + sku + '</a>');
            } else {
                $('td:eq(5)', this).html('<a class="btn btn-xs btn-default btn-sku-add"></a>');
            }
        }

        // Manufacturer
        if (i) $('td:eq(6)', this).append('&nbsp;&nbsp;<a class="btn btn-xs btn-default btn-manufacturer"></a>');
        /*
        if (i) {
            var manufacturer = $('td:eq(6)', this).text();
            $('td:eq(6)', this).html('<a class="btn btn-xs btn-default" style="white-space:unset;">' + manufacturer + '</a>');
        }
        */

        // Categories
        if (i) $('td:eq(7)', this).append('<a class="btn btn-xs btn-default btn-category"></a>');

        // Prices
        var price = $('td:eq(8) span', this).text();
        var special = $('td:eq(8) div.text-danger', this).text();

        if (!price) {
            var price = $('td:eq(8)', this).text();
            special = false;
        }

        if (i) {
            if (special) {
                $('td:eq(8)', this).html('<a class="btn btn-xs btn-default" style="text-decoration:line-through;">' + price + '</a><br /><a class="btn btn-xs btn-danger price-special">' + special + '</a>');
            } else {
                $('td:eq(8)', this).html('<a class="btn btn-xs btn-default">' + price + '</a><br /><a class="btn btn-xs btn-primary price-special"></a>');
            }
        }

        // Quanity
        var quantity = parseInt($('td:eq(9) span.label', this).text());

        var quantity_class;

        if (quantity <= 0) {
            quantity_class  = 'warning';
        } else if (quantity > 0 && quantity < 6) {
            quantity_class = 'danger';
        } else {
            quantity_class = 'success';
        }

        if (i) {
            $('td:eq(9)', this).html('<a class="btn btn-xs btn-' + quantity_class + ' btn-quantity">' + quantity + '</a>');
        }

        // !!!! => to CSS
        $('td:eq(9)', this).css({'width' : '1', 'text-align' : 'center'});
        $('td:eq(10)', this).css({'width' : '1', 'text-align' : 'center'});
        $('td:eq(11)', this).css({'width' : '1', 'text-align' : 'center'});

        // Status
        var status = $('td:eq(10)', this).find('span').attr('data-status');
        var status_text = $('td:eq(10)', this).text();

        if (status) {
            //$('td:eq(10)', this).html('<input type="checkbox" class="status-toggle" data-toggle="toggle" data-on="' + text_enabled + '" data-off="' + text_disabled + '" data-onstyle="success" data-offstyle="danger">');
            $('td:eq(10)', this).html('<input type="checkbox" class="status-toggle" data-toggle="toggle" data-onstyle="success" data-offstyle="default">');
            if (status == 1) {
                $('.status-toggle', this).bootstrapToggle('on');
            } else {
                $('.status-toggle', this).bootstrapToggle('off');
            }
        }
    });

    // Images Click
    $('#form-product tr').delegate('td:eq(2) .btn-images', 'click', function() {
        var filter_data = {
            'action' : 'productImages',
            'product_id' : $(this).closest('tr').children('td:first').find('input').attr('value')
        }; getProductData(filter_data);
    });

    // Name Click
    $('#form-product tr').delegate('td:eq(3) .btn-name', 'click', function() {
        var filter_data = {
            'action' : 'productNames',
            'product_id' : $(this).closest('tr').children('td:first').find('input').attr('value')
        }; getProductData(filter_data);
    });

    // Model Click
    $('#form-product tr').delegate('td:eq(4) .btn-model, td:eq(4) .btn-model-add', 'click', function() {
        var filter_data = {
            'action' : 'productModel',
            'product_id' : $(this).closest('tr').children('td:first').find('input').attr('value')
        }; getProductData(filter_data);
    });

    // Sku Click
    $('#form-product tr').delegate('td:eq(5) .btn-sku, td:eq(5) .btn-sku-add', 'click', function() {
        var filter_data = {
            'action' : 'productSku',
            'product_id' : $(this).closest('tr').children('td:first').find('input').attr('value')
        }; getProductData(filter_data);
    });

    // Manufacturer Click
    $('#form-product tr').delegate('td:eq(6) .btn-manufacturer', 'click', function() {
        var filter_data = {
            'action' : 'getProductManufacturer',
            'product_id' : $(this).closest('tr').children('td:first').find('input').attr('value')
        }; getProductData(filter_data);
    });

    // edit Category
    $('#form-product tr').delegate('td:eq(7) .btn-category', 'click', function() {
        var product_id = $(this).closest('tr').children('td:first').find('input').attr('value');

        var filter_data = {
            'action' : 'getProductCategory',
            'product_id' : product_id
        }

        getProductData(filter_data);
    });

    // edit Price
    $('#form-product tr').delegate('td:eq(8) .btn-default', 'click', function() {
        var product_id = $(this).closest('tr').children('td:first').find('input').attr('value');

        var filter_data = {
            'action' : 'getPrice',
            'product_id' : product_id
        }

        getProductData(filter_data);
    });

    // edit Price Special
    $('#form-product tr').delegate('td:eq(8) .price-special', 'click', function() {
        var product_id = $(this).closest('tr').children('td:first').find('input').attr('value');

        var filter_data = {
            'action' : 'getPriceSpecial',
            'product_id' : product_id
        }

        getProductData(filter_data);
    });

    // edit Quantity
    $('#form-product tr').delegate('td:eq(9) .btn-quantity', 'click', function() {
        var product_id = $(this).closest('tr').children('td:first').find('input').attr('value');

        popoverQuantity(product_id);
    });

    // edit Status
    $('#form-product .status-toggle').change(function() {
        var product_id = $(this).closest('tr').children('td:first').find('input').attr('value');

        if ($(this).prop('checked') == true) {
            var status = 1;
        } else {
            var status = 0;
        }

        changeProductStatus(product_id, status);
    });

    // Popover Destroy
    $('html').on('click', function(e) {
        if (typeof $(e.target).data('original-title') == 'undefined' && !$(e.target).parents().is('.popover.in')) {
            $('[data-original-title]').popover('destroy');
        }
    });

    function ShowHide(key) {
        var data = { 'key' : key };
        $.ajax({
            url: 'index.php?route=extension/admin_product_filter/ShowHide&user_token=' + getURLVar('user_token'),
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(json) {
                if (json['success']) {
                    if (json['column_key']) {
                        $('#form-product table tr').each(function (i, e) {
                            $('td:eq(' + json['column_key'] + ')', this).toggle();
                        });
                    }
                    if (json['column_left'] == 0) {
                        setTimeout(function(){
                            addStyle('left', 'hide');
                        }, 150);

                    }
                    if (json['column_left'] == 1) {
                        setTimeout(function(){
                            addStyle('left', 'show');
                        }, 150);
                    }
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    function addStyle(position, action) {
        var screen_width = $(window).width();

        var window_width = $(window).width();

        var column_left_width = $('#column-left').width();

        var filter_product_position = false;
        if ($('#filter-product').css('position') == 'absolute') {
            var filter_product_position = true;
        }

        var column_left_hide = $('#column-left').hasClass('column-left-hide');

        var products = $('.col-md-9.col-md-pull-3.col-sm-12');

        switch (position + '|' + action) {

            case 'left' + '|' + 'hide' :
                if (window_width > 767) {
                    $('#column-left').addClass('column-left-hide');
                    $('#navigation .left-hide').html('<i class="fa fa-fast-forward"></i>')
                    $('#column-left.column-left-hide').attr('style', 'left: -' + (column_left_width - 25) + 'px!important');

                    if (filter_product_position) {
                        products.attr('style', 'right:0;width:' + (screen_width - column_left_width + 200) + 'px;');
                    }
                }
                break;

            case 'left' + '|' + 'show' :
                $('#column-left').removeClass('column-left-hide');
                $('#navigation .left-hide').html('<i class="fa fa-fast-backward"></i>');
                $('#column-left').attr('style', '');
                if (filter_product_position) {
                    products.attr('style', 'right:0;width:' + (screen_width - column_left_width - 40) + 'px;');
                }
                break;

            case 'left' + '|' + 'resize' :
                if (window_width < 768) {
                    addStyle('left', 'show');
                }
                break;

            case 'right' + '|' + 'hide' :
                if (window_width > 991) {
                    $('#filter-product').attr('style', 'position:absolute;left:initial;top:95px;width:250px;right:-190px;');
                    $('#filter-product .right-hide').html('<i class="fa fa-fast-backward"></i>');

                    if (column_left_hide) {
                        products.attr('style', 'right:0;width:' + (screen_width - 60) + 'px;');
                    } else {
                        products.attr('style', 'right:0;width:' + (screen_width - column_left_width - 40) + 'px;');
                    }
                }
                break;

            case 'right' + '|' + 'show' :
                $('#filter-product').attr('style', '');
                $('.col-md-9.col-md-pull-3.col-sm-12').attr('style', '');
                $('#filter-product .right-hide').html('<i class="fa fa-fast-forward"></i>');
                break;

            case 'right' + '|' + 'resize' :
                if (window_width > 991) {
                    addStyle('right', 'hide');
                } else {
                    addStyle('right', 'show');
                }
                break;
        }
    }

    $(window).resize(function(){
        if ($('#filter-product').css('position') == 'absolute') {
            addStyle('right', 'resize');
        }
        if ($('#column-left').hasClass('column-left-hide')) {
            addStyle('left', 'resize');
        }
        //$('#filter-product .bootstrap-select .dropdown-menu.open').css({'min-width':$('#filter-product').width()})
        //$('#filter-product .bootstrap-select .dropdown-menu.open').css({'min-width':$('#filter-product').width()})
       /* console.log($('#filter-product').width());
        var filter_product_width = $('#filter-product').width();

        setTimeout(function(){
            $('#filter-product .bootstrap-select .dropdown-menu.open').attr('style', function(i,s) { return s + 'max-width: ' + filter_product_width + 'px!important;' });
        }, 150);*/

    });

    $('#navigation .left-hide').on('click', function() {
        if ($('#column-left').hasClass('column-left-hide')) {
            addStyle('left', 'show');
        } else {
            addStyle('left', 'hide');
        }
    });

    $('#filter-product .right-hide').on('click', function() {
        if ($('#filter-product').css('position') == 'absolute') {
            addStyle('right', 'show');
        } else {
            addStyle('right', 'hide');
        }
    });

    if (!apf_settings.column_left) {
        addStyle('left', 'hide');
    }

    if (!apf_settings.column_right) {
        addStyle('right', 'hide');
    }

    $('#filter-product input, #filter-product select').on('keypress', function(e) {
        if(e.which == 13) {
            jQuery(this).blur();
            jQuery('#button-filter').click();
        }
    });

//    console.log($('#filter-product').width());
    var filter_product_width = $('#filter-product').width();

   // $('#filter-product .dropdown-menu.open').css({'max-width: ' : filter_product_width });
    // $('#filter-product .bootstrap-select .dropdown-menu.open').attr('style', function(i,s) { return s + 'max-width: ' + filter_product_width + 'px!important;' });

});


/*
function popoverProductManufacturer2(product_id, manufacturer_id) {
    var td_manufacturer = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(6)');

    html = '';

    html += '<div class="form-group">';
    html += '<label class="control-label" for="select-popover-manufacturer" style="display: block">' + entry_manufacturer + '</label>';
    html += '<select name="popover_manufacturer" id="select-popover-manufacturer2" data-live-search="true">';
    // html += '<option value="0" selected="selected">' + text_none + '</option>';
    $(manufacturers).each(function(key, manufacturer) {
        if (manufacturer.manufacturer_id == manufacturer_id) {
            html += '<option value="' + manufacturer.manufacturer_id + '" selected="selected">' + manufacturer.name + '</option>';
        } else {
            html += '<option value="' + manufacturer.manufacturer_id + '">' + manufacturer.name + '</option>';
        }
    });
    html += '</select>';
    html += '</div>';

    html += '<div style="display:block;text-align:right;width:100%;margin-bottom:15px;"><button id="popover-manufacturer-save" class="btn btn-success" type="button"><i class="fa fa-save"></i></button><input type="hidden" name="product_id" value="' + product_id + '" /></div>';

    td_manufacturer.html(html);

}
*/

function modalProductImages(product_id, product_image, product_thumb, product_images) {
    var html = '<div id="modalProductImages" class="modal fade" role="dialog">';
        html += '<div class="modal-dialog">';
            html += '<div class="modal-content">';
                html += '<div class="modal-header">';
                    html += '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    html += '<h4 class="modal-title">' + tab_image + '</h4>';
                html += '</div>';
                html += '<div class="modal-body">';

                html += '<form id="form-images">';

                    html += '<div class="table-responsive">';
                        html += '<table class="table table-striped table-bordered table-hover">';
                            html += '<thead>';
                            html += '<tr>';
                                html += '<td class="text-left">' + entry_image + '</td>';
                            html += '</tr>';
                            html += '</thead>';
                            html += '<tbody>';
                            html += '<tr>';
                                html += '<td class="text-left"><a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="' + product_thumb + '" alt="" title="" data-placeholder="' + placeholder + '" /></a>';
                                html += '<input type="hidden" name="image" value="' + product_image + '" id="input-image" /></td>';
                            html += '</tr>';
                            html += '</tbody>';
                        html += '</table>';
                    html += '</div>';

                    html += '<div class="table-responsive">';
                        html += '<table id="images" class="table table-striped table-bordered table-hover">';
                            html += '<thead>';
                            html += '<tr>';
                                html += '<td class="text-left">' + entry_additional_image + '</td>';
                                html += '<td class="text-right">' + entry_sort_order + '</td>';
                                html += '<td></td>';
                            html += '</tr>';
                            html += '</thead>';
                            html += '<tbody>';

                            var image_row = 0;
                            $.each(product_images, function(i, product_image) {
                                html += '<tr id="image-row' + image_row + '">';
                                    html += '<td class="text-left"><a href="" id="thumb-image' + image_row + '" data-toggle="image" class="img-thumbnail"><img src="' + product_image.thumb + '" alt="" title="" data-placeholder="' + placeholder + '" /></a>';
                                    html += '<input type="hidden" name="product_images[' + image_row + '][image]" value="' + product_image.image + '" id="input-image' + image_row + '" /></td>';
                                    html += '<td class="text-right"><input type="text" name="product_images[' + image_row + '][sort_order]" value="' + product_image.sort_order + '" placeholder="' + entry_sort_order + '" class="form-control" /></td>';
                                    html += '<td class="text-left"><button type="button" onclick="$(\'#image-row' + image_row + '\').remove();" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
                                html += '</tr>';
                                image_row++;
                            });

                            html += '</tbody>';
                            html += '<tfoot>';
                            html += '<tr>';
                                html += '<td colspan="2"></td>';
                                html += '<td class="text-left"><button type="button" id="button-image-add" onclick="addImage(' + image_row + ');" data-toggle="tooltip" title="' + button_image_add + '" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>';
                            html += '</tr>';
                            html += '</tfoot>';
                        html += '</table>';
                    html += '</div>';

                    html += '<div class="text-right"><button onclick="saveImages(' + product_id + ');" class="btn btn-success modal-images-save" type="button" data-toggle="tooltip" title="' + button_save + '"><i class="fa fa-save"></i>&nbsp;' + button_save + '</button>&nbsp;&nbsp;<button class="btn btn-default" type="button" data-toggle="tooltip" title="' + button_cancel + '" data-dismiss="modal"><i class="fa fa-reply"></i></button></div>';
                    // html += '<input type="hidden" name="product_id" value="' + product_id + '" />';

                html += '</form>';

                html += '</div>';

            html += '</div>';
        html += '</div>';
    html += '</div>';

    $('body').append(html);

    $('#modalProductImages').modal('show');

    $('#modalProductImages').on('hidden.bs.modal', function (e) {
        $(this).remove();
    });

    $(document).keypress(function(e) {
        if ($('#modalProductImages').hasClass('in') && (e.keycode == 13 || e.which == 13)) {
            e.preventDefault();
            $('#modalProductImages .modal-images-save').click();
        }
    });

}

function addImage(image_row) {
    html  = '<tr id="image-row' + image_row + '">';
	    html += '<td class="text-left"><a href="" id="thumb-image' + image_row + '"data-toggle="image" class="img-thumbnail"><img src="' + placeholder + '" alt="" title="" data-placeholder="' + placeholder + '" /></a><input type="hidden" name="product_images[' + image_row + '][image]" value="" id="input-image' + image_row + '" /></td>';
	    html += '<td class="text-right"><input type="text" name="product_images[' + image_row + '][sort_order]" value="" placeholder="' + entry_sort_order + '" class="form-control" /></td>';
	    html += '<td class="text-left"><button type="button" onclick="$(\'#image-row' + image_row  + '\').remove();" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
	html += '</tr>';

	$('#images tbody').append(html);

	image_row++;

    $('#modalProductImages #button-image-add').attr('onclick', 'addImage(' + image_row + ')');
}

function saveImages(product_id) {
    var filter_data = $('#form-images').serializeArray();

    filter_data.push( {'name' : 'action', 'value' : 'productImages'}, { 'name' : 'product_id', 'value' : product_id });

    editProductData(filter_data);
}

function popoverProductNames(product_id, names, languages) {
    var element = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(3) .btn-default');

    var popover = element.data('bs.popover');

    $('.popover').popover('destroy');

    if (popover) return;

    element.popover({
        html: true,
        placement: function(context, src) {
            $(context).addClass('popover-names');
            return 'right';
        },
        trigger: 'manual',
        container: 'body',
        content: function() {
            var html = '';
            html += '<div class="tab-content">';
                html += '<div class="tab-pane active" id="tab-names">';
                    html += '<ul class="nav nav-tabs" id="language">';
                        $.each(languages, function(i, language) {
                        html += '<li><a href="#language' + language.language_id + '" data-toggle="tab"><img src="language/' + language.code + '/' + language.code + '.png" title="' + language.name + '" /> ' + language.name + '</a></li>';
                        });
                    html += '</ul>';
                    html += '<div class="tab-content">';
                    $.each(languages, function(i, language) {
                        html += '<div class="tab-pane" id="language' + language.language_id +'">';
                            html += '<div class="form-group required">';
                                if (names[language.language_id]) {
                                    var value = names[language.language_id].name;
                                } else {
                                    var value = '';
                                }
                                html += '<div class="input-group">';
                                html += '<input type="text" name="names[' + language.language_id + '][name]" value="' +  value + '" placeholder="entry_name" id="input-name' + language.language_id + '" class="form-control" />';
                                html += '<span class="input-group-btn"><button class="btn btn-success popover-names-save" type="button"><i class="fa fa-save"></i></button></span></div>';
                            html += '</div>';
                        html += '</div>';
                    });
                    html += '<input type="hidden" name="product_id" value="' + product_id + '" />';
                    html += '</div>';
                html += '</div>';
            html += '</div>';

            return html;
        }
    });

    element.popover('show');

    setTimeout(function(){
        $('#language a:first').tab('show');
        $('.popover-content .tab-pane.active input').select();
    }, 150);

    $('.popover-names').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('.popover-names-save').click();
        }
    });

    $('.popover-names-save').on('click', function() {
        var names = [];
        var key = 0;
        $.each(languages, function(i, language) {
            names[key++] = {
                'language_id'   : language.language_id,
                'name'          : $('#tab-names #input-name' + language.language_id).val()
            }
        });

        var filter_data = {
            'action'                : 'productNames',
            'product_id'            : $('.popover-content input[name=\'product_id\']').val(),
            'names'      : names,
        }

        editProductData(filter_data);
    });
}

function popoverProductModel(product_id, model) {
    var element = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(4) .btn-default');

    var popover = element.data('bs.popover');

    $('.popover').popover('destroy');

    if (popover) return;

    element.popover({
        html: true,
        placement: 'right',
        trigger: 'manual',
        content: function() {
            return '<div class="input-group"><input type="text" value="' + model + '" id="popover-model" class="form-control" /><span class="input-group-btn"><button id="popover-model-save" class="btn btn-success" type="button"><i class="fa fa-save"></i></button></span></div>';
        }
    });

    element.popover('show');

    $('#popover-model').select();

    $('#popover-model').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('#popover-model-save').click();
        }
    });

    $('#popover-model-save').on('click', function() {
        var filter_data = {
            'action'        : 'productModel',
            'product_id'    : $(this).closest('tr').children('td:first').find('input').attr('value'),
            'model'           : $('.popover-content input').val()
        }

        editProductData(filter_data);
    });
}

function popoverProductSku(product_id, sku) {
    var element = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(5) .btn-default');

    var popover = element.data('bs.popover');

    $('.popover').popover('destroy');

    if (popover) return;

    element.popover({
        html: true,
        placement: 'right',
        trigger: 'manual',
        content: function() {
            return '<div class="input-group"><input type="text" value="' + sku + '" id="popover-sku" class="form-control" /><span class="input-group-btn"><button id="popover-sku-save" class="btn btn-success" type="button"><i class="fa fa-save"></i></button></span></div>';
        }
    });

    element.popover('show');

    $('#popover-sku').select();

    $('#popover-sku').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('#popover-sku-save').click();
        }
    });

    $('#popover-sku-save').on('click', function() {
        var filter_data = {
            'action'        : 'productSku',
            'product_id'    : $(this).closest('tr').children('td:first').find('input').attr('value'),
            'sku'           : $('.popover-content input').val()
        }

        editProductData(filter_data);
    });
}

function popoverProductManufacturer(product_id, manufacturer_id) {
    var element = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(6) .btn-manufacturer');

    var popover = element.data('bs.popover');

    $('.popover').popover('destroy');

    if (popover) return;

    element.popover({
        html: true,
        placement: function(context, src) {
            $(context).addClass('popover-manufacturer');
            return 'left';
        },
        trigger: 'manual',
        container: 'body',
        content: function() {
            var html = '<div class="form-group">';
            html += '<label class="control-label" for="select-popover-manufacturer" style="display: block">' + entry_manufacturer + '</label>';
            html += '<div class="input-group">';
            html += '<select name="popover_manufacturer" id="select-popover-manufacturer" data-live-search="true">';
            $(manufacturers).each(function(key, manufacturer) {
                if (manufacturer.manufacturer_id == manufacturer_id) {
                    html += '<option value="' + manufacturer.manufacturer_id + '" selected="selected">' + manufacturer.name + '</option>';
                } else {
                    html += '<option value="' + manufacturer.manufacturer_id + '">' + manufacturer.name + '</option>';
                }
            });
            html += '</select>';
            html += '<span class="input-group-btn"><button id="popover-manufacturer-save" class="btn btn-success" type="button"><i class="fa fa-save"></i></button></span></div>';
            html += '<input type="hidden" name="product_id" value="' + product_id + '" />';
            html += '</div>';

            return html;
        }
    });

    element.popover('show');

    setTimeout(function(){
        $('#select-popover-manufacturer').selectpicker({
            width: '100%'
        }).focus();
    }, 150);

    $(document).on('click', '.popover-manufacturer .dropdown-menu li', function(){
        $('#popover-manufacturer-save').focus();
        // $('#popover-manufacturer-save').click();
    });

    $('#popover-manufacturer-save').on('click', function() {
        var filter_data = {
            'action'                : 'editProductManufacturer',
            'product_id'            : $('.popover-content input[name=\'product_id\']').val(),
            'manufacturer_id'       : $('#select-popover-manufacturer').val(),
            'manufacturer_name'     : $('#select-popover-manufacturer').find('option:selected').text()
        }

        editProductData(filter_data);
    });
}

function popoverProductCategory(product_id, product_category, main_category_id) {
    var element = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(7) .btn-category');

    var popover = element.data('bs.popover');

    $('.popover').popover('destroy');

    if (popover) return;

    element.popover({
        html: true,
        placement: 'left',
        trigger: 'manual',
        container: 'body',
        content: function() {
            html = '';
            if (main_category_id || main_category_id == 0) {
                html += '<div class="form-group">';
                html += '<label class="control-label" for="select-main-category">' + entry_main_category + '</label>';
                html += '<select name="main_category" id="select-main-category" data-live-search="true">';
                html += '<option value="0" selected="selected">' + text_none + '</option>';
                $(categories).each(function(key, category) {
                    if (category.category_id == main_category_id) {
                        html += '<option value="' + category.category_id + '" selected="selected">' + category.name + '</option>';
                    } else {
                        html += '<option value="' + category.category_id + '">' + category.name + '</option>';
                    }
                });
                html += '</select>';
                html += '</div>';
            }
            html += '<div class="form-group">';
            html += '<label class="control-label" for="input-category"><span data-toggle="tooltip" title="' + help_category + '">' + entry_category + '</span></label>';
            html += '<select name="show_category" id="select-show-category" data-live-search="true">';
            html += '<option value="0" selected="selected"></option>';
            $(categories).each(function(key, category) {
                html += '<option value="' + category.category_id + '">' + category.name + '</option>';
            });
            html += '</select>';
            // old variant: html += '<input type="text" name="category" value="" placeholder="' + entry_category + '" id="input-category" class="form-control" />';
            html += '<div id="product-category" class="well well-sm" style="min-width:244px;width:368px;height:150px;margin-bottom:0;overflow:auto;">';
            html += '<table class="table table-striped">';
            $(product_category).each(function(key, category) {
                if (category.category_id != main_category_id) {
                    html += '<tr><td class="checkbox">';
                    html += '<label id="product-category' + category.category_id + '">';
                    html += '<input type="checkbox" name="product_category" value="' + category.category_id + '" checked="checked" />&nbsp;';
                    html += category.name;
                    html += '</label>';
                    html += '</td></tr>';
                }
            });
            html += '</table>';
            html += '</div>';
            html += '<a class="select-all" onclick="$(this).parent().find(\':checkbox\').prop(\'checked\', true);">' + text_select_all + '</a> / <a class="unselect-all" onclick="$(this).parent().find(\':checkbox\').prop(\'checked\', false);">' + text_unselect_all + '</a>';
            html += '</div>';
            html += '<div style="display:block;text-align:right;width:100%;margin-bottom:15px;"><button id="popover-category-save" class="btn btn-success" type="button"><i class="fa fa-save"></i></button><input type="hidden" name="product_id" value="' + product_id + '" /></div>';

            return html;
        }
    });

    element.popover('show');

    setTimeout(function(){
        $('#select-main-category, #select-show-category').selectpicker({
            width: '100%'
        });
    }, 150);

    $('#select-show-category').change(function() {
        $('#product-category' + $(this).val()).closest('tr').remove();

        html = '<tr><td class="checkbox"><label id="product-category' + $(this).val() + '">';
        html += '<input type="checkbox" name="product_category" value="' + $(this).val() + '" checked="checked" />&nbsp;';
        html += $(this).find('option:selected').text();
        html += '</label></td></tr>';

        $('#product-category table').append(html);

        $('#select-show-category').selectpicker('val', 0);
    });

    /* old variant
    $('input[name=\'category\']').autocomplete({
        'source': function(request, response) {
            $.ajax({
                url: 'index.php?route=catalog/category/autocomplete&user_token='  + getURLVar('user_token') + '&filter_name=' +  encodeURIComponent(request),
                dataType: 'json',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'],
                            value: item['category_id']
                        }
                    }));
                }
            });
        },
        'select': function(item) {
            $('input[name=\'category\']').val('');

            $('#product-category' + item['value']).closest('tr').remove();

            html = '<tr><td class="checkbox"><label id="product-category' + item['value'] + '">';
            html += '<input type="checkbox" name="product_category" value="' + item['value'] + '" checked="checked" />&nbsp;';
            html += item['label'];
            html += '</label></td></tr>';

            $('#product-category table').append(html);
        }
    });
    */

    $('#popover-category-save').on('click', function() {
        var product_id = $('.popover-content input[name=\'product_id\']').val();

        var product_category = [];

        $('#product-category tr td input:checked').each(function() {
            product_category.push($(this).val());
        });

        var main_category_id = $('.popover-content select[name=\'main_category\']').val();

        var filter_data = {
            'action'                : 'editProductCategory',
            'product_id'            : product_id,
            'product_category'      : product_category,
            'main_category_id'      : main_category_id
        }

        editProductData(filter_data);
    });
}



function popoverPrice(product_id, price) {
    var element = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(8) .btn-default');

    var popover = element.data('bs.popover');

    $('.popover').popover('destroy');

    if (popover) return;

    element.popover({
        html: true,
        placement: 'left',
        trigger: 'manual',
        content: function() {
            return '<div class="input-group"><input type="text" value="' + price + '" id="popover-price" class="form-control" /><span class="input-group-btn"><button id="popover-price-save" class="btn btn-success" type="button"><i class="fa fa-save"></i></button></span></div>';
        }
    });

    element.popover('show');

    //element.siblings('.popover').find('input').select();
    $('#popover-price').select();

    // float number
    $('#popover-price').keypress(function(num_event) {
        if(num_event.which < 46 || num_event.which > 59 || num_event.which == 47) {
            num_event.preventDefault();
        }

        if(num_event.which == 46 && $(this).val().indexOf('.') != -1) {
            num_event.preventDefault();
        }
    });

    $('#popover-price').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('#popover-price-save').click();
        }
    });

    $('#popover-price-save').on('click', function() {
        var product_id = $(this).closest('tr').children('td:first').find('input').attr('value');

        var price = $('.popover-content input').val();

        var filter_data = {
            'action'                : 'editPrice',
            'product_id'            : product_id,
            'price'                 : price
        }

        editProductData(filter_data);
    });
}

function popoverPriceSpecial(product_id, price, product_special_id) {
    var element = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(8) .price-special');

    var popover = element.data('bs.popover');

    $('.popover').popover('destroy');

    if (popover) return;

    element.popover({
        html: true,
        placement: 'left',
        trigger: 'manual',
        content: function() {
            return '<div class="input-group"><input type="text" value="' + price + '" id="popover-price-special" class="form-control" /><span class="input-group-btn"><button id="popover-price-special-save" class="btn btn-success" type="button"><i class="fa fa-save"></i></button></span></div>';
        }
    });

    element.popover('show');

    //element.siblings('.popover').find('input').select();
    $('#popover-price-special').select();

    // float number
    $('#popover-price-special').keypress(function(num_event) {
        if(num_event.which < 46 || num_event.which > 59 || num_event.which == 47) {
            num_event.preventDefault();
        }

        if(num_event.which == 46 && $(this).val().indexOf('.') != -1) {
            num_event.preventDefault();
        }
    });

    $('#popover-price-special').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('#popover-price-special-save').click();
        }
    });

    $('#popover-price-special-save').on('click', function() {
        var product_id = $(this).closest('tr').children('td:first').find('input').attr('value');

        var price = $('.popover-content input').val();

        var filter_data = {
            'action'                : 'editPriceSpecial',
            'product_id'            : product_id,
            'price'                 : price,
            'product_special_id'    : product_special_id
        }

        editProductData(filter_data);
    });
}

function popoverQuantity(product_id) {
    var element = $('#form-product input[value=\'' + product_id + '\']').closest('tr').find('td:eq(9) .btn-quantity');

    var quantity = parseInt(element.text());

    var popover = element.data('bs.popover');

    $('.popover').popover('destroy');

    if (popover) return;

    element.popover({
        html: true,
        placement: 'left',
        trigger: 'manual',
        content: function() {
            return '<div class="input-group"><input type="text" value="' + quantity + '" id="popover-quantity" class="form-control" /><span class="input-group-btn"><button id="popover-quantity-save" class="btn btn-success" type="button"><i class="fa fa-save"></i></button></span></div>';
        }
    });

    element.popover('show');

    element.siblings('.popover').find('input').select();

    // float number
    $('#popover-quantity').keypress(function(num_event) {
        if(num_event.which < 45 || num_event.which > 57 || num_event.which == 46 || num_event.which == 47) {
            num_event.preventDefault();
        }

        if(num_event.which == 45 && $(this).val().indexOf('-') != -1) {
            num_event.preventDefault();
        }
    });

    $('#popover-quantity').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            $('#popover-quantity-save').click();
        }
    });

    $('#popover-quantity-save').on('click', function() {
        var product_id = $(this).closest('tr').children('td:first').find('input').attr('value');

        var quantity = $('.popover-content input').val();

        var filter_data = {
            'action'                : 'editQuantity',
            'product_id'            : product_id,
            'quantity'              : quantity
        }

        editProductData(filter_data);
    });
}

function getProductData(data) {
    // data.push({'name' : 'license_key', 'value' : apf_settings['license_key']});
    if (data.action) {
        data.license_key = apf_settings['license_key'];
    } else {
        data.push({'name': 'license_key', 'value': apf_settings['license_key']});
    }
    $.ajax({
        url: 'index.php?route=extension/admin_product_filter/getProductData&user_token=' + getURLVar('user_token'),
        type: 'post',
        dataType: 'json',
        data: data,
        success: function(json) {
            if (json['success']) {
                // get Images
                if (json['action'] == 'productImages') {
                    modalProductImages(json['product_id'], json['product_image'], json['product_thumb'], json['product_images']);
                }

                // get Name
                if (json['action'] == 'productNames') {
                    popoverProductNames(json['product_id'], json['names'], json['languages']);
                }

                // get Model
                if (json['action'] == 'productModel') {
                    popoverProductModel(json['product_id'], json['model']);
                }

                // get Sku
                if (json['action'] == 'productSku') {
                    popoverProductSku(json['product_id'], json['sku']);
                }

                // get Manufacturer ID
                if (json['action'] == 'getProductManufacturer') {
                    popoverProductManufacturer(json['product_id'], json['manufacturer_id']);
                }

                // get Category
                if (json['action'] == 'getProductCategory') {
                    popoverProductCategory(json['product_id'], json['product_category'], json['main_category_id']);
                }

                // Price
                if (json['action'] == 'getPrice') {
                    popoverPrice(json['product_id'], json['price']);
                }

                // Price Special
                if (json['action'] == 'getPriceSpecial') {
                    if (!json['price']) {
                        json['price'] = '0.0000'
                    }

                    if (!json['product_special_id']) {
                        json['product_special_id'] = '';
                    }

                    popoverPriceSpecial(json['product_id'], json['price'], json['product_special_id']);
                }
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

function editProductData(data) {
    if (data.action) {
        data.license_key = apf_settings['license_key'];
    } else {
        data.push({'name': 'license_key', 'value': apf_settings['license_key']});
    }
    $.ajax({
        url: 'index.php?route=extension/admin_product_filter/editProductData&user_token=' + getURLVar('user_token'),
        type: 'post',
        dataType: 'json',
        data: data,
        success: function(json) {
            if (json['success']) {
                $('.popover').popover('destroy');

                $('#form-product tr td').removeClass('edited');

                // Images
                if (json['action'] == 'productImages') {
                    $('#modalProductImages').modal( 'hide' ).data( 'bs.modal', null );

                    var td_images = $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(2)');

                    var alt = $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(3)').text().trim();

                    var html = '<img src="' + json['product_thumb'] + '" alt="' + alt + '" class="img-thumbnail" />'

                    html += '&nbsp;&nbsp;<a class="btn btn-xs btn-default btn-images"></a>';

                    if (json['product_images']) {
                        html += '<div class="product-images">';

                        var i = 0;
                        var count_images = json['product_images'].length;

                        $.each(json['product_images'], function(i, product_image) {
                            if (count_images <= 3 && i < 3) {
                                html += '<img src="' + product_image.thumb + '" alt="' + alt + '" class="img-thumbnail" />&nbsp;';
                            } else if (count_images > 3 && i < 2) {
                                html += '<img src="' + product_image.thumb + '" alt="' + alt + '" class="img-thumbnail" />&nbsp;';
                            }
                            i++;
                        });

                        if (count_images > 3) {
                            html += '<a class="btn btn-xs btn-default disabled">..' + (count_images - 2) + '</a>';
                        }

                        html += '</div>';
                    }

                    td_images.addClass('edited').html(html);
                }

                // Name
                if (json['action'] == 'productNames') {
                    var td_name = $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(3)');

                    html = json['name'] + '&nbsp;&nbsp;<a class="btn btn-xs btn-default btn-name"></a></div>';

                    td_name.addClass('edited').html(html);
                }

                // Model
                if (json['action'] == 'productModel') {
                    var btn = $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(4)');

                    if (json['model'] != '') {
                        btn.addClass('edited').html('<a class="btn btn-xs btn-default btn-model">' + json['model'] + '</a>');
                    } else {
                        btn.addClass('edited').html('<a class="btn btn-xs btn-default btn-model-add"></a>');
                    }
                }

                // Sku
                if (json['action'] == 'productSku') {
                    var btn = $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(5)');

                    if (json['sku'] != '') {
                        btn.addClass('edited').html('<a class="btn btn-xs btn-default btn-sku">' + json['sku'] + '</a>');
                    } else {
                        btn.addClass('edited').html('<a class="btn btn-xs btn-default btn-sku-add"></a>');
                    }
                }

                // Manufacturer
                if (json['action'] == 'editProductManufacturer') {
                    var td_manufacturer = $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(6)');

                    var html = '';

                    if (json['manufacturer_id'] != 0) {
                        html += json['manufacturer_name'];
                    }

                    html += '&nbsp;&nbsp;<a class="btn btn-xs btn-default btn-manufacturer"></a></div>';

                    td_manufacturer.addClass('edited').html(html);
                }

                // Category
                if (json['action'] == 'editProductCategory') {
                    var td_category = $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(7)');

                    var html = '';

                    if (json['main_category_id']) {
                        $(json['product_category']).each(function(key, category) {
                            if (json['main_category_id'] && json['main_category_id'] == category.category_id) {
                                html += '<div><span class="category-level-up">↸</span>&nbsp;' + category.name + '</div>';
                            }
                        });
                    }

                    $(json['product_category']).each(function(key, category) {
                        if (json['main_category_id'] != category.category_id) {
                            html += '<span class="category-level-down">↴</span>&nbsp' + category.name + '<br>';
                        }
                    });

                    html += '<a class="btn btn-xs btn-default btn-category"></a></div>';

                    td_category.addClass('edited').html(html);
                }

                // Price
                if (json['action'] == 'editPrice') {
                    var btn =  $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(8) .btn-default');

                    btn.html(json['price_format']);

                    if (btn.siblings('.price-special').html() != '') {
                        btn.attr('style', 'text-decoration: line-through;')
                    }

                    $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(8)').addClass('edited');
                }

                // Price Special
                if (json['action'] == 'editPriceSpecial') {
                    var btn =  $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(8) .price-special');

                    if (json['price'] == 0) {
                        if (btn.hasClass('btn-primary')) {
                            btn.html('');
                        } else {
                            btn.removeClass('btn-danger').addClass('btn-primary').html('');
                        }

                        btn.siblings('.btn-default').attr('style', '')
                    } else {
                        if (btn.hasClass('btn-primary')) {
                            btn.removeClass('btn-primary').addClass('btn-danger').html(json['price_format']);
                        } else {
                            btn.html(json['price_format']);
                        }

                        btn.siblings('.btn-default').attr('style', 'text-decoration: line-through;')
                    }

                    $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(8)').addClass('edited');
                }

                // Quantity
                if (json['action'] == 'editQuantity') {
                    var btn =  $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(9) .btn-quantity');

                    var quantity = json['quantity'];

                    var quantity_class = '';

                    if (quantity <= 0) {
                        quantity_class  = 'warning';
                    } else if (quantity >= 1 && quantity <= 5) {
                        quantity_class = 'danger';
                    } else {
                        quantity_class = 'success';
                    }

                    btn.removeClass('btn-warning btn-danger btn-success').addClass('btn-' + quantity_class).html(quantity);

                    $('#form-product input[value=\'' + json['product_id'] + '\']').closest('tr').find('td:eq(9)').addClass('edited');
                }
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

// edit Status
function changeProductStatus(product_id, status) {
    $.get('index.php?route=extension/admin_product_filter/changeProductStatus', {
            'product_id'    : product_id,
            'status'        : status,
            'user_token'    : getURLVar('user_token')
        }
    );
}

