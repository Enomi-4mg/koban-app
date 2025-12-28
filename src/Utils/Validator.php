<?php

namespace App\Utils;

class Validator
{
    /**
     * パスワードの強度バリデーション
     * @param string $password
     * @return array [bool $isValid, string $errorMessage]
     */
    private $errors = [];

    public function validateKoban($data)
    {
        // 1. 必須チェック
        if (empty($data['new_name'])) {
            $this->errors['new_name'] = '交番名は必須です。';
        }

        // 2. 文字数チェック (例: 100文字以内)
        if (mb_strlen($data['new_name']) > 100) {
            $this->errors['new_name'] = '交番名は100文字以内で入力してください。';
        }

        // 3. 形式チェック (例: グループコードは半角英数のみ)
        // preg_match は正規表現でチェックします
        if (!empty($data['new_group_code']) && !preg_match('/^[0-9]+$/', $data['new_group_code'])) {
            $this->errors['new_group_code'] = '団体コードは半角数字で入力してください。';
        }

        $phone_raw = ($data['phone_part1'] ?? '') . ($data['phone_part2'] ?? '') . ($data['phone_part3'] ?? '');
        $postal_raw = ($data['postal_part1'] ?? '') . ($data['postal_part2'] ?? '');
        if (!empty($postal_raw) && !preg_match('/^[0-9]+$/', $postal_raw)) {
            $this->errors['postal'] = '郵便番号は半角数字のみで入力してください。';
        }

        return empty($this->errors);
    }
    public static function validatePassword(string $password): array
    {
        // 1. 文字数チェック (NIST推奨の最小8文字)
        if (mb_strlen($password) < 8) {
            return [false, "パスワードは8文字以上で入力してください。"];
        }

        // 2. 複雑性チェック (正規表現)
        // 英大文字、小文字、数字がそれぞれ1つ以上含まれているか
        $hasUpper = preg_match('/[A-Z]/', $password);
        $hasLower = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/[0-9]/', $password);
        $hasSymbol = preg_match('/[!-@#%^&*(),.?":{}|<>]/', $password);

        if (!$hasUpper || !$hasLower || !$hasNumber) {
            return [false, "英大文字、小文字、数字をすべて組み合わせてください。"];
        }

        return [true, ""];
    }

    /**
     * ログインIDのバリデーション
     */
    public static function validateLoginId(string $loginId): array
    {
        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $loginId)) {
            return [false, "IDは4〜20文字の半角英数字（アンダースコア可）で入力してください。"];
        }
        return [true, ""];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
