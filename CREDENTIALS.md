# UziCare System - Default User Credentials

## 🏥 Campus-Based Login System

The UziCare system uses a **single login page** where users log in with their email and password. The campus assignment is tied to each user account, so **no campus selection is needed during login**.

---

## 🔐 Administrator Accounts

### Super Administrator (All Campuses Access)
- **Email:** `superadmin@uzicare.com`
- **Password:** `SuperAdmin123!`
- **Campus:** Main Campus
- **Role:** Admin (Full system access across all campuses)

### Campus Administrators
Each campus has its own dedicated administrator:

| Campus | Email | Password | Employee ID |
|--------|-------|----------|-------------|
| **Main Campus** | `admin.main@uzicare.com` | `Admin123!` | MC-ADM-001 |
| **North Campus** | `admin.north@uzicare.com` | `Admin123!` | NC-ADM-001 |
| **South Campus** | `admin.south@uzicare.com` | `Admin123!` | SC-ADM-001 |
| **East Campus** | `admin.east@uzicare.com` | `Admin123!` | EC-ADM-001 |
| **West Campus** | `admin.west@uzicare.com` | `Admin123!` | WC-ADM-001 |
| **Downtown Campus** | `admin.downtown@uzicare.com` | `Admin123!` | DC-ADM-001 |
| **Satellite Clinic A** | `admin.clinica@uzicare.com` | `Admin123!` | CA-ADM-001 |
| **Satellite Clinic B** | `admin.clinicb@uzicare.com` | `Admin123!` | CB-ADM-001 |

---

## 📦 Inventory Managers

Each campus has an inventory manager who can distribute medicines between campuses:

| Campus | Email | Password | Employee ID |
|--------|-------|----------|-------------|
| **Main Campus** | `mc.inventory@uzicare.com` | `Inventory123!` | MC-INV-001 |
| **North Campus** | `nc.inventory@uzicare.com` | `Inventory123!` | NC-INV-001 |
| **South Campus** | `sc.inventory@uzicare.com` | `Inventory123!` | SC-INV-001 |
| **East Campus** | `ec.inventory@uzicare.com` | `Inventory123!` | EC-INV-001 |
| **West Campus** | `wc.inventory@uzicare.com` | `Inventory123!` | WC-INV-001 |
| **Downtown Campus** | `dc.inventory@uzicare.com` | `Inventory123!` | DC-INV-001 |
| **Satellite Clinic A** | `ca.inventory@uzicare.com` | `Inventory123!` | CA-INV-001 |
| **Satellite Clinic B** | `cb.inventory@uzicare.com` | `Inventory123!` | CB-INV-001 |

---

## 👩‍⚕️ Head Nurses

Each campus has a head nurse for medical record management:

| Campus | Email | Password | Employee ID |
|--------|-------|----------|-------------|
| **Main Campus** | `mc.nurse@uzicare.com` | `Nurse123!` | MC-NUR-001 |
| **North Campus** | `nc.nurse@uzicare.com` | `Nurse123!` | NC-NUR-001 |
| **South Campus** | `sc.nurse@uzicare.com` | `Nurse123!` | SC-NUR-001 |
| **East Campus** | `ec.nurse@uzicare.com` | `Nurse123!` | EC-NUR-001 |
| **West Campus** | `wc.nurse@uzicare.com` | `Nurse123!` | WC-NUR-001 |
| **Downtown Campus** | `dc.nurse@uzicare.com` | `Nurse123!` | DC-NUR-001 |
| **Satellite Clinic A** | `ca.nurse@uzicare.com` | `Nurse123!` | CA-NUR-001 |
| **Satellite Clinic B** | `cb.nurse@uzicare.com` | `Nurse123!` | CB-NUR-001 |

---

## 👔 Account Managers

Available only for main campuses (user management capabilities):

| Campus | Email | Password | Employee ID |
|--------|-------|----------|-------------|
| **Main Campus** | `mc.accounts@uzicare.com` | `Accounts123!` | MC-ACC-001 |
| **North Campus** | `nc.accounts@uzicare.com` | `Accounts123!` | NC-ACC-001 |
| **South Campus** | `sc.accounts@uzicare.com` | `Accounts123!` | SC-ACC-001 |

---

## 🔒 Security Notes

1. **⚠️ IMPORTANT:** Change all default passwords after first login!
2. All accounts are pre-verified and active
3. Campus assignment is automatic based on user account
4. No campus selection needed during login
5. Each user can only access data for their assigned campus (except Super Admin)

---

## 🏗️ System Architecture

- **Single Database Instance** with campus context
- **Role-Based Access Control** with campus filtering
- **Cross-Campus Medicine Distribution** tracking
- **Centralized Admin Oversight** via Super Admin account
- **Campus-Specific Data Organization** for all records

---

## 🚀 Quick Start Testing

1. **Login** at: `/login`
2. **Test Distribution:** Login as inventory manager → Create distribution to another campus
3. **Test Admin Oversight:** Login as Super Admin → View all campus activities
4. **Test User Management:** Login as account manager → Create new users for their campus

---

*Last Updated: September 20, 2025*