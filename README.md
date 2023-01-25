# lknpd-nalog-api

Небольшая PHP-библиотека для формирования и отмены чеков для самозанятых (пользователей "Мой налог")

## Методы

### Подключение библиотеки

```php
spl_autoload('LkNpdNalogApi');

$api = new LkNpdNalogApi(
    'Логин для ЛК НПД ФНС (обычно ИНН, обязательно)',
    'Пароль для ЛК НПД ФНС (обязательно)',
    'Asia/Yekaterinburg' // Часовой пояс (необязательно, нужен для верного отображения времени в чеке). По умолчанию - Europe/Moscow
);
```

### Создание нового чека

Для одной позиции:

```php
$args = [
    'name' => 'Наименование товара',
    // Кол-во целых единиц товара (в шт., по-умолчанию - 1)
    'quantity' => 1,
    'amount' => 149,
    // Необязательные поля
    'clientContactPhone' => null,
    'clientDisplayName' => null
];

$api->createReceipt($args);
```

Для нескольких позиций:

```php
$args = [
    'services' => [
        [
            'name' => 'Наименование товара 1',
            // Кол-во целых единиц товара (в шт., по-умолчанию - 1)
            'quantity' => 1,
            'amount' => 149
        ],
        // ...
    ],
    // Общая сумма в чеке
    'amount' => 149
];

$api->createReceipt($args);
```

### Отмена ранее выданного чека

```php
$args = [
    // ID чека
    'receiptUuid' => '20aabbccdd',
    // СANCEL - Чек сформирован ошибочно
    // REFUND - Возврат средств
    'reason' => 'CANCEL'
];

$api->cancelReceipt($args);
```

### Методы данных

```php
/**
* Наличие ошибок
* @return bool
*/
echo $api->error;

/**
* Сообщения об ошибке
* @return string
*/
echo $api->errorMessage;
echo $api->errorExceptionMessage;

/**
* Идентификатор чека (созданного или отмененного)
* @return string
*/
echo $api->receiptUuid;

/**
* Ссылка на печатуню форму чека
* @return string
*/
echo $api->receiptUrlPrint;

/**
* Ссылка на данные чека в JSON
* @return string
*/
echo $api->receiptUrlJson;

/**
* Ответ на запрос
* @return string
*/
echo $api->response;
```

### Валидация

```php
if (!$api->error) {
    // Все в порядке
    echo "receiptUuid: {$api->receiptUuid}<br>";
} else {
    // Ошибка
    echo "errorMessage: {$api->errorMessage}<br>";
    echo "errorExceptionMessage: {$api->errorExceptionMessage}<br>";
}
```

### Пример использования

Примеры использования приведены в `index.php`.
