<?php
require_once __DIR__ .'/user-input.php';
require_once __DIR__ .'/db.php';

/** データベースのbooksテーブルに関するクラス */
class Books{
    /** @var object データベースを操作するためのインスタンス(DBクラスの静的プロパティの要約変数)*/
    private $dbh;

    /** DBへの追加用に、xss対策済みの本の情報を格納する変数 */
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
            $this->setProperty($userInput);
        }
    }

    /**
     * idから本の情報を取得する。
     * 本の情報の「更新」の場合にsetProperty()から呼び出される。
     * 
     * @param string $_POST['id']を受け取る
     * @return array DBから取得した本の情報
     */
    public function getBookDataById(string $bookId): array{
        $sql = 'SELECT id, title, isbn, price, publish, author FROM books WHERE id = :id';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":id", $bookId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * 本の情報に関するプロパティを設定する。
     * 引数は、「idのみ(更新フォームのvalueの設定)、idが無い(addBooks())、
     * 「少なくともidとタイトルを含む」(updateBooks()) の3パターン
     * 
     * @param array $_POSTを受け取る
     * @return null エラーがある場合は途中で終了する。
     */
    public function setProperty(array $data) {
        $error = false;

        // idがあれば、idのバリデーションを行う。
        // (idはDBへの更新時は必須だが、追加時には無い)
        if (!empty($data['id'])){
            $idError = UserInput::checkId($data['id']);
            if($idError){
                echo $idError .'<br>';
                $error = true;
                return;
            }
        }

        // $dataがidのみの場合(更新フォーム用)は、DBから本の情報を取得する
        if(count($data) === 1){
            $bookdata = $this->getBookDataById($data['id']);
            $bdError = UserInput::isBookData($bookdata);
            if ($bdError){
                echo $bdError;
                $error = true;
            } else {
                $this->setProperty($bookdata);
            }
            // idのみの場合はここで終了する
            return;
        }

        // 各値のバリデーションを行う。
        $bookDataErrors = UserInput::checkBookData($data);
        foreach($bookDataErrors as $bookDataError){
            echo $bookDataError.'<br>';
            $error = true;
        }

        // エラーがなければ、各プロパティに値を設定する
        if(!$error){
            foreach ($data as $key => $val){
                $this->$key = UserInput::e($val);
            }
        }
    }

    /** Booksクラスのプロパティを取得する*/
    public function getProperty(): array {
        $property = [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'price' => $this->price,
            'publish' => $this->publish,
            'author' => $this->author,
        ];
        return $property;
    }

    /** テーブル booksの内容を<table>で出力する */
    public function showList() :void {
        $sql = 'SELECT * FROM books';
        // ↓sql文を実行
        // 表構造のデータが戻ってくる。配列ではない
        $statement = $this->dbh->query($sql);

        // booksテーブルの内容をリスト形式で表示する。
        echo "<table>";
        echo "<tr><th>更新</th><th>書籍名</th><th>ISBN</th><th>価格</th><th>出版日</th><th>著者名</th></tr>";
        
        echo "</table>";
    }

   /** データベースにユーザー入力データを追加する */
   public function add() :void {
        $sql = 'INSERT INTO books(id, title, isbn, price, publish, author) 
                VALUES (NULL, ?, ?, ?, ?, ?)';
        $stmt = $this->dbh->prepare($sql);

        $i = 0; 
        $stmt->bindParam(++$i, $this->title, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->isbn, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->price, PDO::PARAM_INT);
        $stmt->bindParam(++$i, $this->publish, PDO::PARAM_STR);
        $stmt->bindParam(++$i, $this->author, PDO::PARAM_STR);

        $stmt->execute();
}

    /** booksテーブルを更新する */
    public function update(){
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