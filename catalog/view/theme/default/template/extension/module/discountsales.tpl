<?php if($complects){ ?>
<h3><?php echo $name ?></h3>
<div class="carousel_discountsales">
<div id="carousel<?php echo $module; ?>" class="owl-carousel" style="margin-bottom: 25px; margin-top: 20px;">
<?php foreach ($complects as $complect_id=>$complect) { ?>

<div class="item" style="padding: 20px;">

    <?php if($complect['info']['name_complect']){ ?>

    <h2 style="margin-top: 0px;"><?php echo $complect['info']['name_complect']; ?></h2>
    <hr>

    <?php } ?>


<?php

$products = $complect['pruducts'];
$count_products = count($complect['pruducts']);
$count_view_products = 0;
$complect_unique_id = md5(time());
?>

<div class="row row_carusel_top" id="row_carusel<?php echo $complect_id ?>" >
   <script type="text/javascript"><!--

    var discountsales_complects_<?php echo $complect_id ?> = [];

  //--></script>

  <?php foreach ($products as $product) { ?>

  <script type="text/javascript"><!--
    discountsales_complects_<?php echo $complect_id ?>[<?php echo $product['product_id'] ?>] = '<?php echo $complect_id ?>';
    //discountsales_complects_<?php echo $complect_id ?>[<?php echo $product['product_id'] ?>] = <?php echo $product['complect_quantity'] ?>;
  //--></script>
  <div id="cart_discountsales_complect_data<?php echo $product['product_id'].$complect_id ?>" style="display: none">
      <input type="hidden" name="quantity" value="<?php echo $product['complect_quantity'] ?>" />
      <input type="hidden" name="option[1000000]" value="<?php echo $complect_id ?>" />
      <input type="hidden" name="option[1000001]" value="<?php echo $complect_unique_id ?>" />
      <input type="hidden" name="product_id" value="<?php echo $product['product_id'] ?>" />
  </div>
  <div class="product-layout col-lg-3 col-md-3 col-sm-6 col-xs-12 row_carusel<?php echo $complect_id ?>" style="">
    <div class="product-thumb transition">
      <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" class="img-responsive" /></a></div>
      <div class="caption" style="min-height: 10px;">
        <h3><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h3>
        <!--<p><?php echo $product['description']; ?></p>-->
        <?php if ($product['rating']) { ?>
        <div class="rating">
          <?php for ($i = 1; $i <= 5; $i++) { ?>
          <?php if ($product['rating'] < $i) { ?>
          <span class="fa fa-stack"><i class="fa fa-star-o fa-stack-2x"></i></span>
          <?php } else { ?>
          <span class="fa fa-stack"><i class="fa fa-star fa-stack-2x"></i><i class="fa fa-star-o fa-stack-2x"></i></span>
          <?php } ?>
          <?php } ?>
        </div>
        <?php } ?>
        <?php if ($product['price']) { ?>
        <p class="price">
          <?php if (!$product['special']) { ?>
          <?php echo $product['price']; ?>
          <?php } else { ?>
          <span class="price-new"><?php echo $product['special']; ?></span> <span class="price-old"><?php echo $product['price']; ?></span>
          <?php } ?>
          <?php if ($product['tax']) { ?>
          <span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
          <?php } ?>
        </p>
        <?php } ?>
      </div>

    </div>

    <?php if($product['complect_quantity']>1){ ?>
        <div class="product-thumb discountsales_sign discountsales_sign_multiply discountsales_sticker">&nbsp;x <span class="discountsales_multiply"><?php echo $product['complect_quantity']; ?></span></div>
    <?php } ?>

  </div>

    <?php $count_view_products++ ?>

    <?php

        if($count_view_products>0 && $count_products>1){

        ?>

        <div class="product-layout col-lg-1 col-md-3 col-sm-6 col-xs-12 row_carusel<?php echo $complect_id ?> discountsales_sign_top">
          <div class="product-thumb discountsales_sign"><span class="discountsales_plus">+</span></div>
        </div>

        <?php

        }
        $count_products--;

        ?>

        <?php

        if(!$count_products){

        ?>

        <div class="product-layout col-lg-1 col-md-3 col-sm-6 col-xs-12 row_carusel<?php echo $complect_id ?>" style="width: auto">
            <div class="product-thumb discountsales_sign"><span class="discountsales_plus">=</span> <s><?php echo $complect['info']['total_complect_old_price_text'] ?></s><span class="discountsales_new_cost"><?php echo $complect['info']['total_complect_price_text'] ?></span>
                <div class="button-group" style=" margin-top: 5px">
                <button class="button-complect<?php echo $complect_id ?>" type="button"  style="width: 100%;" onclick="addComplectToCart(discountsales_complects_<?php echo $complect_id ?>,cart);"><i class="fa fa-shopping-cart"></i> <span class="hidden-xs hidden-sm hidden-md"><?php echo $button_cart; ?></span></button>
            </div>
            </div>
        </div>

        <?php

        }

        ?>


  <?php } ?>
</div>
</div>
<?php } ?>
</div>
</div>
<?php } ?>

<script type="text/javascript"><!--
    $(document).ready(function() {

        $('#carousel<?php echo $module; ?>').owlCarousel({
                items: 1,
                autoPlay: 3000,
                navigation: true,
                navigationText: ['<i class="fa fa-chevron-left fa-5x"></i>', '<i class="fa fa-chevron-right fa-5x"></i>'],
                pagination: true,
                itemsDesktopSmall: [979, 2],
                itemsDesktop: [1218, 1],
                itemsMobile : [578, 1],
                autoHeight : false
        });

        $('.row_carusel_top div.clearfix').remove();

    });
--></script>