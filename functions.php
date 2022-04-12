<?php
/* Дочерняя тема */
add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
function enqueue_parent_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

/* Добавляйте свой код после этой строки */

//Передача заявок в Б24
add_action('woocommerce_thankyou', 'my_custom_tracking');
function my_custom_tracking($order_id)
{

    // Получаем информации по заказу
    $order = wc_get_order($order_id);
    $order_data = $order->get_data();

    // Получаем базовую информация по заказу
    $order_id = $order_data['id'];
    $order_currency = $order_data['currency'];
    $order_payment_method_title = $order_data['payment_method_title'];
    $order_shipping_totale = $order_data['shipping_total'];
    $order_total = $order_data['total'];

    $order_base_info = "<hr><strong>Общая информация по заказу</strong><br>
  ID заказа: $order_id<br>
  Валюта заказа: $order_currency<br>
  Метода оплаты: $order_payment_method_title<br>
  Стоимость доставки: $order_shipping_totale<br>
  Итого с доставкой: $order_total<br>";

    // Получаем информация по клиенту
    $order_customer_id = $order_data['customer_id'];
    $order_customer_ip_address = $order_data['customer_ip_address'];
    $order_billing_first_name = $order_data['billing']['first_name'];
    $order_billing_last_name = $order_data['billing']['last_name'];
    $order_billing_email = $order_data['billing']['email'];
    $order_billing_phone = $order_data['billing']['phone'];

    $order_client_info = "<hr><strong>Информация по клиенту</strong><br>
  ID клиента = $order_customer_id<br>
  IP адрес клиента: $order_customer_ip_address<br>
  Имя клиента: $order_billing_first_name<br>
  Фамилия клиента: $order_billing_last_name<br>
  Email клиента: $order_billing_email<br>
  Телефон клиента: $order_billing_phone<br>";

    // Получаем информации по товару
    $order->get_total();
    $line_items = $order->get_items();
    foreach ($line_items as $item) {
        $product = $order->get_product_from_item($item);
        $sku = $product->get_sku(); // артикул товара
        $id = $product->get_id(); // id товара
        $name = $product->get_name(); // название товара
        $description = $product->get_description(); // описание товара
        $stock_quantity = $product->get_stock_quantity(); // кол-во товара на складе
        $qty = $item['qty']; // количество товара, которое заказали
        $total = $order->get_line_total($item, true, true); // стоимость всех товаров, которые заказали, но без учета доставки

        $product_info[] = "<hr><strong>Информация о товаре</strong><br>
    Название товара: $name<br>
    ID товара: $id<br>
    Артикул: $sku<br>
    Описание: $description<br>
    Заказали (шт.): $qty<br>
    Наличие (шт.): $stock_quantity<br>
    Сумма заказа (без учета доставки): $total;";
    }

    $product_base_infо = implode('<br>', $product_info);

    $subject = "Заказ с сайта № $order_id";

    require_once 'auth.php';

    // Формируем параметры для создания лида в переменной $queryData
    $queryData = http_build_query(array(
        'fields' => array(
            'TITLE' => $subject,
            'COMMENTS' => $order_base_info . ' ' . $order_client_info . ' ' . $order_shipping_info . ' ' . $product_base_infо,
            'PHONE' => ['0' => ['VALUE' => $order_billing_phone, 'VALUE_TYPE' => 'WORK']],
            'EMAIL' => ['0' => ['VALUE' => $order_billing_email, 'VALUE_TYPE' => 'WORK']],
            'NAME' => $order_billing_first_name,
            'LAST_NAME' => $order_billing_last_name,
            'SOURCE_ID' => 76,

        ),
        'params' => array("REGISTER_SONET_EVENT" => "Y"),
    ));

    // Обращаемся к Битрикс24 при помощи функции curl_exec
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    $result = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($result, 1);

    if (array_key_exists('error', $result)) {
        echo "Ошибка при сохранении лида: " . $result['error_description'] . "<br>";
    }
}

function writeToLog($data, $title = '')
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/orderadd.log', $log, FILE_APPEND);
    return true;
}
//конец передачи заявок в Б24

//добавление нового виджета под дисклеймер на сайте
register_sidebar(array(
    'name' => 'Footer Widget 1',
    'id' => 'footer-1',
    'description' => 'Первая область',
    'before_widget' => '<div class="wsfooterwdget">',
    'after_widget' => '</div>',
    'before_title' => '<h2>',
    'after_title' => '</h2>',
));
//конец добавления нового виджета под дисклеймер на сайте
