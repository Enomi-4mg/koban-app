<?php
namespace App\Utils;

class Validator {
    private $errors = [];

    public function validateKoban($data) {
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

    public function getErrors() {
        return $this->errors;
    }
}