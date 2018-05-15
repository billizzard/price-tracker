<?php
require_once ('Mail.php');
$m = date('n');
$url = 'https://uslugi-api.e-podlaskie.eu/zasobRezerwacjaGet?Rok=2018&Miesiac=' . $m;

$dbh = new PDO('mysql:host=localhost;dbname=symfony_price_tracker', 'root', 'root');
sendNotification();
$dbh->query('CREATE TABLE IF NOT EXISTS `karta` (
  id int(11) NOT NULL AUTO_INCREMENT,
  page  TEXT NOT NULL, 
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;');

$res = $dbh->query('SELECT * FROM karta ORDER BY id DESC LIMIT 1');
$res = $res->fetchAll(PDO::FETCH_ASSOC);

$content = @file_get_contents($url);
if (!$content) {
    // logging: нет результата для сайта
}

if ($res) {
    foreach ($res as $row) {
        if ($row['page'] != $content) {
            add($dbh, $content);
        }
    }
} else {
    add($dbh, $content);
}

function add($dbh, $page)
{
    $stmt = $dbh->prepare('INSERT INTO karta (page) VALUES (:page)');
    $stmt->bindParam(':page', $page);
    $stmt->execute();
    sendNotification();
}

function sendNotification()
{
    $apikey = 'b52ab6cdffe9f3cfa18d9f54b046aac4-us18';
    $listId = '1b8f5048ce';




    $mail = new Mail('no-reply@localhost.ru');
    $mail->setFromName("Иван Иванов"); // Устанавливаем имя в обратном адресе
    if ($mail->send("billizzard@mail.ru", "Тестирование", "Тестирование<br /><b>письма<b>")) echo "Письмо отправлено";
    else echo "Письмо не отправлено";
}

die();


//return $content;