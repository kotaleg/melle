<template>
    <section class="lk-history">
       <div class="lk-history__table-wrap">
          <table class="lk-history__table">
             <thead>
                <tr>
                   <th>Товар</th>
                   <th>Дата оформления</th>
                   <th>Количество товаров</th>
                   <th>Сумма заказа </th>
                   <th>Статус оплаты</th>
                   <th></th>
                </tr>
             </thead>
             <tbody>
                <tr>
                   <td>
                      <div class="lk-history__order"><a href="/cabinet/action.orders/pk/742">Заказ № 742</a></div>
                   </td>
                   <td>
                      <div class="lk-history__data"><span>22.01.2019</span></div>
                   </td>
                   <td>
                      <div class="lk-history__amount"><span>3 </span><span>товара</span></div>
                   </td>
                   <td>
                      <div class="lk-history__price"><span class="lk-history__price-default">4 497 <span class="ruble-sign">Р</span></span></div>
                   </td>
                   <td>
                      <div class="lk-history__status"><span>Ожидает оплаты</span></div>
                   </td>
                   <td>
                      <div class="lk-history__del"><a href="/cabinet/action.reOrder/pk/742"><span>Повторить</span></a></div>
                   </td>
                </tr>
             </tbody>
          </table>
       </div>
    </section>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
    components: {

    },
    computed: {

    },
    methods: {

    },
    created() {
        this.$store.dispatch('account/initData')
    },
}
</script>

<style lang="scss">

</style>