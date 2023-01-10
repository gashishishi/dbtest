<?php
require_once 'db_open.php';
// booksテーブルを操作するクラス
class Books{
    // DbOpenオブジェクトの委譲先になる
    private DbOpen $dbo;
    // DbOpenでデータベースに接続したDPOオブジェクト
    private object $dbh;

    private $id = Null;
    private $title;
    private $isbn;
    private $price;
    private $publish;
    private $author;
    
    public function __construct($userInput = null){
        $this->dbo = new DbOpen;
        $this->dbh = $this->dbo->dbOp();
        if ($userInput !== null) {
            $validationError = self::checkInput($userInput);
            if (empty($validationError)) {
                $this->setBookData($userInput);
            } else {
                echo "エラーです。";
            }
        }
    }

    // XSS対策
    public function e(string $str) :string {
        htmlspecialchars($str, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        return $str;
    }

    public function setBookData($userInput){
        foreach ($userInput as $key=>$val){
            $this->$key = $this->e($val);
        }
    }

    public function setId($bookId){
        if (empty($bookId)){
            echo "idを指定してください。";
        } else if (!preg_match('/\A\d{1,11}+\z/u', $bookId)) {
            echo "idが正しくありません。";
        } else {
            $this->id = $this->e(($bookId));
        }
    }

    // テーブル booksの内容を<table>にして表示する
    public function showBooksList() :void {
        $sql = 'SELECT * FROM books';
        
        // ↓sql文を実行
        // 表構造のデータが戻ってくる。配列ではない
        $statement = $this -> dbh->query($sql);

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
            $id =  $this->e($row[0]);
            $title = $this->e($row[1]);
            $isbn = $this->e($row[2]);
            $price = $this->e($row[3]);
            $publish = $this->e($row[4]);
            $author = $this->e($row[5]);
            
            $booksList = <<<EOD
            <tr>
            <td><a href="edit.php?id={$id}">更新</a></td>
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
    public function addInput() :void {
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
        //                          ↓bindParamは<>を直さないので、直接$_POSTを書いてはいけない。
        $stmt->bindParam(++$i, $this->title, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->isbn, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->price, PDO::PARAM_INT);
        $stmt->bindParam(++$i, $this->publish, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->author, PDO::PARAM_STR);

        $stmt->execute();
        echo "データが追加されました。";
        echo "<a href='list.php'> リストへ戻る <a>";
    }

    // idをもとに、編集用フォームを表示する。
    public function showEditForm() {            
        $sql = 'SELECT id, title, isbn, price, publish, author FROM books WHERE id = :id';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result){
            echo "指定したデータはありません";
        } else {
            $this->setBookData($result);
            
            // フォームを表示する
            // 今回のような場合、普通ヒアドキュメントにせず、htmlで書く。
            // $htmlFormの内容を別の場所で使うんならヒアドキュメントにするべき
            $htmlForm = <<<EOD
            <form action="update.php" method="post">
            <p>
            <label for="title"> タイトル（必須・20 文字まで）:</label>
            <input type="text" name="title" value="{$this->title}">
            </p>
            <p>
            <label for="isbn">ISBN（13 桁までの数字）:</label>
            <input type="number" name="isbn" max="9999999999999" maxlength="13" value="{$this->isbn}">
            </p>
            <p>
            <label for="price"> 定価（6 桁までの数字）:</label>
            <input type="number" name="price" max="999999" maxlength="6" value="{$this->price}">
            </p>
            <p>
            <label for="published"> 出版日:</label>
            <input type="date" name="publish" value="{$this->publish}">
            </p>
            <p>
            <label for="author"> 著者（80 文字まで）:</label>
            <input type="text" name="author" maxlength="80" value="{$this->author}">
            </p>
            <p class="button">
            <input type="hidden" name="id" value="{$this->id}">
            <input type="submit" value=" 送信する">
            </p>
            </form>
            EOD;
            echo $htmlForm;        
        }
    }


    // 次ここから。テキストp.234
    public function updateBooks(){
        $sql = "UPDATE books SET
        title = ?,
        isbn = ?,
        price = ?,
        publish = ?,
        author = ?,
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


    // 入力内容($_POST)をバリデーションでチェックする。
    static function checkInput() :array {
        // エラーメッセージを格納する変数
        $validationError = [];

        // エラーメッセージ
        $titleError = [
                    'NoTitle' => 'タイトルは必須です。',
                    'CharacterLimit' => '作品名は20文字までです'
                ];
        $isbnError = 'isbnは数字13桁までです。';
        $priceError = '価格は数字6桁までです。';
        $publishError = [
                        'NoDate' => '日付は必須です。',
                        'IncorrectDate' => '正しい日付を入力してください。'
                    ];
        $authorError = '著者名は80文字以内で入力してください。';


        // 作品名のバリデーション
        if (empty($_POST['title'])){
            $validationError[] = $titleError['NoTitle'];
        } else if(!preg_match('/\A[[:^cntrl:]]{1,20}\z/u', $_POST['title'])){
            $validationError[] = $titleError['CharacterLimit'];
        }

        // isbnのバリデーション
        if (!preg_match('/\A\d{0,13}\z/u', $_POST['isbn'])){
            $validationError[] = $isbnError;
        }

        // 価格のバリデーション
        if (!preg_match('/\A\d{0,6}\z/u', $_POST['price'])){
            $validationError[] = $priceError;
        }

        // 日付のバリデーション
        if (empty($_POST['publish'])){
            $validationError[] = $publishError['NoDate'];
        }

        if (!strtotime($_POST['publish'])){
            $validationError[] = $publishError['IncorrectDate'];
        }
        
        // 著者名のバリデーション
        if (!preg_match('/\A[[:^cntrl:]]{0,80}\z/u', $_POST['author'])){
            $validationError[] = $authorError;
        };

        return $validationError;
    }
}