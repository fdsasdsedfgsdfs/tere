package com.anticheat.managers;

import com.anticheat.AdvancedAntiCheat;
import com.anticheat.data.PlayerData;
import com.anticheat.data.ViolationData;
import com.anticheat.enums.HackType;
import com.anticheat.utils.MathUtils;
import com.anticheat.utils.LocationUtils;
import org.bukkit.Location;
import org.bukkit.entity.Entity;
import org.bukkit.entity.LivingEntity;
import org.bukkit.entity.Player;
import org.bukkit.event.entity.EntityDamageByEntityEvent;
import org.bukkit.util.Vector;

import java.util.*;
import java.util.concurrent.ConcurrentHashMap;

public class KillAuraDetector {
    
    private final AdvancedAntiCheat plugin;
    private final Map<UUID, KillAuraData> playerData;
    private final Map<UUID, List<AttackData>> recentAttacks;
    
    // Yapılandırma
    private static final double MAX_ATTACK_ANGLE = 90.0; // Derece
    private static final double SUSPICIOUS_ANGLE_THRESHOLD = 45.0;
    private static final int MAX_ENTITIES_PER_SECOND = 3;
    private static final double HEAD_SNAP_THRESHOLD = 30.0; // Derece/tick
    private static final long ATTACK_WINDOW_MS = 1000; // 1 saniye
    private static final int MIN_ATTACKS_FOR_DETECTION = 5;
    
    public KillAuraDetector(AdvancedAntiCheat plugin) {
        this.plugin = plugin;
        this.playerData = new ConcurrentHashMap<>();
        this.recentAttacks = new ConcurrentHashMap<>();
    }
    
    public void checkAttack(Player attacker, Entity target, EntityDamageByEntityEvent event) {
        if (attacker.hasPermission("anticheat.bypass")) return;
        
        PlayerData pData = plugin.getPlayerDataManager().getPlayerData(attacker.getUniqueId());
        if (pData == null) return;
        
        KillAuraData kData = playerData.computeIfAbsent(attacker.getUniqueId(), 
            k -> new KillAuraData());
        
        // Saldırı verilerini kaydet
        AttackData attackData = new AttackData(
            System.currentTimeMillis(),
            attacker.getLocation().clone(),
            target.getLocation().clone(),
            attacker.getLocation().getYaw(),
            attacker.getLocation().getPitch()
        );
        
        // Son saldırıları güncelle
        List<AttackData> attacks = recentAttacks.computeIfAbsent(attacker.getUniqueId(), 
            k -> new ArrayList<>());
        attacks.add(attackData);
        
        // Eski saldırıları temizle
        attacks.removeIf(attack -> 
            System.currentTimeMillis() - attack.timestamp > ATTACK_WINDOW_MS);
        
        // Testleri çalıştır
        checkAngleConsistency(attacker, kData, attackData, attacks);
        checkMultipleTargets(attacker, kData, attacks);
        checkHeadSnapping(attacker, kData, attackData);
        checkAttackPattern(attacker, kData, attacks);
        checkReachDistance(attacker, kData, attackData);
        checkAimAccuracy(attacker, kData, attackData);
        
        // ML analizi
        performMLAnalysis(attacker, kData, pData);
        
        // Verileri güncelle
        kData.updateData(attackData);
    }
    
    private void checkAngleConsistency(Player player, KillAuraData kData, AttackData attack, List<AttackData> attacks) {
        if (attacks.size() < 3) return;
        
        // Son 3 saldırının açı tutarlılığını kontrol et
        List<Double> angles = new ArrayList<>();
        for (int i = attacks.size() - 3; i < attacks.size(); i++) {
            AttackData attackData = attacks.get(i);
            double angle = calculateAttackAngle(attackData.attackerLocation, attackData.targetLocation);
            angles.add(angle);
        }
        
        // Açı varyansını hesapla
        double variance = MathUtils.calculateVariance(angles);
        
        if (variance < 2.0) { // Çok tutarlı açılar
            flagViolation(player, HackType.KILL_AURA, 
                "Tutarlı saldırı açıları (var: %.2f)".formatted(variance), 
                5 + (int)(10 * (2.0 - variance)));
        }
    }
    
    private void checkMultipleTargets(Player player, KillAuraData kData, List<AttackData> attacks) {
        if (attacks.size() < MIN_ATTACKS_FOR_DETECTION) return;
        
        // Son saniyedeki farklı hedef sayısını hesapla
        Set<Location> uniqueTargets = new HashSet<>();
        long currentTime = System.currentTimeMillis();
        
        for (AttackData attack : attacks) {
            if (currentTime - attack.timestamp <= 1000) {
                uniqueTargets.add(attack.targetLocation);
            }
        }
        
        if (uniqueTargets.size() > MAX_ENTITIES_PER_SECOND) {
            flagViolation(player, HackType.KILL_AURA, 
                "Çok fazla hedef (%d entity/s)".formatted(uniqueTargets.size()),
                10 * uniqueTargets.size());
        }
    }
    
    private void checkHeadSnapping(Player player, KillAuraData kData, AttackData attack) {
        if (kData.lastAttack == null) return;
        
        // Yaw değişimini hesapla
        double yawDiff = Math.abs(attack.yaw - kData.lastAttack.yaw);
        if (yawDiff > 180) yawDiff = 360 - yawDiff;
        
        // Pitch değişimini hesapla
        double pitchDiff = Math.abs(attack.pitch - kData.lastAttack.pitch);
        
        // Zaman farkını hesapla (tick olarak)
        long timeDiff = attack.timestamp - kData.lastAttack.timestamp;
        double tickDiff = timeDiff / 50.0; // MS to ticks
        
        if (tickDiff > 0) {
            double yawSpeed = yawDiff / tickDiff;
            double pitchSpeed = pitchDiff / tickDiff;
            
            // Ani kafa hareketleri tespit et
            if (yawSpeed > HEAD_SNAP_THRESHOLD || pitchSpeed > HEAD_SNAP_THRESHOLD) {
                flagViolation(player, HackType.KILL_AURA,
                    "Ani kafa hareketi (yaw: %.1f°/tick, pitch: %.1f°/tick)".formatted(yawSpeed, pitchSpeed),
                    (int)(yawSpeed + pitchSpeed));
            }
        }
    }
    
    private void checkAttackPattern(Player player, KillAuraData kData, List<AttackData> attacks) {
        if (attacks.size() < 5) return;
        
        // Saldırı aralıklarını analiz et
        List<Long> intervals = new ArrayList<>();
        for (int i = 1; i < attacks.size(); i++) {
            long interval = attacks.get(i).timestamp - attacks.get(i-1).timestamp;
            intervals.add(interval);
        }
        
        // İstatistiksel analiz
        double mean = intervals.stream().mapToLong(Long::longValue).average().orElse(0);
        double variance = MathUtils.calculateVariance(intervals.stream().mapToDouble(Long::doubleValue).boxed().toList());
        
        // Çok düzenli aralıklar (bot benzeri)
        if (variance < 100 && mean < 200) { // 100ms variance, 200ms ortalama
            flagViolation(player, HackType.KILL_AURA,
                "Düzenli saldırı deseni (var: %.0f, avg: %.0fms)".formatted(variance, mean),
                15);
        }
    }
    
    private void checkReachDistance(Player player, KillAuraData kData, AttackData attack) {
        double distance = attack.attackerLocation.distance(attack.targetLocation);
        
        // 1.19'da max reach 3.0 blok (survival)
        double maxReach = player.getGameMode().name().equals("CREATIVE") ? 5.0 : 3.0;
        
        if (distance > maxReach + 0.5) { // 0.5 blok tolerans
            flagViolation(player, HackType.REACH,
                "Uzun erişim mesafesi (%.2f blok)".formatted(distance),
                (int)((distance - maxReach) * 10));
        }
    }
    
    private void checkAimAccuracy(Player player, KillAuraData kData, AttackData attack) {
        // Oyuncunun baktığı yön ile hedefin yönü arasındaki açıyı hesapla
        Vector playerDirection = attack.attackerLocation.getDirection();
        Vector targetDirection = attack.targetLocation.toVector()
            .subtract(attack.attackerLocation.toVector()).normalize();
        
        double angle = Math.toDegrees(playerDirection.angle(targetDirection));
        
        if (angle > MAX_ATTACK_ANGLE) {
            flagViolation(player, HackType.KILL_AURA,
                "Görüş açısı dışında saldırı (%.1f°)".formatted(angle),
                (int)(angle / 10));
        } else if (angle < 5.0 && kData.perfectHits.incrementAndGet() > 10) {
            // Çok mükemmel nişan alma
            flagViolation(player, HackType.AIM_ASSIST,
                "Çok mükemmel nişan alma (%d perfect hit)".formatted(kData.perfectHits.get()),
                5);
        }
    }
    
    private void performMLAnalysis(Player player, KillAuraData kData, PlayerData pData) {
        if (plugin.getMlEngine() == null || !plugin.getMlEngine().isModelReady()) return;
        
        // ML analizi için veri güncelle
        plugin.getMlEngine().updatePlayerProfile(player.getUniqueId(), pData);
        
        // Analiz sonucunu al
        var result = plugin.getMlEngine().analyzePlayer(player.getUniqueId(), pData);
        
        if (result.isHacking() && result.getConfidence() > 0.8) {
            flagViolation(player, HackType.KILL_AURA,
                "ML Tespiti: " + result.getReason(),
                (int)(result.getConfidence() * 20));
        }
    }
    
    private double calculateAttackAngle(Location attacker, Location target) {
        Vector direction = target.toVector().subtract(attacker.toVector()).normalize();
        Vector forward = attacker.getDirection();
        return Math.toDegrees(forward.angle(direction));
    }
    
    private void flagViolation(Player player, HackType hackType, String reason, int addedVL) {
        ViolationData violation = new ViolationData(
            player.getUniqueId(),
            hackType,
            reason,
            addedVL,
            System.currentTimeMillis(),
            player.getLocation().clone()
        );
        
        plugin.getViolationManager().addViolation(violation);
    }
    
    public void cleanupPlayer(UUID playerUUID) {
        playerData.remove(playerUUID);
        recentAttacks.remove(playerUUID);
    }
    
    // Veri sınıfları
    private static class KillAuraData {
        AttackData lastAttack;
        final java.util.concurrent.atomic.AtomicInteger perfectHits = new java.util.concurrent.atomic.AtomicInteger(0);
        final List<Double> recentAngles = new ArrayList<>();
        final List<Long> attackIntervals = new ArrayList<>();
        
        void updateData(AttackData attack) {
            this.lastAttack = attack;
            
            // Sliding window güncelle
            if (recentAngles.size() > 10) {
                recentAngles.remove(0);
            }
            if (attackIntervals.size() > 10) {
                attackIntervals.remove(0);
            }
        }
    }
    
    private static class AttackData {
        final long timestamp;
        final Location attackerLocation;
        final Location targetLocation;
        final float yaw;
        final float pitch;
        
        AttackData(long timestamp, Location attackerLocation, Location targetLocation, float yaw, float pitch) {
            this.timestamp = timestamp;
            this.attackerLocation = attackerLocation;
            this.targetLocation = targetLocation;
            this.yaw = yaw;
            this.pitch = pitch;
        }
    }
    
    // Getter'lar ve utility metodlar
    public Map<UUID, KillAuraData> getPlayerData() {
        return playerData;
    }
    
    public boolean hasRecentViolations(UUID playerUUID) {
        return recentAttacks.containsKey(playerUUID) && 
               !recentAttacks.get(playerUUID).isEmpty();
    }
}