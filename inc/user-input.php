<?php
class UserInput{
    // エラーメッセージ

    // booksテーブルに関するエラーメッセージ
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

    // ログイン入力についてのエラーメッセージ
    private const USERNAME_ERROR =[
        'NoUsername' => 'ユーザー名を入力してください。',
        'NotExistUsername' => 'ログインに失敗しました。'
    ];
    private const PASSWORD_ERROR = [
        'NoPassword' => 'パスワードを入力してください。',
        'WrongPassword' => 'ログインに失敗しました。',
    ];

    // XSS対策
    static function e($str){
        htmlspecialchars($str, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        return $str;
    }

    // 入力内容($data)をバリデーションでチェックする。

    // booksのidのバリデーション
    static function checkId(string $id): Generator{
        if ($id){
            // idのバリデーション
            if (empty($id)){
                yield self::ID_ERROR['NoId'];
            } else if (!preg_match('/\A\d{1,11}+\z/u', $id)) {
                yield self::ID_ERROR['IncorrectId'];
            }
        }
    }

    // booksの各情報のバリデーション
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

    // static function checkAddOrUpdate(array $data) {
    //     $add = false;
    //     if (!empty($data['id'])){
    //         echo "エラーが発生しました.";
    //         return ;
    //     }
    //     $BookDataError = self::checkBookData($data);
    //     foreach ($BookDataError as $e){
    //         $error[] = $e;
    //     }
    //     return $error;
    // }

    
    // ログイン入力のバリデーション
    // 最初に行う簡易チェック
    static function checkSimple(array $inputData): array {
        $error = [];
        if (empty($inputData['username'])){
            $error[] = self::USERNAME_ERROR['NoUsername'];
        }
        if (empty($inputData['password'])){
            $error[] = self::PASSWORD_ERROR['NoPassword'];
        }
        return $error;
    }

    // 入力されたpasswordとDBのパスワードを比較する。
    static function checkPassword($inputPass, $savedPass){
        if(!password_verify($inputPass, $savedPass)) { 
            return self::PASSWORD_ERROR['WrongPassword'];
        }
    }

    // ユーザー名が存在するかのチェック
    static function isUserName($stmtResult): bool {
        // $resultが空ならusernameが存在しない
        if (!$stmtResult){
            return true;
        }
        return false;
    }


}