package com.anticheat;

import com.anticheat.commands.AntiCheatCommand;
import com.anticheat.config.ConfigManager;
import com.anticheat.listeners.*;
import com.anticheat.ml.MLDetectionEngine;
import com.anticheat.managers.*;
import com.anticheat.utils.Logger;
import org.bukkit.Bukkit;
import org.bukkit.plugin.PluginManager;
import org.bukkit.plugin.java.JavaPlugin;

public class AdvancedAntiCheat extends JavaPlugin {

    private static AdvancedAntiCheat instance;
    
    // Managers
    private ConfigManager configManager;
    private ViolationManager violationManager;
    private PlayerDataManager playerDataManager;
    private PunishmentManager punishmentManager;
    private AlertManager alertManager;
    private MLDetectionEngine mlEngine;
    
    // Combat Detection
    private KillAuraDetector killAuraDetector;
    private AimbotDetector aimbotDetector;
    private TriggerBotDetector triggerBotDetector;
    private CriticalsDetector criticalsDetector;
    private AutoClickerDetector autoClickerDetector;
    private ReachDetector reachDetector;
    private VelocityDetector velocityDetector;
    private HitBoxDetector hitBoxDetector;
    
    // Movement Detection
    private FlyDetector flyDetector;
    private SpeedDetector speedDetector;
    private NoFallDetector noFallDetector;
    private JesusDetector jesusDetector;
    private PhaseDetector phaseDetector;
    private StepDetector stepDetector;
    private SpiderDetector spiderDetector;
    
    // Visual Detection
    private XrayDetector xrayDetector;
    private ESPDetector espDetector;
    private FreecamDetector freecamDetector;
    
    // Automation Detection
    private ScaffoldDetector scaffoldDetector;
    private AutoMineDetector autoMineDetector;
    private FastBreakDetector fastBreakDetector;
    private ChestStealerDetector chestStealerDetector;
    private AutoFishDetector autoFishDetector;

    @Override
    public void onEnable() {
        instance = this;
        
        Logger.info("§a[AdvancedAntiCheat] §7Plugin başlatılıyor...");
        
        // Config yükle
        initializeConfig();
        
        // Managers'ları başlat
        initializeManagers();
        
        // Detectors'ları başlat
        initializeDetectors();
        
        // Listeners'ları kaydet
        registerListeners();
        
        // Commands'ları kaydet
        registerCommands();
        
        // Machine Learning Engine'i başlat
        initializeMachineLearning();
        
        // Metrikleri başlat
        startMetrics();
        
        Logger.info("§a[AdvancedAntiCheat] §7Plugin başarıyla yüklendi! (v" + getDescription().getVersion() + ")");
        Logger.info("§a[AdvancedAntiCheat] §7Minecraft 1.19.4 için optimize edildi");
        Logger.info("§a[AdvancedAntiCheat] §7Makine öğrenmesi motoru aktif");
    }

    @Override
    public void onDisable() {
        Logger.info("§c[AdvancedAntiCheat] §7Plugin kapatılıyor...");
        
        // ML Engine'i kaydet ve kapat
        if (mlEngine != null) {
            mlEngine.saveModel();
            mlEngine.shutdown();
        }
        
        // Player verilerini kaydet
        if (playerDataManager != null) {
            playerDataManager.saveAllData();
        }
        
        // Config'i kaydet
        if (configManager != null) {
            configManager.saveConfig();
        }
        
        Logger.info("§c[AdvancedAntiCheat] §7Plugin kapatıldı!");
    }

    private void initializeConfig() {
        configManager = new ConfigManager(this);
        configManager.loadConfig();
    }

    private void initializeManagers() {
        violationManager = new ViolationManager(this);
        playerDataManager = new PlayerDataManager(this);
        punishmentManager = new PunishmentManager(this);
        alertManager = new AlertManager(this);
    }

    private void initializeDetectors() {
        // Combat Detectors
        killAuraDetector = new KillAuraDetector(this);
        aimbotDetector = new AimbotDetector(this);
        triggerBotDetector = new TriggerBotDetector(this);
        criticalsDetector = new CriticalsDetector(this);
        autoClickerDetector = new AutoClickerDetector(this);
        reachDetector = new ReachDetector(this);
        velocityDetector = new VelocityDetector(this);
        hitBoxDetector = new HitBoxDetector(this);
        
        // Movement Detectors
        flyDetector = new FlyDetector(this);
        speedDetector = new SpeedDetector(this);
        noFallDetector = new NoFallDetector(this);
        jesusDetector = new JesusDetector(this);
        phaseDetector = new PhaseDetector(this);
        stepDetector = new StepDetector(this);
        spiderDetector = new SpiderDetector(this);
        
        // Visual Detectors
        xrayDetector = new XrayDetector(this);
        espDetector = new ESPDetector(this);
        freecamDetector = new FreecamDetector(this);
        
        // Automation Detectors
        scaffoldDetector = new ScaffoldDetector(this);
        autoMineDetector = new AutoMineDetector(this);
        fastBreakDetector = new FastBreakDetector(this);
        chestStealerDetector = new ChestStealerDetector(this);
        autoFishDetector = new AutoFishDetector(this);
    }

    private void registerListeners() {
        PluginManager pm = Bukkit.getPluginManager();
        
        // Ana listeners
        pm.registerEvents(new PlayerListener(this), this);
        pm.registerEvents(new CombatListener(this), this);
        pm.registerEvents(new MovementListener(this), this);
        pm.registerEvents(new BlockListener(this), this);
        pm.registerEvents(new InventoryListener(this), this);
        pm.registerEvents(new PacketListener(this), this);
    }

    private void registerCommands() {
        getCommand("anticheat").setExecutor(new AntiCheatCommand(this));
    }

    private void initializeMachineLearning() {
        try {
            mlEngine = new MLDetectionEngine(this);
            mlEngine.initialize();
            Logger.info("§a[ML] §7Makine öğrenmesi motoru başlatıldı");
        } catch (Exception e) {
            Logger.error("§c[ML] §7Makine öğrenmesi motoru başlatılamadı: " + e.getMessage());
        }
    }

    private void startMetrics() {
        // Performans metrikleri ve istatistikler
        Bukkit.getScheduler().runTaskTimerAsynchronously(this, () -> {
            if (playerDataManager != null) {
                playerDataManager.updateStatistics();
            }
        }, 20L * 60, 20L * 60); // Her dakika
    }

    // Getters
    public static AdvancedAntiCheat getInstance() {
        return instance;
    }

    public ConfigManager getConfigManager() {
        return configManager;
    }

    public ViolationManager getViolationManager() {
        return violationManager;
    }

    public PlayerDataManager getPlayerDataManager() {
        return playerDataManager;
    }

    public PunishmentManager getPunishmentManager() {
        return punishmentManager;
    }

    public AlertManager getAlertManager() {
        return alertManager;
    }

    public MLDetectionEngine getMlEngine() {
        return mlEngine;
    }

    // Combat Detectors
    public KillAuraDetector getKillAuraDetector() {
        return killAuraDetector;
    }

    public AimbotDetector getAimbotDetector() {
        return aimbotDetector;
    }

    public TriggerBotDetector getTriggerBotDetector() {
        return triggerBotDetector;
    }

    public CriticalsDetector getCriticalsDetector() {
        return criticalsDetector;
    }

    public AutoClickerDetector getAutoClickerDetector() {
        return autoClickerDetector;
    }

    public ReachDetector getReachDetector() {
        return reachDetector;
    }

    public VelocityDetector getVelocityDetector() {
        return velocityDetector;
    }

    public HitBoxDetector getHitBoxDetector() {
        return hitBoxDetector;
    }

    // Movement Detectors
    public FlyDetector getFlyDetector() {
        return flyDetector;
    }

    public SpeedDetector getSpeedDetector() {
        return speedDetector;
    }

    public NoFallDetector getNoFallDetector() {
        return noFallDetector;
    }

    public JesusDetector getJesusDetector() {
        return jesusDetector;
    }

    public PhaseDetector getPhaseDetector() {
        return phaseDetector;
    }

    public StepDetector getStepDetector() {
        return stepDetector;
    }

    public SpiderDetector getSpiderDetector() {
        return spiderDetector;
    }

    // Visual Detectors
    public XrayDetector getXrayDetector() {
        return xrayDetector;
    }

    public ESPDetector getESPDetector() {
        return espDetector;
    }

    public FreecamDetector getFreecamDetector() {
        return freecamDetector;
    }

    // Automation Detectors
    public ScaffoldDetector getScaffoldDetector() {
        return scaffoldDetector;
    }

    public AutoMineDetector getAutoMineDetector() {
        return autoMineDetector;
    }

    public FastBreakDetector getFastBreakDetector() {
        return fastBreakDetector;
    }

    public ChestStealerDetector getChestStealerDetector() {
        return chestStealerDetector;
    }

    public AutoFishDetector getAutoFishDetector() {
        return autoFishDetector;
    }
}