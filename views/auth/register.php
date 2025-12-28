<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width: 450px; margin-top: 60px;">
    
    <div class="box" style="border: 2px solid #1eff1a; padding: 30px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 2px; background: rgba(30, 255, 26, 0.2); box-shadow: 0 0 15px #1eff1a; animation: scan 4s linear infinite;"></div>

        <h2 style="color: #1eff1a; text-shadow: 0 0 10px rgba(30, 255, 26, 0.5); text-align: center; margin-top: 0; letter-spacing: 2px;">
            CREATE NEW ACCOUNT
        </h2>
        
        <p style="color: #888; font-size: 0.8em; text-align: center; margin-bottom: 25px;">
            システムを利用するためのIDとパスワードを設定してください。
        </p>

        <form method="post" action="/register">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <div style="margin-bottom: 20px;">
                <label style="color: #1eff1a; display: block; margin-bottom: 8px; font-size: 0.9em;">LOGIN ID</label>
                <input type="text" name="login_id" required 
                       style="width: 100%; background: #000; border: 1px solid #333; color: #1eff1a; padding: 12px; font-family: monospace; box-sizing: border-box;" 
                       placeholder="4-20文字の半角英数字">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="color: #1eff1a; display: block; margin-bottom: 8px; font-size: 0.9em;">PASSWORD</label>
                <input type="password" name="password" required 
                       style="width: 100%; background: #000; border: 1px solid #333; color: #1eff1a; padding: 12px; font-family: monospace; box-sizing: border-box;" 
                       placeholder="8文字以上 (英大・小・数を含む)">
            </div>

            <div style="background: rgba(30, 255, 26, 0.05); border-left: 3px solid #ffff00; padding: 10px; margin-bottom: 30px;">
                <p style="color: #ffff00; font-size: 0.75em; margin: 0;">
                    ※登録直後の権限は「閲覧のみ」に設定されます。データの追加・編集が必要な場合は管理者に申請してください。
                </p>
            </div>

            <input type="submit" value="[ 登録を実行する ]" class="btn-primary" 
                   style="width: 100%; padding: 15px; font-weight: bold; font-size: 1.1em; cursor: pointer; border: 1px solid #1eff1a; background: transparent; color: #1eff1a; transition: 0.3s;">
        </form>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="/" style="color: #888; text-decoration: none; font-size: 0.9em;">← ログイン画面へ戻る</a>
    </div>
</div>

<style>
@keyframes scan {
    0% { top: -5%; }
    100% { top: 105%; }
}
/* ホバー時に発光させる */
input[type=submit]:hover {
    background: #1eff1a !important;
    color: #000 !important;
    box-shadow: 0 0 20px #1eff1a;
}
input:focus {
    outline: none;
    border-color: #1eff1a !important;
    box-shadow: 0 0 5px #1eff1a;
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>