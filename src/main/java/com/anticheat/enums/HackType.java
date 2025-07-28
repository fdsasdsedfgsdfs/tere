package com.anticheat.enums;

public enum HackType {
    
    // Combat Hacks
    KILL_AURA("KillAura", "Yakındaki düşmanlara otomatik saldırı", 15, 50),
    AIMBOT("Aimbot", "Ok/yay otomatik nişan alma", 20, 60),
    TRIGGER_BOT("TriggerBot", "Hedef geldiğinde otomatik saldırı", 10, 40),
    CRITICALS("Criticals", "Her saldırıyı kritik yapma", 8, 30),
    AUTO_CLICKER("AutoClicker", "Otomatik tıklama/yüksek CPS", 5, 25),
    REACH("Reach", "Normal menzilin dışına vurma", 15, 45),
    VELOCITY("Velocity", "Knockback etkisini azaltma", 10, 35),
    ANTI_KNOCKBACK("AntiKnockback", "Hiç knockback almama", 12, 40),
    HITBOXES("HitBoxes", "Hitbox'ları büyütme", 8, 30),
    AIM_ASSIST("AimAssist", "Nişan alma yardımı", 6, 25),
    AUTO_TOTEM("AutoTotem", "Totem otomatik kullanma", 5, 20),
    SHIELD_BREAKER("ShieldBreaker", "Kalkan hızlı kırma", 7, 25),
    
    // Movement Hacks
    FLY("Fly", "Havada uçma", 25, 80),
    GLIDE("Glide", "Yavaş süzülme", 15, 50),
    SPEED("Speed", "Hızlı hareket", 12, 40),
    BUNNY_HOP("BunnyHop", "Sürekli zıplama ile hızlanma", 10, 35),
    JESUS("Jesus", "Su üstünde yürüme", 20, 60),
    STEP("Step", "Yüksek bloklara tırmanma", 8, 30),
    SPIDER("Spider", "Duvarlara tırmanma", 15, 45),
    NO_FALL("NoFall", "Düşme hasarını engelleme", 10, 35),
    HIGH_JUMP("HighJump", "Yüksek zıplama", 12, 40),
    LONG_JUMP("LongJump", "Uzun zıplama", 12, 40),
    FAST_LADDER("FastLadder", "Hızlı merdiven çıkma", 5, 20),
    PHASE("Phase", "Bloklardan geçme", 30, 100),
    NO_CLIP("NoClip", "Collision engelleme", 25, 90),
    TELEPORT("Teleport", "Işınlanma", 35, 120),
    LAG_BACK("LagBack", "Lag exploit kullanma", 20, 70),
    
    // Visual Hacks
    XRAY("Xray", "Cevher/blok görme", 20, 65),
    ESP("ESP", "Entity görme", 15, 50),
    TRACERS("Tracers", "Entity'lere çizgi çekme", 10, 35),
    NAME_TAGS("NameTags", "Uzaktan isim görme", 8, 25),
    FULL_BRIGHT("FullBright", "Karanlıkta görme", 5, 15),
    CHEST_ESP("ChestESP", "Sandık görme", 12, 40),
    WALL_HACK("WallHack", "Duvar arkası görme", 18, 55),
    ANTI_BLIND("AntiBlind", "Körlük iptal etme", 6, 20),
    NO_WEATHER("NoWeather", "Hava durumu iptal", 3, 10),
    BREADCRUMBS("Breadcrumbs", "Yol işaretleme", 4, 15),
    FREECAM("Freecam", "Serbest kamera", 15, 50),
    
    // Automation Hacks
    AUTO_BUILD("AutoBuild", "Otomatik yapı inşa", 10, 35),
    AUTO_MINE("AutoMine", "Otomatik madencilik", 12, 40),
    AUTO_EAT("AutoEat", "Otomatik yemek yeme", 5, 20),
    AUTO_ARMOR("AutoArmor", "Otomatik zırh giyme", 6, 25),
    AUTO_FISH("AutoFish", "Otomatik balık tutma", 8, 30),
    SCAFFOLD("Scaffold", "Otomatik blok koyma", 15, 45),
    CHEST_STEALER("ChestStealer", "Hızlı sandık boşaltma", 10, 35),
    FAST_BREAK("FastBreak", "Hızlı blok kırma", 12, 40),
    FAST_PLACE("FastPlace", "Hızlı blok yerleştirme", 8, 30),
    INVENTORY_MOVE("InventoryMove", "Envanter açıkken hareket", 5, 20),
    ANTI_AFK("AntiAFK", "AFK engelleme", 3, 15),
    
    // Misc Hacks
    DERP("Derp", "Kafa sallamak", 1, 5),
    TWERK("Twerk", "Dans etme", 1, 5),
    BLINK("Blink", "Gecikmeli hareket", 15, 50),
    TIMER("Timer", "Oyun hızını değiştirme", 20, 65),
    PACKET_FLY("PacketFly", "Packet tabanlı uçma", 30, 100),
    DISABLER("Disabler", "AntiCheat devre dışı bırakma", 50, 200),
    
    // Machine Learning Detection
    ML_DETECTION("ML Detection", "Makine öğrenmesi tespiti", 25, 75),
    BEHAVIOR_ANALYSIS("Behavior Analysis", "Davranış analizi", 20, 60);
    
    private final String displayName;
    private final String description;
    private final int baseViolationLevel;
    private final int maxPunishmentLevel;
    
    HackType(String displayName, String description, int baseViolationLevel, int maxPunishmentLevel) {
        this.displayName = displayName;
        this.description = description;
        this.baseViolationLevel = baseViolationLevel;
        this.maxPunishmentLevel = maxPunishmentLevel;
    }
    
    public String getDisplayName() {
        return displayName;
    }
    
    public String getDescription() {
        return description;
    }
    
    public int getBaseViolationLevel() {
        return baseViolationLevel;
    }
    
    public int getMaxPunishmentLevel() {
        return maxPunishmentLevel;
    }
    
    public HackCategory getCategory() {
        return switch (this) {
            case KILL_AURA, AIMBOT, TRIGGER_BOT, CRITICALS, AUTO_CLICKER, REACH, 
                 VELOCITY, ANTI_KNOCKBACK, HITBOXES, AIM_ASSIST, AUTO_TOTEM, SHIELD_BREAKER 
                 -> HackCategory.COMBAT;
                 
            case FLY, GLIDE, SPEED, BUNNY_HOP, JESUS, STEP, SPIDER, NO_FALL, 
                 HIGH_JUMP, LONG_JUMP, FAST_LADDER, PHASE, NO_CLIP, TELEPORT, LAG_BACK 
                 -> HackCategory.MOVEMENT;
                 
            case XRAY, ESP, TRACERS, NAME_TAGS, FULL_BRIGHT, CHEST_ESP, WALL_HACK, 
                 ANTI_BLIND, NO_WEATHER, BREADCRUMBS, FREECAM 
                 -> HackCategory.VISUAL;
                 
            case AUTO_BUILD, AUTO_MINE, AUTO_EAT, AUTO_ARMOR, AUTO_FISH, SCAFFOLD, 
                 CHEST_STEALER, FAST_BREAK, FAST_PLACE, INVENTORY_MOVE, ANTI_AFK 
                 -> HackCategory.AUTOMATION;
                 
            case ML_DETECTION, BEHAVIOR_ANALYSIS 
                 -> HackCategory.MACHINE_LEARNING;
                 
            default -> HackCategory.MISC;
        };
    }
    
    public boolean isSevere() {
        return baseViolationLevel >= 20;
    }
    
    public boolean requiresImmediateBan() {
        return this == DISABLER || this == PACKET_FLY || baseViolationLevel >= 30;
    }
    
    public enum HackCategory {
        COMBAT("Savaş", "§c"),
        MOVEMENT("Hareket", "§9"),
        VISUAL("Görsel", "§e"),
        AUTOMATION("Otomasyon", "§d"),
        MACHINE_LEARNING("Makine Öğrenmesi", "§a"),
        MISC("Diğer", "§7");
        
        private final String displayName;
        private final String colorCode;
        
        HackCategory(String displayName, String colorCode) {
            this.displayName = displayName;
            this.colorCode = colorCode;
        }
        
        public String getDisplayName() {
            return displayName;
        }
        
        public String getColorCode() {
            return colorCode;
        }
        
        public String getColoredName() {
            return colorCode + displayName;
        }
    }
}