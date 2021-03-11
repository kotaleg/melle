<?php
class ModelApiExport extends Model
{
    private $codename = 'export';
    private $route = 'api/export';

    private $export_path;

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->export_path = $this->getRootPath() . 'exports/';
    }

    private function getRootPath()
    {
        return dirname(DIR_SYSTEM).'/';
    }

    public function actionCsvlinksExport()
    {
        $file = $this->export_path . 'seoLinks.csv';
        if (is_file($file)) { @unlink($file); }
        $this->createPath($file);

        $f = fopen($file, 'w');

        $this->_str = "\xEF\xBB\xBF";
        fwrite($f, $this->_str);
        fclose($f);

        $this->load->model('catalog/product');
        $products = $this->model_catalog_product->getProducts();

        $ex = new \pro_csv\pro_csv('EXPORT');
        $ex->unstrict();
        $ex->setDelimiter(",");
        $ex->setFileMode("a");
        $ex->setColumnHeaders(array('TITLE','URL','EXTRA_DESCRIPTION'));

        $pcount = 0;
        $rows = array();

        $this->load->model('extension/module/extra_description');

        foreach ($products as $product) {

            $extraDescription =  $this->model_extension_module_extra_description
                ->getDescription($product['product_id']);
            $extraDescription = html_entity_decode($extraDescription, ENT_QUOTES, 'UTF-8');
            $extraDescription = strip_tags($extraDescription);

            // we still can have entities like &nbsp; before this step
            $extraDescription = html_entity_decode($extraDescription, ENT_QUOTES, 'UTF-8');

            $rows[] = array(
                $product['name'],
                $this->url->link('product/product', "product_id={$product['product_id']}"),
                $extraDescription,
            );

            $pcount++;
        }

        $json['filePath'] = str_replace($this->getRootPath(), HTTPS_SERVER, $file);
        $json['success'] = $ex->export($file, $rows);

        $json['message'][] = "Обработано {$pcount} товаров.";
        return $json;
    }

    public function actionNoconnectedimageExport()
    {
        $file = $this->export_path . 'noConnectedImageLog.csv';
        if (is_file($file)) { @unlink($file); }
        $this->createPath($file);

        $f = fopen($file, 'w');

        $this->_str = "\xEF\xBB\xBF";
        fwrite($f, $this->_str);
        fclose($f);

        $ex = new \pro_csv\pro_csv('EXPORT');
        $ex->unstrict();
        $ex->setDelimiter(",");
        $ex->setFileMode("a");
        $ex->setColumnHeaders(array('PRODUCT_ID','TITLE','COLOR','SIZE'));

        $pcount = 0;
        $rows = array();

        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');
        $products = $this->model_catalog_product->getProducts();

        foreach ($products as $product) {

            $combinations = $this->model_extension_module_super_offers
                ->getFullCombinations($product['product_id']);

            $options = $this->model_extension_module_super_offers
                ->getOptions($product['product_id']);

            foreach ($combinations as $c) {

                if ($c['quantity'] <= 0) {
                    continue;
                }

                if ($c['image']) {
                    continue;
                }

                /* OPTIONS START */
                $color = '';
                $size = '';
                $optionValues = $this->getOptionValuesForCombination($options, $c);

                foreach ($optionValues as $optionData) {
                    switch ($optionData['optionClass']) {
                        case 'color':
                            $color = $optionData['productOptionName'];
                            break;

                        case 'size':
                            $size = $optionData['productOptionName'];
                            break;
                    }
                }
                /* OPTIONS END */

                $name = ($product['h1']) ? $product['h1'] : $product['name'];

                $rows[] = array(
                    $product['product_id'],
                    $name,
                    $color,
                    $size,
                );
            }

            $pcount++;
        }

        $json['filePath'] = str_replace($this->getRootPath(), HTTPS_SERVER, $file);
        $json['success'] = $ex->export($file, $rows);

        $json['message'][] = "Обработано {$pcount} товаров.";
        return $json;
    }

    public function actionShopscriptExport()
    {
        $file = $this->export_path . 'shopscript-full.csv';
        if (is_file($file)) { @unlink($file); }
        $this->createPath($file);

        $f = fopen($file, 'w');

        $this->_str = "\xEF\xBB\xBF";
        fwrite($f, $this->_str);
        fclose($f);

        $this->load->model('catalog/product');
        $this->load->model('extension/module/super_offers');
        $this->load->model('extension/module/pro_znachek');

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $ex = new \pro_csv\pro_csv('EXPORT');
        $ex->unstrict();
        $ex->setDelimiter(";");
        $ex->setFileMode("a");
        $ex->setToCharset("UTF-8");
        $ex->setColumnHeaders(array(
            'ProdID',
            'Наименование',
            'Цена',
            'Закупочная цена',
            'Валюта',
            'Доступен для заказа',
            'В наличии',
            'Код артикула',
            'Цвет',
            'Размер',
            'Наименование артикула',
            'Коллекция',
            'Описание',
            'Вес',
            'Производитель',
            'Артикул производителя',
            'Страна производства',
            'Страна производителя',
            'Материал',
            'Тип упаковки',
            'Тип и количество батареек',
            'Длина',
            'Диаметр',
            'Объем',
            'Основное назначение',
            'Дополнительное назначение',
            'Вибрация',
            'Новинка',
            'SuperSale',
            'Хит',
            'StopPromo',
            'img_status',
            'Изображения',
            'Изображения',
            'Изображения',
            'Изображения',
            'Изображения',
            'Изображения',
            'Изображения',
            'Изображения',
            'Изображения',
            'Изображения',
        ));

        $pcount = 0;
        $ccount = 0;
        $rows = array();

        $products = $this->model_catalog_product->getProducts();

        foreach ($products as $product) {

            $material = '';
            $collection = '';

            $attributeGroups = $this->model_catalog_product
                ->getProductAttributes($product['product_id']);

            foreach ($attributeGroups as $group) {
                if (strcmp(trim($group['name']), 'Атрибуты') === 0) {
                    foreach ($group['attribute'] as $attr) {
                        if (strcmp(trim($attr['name']), 'Материал') === 0) {
                            $material = $attr['text'];
                        }
                        if (strcmp(trim($attr['name']), 'Коллекция') === 0) {
                            $collection = $attr['text'];
                        }
                    }
                }
            }

            $_new = 0;
            $_hit = 0;

            if (isset($product['znachek'])) {
                $znachekClass = $this->model_extension_module_pro_znachek
                    ->getZnachekClass($product['znachek']);

                switch (trim($znachekClass)) {
                    case 'new':
                        $_new = 1;
                        break;
                    case 'hit':
                        $_hit = 1;
                        break;
                }
            }

            $combinations = $this->model_extension_module_super_offers
                ->getFullCombinations($product['product_id']);
            $options = $this->model_extension_module_super_offers
                ->getOptions($product['product_id']);

            foreach ($combinations as $c) {

                $name = ($product['h1']) ? $product['h1'] : $product['name'];

                $price = $this->tax->calculate(
                    $c['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                if ((float) $product['special']) {
                    $price = $this->tax->calculate(
                        $product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
                }

                $price = preg_replace('/\s+/', '', $price);

                $color = '';
                $size = '';

                // TODO: improve the code
                if (isset($c['required'])) {
                    foreach ($c['required'] as $reqValues) {
                        if (isset($reqValues['option_a'])
                        && isset($reqValues['option_value_a'])) {

                            foreach ($options as $o) {
                                if (!isset($o['class'])
                                || !isset($o['option_id'])) {
                                    continue;
                                }

                                if ($o['option_id'] === $reqValues['option_a']) {
                                    if (isset($o['product_option_value'])) {
                                        foreach ($o['product_option_value'] as $pov) {
                                            if (isset($pov['name'])
                                            && isset($pov['option_value_id'])
                                            && $pov['option_value_id'] === $reqValues['option_value_a']) {

                                                switch (trim($o['class'])) {
                                                    case 'size':
                                                        $size = $pov['name'];
                                                        break;
                                                    case 'color':
                                                        $color = $pov['name'];
                                                        break;
                                                }

                                            }
                                        }
                                    }
                                }
                            }

                        }
                    }
                }

                $rowData = array(
                    (int) $product['product_id'],
                    (string) $name,
                    (float) $price,
                    '',
                    'RUB',
                    (int) ($c['quantity'] > 0) ? 1 : 0,
                    (int) ($c['quantity'] > 0) ? 1 : 0,
                    (string) $name,
                    (string) $color,
                    (string) $size,
                    '',
                    (string) $collection,
                    (string) $product['description'],
                    '',
                    (string) $product['manufacturer'],
                    '',
                    '',
                    '',
                    (string) $material,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '0',
                    (int) $_new,
                    '',
                    (int) $_hit,
                    '',
                    'normal',
                );

                $maxImageCount = 10;
                $usedImageCount = 0;

                if ($product['image']) {
                    $rowData[] = "{$base_path}image/{$product['image']}";
                    $usedImageCount++;
                }

                $productImages = $this->model_catalog_product
                    ->getProductImages($this->request->get['product_id']);

                foreach ($productImages as $pImg) {
                    if ($usedImageCount < $maxImageCount
                    && isset($pImg['image'])) {
                        $images[] = "{$base_path}image/{$pImg['image']}";
                        $usedImageCount++;
                    }
                }

                if ($usedImageCount < 10) {
                    for ($i=0; $i < ($maxImageCount - $usedImageCount); $i++) {
                        $rowData[] = '';
                    }
                }

                // REMOVE DELIMETER FROM VALUES
                foreach ($rowData as $k => $v) {
                    $v = trim(preg_replace('/\s+/', ' ', $v));
                    $rowData[$k] = str_replace(';', '-', $v);
                }

                $rows[] = $rowData;
                $ccount++;
            }

            $pcount++;
        }

        $json['filePath'] = str_replace($this->getRootPath(), HTTPS_SERVER, $file);
        $json['success'] = $ex->export($file, $rows);

        $json['message'][] = "Обработано {$pcount} товаров.";
        $json['message'][] = "Обработано {$ccount} комбинаций опций.";

        return $json;
    }

    public function actionSeoExport()
    {
        $file = $this->export_path . 'seo.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $f = fopen($file, 'w');

        $this->_str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        fwrite($f, $this->_str);

        $pcount = 0;
        $no_price_count = 0;
        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        $this->_str = "<Товары>\n";
        foreach ($this->model_api_import_1c_product->getAllProductsIds() as $pid) {
            $product_data = $this->model_catalog_product->getProduct($pid);
            if ($product_data) {

                $dp = $this->model_extension_module_super_offers->getDefaultValues($product_data['product_id'], $product_data);

                if ((int)$dp['price']) {
                    $this->_str .= "<item id=\"{$product_data['product_id']}\">" .
                        "   <Наименование>" . htmlspecialchars($product_data['name']) . "</Наименование>\n" .
                        "   <HeadTitle>" . htmlspecialchars($product_data['meta_title']) . "</HeadTitle>\n" .
                        "   <seoH1>" . htmlspecialchars($product_data['h1']) . "</seoH1>\n" .
                        "   <description>" . htmlspecialchars($product_data['meta_description']) . "</description>\n" .
                        "   <keyWords>" . htmlspecialchars($product_data['meta_keyword']) . "</keyWords>\n";
                    $this->_str .= "</item>\n";
                    $pcount++;
                } else {
                    $no_price_count++;
                }

            }
        }
        $this->_str .= "</Товары>";
        fwrite($f, $this->_str);

        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    public function actionGoogleExport()
    {
        $file = $this->export_path . 'google.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $f = fopen($file, 'w');

        $this->_str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
            "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\n" .
            "<title>". htmlspecialchars($this->config->get('config_meta_title')) ."</title>\n" .
            "<link>" . htmlspecialchars($base_path) . "</link>\n" .
            "<description>" . htmlspecialchars($this->config->get('config_meta_description')) . "</description>\n" .
            "<channel>\n";
        fwrite($f, $this->_str);

        $pcount = 0;
        $no_price_count = 0;
        $this->load->model('tool/base');
        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        foreach ($this->model_api_import_1c_product->getAllProductsIds() as $pid) {
            $product_data = $this->model_catalog_product->getProduct($pid);
            if ($product_data) {

                $dp = $this->model_extension_module_super_offers->getDefaultValues($product_data['product_id'], $product_data);

                // PASS PRODUCTS
                $pass = false;
                if (isset($dp['price'])) {
                    if ($dp['min_quantity'] <= 0) { $pass = true; }
                    if ($dp['price'] <= 0) { $pass = true; }
                } else { $pass = true; }

                if ($pass === false) {
                    $this->_str = '';

                    $price = (int)preg_replace('/\s+/', '', $dp['price']);
                    $special = (int)preg_replace('/\s+/', '', $dp['special']);
                    if ($special !== false && $special > 0) {
                        $price = $special;
                    }

                    $seo_url = $this->getSeoUrl($product_data['product_id']);
                    $breadcrumbs = $this->getBreadcrumbs($product_data['product_id']);

                    if ($product_data['image']) {
                        $image = $base_path . 'image/' . $product_data['image'];
                    } else {
                        $image = $base_path . 'image/placeholder.png';
                    }

                    $this->_str .= "<item>\n<g:id>{$product_data['product_id']}</g:id>\n" .
                        ((!empty($product_data['h1'])) ? "<g:title>" . htmlspecialchars($product_data['h1']) . "</g:title>\n" : "<g:title>" . htmlspecialchars($product_data['name']) . "</g:title>\n").
                        ((!empty($product_data['meta_description'])) ? "<g:description>" . htmlspecialchars(strip_tags($product_data['meta_description'])) . "</g:description>\n" : "<g:description>Описание у товара скоро появится</g:description>\n").
                        "<g:link>{$seo_url}</g:link>\n" .
                        "<g:image_link>{$image}</g:image_link>\n" .
                        "<g:condition>new</g:condition>".
                        "<g:availability>in stock</g:availability>".
                        "<g:product_type>". htmlspecialchars($breadcrumbs) ."</g:product_type>".
                        ((!empty($product_data['manufacturer'])) ? "<g:brand>" . htmlspecialchars($product_data['manufacturer']) . "</g:brand>\n" : "") .
                        "<g:price>". $price ." RUB</g:price>\n";

                    $this->_str .= "</item>\n";
                    fwrite($f, $this->_str);
                    unset($this->_str);

                    $pcount++;
                } else {
                    $no_price_count++;
                }

            }
        }

        $this->_str = "</channel>\n" .
            "</rss>";

        fwrite($f, $this->_str);
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    public function actionYandexExport()
    {
        $file = $this->export_path . 'yandex.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $f = fopen($file, 'w');

        $this->_str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
            "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n" .
            "<yml_catalog date=\"" . date('Y-m-d H:i') . "\">\n" .
            "<shop>\n" .
            "<name>" . htmlspecialchars($this->config->get('config_meta_title')) . "</name>\n" .
            "<company>" . htmlspecialchars($this->config->get('config_meta_title')) . "</company>\n" .
            "<url>{$base_path}</url>\n" .
            "<currencies>\n" .
            "<currency id=\"RUR\" rate=\"1\" plus=\"0\"/>\n" .
            "</currencies>\n" .
            "<categories>\n";
        fwrite($f, $this->_str);

        foreach ($this->getCategories() as $cat) {
            $group_name = htmlspecialchars($cat['name']);
            $this->_str = "<category id=\"{$cat['category_id']}\">{$group_name}</category>\n";
            $this->setTree($cat['category_id']);
            fwrite($f, $this->_str);
        }

        $this->_str = "</categories>\n" .
            "<offers>\n";
        fwrite($f, $this->_str);

        $pcount = 0;
        $no_price_count = 0;

        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        foreach ($this->model_api_import_1c_product->getAllProductsIds() as $pid) {
            $product_data = $this->model_catalog_product->getProduct($pid);
            if ($product_data) {

                $dp = $this->model_extension_module_super_offers->getDefaultValues($product_data['product_id'], $product_data);

                // PASS PRODUCTS
                $pass = false;
                if (isset($dp['price'])) {
                    if ($dp['min_quantity'] <= 0) { $pass = true; }
                    if ($dp['price'] <= 0) { $pass = true; }
                } else { $pass = true; }

                if ($pass === false) {
                    $this->_str = '';

                    $price = (int)preg_replace('/\s+/', '', $dp['price']);
                    $special = (int)preg_replace('/\s+/', '', $dp['special']);
                    if ($special !== false && $special > 0) {
                        $price = $special;
                    }

                    $seo_url = $this->getSeoUrl($product_data['product_id']);
                    $cc = $this->getCloseCat($product_data['product_id']);

                    if ($product_data['image']) {
                        $image = $base_path . 'image/' . $product_data['image'];
                    } else {
                        $image = $base_path . 'image/placeholder.png';
                    }

                    $available = ($dp['min_quantity'] > 0) ? 'true' : 'false';

                    $this->_str .= "<offer id=\"{$product_data['product_id']}\" available=\"{$available}\">\n" .
                        "<url>{$seo_url}</url>\n" .
                        "<price>". $price ."</price>\n" .
                        "<currencyId>RUR</currencyId>\n" .
                        "<categoryId>{$cc}</categoryId>\n" .
                        "<picture>{$image}</picture>\n" .
                        ((!empty($product_data['h1'])) ? "<name>" . htmlspecialchars($product_data['h1']) . "</name>\n" : "<name>" . htmlspecialchars($product_data['name']) . "</name>\n").
                        ((!empty($product_data['manufacturer'])) ? "<vendor>" . htmlspecialchars($product_data['manufacturer']) . "</vendor>\n" : "") .
                        ((!empty('')) ? "<vendorCode>" . htmlspecialchars('') . "</vendorCode>\n" : "") .
                        ((!empty($product_data['meta_description'])) ? "<description>" . htmlspecialchars(strip_tags($product_data['meta_description'])) . "</description>\n" : "<description>Описание у товара скоро появится</description>\n")
                        ."<sales_notes>мин.сумма заказа: 1000р, мин.партия: 1шт</sales_notes>\n";

                    /* OPTIONS */
                    $options = $this->model_extension_module_super_offers->getOptions($product_data['product_id']);
                    foreach ($options as $o) {
                        $int = (strcmp($o['class'], 'size')===0) ? true : false;
                        foreach ($o['product_option_value'] as $ov) {
                            $this->_str .= "<param name=\"{$o['name']}\"" . (($int) ? " unit=\"INT\"" : "") . ">{$ov['name']}</param>\n";
                        }
                    }

                    $this->_str .= "</offer>\n";
                    fwrite($f, $this->_str);
                    unset($this->_str);

                    $pcount++;
                } else {
                    $no_price_count++;
                }

            }
        }

        $this->_str = "</offers>\n" .
            "</shop>\n" .
            "</yml_catalog>";

        fwrite($f, $this->_str);
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    public function actionRetailrocketExport()
    {
        $file = $this->export_path . 'rr.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $xmlObject = new \pro_xml\pro_xml('', [
            'version' => '1.0',
            'encoding' => 'UTF-8',
        ]);

        $ymlCatalog = $xmlObject->addChild('yml_catalog', true)
            ->setAttribute('date', date('Y-m-d H:i'));

        $shop = $ymlCatalog->addChild('shop', true)
            ->add([
                'name' => htmlspecialchars($this->config->get('config_meta_title')),
                'company' => htmlspecialchars($this->config->get('config_meta_title')),
                'url' => $base_path,
                'currencies' => [
                    'currency' => [
                        '@id' => 'RUR',
                        '@rate' => '1',
                        '@plus' => '0'
                    ],
            ]]);

        $categories = $shop->addChild('categories', true);

        foreach ($this->getCategories() as $cat) {
            $categories->addChild(['category' => htmlspecialchars($cat['name'])], true)
                ->setAttribute('id', $cat['category_id']);
        }

        $offers = $shop->addChild('offers', true);

        $pcount = 0;
        $ccount = 0;
        $no_price_count = 0;

        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        $products = $this->model_catalog_product->getProducts();

        foreach ($products as $product) {

            $combinations = $this->model_extension_module_super_offers
                ->getFullCombinations($product['product_id']);
            $options = $this->model_extension_module_super_offers
                ->getOptions($product['product_id']);

            foreach ($combinations as $c) {

                if ($c['quantity'] <= 0) {
                    continue;
                }

                $this->load->model('extension/module/offer_id');
                $offerId = $this->model_extension_module_offer_id->createAndReturnId($c['import_id']);

                if (!$offerId) {
                    continue;
                }

                $name = ($product['h1']) ? $product['h1'] : $product['name'];

                $price = $this->tax->calculate(
                    $c['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                $price = (int) preg_replace('/\s+/', '', $price);

                if ($product['image'] && is_file(DIR_IMAGE.$product['image'])) {
                    $image = $base_path . 'image/' . $product['image'];
                } else {
                    $image = $base_path . 'image/placeholder.png';
                }

                if (isset($c['image']) && is_file(DIR_IMAGE.$c['image'])) {
                    $image = $base_path . 'image/' . $c['image'];
                }

                $available = ($c['quantity'] > 0) ? 'true' : 'false';

                $offerData = [
                    '@id' => $offerId,
                    '@available' => $available,
                    'url' => $this->getSeoUrl($product['product_id']),
                    'price' => $price,
                    'currencyId' => 'RUR',
                    'categoryId' => $this->getCloseCat($product['product_id']),
                    'picture' => $image,
                    'name' => htmlspecialchars($name),
                ];

                if (!empty($product['manufacturer'])) {
                    $offerData['vendor'] = htmlspecialchars($product['manufacturer']);
                }

                $offerData['description'] = 'Описание у товара скоро появится';
                if (!empty($product['description'])) {
                    $offerData['description'] = htmlspecialchars(strip_tags($product['description']));
                }

                $offerData['sales_notes'] = 'мин.сумма заказа: 1000р, мин.партия: 1шт';

                if (isset($product['sku']) && $product['sku']) {
                    $offerData['shop-sku'] = $product['sku'];
                }

                if ($c['barcode']) {
                    $offerData['barcode'] = htmlspecialchars($c['barcode']);
                }

                $offer = $offers->add([
                    'offer' => $offerData,
                ], true);

                /* OPTIONS */
                $optionValues = $this->getOptionValuesForCombination($options, $c);

                foreach ($optionValues as $optionData) {
                    $offer->add([
                        'param' => [
                            '@name' => $optionData['optionName'],
                            '@' => $optionData['productOptionName'],
                        ]
                    ]);
                }

                $ccount++;
            }

            $pcount++;
        }

        $f = fopen($file, 'w');
        fwrite($f, $xmlObject->xml());
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";
        $json['message'][] = "Обработано {$ccount} комбинаций опций.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    public function actionYandexoffersExport()
    {
        $file = $this->export_path . 'yandex-offers.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $xmlObject = new \pro_xml\pro_xml('', [
            'version' => '1.0',
            'encoding' => 'UTF-8',
        ]);

        $ymlCatalog = $xmlObject->addChild('yml_catalog', true)
            ->setAttribute('date', date('Y-m-d H:i'));

        $shop = $ymlCatalog->addChild('shop', true)
            ->add([
                'name' => htmlspecialchars($this->config->get('config_meta_title')),
                'company' => htmlspecialchars($this->config->get('config_meta_title')),
                'url' => $base_path,
                'currencies' => [
                    'currency' => [
                        '@id' => 'RUR',
                        '@rate' => '1',
                        '@plus' => '0'
                    ],
            ]]);

        $categories = $shop->addChild('categories', true);

        foreach ($this->getCategories() as $cat) {
            $categories->addChild(['category' => htmlspecialchars($cat['name'])], true)
                ->setAttribute('id', $cat['category_id']);
        }

        $offers = $shop->addChild('offers', true);

        $pcount = 0;
        $ccount = 0;
        $no_price_count = 0;

        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        $products = $this->model_catalog_product->getProducts();

        foreach ($products as $product) {

            $combinations = $this->model_extension_module_super_offers
                ->getFullCombinations($product['product_id']);
            $options = $this->model_extension_module_super_offers
                ->getOptions($product['product_id']);

            foreach ($combinations as $c) {

                $price = $this->tax->calculate(
                    $c['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                $price = (int) preg_replace('/\s+/', '', $price);

                if ($product['image'] && is_file(DIR_IMAGE.$product['image'])) {
                    $image = $base_path . 'image/' . $product['image'];
                } else {
                    $image = $base_path . 'image/placeholder.png';
                }

                if (isset($c['image']) && is_file(DIR_IMAGE.$c['image'])) {
                    $image = $base_path . 'image/' . $c['image'];
                }

                $offerId = hash('crc32b', hash('sha256', $c['import_id']));
                $available = ($c['quantity'] > 0) ? 'true' : 'false';

                $offerData = [
                    '@id' => $offerId,
                    '@available' => $available,
                    'url' => $this->getSeoUrl($product['product_id']),
                    'price' => $price,
                    'currencyId' => 'RUR',
                    'categoryId' => $this->getCloseCat($product['product_id']),
                    'picture' => $image,
                ];

                if (!empty($product['manufacturer'])) {
                    $offerData['vendor'] = htmlspecialchars($product['manufacturer']);
                }

                $offerData['description'] = 'Описание у товара скоро появится';
                if (!empty($product['description'])) {
                    $offerData['description'] = htmlspecialchars(strip_tags($product['description']));
                }

                $offerData['sales_notes'] = 'мин.сумма заказа: 1000р, мин.партия: 1шт';

                if (isset($product['sku']) && $product['sku']) {
                    $offerData['shop-sku'] = $product['sku'];
                }

                if ($c['barcode']) {
                    $offerData['barcode'] = htmlspecialchars($c['barcode']);
                }

                $offer = $offers->add([
                    'offer' => $offerData,
                ], true);

                /* OPTIONS START */
                $color = '';
                $size = '';
                $optionValues = $this->getOptionValuesForCombination($options, $c);

                foreach ($optionValues as $optionData) {
                    switch ($optionData['optionClass']) {
                        case 'color':
                            $color = $optionData['productOptionName'];
                            break;

                        case 'size':
                            $size = $optionData['productOptionName'];
                            break;
                    }

                    $offer->add([
                        'param' => [
                            '@name' => $optionData['optionName'],
                            '@' => $optionData['productOptionName'],
                        ]
                    ]);
                }
                /* OPTIONS END */

                $name = ($product['h1']) ? $product['h1'] : $product['name'];
                $name = trim(implode(' ', array($name, $color, $size)));

                $offer->addChild(['name' => htmlspecialchars($name)]);

                $ccount++;
            }

            $pcount++;
        }

        $f = fopen($file, 'w');
        fwrite($f, $xmlObject->xml());
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";
        $json['message'][] = "Обработано {$ccount} комбинаций опций.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    public function actionYandexoffers3Export()
    {
        $file = $this->export_path . 'yandex-offers-3-and-more.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $xmlObject = new \pro_xml\pro_xml('', [
            'version' => '1.0',
            'encoding' => 'UTF-8',
        ]);

        $ymlCatalog = $xmlObject->addChild('yml_catalog', true)
            ->setAttribute('date', date('Y-m-d H:i'));

        $shop = $ymlCatalog->addChild('shop', true)
            ->add([
                'name' => htmlspecialchars($this->config->get('config_meta_title')),
                'company' => htmlspecialchars($this->config->get('config_meta_title')),
                'url' => $base_path,
                'currencies' => [
                    'currency' => [
                        '@id' => 'RUR',
                        '@rate' => '1',
                        '@plus' => '0'
                    ],
            ]]);

        $categories = $shop->addChild('categories', true);

        foreach ($this->getCategories() as $cat) {
            $categories->addChild(['category' => htmlspecialchars($cat['name'])], true)
                ->setAttribute('id', $cat['category_id']);
        }

        $offers = $shop->addChild('offers', true);

        $pcount = 0;
        $ccount = 0;
        $no_price_count = 0;

        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        $products = $this->model_catalog_product->getProducts();

        foreach ($products as $product) {

            $combinations = $this->model_extension_module_super_offers
                ->getFullCombinations($product['product_id']);
            $options = $this->model_extension_module_super_offers
                ->getOptions($product['product_id']);

            foreach ($combinations as $c) {

                $price = $this->tax->calculate(
                    $c['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                $price = (int) preg_replace('/\s+/', '', $price);

                if ($product['image'] && is_file(DIR_IMAGE.$product['image'])) {
                    $image = $base_path . 'image/' . $product['image'];
                } else {
                    $image = $base_path . 'image/placeholder.png';
                }

                if (isset($c['image']) && is_file(DIR_IMAGE.$c['image'])) {
                    $image = $base_path . 'image/' . $c['image'];
                }

                $offerId = hash('crc32b', hash('sha256', $c['import_id']));
                $available = ($c['quantity'] >= 3) ? 'true' : 'false';

                $offerData = [
                    '@id' => $offerId,
                    '@available' => $available,
                    'url' => $this->getSeoUrl($product['product_id']),
                    'price' => $price,
                    'currencyId' => 'RUR',
                    'categoryId' => $this->getCloseCat($product['product_id']),
                    'picture' => $image,
                ];

                if (!empty($product['manufacturer'])) {
                    $offerData['vendor'] = htmlspecialchars($product['manufacturer']);
                }

                $offerData['description'] = 'Описание у товара скоро появится';
                if (!empty($product['description'])) {
                    $offerData['description'] = htmlspecialchars(strip_tags($product['description']));
                }

                $offerData['sales_notes'] = 'мин.сумма заказа: 1000р, мин.партия: 1шт';

                if (isset($product['sku']) && $product['sku']) {
                    $offerData['shop-sku'] = $product['sku'];
                }

                if ($c['barcode']) {
                    $offerData['barcode'] = htmlspecialchars($c['barcode']);
                }

                $offer = $offers->add([
                    'offer' => $offerData,
                ], true);

                /* OPTIONS START */
                $color = '';
                $size = '';
                $optionValues = $this->getOptionValuesForCombination($options, $c);

                foreach ($optionValues as $optionData) {
                    switch ($optionData['optionClass']) {
                        case 'color':
                            $color = $optionData['productOptionName'];
                            break;

                        case 'size':
                            $size = $optionData['productOptionName'];
                            break;
                    }

                    $offer->add([
                        'param' => [
                            '@name' => $optionData['optionName'],
                            '@' => $optionData['productOptionName'],
                        ]
                    ]);
                }
                /* OPTIONS END */

                $name = ($product['h1']) ? $product['h1'] : $product['name'];
                $name = trim(implode(' ', array($name, $color, $size)));

                $offer->addChild(['name' => htmlspecialchars($name)]);

                $ccount++;
            }

            $pcount++;
        }

        $f = fopen($file, 'w');
        fwrite($f, $xmlObject->xml());
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";
        $json['message'][] = "Обработано {$ccount} комбинаций опций.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    public function actionAliexpressExport()
    {
        $file = $this->export_path . 'aliexpress.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $xmlObject = new \pro_xml\pro_xml('', [
            'version' => '1.0',
            'encoding' => 'UTF-8',
        ]);

        $ymlCatalog = $xmlObject->addChild('yml_catalog', true)
            ->setAttribute('date', date('Y-m-d H:i'));

        $shop = $ymlCatalog->addChild('shop', true)
            ->add([
                'name' => htmlspecialchars($this->config->get('config_meta_title')),
                'company' => htmlspecialchars($this->config->get('config_meta_title')),
                'url' => $base_path,
                'currencies' => [
                    'currency' => [
                        '@id' => 'RUR',
                        '@rate' => '1',
                        '@plus' => '0'
                    ],
            ]]);

        $categories = $shop->addChild('categories', true);

        foreach ($this->getCategories() as $cat) {
            $categories->addChild(['category' => htmlspecialchars($cat['name'])], true)
                ->setAttribute('id', $cat['category_id']);
        }

        $offers = $shop->addChild('offers', true);

        $pcount = 0;
        $ccount = 0;
        $no_price_count = 0;

        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        $products = $this->model_catalog_product->getProducts();

        foreach ($products as $product) {

            $combinations = $this->model_extension_module_super_offers
                ->getFullCombinations($product['product_id']);
            $options = $this->model_extension_module_super_offers
                ->getOptions($product['product_id']);

            foreach ($combinations as $c) {

                $price = $this->tax->calculate(
                    $c['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                $price = (int) preg_replace('/\s+/', '', $price);

                if ($product['image'] && is_file(DIR_IMAGE.$product['image'])) {
                    $image = $base_path . 'image/' . $product['image'];
                } else {
                    $image = $base_path . 'image/placeholder.png';
                }

                if (isset($c['image']) && is_file(DIR_IMAGE.$c['image'])) {
                    $image = $base_path . 'image/' . $c['image'];
                }

                $offerId = hash('crc32b', hash('sha256', $c['import_id']));
                $groupId = hash('crc32b', hash('sha256', $product['product_id']));
                $available = ((int) $c['quantity'] >= 3) ? 'true' : 'false';

                $offerData = [
                    '@id' => $offerId,
                    '@gid' => $groupId,
                    '@available' => $available,
                    'url' => $this->getSeoUrl($product['product_id']),
                    'price' => $price,
                    'currencyId' => 'RUR',
                    'categoryId' => $this->getCloseCat($product['product_id']),
                    'picture' => $image,
                    'quantity' => (int) $c['quantity'],
                ];

                if (!empty($product['manufacturer'])) {
                    $offerData['vendor'] = htmlspecialchars($product['manufacturer']);
                }

                $offerData['description'] = 'Описание у товара скоро появится';
                if (!empty($product['description'])) {
                    $offerData['description'] = htmlspecialchars(strip_tags($product['description']));
                }

                $offerData['sales_notes'] = 'мин.сумма заказа: 1000р, мин.партия: 1шт';

                if (isset($product['sku']) && $product['sku']) {
                    $offerData['shop-sku'] = $product['sku'];
                }

                if ($c['barcode']) {
                    $offerData['barcode'] = htmlspecialchars($c['barcode']);
                }

                $offer = $offers->add([
                    'offer' => $offerData,
                ], true);

                /* OPTIONS START */
                $color = '';
                $size = '';
                $optionValues = $this->getOptionValuesForCombination($options, $c);

                foreach ($optionValues as $optionData) {
                    switch ($optionData['optionClass']) {
                        case 'color':
                            $color = $optionData['productOptionName'];
                            break;

                        case 'size':
                            $size = $optionData['productOptionName'];
                            break;
                    }

                    $offer->add([
                        'param' => [
                            '@name' => $optionData['optionName'],
                            '@' => $optionData['productOptionName'],
                        ]
                    ]);
                }
                /* OPTIONS END */

                $name = ($product['h1']) ? $product['h1'] : $product['name'];
                $name = trim(implode(' ', array($name, $color, $size)));

                $offer->addChild(['name' => htmlspecialchars($name)]);

                $ccount++;
            }

            $pcount++;
        }

        $f = fopen($file, 'w');
        fwrite($f, $xmlObject->xml());
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";
        $json['message'][] = "Обработано {$ccount} комбинаций опций.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    private function getYandexOffersDataFromExcel()
    {
        $data = array();

        $excelFile = $this->getRootPath() . 'protected/runtime/exchange/yandex-offers-pricelist.xlsx';

        if (!is_file($excelFile)) {
            return $data;
        }

        $reader = \pro_spreadsheet\reader::createReaderForFile($excelFile);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($excelFile);
        $worksheet = $spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $dataItem = array(
                'importIdHash' => false,
                'recommendedDiscount' => false,
                'recommendedPrice' => false,
                'newPrice' => false,
            );

            foreach ($cellIterator as $cell) {
                switch($cell->getColumn()) {
                case 'B':
                    $dataItem['importIdHash'] = (string) $cell->getValue();
                    break;
                case 'N':
                    $dataItem['recommendedPrice'] = (double) $cell->getValue();
                    break;
                case 'P':
                    $dataItem['recommendedDiscount'] = (double) $cell->getValue();
                    break;
                }
            }

            if ($dataItem['importIdHash'] !== false
            && $dataItem['recommendedPrice'] !== false
            && $dataItem['recommendedDiscount'] !== false) {
                if ($dataItem['recommendedPrice'] > 0
                && $dataItem['recommendedDiscount'] > 0) {
                    $randomDescrease = random_int(1, 5);

                    if ($randomDescrease < $dataItem['recommendedPrice']) {
                        $dataItem['newPrice'] = $dataItem['recommendedPrice'] - $randomDescrease;

                        $data[$dataItem['importIdHash']] = $dataItem;
                    }
                }
            }
        }

        return $data;
    }

    public function actionYandexoffersTest()
    {
        $file = $this->export_path . 'yandex-offers-test.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        // LOAD FROM EXCEL START
        $excelData = $this->getYandexOffersDataFromExcel();
        // LOAD FROM EXCEL END

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $xmlObject = new \pro_xml\pro_xml('', [
            'version' => '1.0',
            'encoding' => 'UTF-8',
        ]);

        $ymlCatalog = $xmlObject->addChild('yml_catalog', true)
            ->setAttribute('date', date('Y-m-d H:i'));

        $shop = $ymlCatalog->addChild('shop', true)
            ->add([
                'name' => htmlspecialchars($this->config->get('config_meta_title')),
                'company' => htmlspecialchars($this->config->get('config_meta_title')),
                'url' => $base_path,
                'currencies' => [
                    'currency' => [
                        '@id' => 'RUR',
                        '@rate' => '1',
                        '@plus' => '0'
                    ],
            ]]);

        $categories = $shop->addChild('categories', true);

        foreach ($this->getCategories() as $cat) {
            $categories->addChild(['category' => htmlspecialchars($cat['name'])], true)
                ->setAttribute('id', $cat['category_id']);
        }

        $offers = $shop->addChild('offers', true);

        $pcount = 0;
        $ccount = 0;
        $no_price_count = 0;

        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        $products = $this->model_catalog_product->getProducts();

        foreach ($products as $product) {

            $combinations = $this->model_extension_module_super_offers
                ->getFullCombinations($product['product_id']);
            $options = $this->model_extension_module_super_offers
                ->getOptions($product['product_id']);

            foreach ($combinations as $c) {

                $price = $this->tax->calculate(
                    $c['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                if ((float) $product['special']) {
                    $price = $this->tax->calculate(
                        $product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
                }

                $price = (int) preg_replace('/\s+/', '', $price);

                if ($product['image'] && is_file(DIR_IMAGE.$product['image'])) {
                    $image = $base_path . 'image/' . $product['image'];
                } else {
                    $image = $base_path . 'image/placeholder.png';
                }

                if (isset($c['image']) && is_file(DIR_IMAGE.$c['image'])) {
                    $image = $base_path . 'image/' . $c['image'];
                }

                $offerId = hash('crc32b', hash('sha256', $c['import_id']));
                $available = ($c['quantity'] > 0) ? 'true' : 'false';

                // LOAD FROM EXCEL START
                if (isset($excelData[$offerId]['newPrice'])) {
                    $price = $excelData[$offerId]['newPrice'];
                }
                // LOAD FROM EXCEL END

                $offerData = [
                    '@id' => $offerId,
                    '@available' => $available,
                    'url' => $this->getSeoUrl($product['product_id']),
                    'price' => $price,
                    'currencyId' => 'RUR',
                    'categoryId' => $this->getCloseCat($product['product_id']),
                    'picture' => $image,
                ];

                if (!empty($product['manufacturer'])) {
                    $offerData['vendor'] = htmlspecialchars($product['manufacturer']);
                }

                $offerData['description'] = 'Описание у товара скоро появится';
                if (!empty($product['description'])) {
                    $offerData['description'] = htmlspecialchars(strip_tags($product['description']));
                }

                $offerData['sales_notes'] = 'мин.сумма заказа: 1000р, мин.партия: 1шт';

                if (isset($product['sku']) && $product['sku']) {
                    $offerData['shop-sku'] = $product['sku'];
                }

                if ($c['barcode']) {
                    $offerData['barcode'] = htmlspecialchars($c['barcode']);
                }

                $offer = $offers->add([
                    'offer' => $offerData,
                ], true);

                /* OPTIONS START */
                $color = '';
                $size = '';
                $optionValues = $this->getOptionValuesForCombination($options, $c);

                foreach ($optionValues as $optionData) {
                    switch ($optionData['optionClass']) {
                        case 'color':
                            $color = $optionData['productOptionName'];
                            break;

                        case 'size':
                            $size = $optionData['productOptionName'];
                            break;
                    }

                    $offer->add([
                        'param' => [
                            '@name' => $optionData['optionName'],
                            '@' => $optionData['productOptionName'],
                        ]
                    ]);
                }
                /* OPTIONS END */

                $name = ($product['h1']) ? $product['h1'] : $product['name'];
                $name = trim(implode(' ', array($name, $color, $size)));

                $offer->addChild(['name' => htmlspecialchars($name)]);

                $ccount++;
            }

            $pcount++;
        }

        $f = fopen($file, 'w');
        fwrite($f, $xmlObject->xml());
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";
        $json['message'][] = "Обработано {$ccount} комбинаций опций.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    private function getOptionValuesForCombination($options, $combination)
    {
        $optionValues = array();
        if (isset($combination['required'])) {
            foreach ($combination['required'] as $reqValues) {
                if (isset($reqValues['option_a'])
                && isset($reqValues['option_value_a'])) {

                    foreach ($options as $o) {
                        if (!isset($o['class'])
                        || !isset($o['option_id'])
                        || !isset($o['name'])) {
                            continue;
                        }

                        if ($o['option_id'] === $reqValues['option_a']) {
                            if (isset($o['product_option_value'])) {
                                foreach ($o['product_option_value'] as $pov) {
                                    if (isset($pov['name'])
                                    && isset($pov['option_value_id'])
                                    && $pov['option_value_id'] === $reqValues['option_value_a']) {
                                        $optionValues[] = [
                                            'optionName' => $o['name'],
                                            'optionClass' => $o['class'],
                                            'productOptionName' => $pov['name'],
                                        ];
                                    }
                                }
                            }
                        }
                    }

                }
            }
        }

        return $optionValues;
    }

    private function getSeoUrl($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
            WHERE `query` = '" . $this->db->escape('product_id=' . (int)$product_id) . "'
            AND store_id = '" . (int)$this->config->get('config_store_id') . "'
            AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        if ($query->num_rows && $query->row['keyword']) {
            $this->load->model('tool/base');
            return $this->model_tool_base->getBase() . $query->row['keyword'];
        }

        return $this->url->link('product/product', 'product_id=' . (int)$product_id, true);
    }

    private function getBreadcrumbs($product_id)
    {
        $categories = $this->getProductCategories($product_id);
        $most_closed = $this->getMostCloseCategories($categories);

        if ($most_closed) {
            $most_closed = array_shift ($most_closed);

            $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c
                LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
                WHERE c.category_id = '" . (int)$most_closed . "'
                AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

            if ($query->num_rows) {
                return ($query->row['meta_title']) ? $query->row['meta_title'] : $query->row['name'];
            }
        }

        return '';
    }

    private function getCloseCat($product_id)
    {
        $categories = $this->getProductCategories($product_id);
        $most_closed = $this->getMostCloseCategories($categories);

        if ($most_closed) {
            return array_shift ($most_closed);
        }

        return 0;
    }

    public function getClosestCategoryNameForProduct($productId)
    {
        $mostCloseId = $this->getCloseCat($productId);
        return $this->getCategoryName($mostCloseId);
    }

    public function getRootCategoryNameForProduct($productId)
    {
        $mostCloseId = $this->getCloseCat($productId);

        $mostClose = $this->getParentCategory($mostCloseId);
        while (isset($mostClose['parent_id']) && $mostClose['parent_id']) {
            $mostClose = $this->getParentCategory($mostClose['parent_id']);
        }

        if (isset($mostClose['category_id'])) {
            return $this->getCategoryName($mostClose['category_id']);
        } else {
            return $this->getCategoryName($mostCloseId);
        }
    }

    private function getParentCategory($categoryId)
    {
        return $this->db->query("SELECT * FROM `". DB_PREFIX ."category`
            WHERE `category_id` = '" . (int)$categoryId . "'")->row;
    }

    private function getCategoryName($categoryId)
    {
        $descriptionData = $this->db->query("SELECT * FROM `". DB_PREFIX ."category_description`
            WHERE `category_id` = '" . (int) $categoryId . "'
            AND `language_id` = '" . (int) $this->config->get('config_language_id') . "'")->row;

        if (isset($descriptionData['name'])) {
            return $descriptionData['name'];
        }
        return '---';
    }

    public function getProductCategories($product_id)
    {
        $categories = array();

        $query = $this->db->query("SELECT * FROM `". DB_PREFIX ."product_to_category`
            WHERE `product_id` = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $categories[] = $result['category_id'];
        }

        return $categories;
    }

    private function getCategoryIdPath($category_id)
    {
        $query = $this->db->query("SELECT cp.category_id, GROUP_CONCAT(cp.path_id ORDER BY level SEPARATOR '-') AS path
            FROM `". DB_PREFIX ."category_path` cp
            WHERE cp.category_id = '". (int)$category_id ."'
            GROUP BY cp.category_id");

        if (isset($query->row['path'])) {
            return $query->row;
        }
    }

    private function getMostCloseCategories($categories)
    {
        $filtered = array();
        $pathes = array();

        foreach ($categories as $cid) {
            $pathes[] = $this->getCategoryIdPath($cid);
        }

        // SORT BY PATH LENGTH
        usort($pathes, function($a, $b) {
            return strlen($b['path']) - strlen($a['path']);
        });

        foreach ($pathes as $k1 => $orig_pd) {
            foreach ($pathes as $k2 => $test_pd) {
                if ($k1 === $k2) { continue; }

                if (strpos($orig_pd['path'], $test_pd['path']) !== false) {
                    unset($pathes[$k2]);
                }
            }
        }

        $filtered = array_map(function($pd) {
            return $pd['category_id'];
        }, $pathes);

        return ($filtered) ?: $categories;
    }

    public function getCategories($parent_id = null, $top = null)
    {
        $sql = "SELECT c.*, cd.name FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
            WHERE cd.language_id = '".(int)$this->config->get('config_language_id')."'";

        if ($parent_id !== null) {
            $sql .= " AND c.parent_id = '". (int)$parent_id ."' ";
        }
        if ($top !== null) {
            $sql .= " AND c.top = '". (int)$top ."' ";
        }

        $sql .= "ORDER BY c.category_id";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    private function setTree($parent_id)
    {
        $cats = $this->getCategories($parent_id);

        if ($cats) {
            foreach ($cats as $cat) {
                $group_name = htmlspecialchars($cat['name']);
                $this->_str .= "    <category id=\"{$cat['category_id']}\" parentId=\"{$parent_id}\">{$group_name}</category>\n";
                $this->setTree($cat['category_id']);
            }
        }
    }

    private function createPath($path)
    {
        if (!is_dir(dirname($path))) {
            $d = new \import_1c\import_1c_dir;
            $d::createDir(dirname($path));
            unset($d);
        }
    }
}
