<?php
/** @var array $_['roles'] */
$roles = $_['roles'] ?? [];
?>

<div id="app-content" class="verein-admin-roles">
    <div class="app-navigation-spacer"></div>
    
    <div class="container">
        <h2>Rollen verwalten</h2>
        
        <div class="roles-actions">
            <button id="btn-add-role" class="button primary">
                <span class="icon icon-add"></span> Neue Rolle
            </button>
        </div>

        <?php if (empty($roles)): ?>
            <p class="empty-content">
                <span class="icon icon-roles"></span>
                <h3>Noch keine Rollen vorhanden</h3>
                <p>Erstelle deine erste Rolle mit dem Button oben.</p>
            </p>
        <?php else: ?>
            <table class="roles-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Verein-Typ</th>
                        <th>Beschreibung</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr class="role-row" data-role-id="<?php echo htmlspecialchars($role['id'] ?? '') ?>">
                            <td class="role-name"><?php echo htmlspecialchars($role['name'] ?? '') ?></td>
                            <td class="role-club-type"><?php echo htmlspecialchars($role['club_type'] ?? '') ?></td>
                            <td class="role-description"><?php echo htmlspecialchars($role['description'] ?? '') ?></td>
                            <td class="role-actions">
                                <button class="button btn-edit-role" title="Bearbeiten">
                                    <span class="icon icon-edit"></span>
                                </button>
                                <button class="button btn-delete-role" title="Löschen">
                                    <span class="icon icon-delete"></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
#app-content.verein-admin-roles {
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.roles-actions {
    margin: 20px 0;
}

.roles-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.roles-table th,
.roles-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.roles-table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.roles-table tr:hover {
    background-color: #fafafa;
}

.role-actions button {
    margin-right: 8px;
}

.empty-content {
    text-align: center;
    padding: 40px 20px;
}
</style>

<?php
// Move admin page JS into an external file to comply with CSP (avoid inline scripts)
script('verein', 'admin-roles');
?>
