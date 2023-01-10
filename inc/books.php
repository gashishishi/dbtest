<?php
require_once __DIR__ .'/user-input.php';
require_once __DIR__ .'/db.php';
// booksテーブルを操作するクラス
class Books{
    //データベースを操作するための、DBクラスのインスタンス
    private $dbh;

    // DBへの追加用に、xss対策済みの本の情報を格納する変数
    private $id;
    private $title;
    private $isbn;
    private $price;
    private $publish;
    private $author;

    public function __construct(array $userInput = null){
        // データベースに接続する
        $this->dbh = DB::getDbInstance()->getDbh();

        // もし引数があれば本の情報を設定する。
        if (isset($userInput)){
            $this->setBookData($userInput);
        }
    }

    public function getBookDataById($bookId): array{
        $sql = 'SELECT id, title, isbn, price, publish, author FROM books WHERE id = :id';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":id", $bookId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    // 本の情報に関するプロパティを設定する。
    // 引数は、「idのみ(更新フォームのvalueの設定)、idが無い(addBooks())、
    // 「少なくともidとタイトルを含む」(updateBooks()) の3パターン
    public function setBookData(array $data) {
        $validationError = false;

        // idがあれば、idのバリデーションを行う。
        // (idはDBへの更新時は必須だが、追加時には無い)
        if (!empty($data['id'])){
            $idError = UserInput::checkId($data['id']);
            foreach ($idError as $error){
                echo $error .'<br>';
                $validationError = true;
            }
        }

        // $dataがidのみの場合(更新フォーム用)は、DBから本の情報を取得する
        if(count($data) === 1){
            $bd = $this->getBookDataById($data['id']);

            // $bdの値が空なら、指定idの行は存在しない
            if (!$bd){
                echo "指定したデータはありません";
                $validationError = true;
            } else {
                $this->setBookData($bd);
            }
            return;
        }

        // 各値のバリデーションを行う。
        $bookDataError = UserInput::checkBookData($data);
        foreach($bookDataError as $error){
            echo $error.'<br>';
            $validationError = true;
        }

        // エラーがなければ、各プロパティに値を設定する
        if(!$validationError){
            foreach ($data as $key => $val){
                $this->$key = UserInput::e($val);
            }
        }
    }

    // Booksクラスのプロパティを取得する
    public function getBooksProperty(): array {
        $booksProperty = [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'price' => $this->price,
            'publish' => $this->publish,
            'author' => $this->author,
        ];
        return $booksProperty;
    }

    // テーブル booksの内容を<table>で出力する。
    public function showBooksList() :void {
        $sql = 'SELECT * FROM books';
        // ↓sql文を実行
        // 表構造のデータが戻ってくる。配列ではない
        $statement = $this->dbh->query($sql);

        // foreachならfetchせずに直接回せる
        // データベースから取り出した値であっても、タグが含まれていたら実行されてしまう
        // ユーザー入力値はすべてhtmlspecialcharsする
        // while($row = $statement->fetch()){
        //     echo "書籍名：" .e($row[0]) ."<br>";
        //     echo "著者名：" .e($row[1]) ."<br><br>";
        // }
        // foreach($statement as $row){
        //     echo "書籍名：" .$this->e($row[0]) ."<br>";
        //     echo "著者名：" .$this->e($row[1]) ."<br><br>";
        // }

        // booksテーブルの内容をリスト形式で表示する。
        echo "<table>";
        echo "<tr><th>更新</th><th>書籍名</th><th>ISBN</th><th>価格</th><th>出版日</th><th>著者名</th></tr>";
        foreach($statement as $row){
            $id = UserInput::e($row[0]);
            $title = UserInput::e($row[1]);
            $isbn = UserInput::e($row[2]);
            $price = UserInput::e($row[3]);
            $publish = UserInput::e($row[4]);
            $author = UserInput::e($row[5]);
            
            $booksList = <<<EOD
            <tr>
            <td><a href="input-form.php?id={$id}">更新</a></td>
            <td>$title</td>
            <td>$isbn</td>
            <td>$price</td>
            <td>$publish</td>
            <td>$author</td>
            </tr>
            EOD;
            
            echo $booksList;
        }
        echo "</table>";
    }

   // データベースにユーザー入力データを追加する
   public function addBooks() :void {
        // 経産省の独立行政法人の指導によるとDBには安全な方法で値を出し入れする必要があるので、以下の回りくどい書き方は必須。
        // 以下は、この書き方しか無い。(テキストでは書いてる):titleを $_POST['title']にできるけど、やってはいけない。ここは、こう書くもの。
        //books(~~~)の()内は、全てのフィールドに値を入れる場合のみ省略できる。
        $sql = 'INSERT INTO books(id, title, isbn, price, publish, author) 
                VALUES (NULL, ?, ?, ?, ?, ?)';
        $stmt = $this->dbh->prepare($sql);
        // var_dump($stmt);
        // $price = (int) $_POST['price'];  ←キャストいらない
        // 一個ずつ、不都合な入力値(SQLインジェクション)などがあれば除去する。こう書くものなので諦めよう
        // この書き方しかないプリペアドステートメントとバインド機構は必須(IPAの「安全なウェブサイトの作り方」を見よう)
        // プリペアードステートメント → sql文の構文チェック
        // バインド機構 → bindParam() sql文として不都合な文字を除去

        $i = 0; 
                            //  ↓bindParamは<>を直さないので、ここに直接$_POSTを書いてはいけない。
        $stmt->bindParam(++$i, $this->title, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->isbn, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->price, PDO::PARAM_INT);
        $stmt->bindParam(++$i, $this->publish, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->author, PDO::PARAM_STR);

        $stmt->execute();
}

    // booksテーブルを更新する
    public function updateBooks(){
        $sql = "UPDATE books SET
        title = ?,
        isbn = ?,
        price = ?,
        publish = ?,
        author = ?
        WHERE id = ?";

        $stmt = $this -> dbh->prepare($sql);
        $i = 0;

        $stmt->bindParam(++$i, $this->title, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->isbn, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->price, PDO::PARAM_INT);
        $stmt->bindParam(++$i, $this->publish, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->author, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->id, PDO::PARAM_INT);

        $stmt->execute();
    }

}