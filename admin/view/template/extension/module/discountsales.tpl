<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-featured" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>

    <style>
            .small_text_row {
                font-size: 10px;
                color: #aaa;
            }
            .small_text_row:hover {
                color:black;
            }
            #tab-documentation img{
                border: 1px solid #049f9d;
                margin-bottom: 10px;
            }
            thead td {
            background: #efefef;
            }
            table.table > tbody > tr > td{
                padding: 3px;
            }
            .btn-danger{
                padding: 3px;
                padding-left: 6px;
                padding-right: 6px;
            }

            .apply_any_am_ord_link{
                border-bottom: 1px dashed;
                font-size: 10px;
                cursor: pointer;
                color: #1e91cf;
            }

            .add_tbody select, .add_tbody input, .add_tbody textarea, table.add_tbody
            {
                    height: 25px;
                    padding: 3px 8px;
                    font-size: 12px;
                    color: #555;
                    background-color: #fff;
                    background-image: none;
                    border: 1px solid #ccc;
                    border-radius: 3px;
                    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
                    box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
                    -webkit-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
                    -o-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
                    transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
                }
                .add_tbody tr td{
                padding: 8px !important;
                }
                .add_tbody select{

                }
        </style>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-featured" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-name"><?php echo $entry_name; ?></label>
            <div class="col-sm-10">
              <input type="text" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" />
              <?php if ($error_name) { ?>
              <div class="text-danger"><?php echo $error_name; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-product"><?php echo $entry_product; ?></label>
            <div class="col-sm-10">





                <div class="table-responsive">
                            <table id="discounts-complect" class="table table-bordered table-hover">
                                <?php $discount_complect_row = 0; ?>
                                <?php foreach ($discountsales_complect_discount as $discount) { ?>
                                <tbody  class="add_tbody" id="discount-row-complect<?php echo $discount_complect_row; ?>" style="border-left: 0px solid #<?php echo $discount['id_container'] ?>">
                                    <tr>
                                        <td colspan="2">
                                            <?php echo $entry_name_complect; ?>: <?php echo $discount['name_complect'] ?>
                                            <?php $complect_id=''; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="left">
                                            <div class="well well-sm" style="overflow: auto; max-height: 150px; margin-bottom: 0px; ">
                                                <ul style="margin-bottom: 0px;">

                                                <?php ksort($discount['product_quantity']);?>

                                                <?php foreach ($discount['product_quantity'] as $product_id_quantity => $product_quantity) { ?>
                                                <?php
                                                    $quantity = (int)$product_quantity['quantity'];
                                                    $complect_id .= '_'.$product_id_quantity.'_'.$quantity;
                                                ?>
                                                <li>
                                                    <div><?php echo $product_quantity['name']; ?>, <?php echo $text_quantity_products ?>: <?php echo $product_quantity['quantity']; ?>, <?php echo $text_new_price ?>: <?php echo $product_quantity['new_price']; ?></div>
                                                </li>
                                                <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <select name="status_complect[<?php echo $complect_id; ?>]">
                                            <?php if (isset($status_complect[$complect_id]) && $status_complect[$complect_id]) { ?>
                                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                            <option value="0" ><?php echo $text_disabled; ?></option>
                                            <?php } else { ?>
                                            <option value="1"><?php echo $text_enabled; ?></option>
                                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                            <?php } ?>
                                           </select>
                                        </td>
                                      </tr>
                                </tbody>
                                <?php $discount_complect_row++; ?>
                                <?php } ?>
                            </table>
                        </div>






            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-limit"><?php echo $entry_limit; ?></label>
            <div class="col-sm-10">
              <input type="text" name="limit" value="<?php echo $limit; ?>" placeholder="<?php echo $entry_limit; ?>" id="input-limit" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-width"><?php echo $entry_width; ?></label>
            <div class="col-sm-10">
              <input type="text" name="width" value="<?php echo $width; ?>" placeholder="<?php echo $entry_width; ?>" id="input-width" class="form-control" />
              <?php if ($error_width) { ?>
              <div class="text-danger"><?php echo $error_width; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-height"><?php echo $entry_height; ?></label>
            <div class="col-sm-10">
              <input type="text" name="height" value="<?php echo $height; ?>" placeholder="<?php echo $entry_height; ?>" id="input-height" class="form-control" />
              <?php if ($error_height) { ?>
              <div class="text-danger"><?php echo $error_height; ?></div>
              <?php } ?>
            </div>
          </div>


            <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status_show_category_product_discount_complect; ?></label>
            <div class="col-sm-10">
              <select name="status_show_category_product_discount_complect" id="input-status" class="form-control">
                <?php if ($status_show_category_product_discount_complect) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

            <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status_show_manufacturer_discount_complect; ?></label>
            <div class="col-sm-10">
              <select name="status_show_manufacturer_discount_complect" id="input-status" class="form-control">
                <?php if ($status_show_manufacturer_discount_complect) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="status" id="input-status" class="form-control">
                <?php if ($status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script type="text/javascript"><!--
$('input[name=\'product\']').autocomplete({
  source: function(request, response) {
    $.ajax({
      url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
      dataType: 'json',
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item['name'],
            value: item['product_id']
          }
        }));
      }
    });
  },
  select: function(item) {
    $('input[name=\'product\']').val('');

    $('#featured-product' + item['value']).remove();

    $('#featured-product').append('<div id="featured-product' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="product[]" value="' + item['value'] + '" /></div>');
  }
});

$('#featured-product').delegate('.fa-minus-circle', 'click', function() {
  $(this).parent().remove();
});
//--></script></div>
<?php echo $footer; ?>