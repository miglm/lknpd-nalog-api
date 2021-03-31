<?php

spl_autoload('LkNpdNalogApi');

$api = new LkNpdNalogApi(
    'Логин для ЛК ФНС (обязательно)',
    'Пароль для ЛК ФНС (обязательно)',
    'Asia/Yekaterinburg' // Часовой пояс (необязательно, нужен для верного отображения времени в чеке). По умолчанию - Europe/Moscow
);

/**
 * Создание нового чека
 */

echo '<h2>Создание нового чека</h2>';

$args = [
    'name' => 'Название товара',
    'amount' => 149,
    'clientContactPhone' => null,
    'clientDisplayName' => null
];

$api->createReceipt($args);

if (!$api->error){
    echo "receiptUuid: {$api->receiptUuid}<br>";
    echo "receiptUrlPrint: {$api->receiptUrlPrint}<br>";
    echo "receiptUrlJson: {$api->receiptUrlJson}<br>";
}else{
    echo "errorMessage: {$api->errorMessage}<br>";
    echo "errorExceptionMessage: {$api->errorExceptionMessage}<br>";
}

/**
 * Отмена ранее выданного чека
 */

echo '<h2>Отмена ранее выданного чека</h2>';

$args = [
    'reason' => 'CANCEL', // REFUND
    'receiptUuid' => $api->receiptUuid
];

$api->cancelReceipt($args);

if (!$api->error){
    echo "receiptUuid: {$api->receiptUuid}<br>";
}else{
    echo "errorMessage: {$api->errorMessage}<br>";
    echo "errorExceptionMessage: {$api->errorExceptionMessage}<br>";
}

echo "<h2>Response</h2> {$api->response}";
