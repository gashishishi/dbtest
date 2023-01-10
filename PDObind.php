<?php
// 例1 名前付けされたプレースホルダを用いてプリペアドステートメントを実行する

/* バインドされた PHP 変数によってプリペアドステートメントを実行する */
$calories = 150;
$colour = 'red';
$sth = $dbh->prepare('SELECT name, colour, calories
    FROM fruit
    WHERE calories < :calories AND colour = :colour');
$sth->bindParam('calories', $calories, PDO::PARAM_INT);
/* 名前の前にも、コロン ":" を付けることができます(オプション) */
$sth->bindParam(':colour', $colour, PDO::PARAM_STR);
$sth->execute();
// sql文の中に、変数のように命名した名前(仮にAとする)を入れといて、
// bindParam(A,$calories)でAと$caloriesを入れ替える、みたいな



// 例2 疑問符プレースホルダを用いてプリペアドステートメントを実行する

/* バインドされた PHP 変数によってプリペアドステートメントを実行する */
$calories = 150;
$colour = 'red';
$sth = $dbh->prepare('SELECT name, colour, calories
    FROM fruit
    WHERE calories < ? AND colour = ?');
$sth->bindParam(1, $calories, PDO::PARAM_INT);
$sth->bindParam(2, $colour, PDO::PARAM_STR);
$sth->execute();
// ?でやると1から始まる連番になる。疑問符で書いたほうが書きやすい
?>