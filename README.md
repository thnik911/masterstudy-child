# masterstudy-child

## Данный репозиторий является доработкой CMS Wordpress. Проект пока находится в стадии развития.

Подробнее о файлах в репозитории:

### functions.php, footer.php и style.css являются файлами дочерней темы Master Study.

#### functions.php 

Отвечает за передачу заказов из Woocoomerce в Битрикс24. На основании заказа покупателя создается лид. 

**Как запустить**:
1. Либо внести доработку в файл functions.php основной темы (что делать не рекомендуется), либо создать дочернюю тему и вставить в свой functions.php код из файла репозитория с 10 по 120 строку. auth.php необходимо также необходимо разместить на хостинге с поддержкой SSL, можно в этой же папке с дочерней темой.
2. В разделе "Разработчикам" необходимо создать входящий вебхук с правами на CRM (crm). Подробнее как создать входящий / исходящий вебхук: [Ссылки на документацию 1С-Битрикс](https://github.com/thnik911/masterstudy-child/blob/main/README.md#%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B8-%D0%BD%D0%B0-%D0%B4%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%86%D0%B8%D1%8E-1%D1%81-%D0%B1%D0%B8%D1%82%D1%80%D0%B8%D0%BA%D1%81).
3. Полученный "Вебхук для вызова rest api" прописать в auth.php в строку 8.
4. Создать заказ в Woocommerce. Результатом будет созданный лид в Битркс24

#### footer.php и style.css

Отвечают за отображение блоков на сайте.

#### useraddNew.php 

Является отдельным скриптом, который работает каждый час по cron. Его задачей является создание сделки в Битрикс24, если пользователь зарегистрировался в Wordpress. Далее с этой сделкой работает отдельный менеджер, который уточняет интерес клиента и приобретении услуги. При создании идет проверка на дубликаты.

**Как запустить**:
1. useraddNew.php и auth.php необходимо разместить на хостинге с поддержкой SSL. В строках 11-14 прописать авторизацию до базы данных Wordpress
2. В разделе "Разработчикам" необходимо создать входящий вебхук с правами на CRM (crm). Подробнее как создать входящий / исходящий вебхук: [Ссылки на документацию 1С-Битрикс](https://github.com/thnik911/masterstudy-child/blob/main/README.md#%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B8-%D0%BD%D0%B0-%D0%B4%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%86%D0%B8%D1%8E-1%D1%81-%D0%B1%D0%B8%D1%82%D1%80%D0%B8%D0%BA%D1%81).
3. Полученный "Вебхук для вызова rest api" прописать в auth.php в строках 10-12.
4. В скрипте useraddNew.php необходимо внести правки в строки: 103 - в SOURCE_ID укажите свой источник, 106 - в TYPE_ID укажите тип клиента, 122 - укажите свой статус в воронке сделок, на котором будет создаваться сделка в Битрикс24, 125 - укажите ID ответственного, который будет ответственным за сделку, 126 - укажите ID воронки сделки, 127 - укажите источник сделки.
5. Вручную в строке бразуера вводим: https://yourdomain.com/path/useraddNew.php
6. Результатом будет: создание контакта + сделки в Битрикс24 для всех пользователей, которые есть в Wordpress.

#### findDeal.php

Является продолжением functions.php. Если с клиентом уже работает менеджер, то необходимо сообщить об оплате услуги менеджеру.

**Механизм работы**:
1. На основании лида в Битрикс24 автоматически создается сделка в отдельном направлении. При создании сделки запускается вебхук, который по клиенту ищет сделки в работе по определенному направлению и этапу (то есть, есть ли такая сделка в работе у менеджера).
2. Если такую сделку найти удалось, ставится задача менеджеру, что клиент оплатил. Сделка менеджера автоматически завершается как успешная. Если найти такую сделку не удалось, руководителю отдела продаж ставится задача на поиск такой сделки вручную.

**Как запустить**:
1. findDeal.php и auth.php необходимо разместить на хостинге с поддержкой SSL.
2. В разделе "Разработчикам" необходимо создать входящий вебхук с правами на CRM (crm). Подробнее как создать входящий / исходящий вебхук: [Ссылки на документацию 1С-Битрикс](https://github.com/thnik911/masterstudy-child/blob/main/README.md#%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B8-%D0%BD%D0%B0-%D0%B4%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%86%D0%B8%D1%8E-1%D1%81-%D0%B1%D0%B8%D1%82%D1%80%D0%B8%D0%BA%D1%81).
3. Полученный "Вебхук для вызова rest api" прописать в auth.php.
4. В строках 23-24 скрипта findDeal.php необходимо укзать направление и стадию сделки, которую нужно искать по клиенту (при условии, что менеджер уже работает с данным клиентом и ждет оплаты). 
5. В 43 строке скрипта findDeal.php необходимо указать этап, на который нужно перенести сделку менеджера, если оплата поступила. 
6. В 86-87 строках скрипта findDeal.php необходимо указать ID руковиделя отдела продаж.
7. В 88 строке скрипта findDeal.php необходимо указать ID группы, в которую нужно поставить задачу (опционально).
8. Делаем POST запрос посредством конструкции Webhook* через робот, или бизнес-процессом: https://yourdomain.com/path/findDeal.php?deal1=123&cnt=456
9. Результат: поставленная задача менеджеру об оплате.

**Переменные передаваемые в POST запросе:**

yourdomain.com - адрес сайта, на котором размещены скрипты auth.php и findDeal.php с поддержкой SSL.

path - путь до скрипта.

deal1 - ID сделки, из которой инициирован поиск.

cnt - ID контакта, по которому нужно искать сделки.


### Ссылки на документацию 1С-Битрикс 

<details><summary>Развернуть список</summary>

1. Действие Webhook внутри Бизнес-процесса / робота https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=57&LESSON_ID=8551
2. Как создать Webhook https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=99&LESSON_ID=8581&LESSON_PATH=8771.8583.8581

</details>
