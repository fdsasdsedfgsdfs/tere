package com.anticheat.data;

import org.bukkit.Location;
import org.bukkit.entity.Player;

import java.util.*;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.atomic.AtomicInteger;
import java.util.concurrent.atomic.AtomicLong;

public class PlayerData {
    
    private final UUID playerUUID;
    private final String playerName;
    private final long joinTime;
    
    // Movement data
    private Location lastLocation;
    private Location currentLocation;
    private double lastSpeed;
    private long lastMoveTime;
    private final List<Double> recentSpeeds = new ArrayList<>();
    private final AtomicInteger jumpCount = new AtomicInteger(0);
    private final AtomicLong groundTime = new AtomicLong(0);
    private final AtomicLong airTime = new AtomicLong(0);
    private boolean wasOnGround = true;
    private double fallDistance = 0.0;
    
    // Combat data
    private final AtomicInteger attackCount = new AtomicInteger(0);
    private final AtomicInteger hitCount = new AtomicInteger(0);
    private final AtomicInteger criticalHits = new AtomicInteger(0);
    private final List<Long> clickTimes = new ArrayList<>();
    private final Map<UUID, Integer> hitsByTarget = new ConcurrentHashMap<>();
    private long lastAttackTime = 0;
    private double lastReachDistance = 0.0;
    private float lastYaw = 0.0f;
    private float lastPitch = 0.0f;
    
    // Block interaction data
    private final AtomicInteger blocksPlaced = new AtomicInteger(0);
    private final AtomicInteger blocksBroken = new AtomicInteger(0);
    private final List<Long> blockBreakTimes = new ArrayList<>();
    private final List<Long> blockPlaceTimes = new ArrayList<>();
    private long lastBlockBreakTime = 0;
    private long lastBlockPlaceTime = 0;
    
    // Inventory data
    private final AtomicInteger inventoryActions = new AtomicInteger(0);
    private final List<Long> inventoryActionTimes = new ArrayList<>();
    private long lastInventoryAction = 0;
    
    // Violation data
    private final Map<String, Integer> violationLevels = new ConcurrentHashMap<>();
    private final List<ViolationData> recentViolations = new ArrayList<>();
    private long lastViolationTime = 0;
    
    // Machine Learning features
    private final Map<String, Double> mlFeatures = new ConcurrentHashMap<>();
    private double suspicionScore = 0.0;
    
    // Network data
    private long lastPacketTime = 0;
    private final AtomicInteger packetCount = new AtomicInteger(0);
    private final List<Long> packetTimes = new ArrayList<>();
    
    public PlayerData(UUID playerUUID, String playerName) {
        this.playerUUID = playerUUID;
        this.playerName = playerName;
        this.joinTime = System.currentTimeMillis();
        initializeMLFeatures();
    }
    
    private void initializeMLFeatures() {
        // Combat features
        mlFeatures.put("cps_average", 0.0);
        mlFeatures.put("cps_variance", 0.0);
        mlFeatures.put("hit_accuracy", 0.0);
        mlFeatures.put("head_snap_angle", 0.0);
        mlFeatures.put("reach_distance", 0.0);
        mlFeatures.put("aim_consistency", 0.0);
        mlFeatures.put("critical_hit_ratio", 0.0);
        mlFeatures.put("combo_length", 0.0);
        
        // Movement features
        mlFeatures.put("speed_variance", 0.0);
        mlFeatures.put("direction_changes", 0.0);
        mlFeatures.put("jump_pattern", 0.0);
        mlFeatures.put("ground_time_ratio", 0.0);
        mlFeatures.put("velocity_changes", 0.0);
        mlFeatures.put("fall_distance", 0.0);
        mlFeatures.put("liquid_movement", 0.0);
        
        // Block interaction features
        mlFeatures.put("block_break_speed", 0.0);
        mlFeatures.put("block_place_speed", 0.0);
        mlFeatures.put("mining_efficiency", 0.0);
        mlFeatures.put("scaffold_pattern", 0.0);
        mlFeatures.put("inventory_speed", 0.0);
    }
    
    // Movement methods
    public void updateLocation(Location newLocation, boolean onGround) {
        this.lastLocation = this.currentLocation;
        this.currentLocation = newLocation.clone();
        
        if (lastLocation != null) {
            double distance = lastLocation.distance(newLocation);
            long timeDiff = System.currentTimeMillis() - lastMoveTime;
            
            if (timeDiff > 0) {
                double speed = distance / (timeDiff / 1000.0);
                updateSpeed(speed);
            }
        }
        
        // Ground time tracking
        if (onGround && !wasOnGround) {
            airTime.addAndGet(System.currentTimeMillis() - lastMoveTime);
        } else if (!onGround && wasOnGround) {
            groundTime.addAndGet(System.currentTimeMillis() - lastMoveTime);
            jumpCount.incrementAndGet();
        }
        
        this.wasOnGround = onGround;
        this.lastMoveTime = System.currentTimeMillis();
        
        // Update ML features
        updateMovementFeatures();
    }
    
    private void updateSpeed(double speed) {
        this.lastSpeed = speed;
        recentSpeeds.add(speed);
        
        // Keep only recent speeds (last 20)
        if (recentSpeeds.size() > 20) {
            recentSpeeds.remove(0);
        }
    }
    
    // Combat methods
    public void updateAttack(Location targetLocation, float yaw, float pitch) {
        attackCount.incrementAndGet();
        lastAttackTime = System.currentTimeMillis();
        
        if (currentLocation != null && targetLocation != null) {
            lastReachDistance = currentLocation.distance(targetLocation);
        }
        
        // Update head movement
        if (lastYaw != 0 && lastPitch != 0) {
            float yawDiff = Math.abs(yaw - lastYaw);
            float pitchDiff = Math.abs(pitch - lastPitch);
            
            if (yawDiff > 180) yawDiff = 360 - yawDiff;
            
            mlFeatures.put("head_snap_angle", Math.max(yawDiff, pitchDiff));
        }
        
        this.lastYaw = yaw;
        this.lastPitch = pitch;
        
        updateCombatFeatures();
    }
    
    public void updateClick() {
        clickTimes.add(System.currentTimeMillis());
        
        // Keep only recent clicks (last 50)
        if (clickTimes.size() > 50) {
            clickTimes.remove(0);
        }
        
        updateCPSFeatures();
    }
    
    public void updateHit(UUID targetUUID, boolean isCritical) {
        hitCount.incrementAndGet();
        if (isCritical) {
            criticalHits.incrementAndGet();
        }
        
        hitsByTarget.merge(targetUUID, 1, Integer::sum);
        updateCombatFeatures();
    }
    
    // Block interaction methods
    public void updateBlockBreak(long time) {
        blocksBroken.incrementAndGet();
        lastBlockBreakTime = time;
        blockBreakTimes.add(time);
        
        if (blockBreakTimes.size() > 20) {
            blockBreakTimes.remove(0);
        }
        
        updateBlockFeatures();
    }
    
    public void updateBlockPlace(long time) {
        blocksPlaced.incrementAndGet();
        lastBlockPlaceTime = time;
        blockPlaceTimes.add(time);
        
        if (blockPlaceTimes.size() > 20) {
            blockPlaceTimes.remove(0);
        }
        
        updateBlockFeatures();
    }
    
    // ML Feature calculations
    private void updateCPSFeatures() {
        if (clickTimes.size() < 2) return;
        
        // Calculate CPS for last 5 seconds
        long cutoff = System.currentTimeMillis() - 5000;
        List<Long> recentClicks = clickTimes.stream()
            .filter(time -> time > cutoff)
            .toList();
        
        double cps = recentClicks.size() / 5.0;
        mlFeatures.put("cps_average", cps);
        
        // Calculate CPS variance
        if (recentClicks.size() > 3) {
            List<Double> intervals = new ArrayList<>();
            for (int i = 1; i < recentClicks.size(); i++) {
                intervals.add((double)(recentClicks.get(i) - recentClicks.get(i-1)));
            }
            
            double variance = calculateVariance(intervals);
            mlFeatures.put("cps_variance", variance);
        }
    }
    
    private void updateCombatFeatures() {
        // Hit accuracy
        if (attackCount.get() > 0) {
            double accuracy = (double) hitCount.get() / attackCount.get();
            mlFeatures.put("hit_accuracy", accuracy);
        }
        
        // Critical hit ratio
        if (hitCount.get() > 0) {
            double critRatio = (double) criticalHits.get() / hitCount.get();
            mlFeatures.put("critical_hit_ratio", critRatio);
        }
        
        // Reach distance
        mlFeatures.put("reach_distance", lastReachDistance);
        
        // Combo length (hits on same target)
        int maxCombo = hitsByTarget.values().stream().mapToInt(Integer::intValue).max().orElse(0);
        mlFeatures.put("combo_length", (double) maxCombo);
    }
    
    private void updateMovementFeatures() {
        // Speed variance
        if (recentSpeeds.size() > 3) {
            double variance = calculateVariance(recentSpeeds);
            mlFeatures.put("speed_variance", variance);
        }
        
        // Ground time ratio
        long totalTime = groundTime.get() + airTime.get();
        if (totalTime > 0) {
            double groundRatio = (double) groundTime.get() / totalTime;
            mlFeatures.put("ground_time_ratio", groundRatio);
        }
        
        // Jump pattern
        mlFeatures.put("jump_pattern", (double) jumpCount.get());
        
        // Fall distance
        mlFeatures.put("fall_distance", fallDistance);
    }
    
    private void updateBlockFeatures() {
        // Block break speed
        if (blockBreakTimes.size() > 1) {
            List<Long> intervals = new ArrayList<>();
            for (int i = 1; i < blockBreakTimes.size(); i++) {
                intervals.add(blockBreakTimes.get(i) - blockBreakTimes.get(i-1));
            }
            double avgInterval = intervals.stream().mapToLong(Long::longValue).average().orElse(0);
            mlFeatures.put("block_break_speed", 1000.0 / avgInterval); // Blocks per second
        }
        
        // Block place speed
        if (blockPlaceTimes.size() > 1) {
            List<Long> intervals = new ArrayList<>();
            for (int i = 1; i < blockPlaceTimes.size(); i++) {
                intervals.add(blockPlaceTimes.get(i) - blockPlaceTimes.get(i-1));
            }
            double avgInterval = intervals.stream().mapToLong(Long::longValue).average().orElse(0);
            mlFeatures.put("block_place_speed", 1000.0 / avgInterval); // Blocks per second
        }
    }
    
    private double calculateVariance(List<Double> values) {
        if (values.size() < 2) return 0.0;
        
        double mean = values.stream().mapToDouble(Double::doubleValue).average().orElse(0);
        double variance = values.stream()
            .mapToDouble(val -> Math.pow(val - mean, 2))
            .average()
            .orElse(0);
        
        return variance;
    }
    
    // Violation methods
    public void addViolation(ViolationData violation) {
        recentViolations.add(violation);
        lastViolationTime = System.currentTimeMillis();
        
        String hackType = violation.getHackType().name();
        violationLevels.merge(hackType, violation.getViolationLevel(), Integer::sum);
        
        // Update suspicion score
        updateSuspicionScore();
        
        // Clean old violations (older than 10 minutes)
        recentViolations.removeIf(v -> 
            System.currentTimeMillis() - v.getTimestamp() > 600000);
    }
    
    private void updateSuspicionScore() {
        double score = 0.0;
        long now = System.currentTimeMillis();
        
        // Recent violations weight more
        for (ViolationData violation : recentViolations) {
            long age = now - violation.getTimestamp();
            double weight = Math.max(0.1, 1.0 - (age / 600000.0)); // 10 minutes decay
            score += violation.getViolationLevel() * weight;
        }
        
        this.suspicionScore = Math.min(100.0, score);
    }
    
    // Getters
    public UUID getPlayerUUID() { return playerUUID; }
    public String getPlayerName() { return playerName; }
    public long getJoinTime() { return joinTime; }
    public Location getLastLocation() { return lastLocation; }
    public Location getCurrentLocation() { return currentLocation; }
    public double getLastSpeed() { return lastSpeed; }
    public List<Double> getRecentSpeeds() { return new ArrayList<>(recentSpeeds); }
    public int getJumpCount() { return jumpCount.get(); }
    public long getGroundTime() { return groundTime.get(); }
    public long getAirTime() { return airTime.get(); }
    public boolean isWasOnGround() { return wasOnGround; }
    public double getFallDistance() { return fallDistance; }
    public int getAttackCount() { return attackCount.get(); }
    public int getHitCount() { return hitCount.get(); }
    public int getCriticalHits() { return criticalHits.get(); }
    public List<Long> getClickTimes() { return new ArrayList<>(clickTimes); }
    public long getLastAttackTime() { return lastAttackTime; }
    public double getLastReachDistance() { return lastReachDistance; }
    public int getBlocksPlaced() { return blocksPlaced.get(); }
    public int getBlocksBroken() { return blocksBroken.get(); }
    public Map<String, Integer> getViolationLevels() { return new HashMap<>(violationLevels); }
    public List<ViolationData> getRecentViolations() { return new ArrayList<>(recentViolations); }
    public long getLastViolationTime() { return lastViolationTime; }
    public Map<String, Double> getMlFeatures() { return new HashMap<>(mlFeatures); }
    public double getSuspicionScore() { return suspicionScore; }
    
    // Setters
    public void setFallDistance(double fallDistance) { this.fallDistance = fallDistance; }
    public void setSuspicionScore(double suspicionScore) { this.suspicionScore = suspicionScore; }
    
    // Utility methods
    public double getCurrentCPS() {
        long cutoff = System.currentTimeMillis() - 1000; // Last 1 second
        return clickTimes.stream()
            .filter(time -> time > cutoff)
            .count();
    }
    
    public boolean hasRecentViolations(long timeWindow) {
        return lastViolationTime > System.currentTimeMillis() - timeWindow;
    }
    
    public int getTotalViolationLevel() {
        return violationLevels.values().stream().mapToInt(Integer::intValue).sum();
    }
}