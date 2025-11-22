document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('btn-add-role');
    
    if (addBtn) {
        addBtn.addEventListener('click', async function() {
            const name = prompt('Name der neuen Rolle:');
            if (!name) return;
            const description = prompt('Beschreibung (optional):') || '';
            try {
                const res = await fetch('/apps/verein/api/roles', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, description, permissions: [] })
                });
                if (!res.ok) throw new Error('Fehler beim Anlegen der Rolle');
                alert('Rolle angelegt');
                window.location.reload();
            } catch (err) {
                console.error(err);
                alert('Fehler beim Anlegen der Rolle');
            }
        });
    }

    // Edit buttons
    document.querySelectorAll('.btn-edit-role').forEach(btn => {
        btn.addEventListener('click', async function() {
            const row = this.closest('.role-row');
            const roleId = row.dataset.roleId;
            const currentName = row.querySelector('.role-name').textContent.trim();
            const currentDesc = row.querySelector('.role-description').textContent.trim();
            const name = prompt('Neuer Name:', currentName);
            if (name === null) return;
            const description = prompt('Neue Beschreibung:', currentDesc) || '';
            try {
                const res = await fetch(`/apps/verein/api/roles/${roleId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, description })
                });
                if (!res.ok) throw new Error('Fehler beim Aktualisieren');
                alert('Rolle aktualisiert');
                window.location.reload();
            } catch (err) {
                console.error(err);
                alert('Fehler beim Aktualisieren der Rolle');
            }
        });
    });

    // Delete buttons
    document.querySelectorAll('.btn-delete-role').forEach(btn => {
        btn.addEventListener('click', async function() {
            const roleId = this.closest('.role-row').dataset.roleId;
            if (!confirm('Rolle wirklich löschen?')) return;
            try {
                const res = await fetch(`/apps/verein/api/roles/${roleId}`, { method: 'DELETE' });
                if (!res.ok) throw new Error('Fehler beim Löschen');
                alert('Rolle gelöscht');
                window.location.reload();
            } catch (err) {
                console.error(err);
                alert('Fehler beim Löschen der Rolle');
            }
        });
    });
});
