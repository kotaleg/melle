<?php
$price_ds = 0.00;
$product_id_ds = 0;
if(isset($price) && $price){
    $price_ds = (float)$price;
    if(isset($special) && $special){
        $price_ds = (float)$special;
    }
}
if(isset($product_id) && $product_id){
    $product_id_ds = (int)$product_id;
}
?>
<script type="text/javascript" >
    $(document).ready(function() {
    $.ajax({
        url: 'index.php?route=total/discountsales/renderDiscountSales',
        type: 'post',
        dataType: 'html',
        data: 'price_ds=<?php echo $price_ds ?>&product_id_ds=<?php echo $product_id_ds ?>',
        beforeSend: function() {
        },
        complete: function() {
        },
        success: function(response) {
                   $('#discountsales').append(response);
        }
    });
    });
</script>
<div id='discountsales'></div>