-- SQLite version of Vessel Data Logger database
-- Converted from MySQL schema

-- Create vessels table
CREATE TABLE IF NOT EXISTS vessels (
  VesselID INTEGER PRIMARY KEY AUTOINCREMENT,
  VesselName VARCHAR(100) NOT NULL UNIQUE,
  VesselType VARCHAR(50) DEFAULT 'Fishing Vessel',
  Owner VARCHAR(100),
  YearBuilt INTEGER,
  Length DECIMAL(6,2),
  CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  IsActive BOOLEAN DEFAULT 1,
  Notes TEXT,
  RPMMin INTEGER DEFAULT 650,
  RPMMax INTEGER DEFAULT 1750,
  TempMin INTEGER DEFAULT 20,
  TempMax INTEGER DEFAULT 400,
  PressureMin INTEGER DEFAULT 20,
  PressureMax INTEGER DEFAULT 400,
  GenMin INTEGER DEFAULT 20,
  GenMax INTEGER DEFAULT 400
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
  UserID INTEGER PRIMARY KEY AUTOINCREMENT,
  Username VARCHAR(50) UNIQUE NOT NULL,
  Email VARCHAR(100) UNIQUE NOT NULL,
  PasswordHash VARCHAR(255) NOT NULL,
  FirstName VARCHAR(50) NOT NULL,
  LastName VARCHAR(50) NOT NULL,
  IsAdmin BOOLEAN DEFAULT 0,
  IsActive BOOLEAN DEFAULT 1,
  CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  LastLogin TIMESTAMP NULL,
  ResetToken VARCHAR(100) NULL,
  ResetTokenExpiry TIMESTAMP NULL
);

-- Create gears table
CREATE TABLE IF NOT EXISTS gears (
  EntryID INTEGER PRIMARY KEY AUTOINCREMENT,
  VesselID INTEGER NOT NULL DEFAULT 1,
  Side TEXT NOT NULL CHECK (Side IN ('Port', 'Starboard', 'Center Main')),
  EntryDate DATE NOT NULL,
  OilPress INTEGER NOT NULL,
  Temp INTEGER NOT NULL,
  Notes TEXT,
  RecordedBy VARCHAR(100),
  Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  GearHrs INTEGER NOT NULL,
  FOREIGN KEY (VesselID) REFERENCES vessels(VesselID) ON DELETE CASCADE
);

-- Create generators table
CREATE TABLE IF NOT EXISTS generators (
  EntryID INTEGER PRIMARY KEY AUTOINCREMENT,
  VesselID INTEGER NOT NULL DEFAULT 1,
  Side TEXT NOT NULL CHECK (Side IN ('Port', 'Starboard', 'Center Main')),
  EntryDate DATE NOT NULL,
  FuelPress INTEGER NOT NULL,
  OilPress INTEGER NOT NULL,
  WaterTemp INTEGER NOT NULL,
  Notes TEXT,
  RecordedBy VARCHAR(100) NOT NULL,
  Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  GenHrs INTEGER NOT NULL,
  FOREIGN KEY (VesselID) REFERENCES vessels(VesselID) ON DELETE CASCADE
);

-- Create mainengines table
CREATE TABLE IF NOT EXISTS mainengines (
  EntryID INTEGER PRIMARY KEY AUTOINCREMENT,
  VesselID INTEGER NOT NULL DEFAULT 1,
  Side TEXT NOT NULL CHECK (Side IN ('Port', 'Starboard', 'Center Main')),
  EntryDate DATE NOT NULL,
  RPM INTEGER NOT NULL,
  OilPressure INTEGER NOT NULL,
  WaterTemp INTEGER NOT NULL,
  Notes TEXT,
  RecordedBy VARCHAR(100) NOT NULL,
  Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  MainHrs INTEGER NOT NULL,
  FuelPress INTEGER NOT NULL,
  OilTemp INTEGER NOT NULL,
  FOREIGN KEY (VesselID) REFERENCES vessels(VesselID) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_gears_vessel_date ON gears(VesselID, EntryDate);
CREATE INDEX IF NOT EXISTS idx_generators_vessel_date ON generators(VesselID, EntryDate);
CREATE INDEX IF NOT EXISTS idx_mainengines_vessel_date ON mainengines(VesselID, EntryDate);

-- Insert default admin user (password: admin123)
INSERT OR IGNORE INTO users (Username, Email, PasswordHash, FirstName, LastName, IsAdmin, IsActive) 
VALUES ('admin', 'admin@vessel.local', '$2y$10$ugTuP6EfjQqsiYjSyghi5e9Dxpv7jdzCMwQNPMPpmPM0UZDhuyKNK', 'Admin', 'User', 1, 1);

-- Insert sample vessel
INSERT OR IGNORE INTO vessels (VesselID, VesselName, VesselType, Owner, YearBuilt, Length, IsActive, Notes, RPMMin, RPMMax, TempMin, TempMax, PressureMin, PressureMax, GenMin, GenMax) 
VALUES (3, 'Rusty Zeller', 'Towboat', 'Florida Marine Transporters', 2021, 120.00, 1, 'Formerly Dave B Fate', 650, 1750, 20, 380, 20, 400, 20, 185);
