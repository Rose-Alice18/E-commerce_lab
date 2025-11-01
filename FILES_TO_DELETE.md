# Files Safe to Delete Before Production Deployment

## ⚠️ IMPORTANT: DO NOT DELETE THESE MANUALLY - READ CAREFULLY

This document lists files that can be safely deleted before deploying to production.
The `.gitignore` file will prevent most of these from being committed anyway.

---

## 🔴 MUST DELETE (Security Risk)

### 1. Development/Testing Files
These contain old testing code or duplicate functionality:

```
❌ classes/user_class.php          # Duplicate of customer_class.php (if not used)
❌ controllers/user_controller.php  # Duplicate of customer_controller.php (if not used)
❌ actions/check_email_action.php   # Old/unused action file (verify first)
```

**Action**: Check if `user_class.php` and `user_controller.php` are used anywhere. If not, delete them.

### 2. Documentation Files (Contains Passwords!)
```
❌ LOGIN_CREDENTIALS.md            # Contains test passwords - DELETE BEFORE DEPLOYMENT!
```

**Action**: **DELETE THIS FILE IMMEDIATELY** before pushing to Git or deploying to server.

### 3. Command Files (Personal Notes)
```
❌ commands/git.txt                        # Your personal git notes
❌ commands/fastapi.txt                    # FastAPI notes (not related to this project)
❌ commands/Ashesi_server_ecommerce_setup.txt  # Server setup notes
```

**Action**: Keep locally for reference, but don't deploy to server. Already in `.gitignore`.

---

## 🟡 OPTIONAL DELETE (Depends on Usage)

### 1. Database Files (After Import)
```
⚠️ db/dbforlab.sql              # Old database schema (superseded by pharmavault_db.sql)
⚠️ db/add_missing_columns.sql   # One-time migration script (if already applied)
⚠️ db/pharmavault_production.sql # Delete from server AFTER successful import
```

**Action**:
- Keep `db/pharmavault_production.sql` in Git for deployment
- Delete from server after importing to database
- Delete `db/dbforlab.sql` if you're sure you don't need it

### 2. Deployment Files (Delete from Server Only)
```
⚠️ deploy.bat                   # Windows deployment script (not needed on Linux server)
⚠️ DEPLOYMENT_GUIDE.md          # Deployment instructions (optional - can keep)
⚠️ FILES_TO_DELETE.md           # This file (optional - can keep)
```

**Action**: Keep in Git repository, but can delete from server after reading.

### 3. IDE/Development Files
```
⚠️ .claude/                     # Claude Code IDE settings (already in .gitignore)
⚠️ .vscode/                     # VS Code settings (if exists)
⚠️ .idea/                       # PHPStorm settings (if exists)
```

**Action**: Already in `.gitignore`, won't be deployed.

---

## 🟢 KEEP THESE (Required for Production)

### ✅ Core Application Files
```
✅ about.php
✅ index.php
✅ actions/         # All action files needed
✅ admin/           # All admin pages needed
✅ classes/         # All class files needed
✅ controllers/     # All controller files needed
✅ css/             # All stylesheets needed
✅ js/              # All JavaScript files needed
✅ login/           # Login system needed
✅ settings/        # Configuration files needed
✅ view/            # View templates needed
✅ uploads/         # User upload directory (but not the files inside)
```

### ✅ Configuration Files
```
✅ .gitignore       # Prevents sensitive files from being committed
✅ .htaccess        # Apache web server configuration (if exists)
```

### ✅ Database Files to Keep in Git
```
✅ db/pharmavault_production.sql   # Production database schema
```

---

## 📋 Detailed Deletion Checklist

### Before Committing to Git:

- [ ] **DELETE** `LOGIN_CREDENTIALS.md` (CRITICAL - contains passwords!)
- [ ] **VERIFY** `classes/user_class.php` is not used, then delete
- [ ] **VERIFY** `controllers/user_controller.php` is not used, then delete
- [ ] **VERIFY** `actions/check_email_action.php` is not used, then delete
- [ ] **OPTIONALLY DELETE** `db/dbforlab.sql` if not needed
- [ ] **KEEP** `commands/` folder (already in .gitignore, won't be committed)
- [ ] **KEEP** `.claude/` folder (already in .gitignore, won't be committed)

### After Deploying to Server:

- [ ] **DELETE** `deploy.bat` from server (Windows script, not needed on Linux)
- [ ] **DELETE** `db/pharmavault_production.sql` from server after importing
- [ ] **OPTIONALLY DELETE** `DEPLOYMENT_GUIDE.md` from server after reading
- [ ] **OPTIONALLY DELETE** `FILES_TO_DELETE.md` from server after reading

---

## 🔍 How to Verify if a File is Used

### Check if user_class.php is used:
```bash
cd c:\xampp\htdocs\register_sample
grep -r "user_class.php" . --exclude-dir=.git
grep -r "new User()" . --exclude-dir=.git
grep -r "User::" . --exclude-dir=.git
```

### Check if user_controller.php is used:
```bash
grep -r "user_controller.php" . --exclude-dir=.git
grep -r "_user_ctr" . --exclude-dir=.git
```

### Check if check_email_action.php is used:
```bash
grep -r "check_email_action.php" . --exclude-dir=.git
grep -r "check_email" js/ --exclude-dir=.git
```

---

## 📦 What Gets Deployed (Based on .gitignore)

### ✅ WILL be deployed (committed to Git):
- All PHP application files
- All CSS and JavaScript files
- Database schema files
- Configuration templates
- `.htaccess` files

### ❌ WILL NOT be deployed (ignored by Git):
- `settings/db_cred.php` (sensitive credentials)
- `uploads/*` (user-uploaded files)
- `logs/` (log files)
- `.claude/` (IDE settings)
- `.vscode/` or `.idea/` (IDE settings)
- `commands/` folder (personal notes)

---

## 🚀 Recommended Deletion Sequence

### Step 1: Delete Security Risks (DO THIS FIRST!)
```bash
cd c:\xampp\htdocs\register_sample
del LOGIN_CREDENTIALS.md
```

### Step 2: Verify and Delete Unused Files
```bash
# Only delete these if you confirmed they're not used:
# del classes\user_class.php
# del controllers\user_controller.php
# del actions\check_email_action.php
```

### Step 3: Delete Old Database Files (Optional)
```bash
# Only if you're 100% sure you don't need them:
# del db\dbforlab.sql
# del db\add_missing_columns.sql
```

### Step 4: Commit Clean Code to Git
```bash
git add .
git commit -m "Production ready: Removed sensitive files and unused code"
git push origin main
```

### Step 5: After Server Deployment
```bash
# SSH into server
ssh -C roseline.tsatsu@169.239.251.102 -p 422

# Navigate to project
cd ~/public_html/pharmavault

# Delete deployment files after reading
rm -f deploy.bat
rm -f db/pharmavault_production.sql  # After importing to database
rm -f DEPLOYMENT_GUIDE.md  # Optional
rm -f FILES_TO_DELETE.md   # Optional
```

---

## 📊 Summary Table

| File/Folder | Delete Locally? | Delete from Server? | Why? |
|-------------|----------------|---------------------|------|
| `LOGIN_CREDENTIALS.md` | ✅ YES | ✅ YES | Contains passwords |
| `commands/` | ❌ NO (keep local) | ✅ YES | Personal notes |
| `classes/user_class.php` | ⚠️ Maybe | ⚠️ Maybe | If unused |
| `controllers/user_controller.php` | ⚠️ Maybe | ⚠️ Maybe | If unused |
| `db/dbforlab.sql` | ⚠️ Maybe | ⚠️ Maybe | Old schema |
| `db/pharmavault_production.sql` | ❌ NO | ✅ YES (after import) | Needed for deployment |
| `deploy.bat` | ❌ NO | ✅ YES | Windows-only script |
| `DEPLOYMENT_GUIDE.md` | ❌ NO | ⚠️ Optional | Documentation |
| `.claude/` | ❌ NO | Auto-ignored | IDE settings |
| `uploads/*` (files inside) | ❌ NO | ❌ NO | User content |

---

## ⚠️ FINAL WARNING

**NEVER DELETE**:
- Any file in `actions/` unless you're 100% sure it's unused
- Any file in `classes/` unless you're 100% sure it's unused
- Any file in `controllers/` unless you're 100% sure it's unused
- `settings/db_cred.php` (keep on server, just don't commit to Git)
- `.htaccess` files
- `.gitignore` file

**When in doubt, KEEP the file!**

---

## 🔧 Quick Command to Delete Safe Files

Run this in Git Bash or PowerShell (review first!):

```bash
cd c:\xampp\htdocs\register_sample

# Delete critical security risk
rm -f LOGIN_CREDENTIALS.md

# Optionally delete old database file (only if sure!)
# rm -f db/dbforlab.sql
```

---

**Remember**: The `.gitignore` file will automatically prevent most sensitive files from being committed. Focus on deleting `LOGIN_CREDENTIALS.md` before anything else!
