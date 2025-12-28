<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container" style="max-width: 900px; margin-top: 20px;">

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; border-bottom: 2px solid #1eff1a; padding-bottom: 10px;">
        <h2 style="color: #1eff1a; margin: 0;">&gt; ADMIN_USER_MANAGEMENT</h2>

        <div style="display: flex; gap: 10px;">
            <?php if (isCurrentSuperAdmin()): ?>
                <a href="/admin/users/create" class="btn-primary" style="text-decoration: none; background: #1eff1a; color: #000; padding: 5px 15px; font-weight: bold;">
                    + NEW_ENTRY
                </a>
            <?php endif; ?>
            <a href="/admin/users/export" class="btn-detail" style="text-decoration: none; border: 1px solid #1eff1a; color: #1eff1a; padding: 5px 15px; font-size: 0.8em;">
                CSV_EXPORT
            </a>
        </div>
    </div>

    <div class="box" style="padding: 10px; border: 1px solid #333;">
        <table style="width: 100%; border-collapse: collapse; color: #1eff1a;">
            <thead>
                <tr style="border-bottom: 1px solid #555; text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px; text-align: center;">PERM_DATA</th>
                    <th style="padding: 10px; text-align: center;">PERM_ADMIN</th>
                    <th style="padding: 10px; text-align: center;">PERM_LOG</th>
                    <th style="padding: 10px; text-align: right;">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admin_list as $admin): ?>
                    <tr style="border-bottom: 1px solid #222;">
                        <td style="padding: 10px;">
                            <?php echo h($admin['login_id']); ?>
                            <?php if ($admin['login_id'] === $_SESSION['login_id']) echo ' <span style="color:#888;">(YOU)</span>'; ?>
                        </td>
                        <td style="text-align: center; color: <?php echo $admin['perm_data'] ? '#1eff1a' : '#444'; ?>;">
                            <?php echo $admin['perm_data'] ? 'ENABLED' : '--------'; ?>
                        </td>
                        <td style="text-align: center; color: <?php echo $admin['perm_admin'] ? '#1eff1a' : '#444'; ?>;">
                            <?php echo $admin['perm_admin'] ? 'ENABLED' : '--------'; ?>
                        </td>
                        <td style="text-align: center; color: <?php echo $admin['perm_log'] ? '#1eff1a' : '#444'; ?>;">
                            <?php echo $admin['perm_log'] ? 'ENABLED' : '--------'; ?>
                        </td>
                        <td style="padding: 10px; text-align: right;">
                            <a href="/admin/users/edit?id=<?php echo h($admin['login_id']); ?>"
                                style="color: #000; background: #1eff1a; padding: 2px 8px; text-decoration: none; font-size: 0.9em; font-weight: bold;">
                                EDIT_DETAIL
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <a href="/" style="color: #888; text-decoration: none;">&lt; RETURN_TO_DASHBOARD</a>
    </div>
</div>