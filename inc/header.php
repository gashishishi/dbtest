
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../inc/dbtest_style.css">
        <title>書籍データベース</title>
</head>
<header>
    <h1>書籍リスト</h1>
</header>
<body>
    <div class="header-nav">
        <ul id='nav'>
            <li><a href="./">ホーム</li></a>
            <li><a href="./input-form.php">追加</li></a>
            <li><a href="./logout.php"><?= empty($_SESSION['login']) ? 'ログイン':'ログアウト'?></li></a>
        </ul>
    </div>