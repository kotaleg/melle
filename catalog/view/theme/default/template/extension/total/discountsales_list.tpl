<!--<h4><?php echo $data['text_discountsales_programme']; ?></h4>-->
<table width='100%' class="table table-hover table-bordered ">
    <?php $row = 0; ?>
        <thead>
                <td style="text-align: center"><?php echo $data['text_discountsales_value_ot']; ?></td>
                <td style="text-align: center"><?php echo $data['text_discountsales_value_do']; ?></td>
                <td style="text-align: center"><?php echo $data['text_discountsales_value_discount']; ?></td>
        </thead>
        <?php foreach($results as $limit=>$discountsales){ ?>
            <?php if(!$row && (int)$discountsales['value_data_float']){ ?>
            <tr>
                <td style="text-align: center"><?php echo $discountsales['min']; ?></td>
                <td style="text-align: center"><?php echo $discountsales['sales_limitation']; ?></td>
                <td><?php echo $discountsales['text']; ?> <?php echo $discountsales['value_data']; ?></td>
            </tr>
            <?php $row++; ?>
            <?php }elseif($row){ ?>
            <tr>
                <td style="text-align: center"><?php echo $discountsales['min']; ?></td>
                <?php if((int)$discountsales['sales_limitation_float']==1000000000){ ?>
                    <td style="text-align: center">&#8734;</td>
                <?php }else{ ?>
                    <td style="text-align: center"><?php echo $discountsales['sales_limitation']; ?></td>
                <?php } ?>

                <td><?php echo $discountsales['text']; ?> <?php echo $discountsales['value_data']; ?></td>
            </tr>
            <?php $row++; ?>
            <?php } ?>
    <?php } ?>
</table>