<?php
require_once ('Mail.php');
$m = date('n');
$url = 'https://uslugi-api.e-podlaskie.eu/zasobRezerwacjaGet?Rok=2018&Miesiac=' . $m;

try {
    $dbh = new PDO('mysql:host=localhost;dbname=symfony_price_tracker', 'root', 'root');

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

    function sendNotification()
    {
        $mail = new Mail('88billizzard88@gmail.com');
        $mail->setFromName("Хмылко Владимир"); // Устанавливаем имя в обратном адресе
        $mail->send("billizzard@mail.ru", "Изменения на странице с клендарем", "На странице что-то изменилось, возможно появились зеленые даты. <br>Письмо сгенерировано автоматически, отвечать не нужно.");

//        $mail = new Mail('88billizzard88@gmail.com');
//        $mail->setFromName("Хмылко Владимир"); // Устанавливаем имя в обратном адресе
//        $mail->send("elen.02@mail.ru", "Изменения на странице с клендарем", "На странице что-то изменилось, возможно появились зеленые даты. <br>Письмо сгенерировано автоматически, отвечать не нужно.");
        
    }

    function add($dbh, $page)
    {
        $stmt = $dbh->prepare('INSERT INTO karta (page) VALUES (:page)');
        $stmt->bindParam(':page', $page);
        $stmt->execute();
        sendNotification();
    }

    $content = mb_substr($content, 3893);

    if ($res) {
        foreach ($res as $row) {
            if ($row['page'] != $content) {
                add($dbh, $content);
            }
        }
    } else {
        add($dbh, $content);
    }





    die();

} catch (Exception $e) {

}



//return $content;