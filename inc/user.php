<?php
require_once __DIR__ .'/user-input.php';
require_once __DIR__ .'/db.php';

/**
 * ユーザー情報(名前、パスワード)を扱うクラス。
 * セッションIDやトークンの生成もこのクラスで行う。
 */
class User{
    /** @var object データベースを操作するためのインスタンス(DBクラスの静的プロパティの要約変数)*/
    private $dbh;

    private $username;
    private $password;

    public function __construct(array $userInput, bool $check = false){
        // データベース接続
        $this->dbh = DB::getDbInstance()->getDbh();

        // ユーザー名とパスワードの未入力チェック。
        $nameError = UserInput::checkNameSimple($userInput['username']);
        $passwordError = UserInput::checkPasswordSimple($userInput['password']);
        if($nameError){
            echo $nameError;
            return;
        }
        if($passwordError){
            echo $passwordError;
            return;
        }

            // エラーがなければ設定する
            // ユーザー名の設定
            $this->setUsername($userInput);

            // ユーザー名が設定できたら、DBからハッシュ化されたパスワードを取得し設定する。
            $this->setPassword();
            
            // パスワードのチェック
            $checkPasswordError = UserInput::checkPassword($userInput['password'], $this->password);
            if ($checkPasswordError){
                echo $checkPasswordError;
                return;
            } else {
                $this->createSessionId($userInput['password']);
            }
        }

    }

    /**
     * ユーザー入力からユーザー名を設定する。
     * 入力されたユーザー名にxxs対策を行う
     * @param array $userInput ユーザー入力($_POST)
    */
    public function setUsername(array $userInput){
        $usernameError = UserInput::isUsername($userInput);
        if ($usernameError){
            echo $usernameError;
        } else{
            $this->username = UserInput::e($userInput['username']);
        } else{
            echo UserInput::getNoNameError();
        }
    }

    /** $this->usernameをもとに、DBからパスワードを取得する。*/
    public function setPassword(){
        $sql = 'SELECT password FROM users WHERE username = :username';
        $stmt = $this->dbh->prepare($sql);
        
        $stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // ユーザーが存在する($resultにDBから得たパスワードが入っている)なら
        // パスワードをセット
        if($result){
            $this->password = UserInput::e($result['password']);
        }
    }

    /**
     * ユーザー名がDBにあるか調べる。
     *
     * @param string $username 対象のユーザー名
     * @return boolean 存在すればtrue
     */
    public function isUsernameInDb(string $username): bool {
        $sql = "SELECT count(*) as ct FROM users WHERE username = ?";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(1, $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /** セッションidを再生成する。*/
    public function createSessionId(){
        session_regenerate_id(true);
        $_SESSION['login'] = true;
        header("Location: index.php");
    }

    /** トークンを生成する 
     * @return string 生成したトークン
    */
    static function createToken(): string {
        $token = bin2hex(random_bytes(20));
        $_SESSION['token'] = $token;
        return $token;
    }
    
}