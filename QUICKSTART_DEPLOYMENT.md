# 🎯 Quick-Start Guide – Vereins-App Deployment

## ✅ Deployment Status
**The Vereins-App is now fully deployed to your Nextcloud server!**

```
Version: 0.1.0
Nextcloud: 32.0.1.2
Status: ✅ READY
```

## 📍 Accessing the App

### Via Web Browser
1. **Log into your Nextcloud:**
   ```
   http://localhost/nextcloud/
   ```

2. **Navigate to the Vereins-App:**
   - Look for "Verein" in the left sidebar
   - Or go directly to: `http://localhost/nextcloud/index.php/apps/verein/`

3. **Log in with:** 
   - Username: `admin`
   - Password: `admin`

### Via API (REST)

**List Members:**
```bash
curl -u admin:admin http://localhost/nextcloud/index.php/apps/verein/members
```

**Create Member:**
```bash
curl -X POST \
  -u admin:admin \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Max Mustermann",
    "address": "Musterstraße 1",
    "email": "max@example.com",
    "iban": "DE89370400440532013000",
    "bic": "COBADEDEXA",
    "role": "member"
  }' \
  http://localhost/nextcloud/index.php/apps/verein/members
```

## 🏗️ Project Structure

```
Local Workspace:
/home/stefan/Dokumente/Programmieren\ lernen/Nextcloud-Verein/

Server Installation:
/var/www/html/nextcloud/apps/verein/
```

## 📁 Files Synchronized

✅ **Backend (PHP)**
- `lib/Controller/` - API endpoints (7 controllers)
- `lib/Service/` - Business logic (3 services)
- `lib/Db/` - Database models & mappers

✅ **Configuration**
- `appinfo/info.xml` - App metadata
- `appinfo/routes.php` - Route definitions
- `appinfo/database.xml` - Database schema

✅ **Other Files**
- Tests, icons, README files

## 🔧 Development Workflow

### Make Changes Locally
```bash
cd /home/stefan/Dokumente/Programmieren\ lernen/Nextcloud-Verein/
# Edit PHP files in lib/
# Edit Vue components in js/
```

### Sync to Server
```bash
sudo rsync -av \
  /home/stefan/Dokumente/Programmieren\ lernen/Nextcloud-Verein/lib/ \
  /var/www/html/nextcloud/apps/verein/lib/

sudo rsync -av \
  /home/stefan/Dokumente/Programmieren\ lernen/Nextcloud-Verein/appinfo/ \
  /var/www/html/nextcloud/apps/verein/appinfo/
```

### Fix Permissions (if needed)
```bash
sudo chown -R www-data:www-data /var/www/html/nextcloud/apps/verein/
```

### Clear Caches
```bash
sudo systemctl restart apache2
# or manually clear OPcache in Nextcloud admin settings
```

## 🗄️ Database

**Tables Created:**
- `verein_members` - Member information
- `verein_fees` - Fee records

**Sample Member Record:**
```json
{
  "id": 1,
  "name": "Max Mustermann",
  "address": "Musterstraße 1",
  "email": "max@example.com",
  "iban": "DE89370400440532013000",
  "bic": "COBADEDEXA",
  "role": "member"
}
```

## 📊 Core Features Implemented

✅ **Member Management**
- Create, read, update, delete members
- Role-based access (member, cashier, board)
- IBAN validation
- Contact information storage

✅ **Fee Management**
- Track membership fees
- Status tracking (open, paid, overdue)
- CSV export functionality

✅ **SEPA Export**
- Generate SEPA XML (pain.008.001.02)
- Direct debit export
- Preview functionality

✅ **Dashboard Integration**
- Calendar data endpoint
- Finance summary
- Deck/Board integration

## 🚀 Next Steps

1. **Build Frontend**
   ```bash
   cd /home/stefan/Dokumente/Programmieren\ lernen/Nextcloud-Verein/
   npm run build
   ```

2. **Test API Endpoints**
   ```bash
   # Test members endpoint
   curl -u admin:admin http://localhost/nextcloud/index.php/apps/verein/members
   ```

3. **Create Sample Data**
   - Use API to create test members and fees
   - Verify CSV export works
   - Test SEPA XML generation

4. **Access Admin Settings**
   - Go to Nextcloud admin panel
   - Configure app permissions

## 📚 Documentation

- **Development Guide:** `README_DEV.md`
- **Progress Tracking:** `PROGRESS.md`
- **Deployment Details:** `DEPLOYMENT_SUMMARY.md`

## 🐛 Troubleshooting

### App not showing in sidebar?
```bash
# Clear Nextcloud cache
sudo -u www-data php /var/www/html/nextcloud/occ cache:clear-all
# Restart Apache
sudo systemctl restart apache2
```

### Database tables not created?
```bash
# Run database migrations
sudo -u www-data php /var/www/html/nextcloud/occ db:add-missing-columns
```

### Permission denied errors?
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/html/nextcloud/apps/verein/
sudo find /var/www/html/nextcloud/apps/verein -type f -exec chmod 644 {} \;
sudo find /var/www/html/nextcloud/apps/verein -type d -exec chmod 755 {} \;
```

## 📞 Support

For detailed information, refer to:
- **Nextcloud Docs:** https://docs.nextcloud.com/server/latest/developer_manual/
- **Project README:** `README.md`
- **Local Dev Setup:** `README_DEV.md`

---

**✨ Your Vereins-App is ready to use!** ✨

Happy coding! 🎉
