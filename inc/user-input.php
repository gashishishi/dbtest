<?php
/**
 * ユーザー入力のバリデーションチェック、データチェックを行い、
 * xss対策の関数を含むクラス。
 * 配下のメソッドではgetNoNameError(),getNoPasswordError()を除き
 * エラーメッセージをstringまたはarrayで返す。
 */
class UserInput{
    /** エラーメッセージ */

   /** booksテーブルに関するエラーメッセージ */
    private const ID_ERROR = [
        'NoId' => 'idを指定してください。',
        'IncorrectId' => 'idが正しくありません。',
    ];

    private const TITLE_ERROR = [
        'NoTitle' => 'タイトルは必須です。',
        'CharacterLimit' => '作品名は20文字までです。'
    ];
    
    private const ISBN_ERROR = 'isbnは数字13桁までです。';
    private const PRICE_ERROR = '価格は数字6桁までです。';
    private const PUBLISH_ERROR = [
            'NoDate' => '日付は必須です。',
            'IncorrectDate' => '正しい日付を入力してください。'
        ];

    private const AUTHOR_ERROR = '著者名は80文字以内で入力してください。';

    private const BOOKDATA_ERROR = '指定したデータはありません';

    /** ログイン入力についてのエラーメッセージ */
    private const USERNAME_ERROR =[
        'NoUsername' => 'ユーザー名を入力してください。',
        'NotExistUsername' => 'ログインに失敗しました。'
    ];
    private const PASSWORD_ERROR = [
        'NoPassword' => 'パスワードを入力してください。',
        'WrongPassword' => 'ログインに失敗しました。',
    ];

    /** XSS対策 */
    static function e($str){
        htmlspecialchars($str, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        return $str;
    }

    /** 入力内容($data)をバリデーションでチェックする */
    /** 
     * booksのidのバリデーション 
     * 
     * @param string $_POST['id']を受け取る
     * @return  エラーがあればエラーメッセージ文字列を返す
    */
    static function checkId(string $id) {
        if (empty($id)){
            return self::ID_ERROR['NoId'];
        } else if (!preg_match('/\A\d{1,11}+\z/u', $id)) {
            return self::ID_ERROR['IncorrectId'];
        }
    }

    /** 
     * booksの各情報のバリデーション 
     * 
     * @param array $_POSTや$_GETを受け取る
     * @return array エラーがあれば配列で返す
    */
    public static function checkBookData(array $data): array {
        $error = [];
        // 作品名のバリデーション
        if (empty($data['title'])){
            $error[] =  self::TITLE_ERROR['NoTitle'];
        } else if(!preg_match('/\A[[:^cntrl:]]{1,20}\z/u', $data['title'])){
            $error[] =  self::TITLE_ERROR['CharacterLimit'];
        }

        // isbnのバリデーション
        if (!preg_match('/\A\d{0,13}\z/u', $data['isbn'])){
            $error[] =  self::ISBN_ERROR;
        }

        // 価格のバリデーション
        if (!preg_match('/\A\d{0,6}\z/u', $data['price'])){
            $error[] =  self::PRICE_ERROR;
        }

        // 日付のバリデーション
        if (empty($data['publish'])){
            $error[] =  self::PUBLISH_ERROR['NoDate'];
        }

        // 出版日のバリデーション
        if (!strtotime($data['publish'])){
            $error[] =  self::PUBLISH_ERROR['IncorrectDate'];
        }
        
        // 著者名のバリデーション
        if (!preg_match('/\A[[:^cntrl:]]{0,80}\z/u', $data['author'])){
            $error[] =  self::AUTHOR_ERROR;
        }

        return $error;
    }

    static function getBookDataError():string{
        return self::BOOKDATA_ERROR;
    }
    /** ログイン入力のバリデーション */
    /**
     * ユーザー名未入力エラーの取得
     * @return string USERNAME_ERROR['NoUsername']エラー
     */ 
    static function getNoNameError(): string {
        return self::USERNAME_ERROR['NoUsername'];       
    }

    static function getNotExistUsernameError(): string {
        return self::USERNAME_ERROR['NotExistUsername'];
    }
    /**
     * パスワード未入力エラーの取得
     * @return string USERNAME_ERROR['NoPassword']エラー
     */ 
    static function getNoPasswordError(): string {
        return self::PASSWORD_ERROR['NoPassword'];
    }

    /**
     * 入力されたpasswordとDBのパスワードを比較する。
     *
     * @param string $inputPass Usersクラスの$password(ユーザー入力パスワード)
     * @param string $savedPass DB上のパスワード
     * @return string PASSWORD_ERROR['WrongPassword']エラー
     */ 
    static function checkPassword($inputPass, $savedPass):string {
        if(!password_verify($inputPass, $savedPass)) { 
            return self::PASSWORD_ERROR['WrongPassword'];
        }
    }

}