package com.anticheat.data;

import com.anticheat.enums.HackType;
import org.bukkit.Location;

import java.util.UUID;

public class ViolationData {
    
    private final UUID playerUUID;
    private final HackType hackType;
    private final String reason;
    private final int violationLevel;
    private final long timestamp;
    private final Location location;
    private final String serverName;
    private final double confidence;
    
    public ViolationData(UUID playerUUID, HackType hackType, String reason, 
                        int violationLevel, long timestamp, Location location) {
        this(playerUUID, hackType, reason, violationLevel, timestamp, location, "unknown", 1.0);
    }
    
    public ViolationData(UUID playerUUID, HackType hackType, String reason, 
                        int violationLevel, long timestamp, Location location, 
                        String serverName, double confidence) {
        this.playerUUID = playerUUID;
        this.hackType = hackType;
        this.reason = reason;
        this.violationLevel = violationLevel;
        this.timestamp = timestamp;
        this.location = location != null ? location.clone() : null;
        this.serverName = serverName;
        this.confidence = confidence;
    }
    
    // Getters
    public UUID getPlayerUUID() { return playerUUID; }
    public HackType getHackType() { return hackType; }
    public String getReason() { return reason; }
    public int getViolationLevel() { return violationLevel; }
    public long getTimestamp() { return timestamp; }
    public Location getLocation() { return location; }
    public String getServerName() { return serverName; }
    public double getConfidence() { return confidence; }
    
    // Utility methods
    public boolean isRecent(long maxAge) {
        return System.currentTimeMillis() - timestamp < maxAge;
    }
    
    public String getFormattedReason() {
        return hackType.getDisplayName() + ": " + reason;
    }
    
    @Override
    public String toString() {
        return String.format("[%s] %s (VL: %d, Confidence: %.2f)", 
            hackType.getDisplayName(), reason, violationLevel, confidence);
    }
}