# Admin User Creation - Jigar Doshi

## Date
January 23, 2026

## User Details

| Field | Value |
|-------|-------|
| **User ID** | 22 |
| **Username** | jigar |
| **Password** | jigar |
| **Display Name** | Jigar Doshi |
| **Email** | jigar@padelpark.com |
| **Role** | Admin (roleID: 1) |
| **Status** | Active (1) |

## Credentials

```
Login URL: https://pp.paritoshajmera.com/xadmin/
Username: jigar
Password: jigar
```

## Technical Details

### Database Entry
- **Table**: `mx_x_admin_user`
- **Password Hash**: MD5 (`d4a23b0584a5d286fe25ed6b3afb20ef`)
- **Password Hashing Method**: MD5 (legacy)
- **Role Assigned**: Admin (full access)

### Permissions
As an Admin role user (roleID: 1), Jigar Doshi has:
- Full access to all admin modules
- Create, Read, Update, Delete permissions
- Access to all dashboards and reports
- User management capabilities

## Active Admin Users

After creation, the system now has 3 active admin users:

| User ID | Username | Display Name | Role |
|---------|----------|--------------|------|
| 1 | national | National Industries | Admin |
| 3 | admin | Paritosh Ajmera | Admin |
| 22 | jigar | Jigar Doshi | Admin |

## SQL Executed

```sql
INSERT INTO mx_x_admin_user
(roleID, userName, userPass, displayName, userEmail, status, unauthorized, isLeaveManager, techIlliterate)
VALUES
(1, 'jigar', 'd4a23b0584a5d286fe25ed6b3afb20ef', 'Jigar Doshi', 'jigar@padelpark.com', 1, 0, 0, 0);
```

## Security Notes

⚠️ **Important Security Recommendations:**

1. **Change Default Password**: User should change the password immediately after first login
2. **Weak Password**: The password "jigar" is weak and should be updated to a stronger password
3. **MD5 Hashing**: The system uses MD5 for password hashing, which is outdated. Consider upgrading to bcrypt or Argon2 in the future
4. **Email Verification**: The email `jigar@padelpark.com` should be verified
5. **Two-Factor Authentication**: Consider implementing 2FA for admin accounts

## User Settings

Default settings applied:
- `unauthorized`: 0 (no unauthorized access attempts)
- `isLeaveManager`: 0 (not a leave manager)
- `techIlliterate`: 0 (not marked as tech illiterate)
- `userTheme`: NULL (will use system default)
- `userFont`: NULL (will use system default)

## Access Verification

User can now login at:
- Admin Panel: https://pp.paritoshajmera.com/xadmin/

After login, the user will have access to:
- All dashboards (IPA, IPT, PnP, B2B, Inventory, etc.)
- All admin modules
- User management
- System settings
- Reports and analytics

---

**Created By**: Claude Sonnet 4.5
**Date**: 2026-01-23
**Status**: ✅ Active
