package com.anticheat.ml;

import com.anticheat.data.PlayerData;

import java.util.UUID;

public class PlayerBehaviorProfile {
    
    private final UUID playerUUID;
    private long lastUpdate;
    
    // Combat patterns
    private double cpsAverage = 0.0;
    private double cpsVariance = 0.0;
    private double hitAccuracy = 0.0;
    private double headSnapAngle = 0.0;
    private double reachDistance = 0.0;
    private double aimConsistency = 0.0;
    private double criticalHitRatio = 0.0;
    private double comboLength = 0.0;
    
    // Movement patterns
    private double speedVariance = 0.0;
    private double directionChanges = 0.0;
    private double jumpPattern = 0.0;
    private double groundTimeRatio = 0.0;
    private double velocityChanges = 0.0;
    private double fallDistance = 0.0;
    private double liquidMovement = 0.0;
    
    // Block interaction patterns
    private double blockBreakSpeed = 0.0;
    private double blockPlaceSpeed = 0.0;
    private double miningEfficiency = 0.0;
    private double scaffoldPattern = 0.0;
    private double inventorySpeed = 0.0;
    
    public PlayerBehaviorProfile(UUID playerUUID) {
        this.playerUUID = playerUUID;
        this.lastUpdate = System.currentTimeMillis();
    }
    
    public void updateProfile(PlayerData playerData) {
        var features = playerData.getMlFeatures();
        
        // Combat
        this.cpsAverage = features.getOrDefault("cps_average", 0.0);
        this.cpsVariance = features.getOrDefault("cps_variance", 0.0);
        this.hitAccuracy = features.getOrDefault("hit_accuracy", 0.0);
        this.headSnapAngle = features.getOrDefault("head_snap_angle", 0.0);
        this.reachDistance = features.getOrDefault("reach_distance", 0.0);
        this.aimConsistency = features.getOrDefault("aim_consistency", 0.0);
        this.criticalHitRatio = features.getOrDefault("critical_hit_ratio", 0.0);
        this.comboLength = features.getOrDefault("combo_length", 0.0);
        
        // Movement
        this.speedVariance = features.getOrDefault("speed_variance", 0.0);
        this.directionChanges = features.getOrDefault("direction_changes", 0.0);
        this.jumpPattern = features.getOrDefault("jump_pattern", 0.0);
        this.groundTimeRatio = features.getOrDefault("ground_time_ratio", 0.0);
        this.velocityChanges = features.getOrDefault("velocity_changes", 0.0);
        this.fallDistance = features.getOrDefault("fall_distance", 0.0);
        this.liquidMovement = features.getOrDefault("liquid_movement", 0.0);
        
        // Blocks
        this.blockBreakSpeed = features.getOrDefault("block_break_speed", 0.0);
        this.blockPlaceSpeed = features.getOrDefault("block_place_speed", 0.0);
        this.miningEfficiency = features.getOrDefault("mining_efficiency", 0.0);
        this.scaffoldPattern = features.getOrDefault("scaffold_pattern", 0.0);
        this.inventorySpeed = features.getOrDefault("inventory_speed", 0.0);
        
        this.lastUpdate = System.currentTimeMillis();
    }
    
    // Getters
    public UUID getPlayerUUID() { return playerUUID; }
    public long getLastUpdate() { return lastUpdate; }
    public double getCpsAverage() { return cpsAverage; }
    public double getCpsVariance() { return cpsVariance; }
    public double getHitAccuracy() { return hitAccuracy; }
    public double getHeadSnapAngle() { return headSnapAngle; }
    public double getReachDistance() { return reachDistance; }
    public double getAimConsistency() { return aimConsistency; }
    public double getCriticalHitRatio() { return criticalHitRatio; }
    public double getComboLength() { return comboLength; }
    public double getSpeedVariance() { return speedVariance; }
    public double getDirectionChanges() { return directionChanges; }
    public double getJumpPattern() { return jumpPattern; }
    public double getGroundTimeRatio() { return groundTimeRatio; }
    public double getVelocityChanges() { return velocityChanges; }
    public double getFallDistance() { return fallDistance; }
    public double getLiquidMovement() { return liquidMovement; }
    public double getBlockBreakSpeed() { return blockBreakSpeed; }
    public double getBlockPlaceSpeed() { return blockPlaceSpeed; }
    public double getMiningEfficiency() { return miningEfficiency; }
    public double getScaffoldPattern() { return scaffoldPattern; }
    public double getInventorySpeed() { return inventorySpeed; }
}