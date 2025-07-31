# License Protection System - Anti-Piracy Implementation

## 🔒 **How License Protection Works**

### **Each Installation = One License**
- Each company can have **unlimited boats** per installation
- Each **computer/system** needs its own license registration
- **No sharing** licenses between different installations

### **Hardware Fingerprinting**
The system creates a unique "machine ID" for each installation:

```
Machine ID = Hardware Hash + Installation ID
```

**Hardware Components Used:**
- Computer hostname
- Network MAC addresses  
- CPU architecture
- Memory size
- Disk serial numbers (Windows)
- Operating system info

**Installation ID:**
- Generated once during first run
- Stored in `installation.id` file
- Makes each installation unique even on identical hardware

## 🛡️ **Protection Mechanisms**

### **1. License Binding Process**
```
First License Use:
Customer enters license key → System validates with server → 
Machine ID recorded → License bound to this installation

Subsequent Uses:
System checks: License Key + Email + Machine ID → 
If match: Allow access | If different machine: Block access
```

### **2. Multi-Layer Validation**
```
Online Mode:
- Real-time validation with server
- Machine ID verified against database
- License status checked (active/revoked)

Offline Mode:
- Basic license format validation
- Demo licenses always work
- Limited functionality
```

### **3. Database Schema**
```sql
licenses table:
- license_key (VDL-XXXX-XXXX-XXXX)
- customer_email
- machine_id (16-char hash) ← NEW
- status (active/revoked)
- activated_date

license_activations table: ← NEW AUDIT TRAIL
- license_key
- machine_id  
- activation_date
- ip_address
- user_agent
```

## 🚫 **What Prevents License Sharing**

### **Scenario 1: Company tries to share one license**
```
Boat 1 Computer: License activated → Machine ID: ABC123
Boat 2 Computer: Same license → Machine ID: DEF456
Result: Boat 2 gets "License already activated elsewhere" error
```

### **Scenario 2: Copying installation files**
```
Original Install: License works with Machine ID ABC123
Copied to new PC: Hardware different → New Machine ID XYZ789
Result: License validation fails, asks for new license
```

### **Scenario 3: Virtual machines or containers**
```
Each VM/container has different:
- Network interfaces
- Hardware fingerprint
- Installation ID
Result: Each needs separate license
```

## ✅ **Legitimate Use Cases Supported**

### **Hardware Replacement**
```
Problem: Boat gets new computer
Solution: Contact support to transfer license
Process: Admin can reset machine_id in database
```

### **Software Reinstall**
```
Problem: Windows reinstalled on same computer
Solution: Same hardware = same Machine ID = license works
Note: Installation ID regenerated but hardware fingerprint matches
```

### **Multiple Boats, Same Company**
```
Scenario: 10 boats, 1 computer each
Requirement: 10 separate licenses
Benefit: Each boat fully independent, unlimited vessel profiles per boat
```

## 🔧 **Implementation Files**

### **Frontend (Electron)**
- `main.js` - Hardware fingerprinting
- `hardware-fingerprint.js` - Machine ID generation
- `machine.id` - Generated machine ID file

### **Backend (PHP)**
- `machine_id.php` - PHP machine ID functions
- `api/validate-license.php` - License validation with machine binding
- `install.php` - Installation with machine registration

### **Database**
- `add_machine_binding.sql` - Schema updates
- `update_database_machine_binding.php` - Apply updates

## 🎯 **Business Model Impact**

### **Revenue Protection**
- **Before:** 1 license could run on 10 boats
- **After:** 10 boats require 10 licenses
- **Result:** 10x revenue potential per customer

### **Customer Experience**
- **Legitimate users:** No impact, works normally
- **Unlimited boats** per licensed computer
- **Easy installation** process
- **Offline capability** for demo licenses

### **Support Benefits**
- **Audit trail** of license activations
- **Clear error messages** for sharing attempts
- **Transfer capability** for hardware upgrades
- **Remote license management**

## 📊 **Error Messages**

### **License Already Activated**
```json
{
    "error": "License already activated on another installation",
    "message": "This license is already registered to a different computer. Each installation requires its own license key.",
    "support": "Contact support for license transfer if you replaced hardware"
}
```

### **Invalid License**
```json
{
    "error": "Invalid license key or customer email",
    "message": "Please check your license information and try again"
}
```

## 🔄 **Admin Functions Needed**

### **License Management Portal** (Future Enhancement)
```php
// Reset machine binding (hardware replacement)
resetMachineBinding($license_key, $new_machine_id);

// View license usage
getLicenseActivations($license_key);

// Revoke license (refunds/disputes)
revokeLicense($license_key);

// Generate bulk licenses (enterprise customers)
generateBulkLicenses($customer_email, $quantity);
```

## ⚠️ **Important Notes**

### **Demo Licenses Always Work**
```php
$demo_licenses = [
    'VDL-DEMO-TEST-2025',
    'VDL-EVAL-TRIAL-001', 
    'VDL-TEST-OFFLINE-123'
];
// These bypass machine binding for testing
```

### **Offline Mode Limitations**
- Only demo licenses work offline
- No machine binding enforcement offline
- Encourages online activation

### **Privacy Considerations**
- Machine ID is anonymous hash
- No personal data in fingerprint
- Only hardware characteristics used
- Installation ID is random UUID

---

**This system ensures each installation requires its own license while maintaining ease of use for legitimate customers.**
