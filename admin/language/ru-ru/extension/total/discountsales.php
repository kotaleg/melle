<?php
$_['heading_title']    = 'Discount Sales PRO - управление скидками / наценками';
$_['text_success']     = 'Настройки успешно сохранены';
$_['text_money']     = 'в валюте по умолчанию';
$_['text_percent']     = '%';
$_['text_plus']     = 'плюс';
$_['text_minus']     = 'минус';
$_['text_none_all_payments'] = 'Для любого метода оплаты';
$_['text_total']       = 'Учитывать в заказе';

$_['tab_manufacturer']       = 'Скидки <b>по производителю</b>';
$_['tab_category']       = 'Скидки <b>по категории</b>';
$_['tab_complect']       = 'Скидки <b>на комплекты</b>';
$_['tab_sales']       = 'Скидки <b>на сумму заказа</b>';
$_['tab_documentation']       = 'Инструкция';
$_['tab_promo']       = 'Продвижение и настройки';

$_['text_examples']       = 'Примеры';
$_['text_examples_1_an']       = 'Пример наценки 5% при методе оплаты PayPal';
$_['text_examples_1_des']       = '<span style="color:red"><br>Нужно указать сумму заказа до которого нужно делать наценку.<br>Если для любой суммы, укажите какое-нибудь большое число, например, 1 млн.</span>';
$_['text_examples_2_an']       = 'Пример скидки 12% при сумме заказа более 3000 р.';
$_['text_examples_2_des']       = '<span style="color:red"><br>Нужно указать сумму заказа до которого нужно делать (или не делать) скидку.<br>Если для любой суммы, укажите какое-нибудь большое число, например, 1 млн.</span>';
$_['text_enable'] = 'Включено';
$_['entry_sales_limitation']       = 'Cумма заказа, <b>до</b> которой применять скидку, в валюте по умолчанию';
$_['entry_sales_limitation_info']       = 'Применение скидки / наценки будет до, то есть не включая данное число - при любом значении, меньше данного числа. Например, если указать 100 скидка будет применяться к суммам заказа до 100 (меньше 100), но не равно 100';
$_['entry_tax_class']  = 'Класс налога:';
$_['entry_status']     = 'Статус:';
$_['entry_status_all']     = 'Статус работы всех скидок:';
$_['entry_status_exp']     = '<span style="color:red">Временно отключено. Опция будет доступна в ближайшем бесплатном обновлении</span>';
$_['entry_payment'] = 'Способ оплаты:';
$_['entry_delta'] = 'Наценка или скидка:';
$_['entry_category'] = 'Категория';
$_['entry_complect'] = 'Комплект';
$_['entry_name_complect'] = 'Название комплекта (может использоваться в рекламе, будет выводиться в корзине)';
$_['entry_sort_order'] = 'Порядок сортировки';
$_['entry_promo_category'] = 'Показывать скидку (в т.ч. обновлять цену) на категорию?';
$_['entry_promo_discount'] = 'Показывать скидку (в т.ч. обновлять цену) на сумму заказа?';
$_['entry_promo_manufacturer'] = 'Показывать скидку (в т.ч. обновлять цену) на производителя?';
$_['entry_promo_manufacturer_position'] = 'Разместить';
$_['entry_promo_manufacturer_position_on_image'] = 'Сверху картинки';
$_['entry_promo_manufacturer_position_up_h4'] = 'Под названием продукта';
$_['entry_promo_manufacturer_position_css'] = 'Подвесить в css класс';
$_['entry_promo_manufacturer_position_css_info'] = 'Разместите css класс в файлах tpl, где выводится информация о продукте. Класс можно разместить внутри класса DIV с классом `caption` или вывести класс с product_id. Например, МОЙ_CSS_КЛАСС_PRODUCT_ID, где PRODUCT_ID - product_id продукта, а МОЙ_CSS_КЛАСС - класс, который задается в этом поле';
$_['entry_promo_manufacturer_color'] = 'Цвет';
$_['entry_promo_manufacturer_text'] = 'Текст перед скидкой';
$_['entry_promo_to_product'] = 'Показывать скидки (в т.ч. обновлять цену) в карточке товара?';
$_['entry_apply_any_am_ord_link'] = 'До бесконечности';
$_['entry_sales_limit_input'] = 'Максимальная сумма заказа';



$_['entry_manufacturer'] = 'Производитель';
$_['entry_select'] = 'Выбрать';
$_['entry_status_together'] = 'Использовать вместе с другими';
$_['entry_status_together_disabled'] = 'Только, если другие скидки не применялись';
$_['entry_complect_product'] = 'Выбрать товары';
$_['entry_complect_quantity'] = 'Минимальное количество товара в комплетке и другая цена';
$_['entry_complect_quantity_info'] = 'При достижении которого применять скидку. Если в количестве 0 - для любого количества указанного товара. Если Цена пустая, то цена меняться не будет. Если в цене 0, то бесплатно. Если в цене число, то вместо цены в корзине, будет ставиться данная цена';



$_['button_add_discount'] = 'Добавить';
$_['button_remove'] = 'Удалить';
// Error
$_['error_permission'] = 'У Вас нет прав для управления этим модулем!';
$_['error_notifications'] = 'Ошибка проверки бесплатного обновления. Обновления доступны на сервере: www.ocext.com';

$_['text_quantity_products']       = 'Min количество';
$_['text_new_price']       = 'Цена в комлекте';


$_['entry_date_start'] = 'Дата начала';
$_['entry_date_finish'] = 'Дата окончания';


$_['entry_complect_disable_other_discount'] = 'Отключить другие скидки';
$_['entry_disable_on_specprice'] = 'Не применять к товарам, у которых уже есть специальная цена';
$_['entry_disable_on_this_categories'] = 'Не применять скидки к товарам из категорий';
$_['entry_disable_on_this_categories_info'] = 'Создайте наборы категорий и примените их в нужных скидках';
$_['entry_disable_on_this_categories_name'] = 'Название';
$_['entry_disable_need_create'] = 'Создайте список в настройках';

?>