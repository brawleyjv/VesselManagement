// Hardware Fingerprinting for License Protection
// This prevents license sharing between different installations

function generateHardwareFingerprint() {
    const os = require('os');
    const crypto = require('crypto');
    
    // Collect hardware identifiers
    const hwInfo = {
        hostname: os.hostname(),
        platform: os.platform(),
        arch: os.arch(),
        cpus: os.cpus().length,
        totalmem: os.totalmem(),
        networkInterfaces: Object.keys(os.networkInterfaces()),
        // Get MAC addresses for network interfaces
        macAddresses: []
    };
    
    // Extract MAC addresses
    const interfaces = os.networkInterfaces();
    for (const interfaceName in interfaces) {
        const networkInterface = interfaces[interfaceName];
        for (const addressInfo of networkInterface) {
            if (addressInfo.mac && addressInfo.mac !== '00:00:00:00:00:00') {
                hwInfo.macAddresses.push(addressInfo.mac);
            }
        }
    }
    
    // Sort MAC addresses for consistency
    hwInfo.macAddresses.sort();
    
    // Create a stable fingerprint
    const fingerprint = crypto
        .createHash('sha256')
        .update(JSON.stringify(hwInfo))
        .digest('hex')
        .substring(0, 32); // 32 character fingerprint
    
    return fingerprint;
}

function getInstallationId() {
    const fs = require('fs');
    const path = require('path');
    const crypto = require('crypto');
    
    const installIdFile = path.join(__dirname, 'installation.id');
    
    try {
        // Check if installation ID already exists
        if (fs.existsSync(installIdFile)) {
            return fs.readFileSync(installIdFile, 'utf8').trim();
        }
        
        // Generate new installation ID
        const installId = crypto.randomBytes(16).toString('hex');
        fs.writeFileSync(installIdFile, installId);
        return installId;
    } catch (error) {
        console.error('Error managing installation ID:', error);
        // Fallback to hardware fingerprint only
        return generateHardwareFingerprint();
    }
}

function getMachineIdentifier() {
    // Combine hardware fingerprint with installation ID
    const hwFingerprint = generateHardwareFingerprint();
    const installId = getInstallationId();
    
    const crypto = require('crypto');
    return crypto
        .createHash('sha256')
        .update(hwFingerprint + installId)
        .digest('hex')
        .substring(0, 16); // 16 character machine ID
}

module.exports = {
    generateHardwareFingerprint,
    getInstallationId,
    getMachineIdentifier
};
