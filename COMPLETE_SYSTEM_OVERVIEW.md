# Vessel Data Logger - Complete System Overview

## 🚢 Program Purpose
**Vessel Data Logger** is a standalone Windows desktop application for managing marine vessel engine room equipment data logging. It tracks engine parameters, generator data, and equipment status for multiple vessels.

## 🖥️ How The Program Runs

### **Standalone Desktop Application**
- **Technology**: Electron + PHP + SQLite
- **Runtime**: Self-contained - no external dependencies needed
- **Database**: Local SQLite files (no MySQL server required)
- **PHP**: Bundled portable PHP runtime included

### **Installation Process**
1. **Download**: Customer downloads `Vessel Data Logger-Portable.exe` (~88MB)
2. **Run**: Double-click the .exe file
3. **First Launch**: Installation wizard opens automatically
4. **Setup Steps**:
   - Enter license key (validates online)
   - Company information setup
   - Timezone configuration  
   - Admin user creation
   - Machine binding (anti-piracy)
5. **Complete**: App ready for vessel data logging

### **Daily Operation**
1. **Launch**: Click desktop shortcut or run .exe
2. **Interface**: Web-based UI in Electron window (looks like desktop app)
3. **PHP Server**: Starts automatically on localhost:8000
4. **Database**: SQLite files stored locally
5. **Offline**: Works completely offline after initial license validation

## 🏗️ Technical Architecture

### **Components**
```
Vessel Data Logger.exe
├── Electron Shell (Desktop Window)
├── PHP Runtime (Backend Logic)  
├── SQLite Database (Data Storage)
├── Web Interface (HTML/CSS/JS UI)
└── Machine ID System (License Protection)
```

### **File Structure (When Running)**
```
Installation Folder/
├── Vessel Data Logger.exe          # Main executable
├── php-portable/                   # PHP runtime & extensions
├── database/                       # SQLite database files
├── logs/                          # Application logs
├── *.php                          # Application files
├── *.css, *.js, *.html           # UI files
└── installation.complete          # Setup completion flag
```

## 💰 License & Distribution System

### **Purchase Flow**
1. **Customer**: Visits your landing page
2. **Purchase**: PayPal checkout process
3. **License Generation**: Automatic license key creation
4. **Email**: Download link with personalized token sent
5. **Download**: Secure, tracked download of installer

### **License Protection**
- **One License = One Installation** (unlimited vessels per install)
- **Machine Binding**: License tied to hardware fingerprint
- **Online Validation**: Required during installation
- **Anti-Sharing**: Cannot copy to other computers

### **Download System**
- **Secure URLs**: Personalized download tokens
- **Tracking**: All downloads logged with IP/user agent
- **Expiring Links**: Download tokens expire after 7 days
- **Multiple Downloads**: Up to 5 downloads per license

## 🌐 Server-Side Components (logicdock.org)

### **Website Files**
- `landing.html` - Sales page
- `download.html` - Download page
- `api/secure-download.php` - Handles secure downloads
- `api/generate-license.php` - Creates licenses after PayPal
- `api/validate-license.php` - Validates licenses during install

### **Database Tables**
- `licenses` - License keys, customer info, status
- `download_tokens` - Secure download links
- `downloads` - Download history/tracking

## 🔄 Complete User Journey

### **New Customer**
1. **Discovery**: Finds your website
2. **Purchase**: Buys license via PayPal ($X amount)
3. **Email**: Receives download link
4. **Download**: Gets personalized installer file
5. **Install**: Runs setup wizard with license key
6. **Use**: Starts logging vessel data

### **Daily Use**
1. **Launch**: Opens app from desktop
2. **Login**: Uses admin credentials (from setup)
3. **Select Vessel**: Choose which boat to log data for
4. **Data Entry**: Record engine/generator readings
5. **Reports**: View logs, graphs, historical data
6. **Export**: Generate reports for vessel records

### **Multi-Vessel Operations**
- **Unlimited Boats**: One license supports multiple vessels
- **Vessel Switching**: Easy switching between different boats
- **Separate Data**: Each vessel has its own data tracking
- **Centralized**: All managed from one installation

## 🔧 Key Features

### **Engine Room Logging**
- Main engines (Port/Starboard)
- Generators (multiple units)
- Equipment readings (temperatures, pressures, hours)
- Maintenance schedules and alerts

### **Data Management**
- Historical data storage
- Graphical reports and trends
- Export capabilities
- Backup/restore functions

### **Multi-User Support**
- Admin and regular user roles
- User management system
- Access control per functionality

## 🚨 Current Status

### **What Works** ✅
- Electron app architecture
- PHP runtime integration
- SQLite database system
- Installation wizard
- License validation system
- PayPal purchase integration
- Secure download system
- Machine binding protection

### **Recent Fixes** ✅
- PHP extensions loading properly
- Window visibility issues resolved
- Download links pointing to portable installer
- Background process management

### **Ready For** ✅
- Upload to IONOS server
- Customer testing
- Live sales deployment

## 📋 Deployment Checklist

### **Server Upload**
- [ ] Upload all website files to IONOS
- [ ] Update PayPal credentials for live environment
- [ ] Test download system
- [ ] Verify license validation works

### **Final Testing**
- [ ] Complete purchase flow test
- [ ] Download and install test
- [ ] Multi-vessel functionality test
- [ ] License protection verification

The system is designed to be completely self-contained and user-friendly, requiring minimal technical knowledge from your customers while providing robust vessel data management capabilities.
