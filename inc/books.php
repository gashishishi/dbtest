<?php
require_once __DIR__ .'/checker.php';
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

    /**
     * インスタンス時にDBに接続する。
     */
    public function __construct(){
        // データベースに接続する
        $this->dbh = DB::getDbInstance()->getDbh();
    }

    /**
     * idプロパティを設定する。
     *
     * @param string $id booksテーブルのidを指定する。
     * @return void エラーが有る場合、エラーメッセージを表示する。
     */
    public function setId(string $id){
        $id = Checker::e($id);

        // idのバリデーション
        $error = Checker::checkId($id);
        if($error){
            echo $error .'<br>';
            return;
        }
        // idを設定
        $this->id = $id;
    }

    /**
     * idをもとにDBから本の情報を取得する。
     * 
     * @param string booksテーブルのidを指定する。
     * @return null|array 指定idが存在しなければUseInputクラスからのエラーを、存在すれば結果を配列で返す
     */
    public function getDataById(){
         // DBから本の情報を取得する
        $sql = 'SELECT id, title, isbn, price, publish, author FROM books WHERE id = :id';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // もし$resultが空ならエラーメッセージを出力する。
        $error = Checker::checkId($result);
        if ($error){
            echo $error;
            return ;
        }
        return $result;
    }

    /**
     * DBの更新用に初期値となるBooksクラスのプロパティを設定する。
     * 
     * @return null エラーがある場合は途中で終了する。
     */
    public function setEditProperty() {
        $data = $this->getDataById();
        foreach ($data as $key => $val){
            $this->$key = Checker::e($val);
        }
    }

    /**
     * DBの変更(更新or追加)用にBooksクラスのプロパティを設定する。
     *
     * @param array $input
     * @return void
     */
    public function setChangeProperty(array $input){
        $error = false;
        // 各値のバリデーションを行う。
        $bookDataErrors = Checker::checkBookData($input);
        // エラーがあれば表示する。
        foreach($bookDataErrors as $bookDataError){
            echo $bookDataError.'<br>';
            $error = true;
        }
        // エラーがなければ、各プロパティに値を設定する
        if(!$error){
            foreach ($input as $key => $val){
                $this->$key = Checker::e($val);
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

    /**
     * リスト作成のため、booksテーブルのデータを取得。
     *
     * @return $statement DBから取得したデータを返す。
     */
    public function list(){
        $sql = 'SELECT * FROM books';
        // ↓sql文を実行
        // 表構造のデータが戻ってくる。配列ではない
        $statement = $this->dbh->query($sql);
        return $statement;
    }
 
    /**
     * DBから取得したbooksテーブルの検索結果を返す。
     *
     * @param array $get $_GETを受け取る
     * @return 
     */
    public function search(array $get){
        $param = $this->removePagerKey($get);

        // sql文の組み立て
        $sql = 'SELECT * FROM books WHERE 1 '; // 本を検索する。この文にANDをつなげていく。
        foreach($param as $key => $val){
            if(!empty($val)){
                if($key === 'publish'){
                    // 日付の場合、区切りを/で入れられたり、日付まで入れるので、年月でヒットするようにする。
                    $sql .= "AND DATE_FORMAT(publish, '%Y-%m') = DATE_FORMAT('$get[publish]', '%Y-%m')";
                    continue;
                }
                $sql .= "AND $key LIKE '%$val%' "; //指定した$keyの$valがあれば文を追加。
            }
        }
        $sql .= empty($_GET['p']) ? " LIMIT 0,5" : " LIMIT $_GET[p],5";
        // var_dump($sql);

        // ↓sql文を実行
        $statement = $this->dbh->prepare($sql);
        $statement->execute();

        return $statement;
    }

    /**
     * ページャー用の'p'キーを除去し、ついでにxss対策を行う
     *
     * @param array $input ユーザー入力データ
     * @return array pキーを除いた$input。
     */
    public function removePagerKey(array $input): array{
        $param = [];
        foreach($input as $key => $val){
            if ($key === 'p'){
                continue;
            }        
            // xss対策
            $param[$key] = Checker::e($val);
        }
        return $param;
    }

    /**
     * ページャーを出力する。
     *
     * @return void
     */
    public function pager(){
        $sql = 'SELECT count(*) as cnt FROM books'; //データ件数を数える
        $page = $this->dbh->prepare($sql);
        $page->execute();
        $max = $page->fetch()['cnt'] ;

        $pagein = 5;
        
        for($i=0 ; $i <= $max; $i += $pagein)
            echo "<a class='btn' href='./?p=$i'>" . $i/$pagein + 1 .'</a>';
    }


        /**
     * show-json.phpでjson形式のデータを表示する。
     * または、ajax-json.htmlで指定idの本を表示する用。
     *
     * @param [type] $id booksテーブルのid
     * @return void json形式でデータを表示。header()関数があるのでページ冒頭で起動する。
     */
    public function showJson($id){
        $id = Checker::e($id);
        $sql = "SELECT * FROM books WHERE id = ?";
        // ↓sql文を実行
        // 表構造のデータが戻ってくる。配列ではない
        $statement = $this->dbh->prepare($sql);
        //バインドせずにexecuteに値を渡す場合、配列にする。
        $statement ->execute([$id]);
        // json形式で取得する。PHP初級の問題でheaderエンコードの話がある。後で別ページとして組み込む?json形式でダウンロードボタンつけたり?
         //fetchAllのときは二次元配列、fetchのときは1次元配列で返ってくる。jqueryで取り出すとき注意。
        $toJson = $statement->fetch(PDO::FETCH_ASSOC);
        header('Content-type: application/json');
        echo json_encode($toJson);
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