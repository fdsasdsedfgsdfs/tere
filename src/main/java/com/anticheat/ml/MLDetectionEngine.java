package com.anticheat.ml;

import com.anticheat.AdvancedAntiCheat;
import com.anticheat.data.PlayerData;
import com.anticheat.utils.Logger;
import weka.classifiers.Classifier;
import weka.classifiers.functions.MultilayerPerceptron;
import weka.classifiers.trees.RandomForest;
import weka.classifiers.bayes.NaiveBayes;
import weka.classifiers.meta.Vote;
import weka.core.*;
import weka.core.converters.ArffSaver;
import weka.core.converters.ArffLoader;

import java.io.File;
import java.io.IOException;
import java.util.*;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class MLDetectionEngine {
    
    private final AdvancedAntiCheat plugin;
    private final ExecutorService executor;
    
    // Modeller
    private Vote ensembleClassifier;
    private RandomForest randomForest;
    private MultilayerPerceptron neuralNetwork;
    private NaiveBayes naiveBayes;
    
    // Veri yapıları
    private Instances trainingData;
    private final Map<UUID, PlayerBehaviorProfile> playerProfiles;
    private final Map<String, Double> featureWeights;
    
    // Model dosya yolları
    private final File modelDir;
    private final File ensembleModelFile;
    private final File trainingDataFile;
    
    // Yapılandırma
    private static final int TRAINING_BUFFER_SIZE = 1000;
    private static final double CONFIDENCE_THRESHOLD = 0.75;
    private static final int NEURAL_NETWORK_HIDDEN_LAYERS = 10;
    
    public MLDetectionEngine(AdvancedAntiCheat plugin) {
        this.plugin = plugin;
        this.executor = Executors.newFixedThreadPool(2);
        this.playerProfiles = new ConcurrentHashMap<>();
        this.featureWeights = new HashMap<>();
        
        // Model dizinini oluştur
        this.modelDir = new File(plugin.getDataFolder(), "models");
        if (!modelDir.exists()) {
            modelDir.mkdirs();
        }
        
        this.ensembleModelFile = new File(modelDir, "ensemble_model.model");
        this.trainingDataFile = new File(modelDir, "training_data.arff");
        
        initializeFeatureWeights();
    }
    
    public void initialize() throws Exception {
        Logger.info("§a[ML] §7Makine öğrenmesi motoru başlatılıyor...");
        
        // Veri yapısını oluştur
        createDataStructure();
        
        // Önceden eğitilmiş modeli yükle veya yeni model oluştur
        if (ensembleModelFile.exists()) {
            loadModel();
        } else {
            createNewModel();
        }
        
        Logger.info("§a[ML] §7Makine öğrenmesi motoru hazır!");
    }
    
    private void createDataStructure() {
        // Attribute'ları tanımla
        ArrayList<Attribute> attributes = new ArrayList<>();
        
        // Combat özellikler
        attributes.add(new Attribute("cps_average"));           // Ortalama CPS
        attributes.add(new Attribute("cps_variance"));          // CPS varyansı
        attributes.add(new Attribute("hit_accuracy"));          // Vuruş doğruluğu
        attributes.add(new Attribute("head_snap_angle"));       // Kafa dönüş açısı
        attributes.add(new Attribute("reach_distance"));        // Erişim mesafesi
        attributes.add(new Attribute("aim_consistency"));       // Nişan tutarlılığı
        attributes.add(new Attribute("critical_hit_ratio"));    // Kritik vuruş oranı
        attributes.add(new Attribute("combo_length"));          // Kombo uzunluğu
        
        // Movement özellikler
        attributes.add(new Attribute("speed_variance"));        // Hız varyansı
        attributes.add(new Attribute("direction_changes"));     // Yön değişimleri
        attributes.add(new Attribute("jump_pattern"));          // Zıplama deseni
        attributes.add(new Attribute("ground_time_ratio"));     // Yerde kalma oranı
        attributes.add(new Attribute("velocity_changes"));      // Hız değişimleri
        attributes.add(new Attribute("fall_distance"));         // Düşme mesafesi
        attributes.add(new Attribute("liquid_movement"));       // Sıvı içinde hareket
        
        // Block interaction özellikler
        attributes.add(new Attribute("block_break_speed"));     // Blok kırma hızı
        attributes.add(new Attribute("block_place_speed"));     // Blok yerleştirme hızı
        attributes.add(new Attribute("mining_efficiency"));     // Madencilik verimliliği
        attributes.add(new Attribute("scaffold_pattern"));      // İskele deseni
        attributes.add(new Attribute("inventory_speed"));       // Envanter hızı
        
        // Sınıf özelliği (0: Normal, 1: Hack)
        List<String> classValues = Arrays.asList("normal", "hack");
        attributes.add(new Attribute("class", classValues));
        
        // Instances oluştur
        trainingData = new Instances("AntiCheatData", attributes, 0);
        trainingData.setClassIndex(trainingData.numAttributes() - 1);
    }
    
    private void createNewModel() throws Exception {
        Logger.info("§a[ML] §7Yeni model oluşturuluyor...");
        
        // Ensemble classifier oluştur
        ensembleClassifier = new Vote();
        
        // RandomForest
        randomForest = new RandomForest();
        randomForest.setNumTrees(100);
        randomForest.setMaxDepth(15);
        
        // Neural Network
        neuralNetwork = new MultilayerPerceptron();
        neuralNetwork.setHiddenLayers(String.valueOf(NEURAL_NETWORK_HIDDEN_LAYERS));
        neuralNetwork.setLearningRate(0.1);
        neuralNetwork.setMomentum(0.2);
        neuralNetwork.setTrainingTime(200);
        
        // Naive Bayes
        naiveBayes = new NaiveBayes();
        
        // Ensemble'a ekle
        Classifier[] classifiers = {randomForest, neuralNetwork, naiveBayes};
        ensembleClassifier.setClassifiers(classifiers);
        
        // Başlangıç verisiyle eğit (eğer varsa)
        if (trainingDataFile.exists()) {
            loadTrainingData();
            if (trainingData.numInstances() > 10) {
                trainModel();
            }
        }
    }
    
    private void loadModel() throws Exception {
        Logger.info("§a[ML] §7Önceden eğitilmiş model yükleniyor...");
        
        try {
            ensembleClassifier = (Vote) weka.core.SerializationHelper.read(ensembleModelFile.getAbsolutePath());
            Logger.info("§a[ML] §7Model başarıyla yüklendi!");
        } catch (Exception e) {
            Logger.warn("§e[ML] §7Model yüklenemedi, yeni model oluşturuluyor...");
            createNewModel();
        }
    }
    
    public void saveModel() {
        if (ensembleClassifier == null) return;
        
        executor.submit(() -> {
            try {
                weka.core.SerializationHelper.write(ensembleModelFile.getAbsolutePath(), ensembleClassifier);
                saveTrainingData();
                Logger.info("§a[ML] §7Model kaydedildi!");
            } catch (Exception e) {
                Logger.error("§c[ML] §7Model kaydedilemedi: " + e.getMessage());
            }
        });
    }
    
    private void loadTrainingData() throws IOException {
        ArffLoader loader = new ArffLoader();
        loader.setFile(trainingDataFile);
        trainingData = loader.getDataSet();
        trainingData.setClassIndex(trainingData.numAttributes() - 1);
    }
    
    private void saveTrainingData() throws IOException {
        ArffSaver saver = new ArffSaver();
        saver.setInstances(trainingData);
        saver.setFile(trainingDataFile);
        saver.writeBatch();
    }
    
    public CheatDetectionResult analyzePlayer(UUID playerUUID, PlayerData playerData) {
        if (ensembleClassifier == null) {
            return new CheatDetectionResult(false, 0.0, "Model henüz hazır değil");
        }
        
        return executor.submit(() -> {
            try {
                // Oyuncu profilini al veya oluştur
                PlayerBehaviorProfile profile = playerProfiles.computeIfAbsent(playerUUID, 
                    k -> new PlayerBehaviorProfile(playerUUID));
                
                // Profili güncelle
                profile.updateProfile(playerData);
                
                // Özellik vektörü oluştur
                Instance instance = createInstance(profile);
                
                // Tahmin yap
                double[] distribution = ensembleClassifier.distributionForInstance(instance);
                double hackProbability = distribution[1]; // hack sınıfının olasılığı
                
                boolean isHacking = hackProbability > CONFIDENCE_THRESHOLD;
                String reason = generateReason(profile, hackProbability);
                
                // Sonucu döndür
                CheatDetectionResult result = new CheatDetectionResult(isHacking, hackProbability, reason);
                
                // Eğitim verisine ekle (async)
                if (shouldAddToTraining(profile, result)) {
                    addToTrainingData(instance, isHacking);
                }
                
                return result;
                
            } catch (Exception e) {
                Logger.error("§c[ML] §7Analiz hatası: " + e.getMessage());
                return new CheatDetectionResult(false, 0.0, "Analiz hatası");
            }
        }).join();
    }
    
    private Instance createInstance(PlayerBehaviorProfile profile) {
        Instance instance = new DenseInstance(trainingData.numAttributes());
        instance.setDataset(trainingData);
        
        // Combat özellikler
        instance.setValue(0, profile.getCpsAverage());
        instance.setValue(1, profile.getCpsVariance());
        instance.setValue(2, profile.getHitAccuracy());
        instance.setValue(3, profile.getHeadSnapAngle());
        instance.setValue(4, profile.getReachDistance());
        instance.setValue(5, profile.getAimConsistency());
        instance.setValue(6, profile.getCriticalHitRatio());
        instance.setValue(7, profile.getComboLength());
        
        // Movement özellikler
        instance.setValue(8, profile.getSpeedVariance());
        instance.setValue(9, profile.getDirectionChanges());
        instance.setValue(10, profile.getJumpPattern());
        instance.setValue(11, profile.getGroundTimeRatio());
        instance.setValue(12, profile.getVelocityChanges());
        instance.setValue(13, profile.getFallDistance());
        instance.setValue(14, profile.getLiquidMovement());
        
        // Block interaction özellikler
        instance.setValue(15, profile.getBlockBreakSpeed());
        instance.setValue(16, profile.getBlockPlaceSpeed());
        instance.setValue(17, profile.getMiningEfficiency());
        instance.setValue(18, profile.getScaffoldPattern());
        instance.setValue(19, profile.getInventorySpeed());
        
        return instance;
    }
    
    private void addToTrainingData(Instance instance, boolean isHacking) {
        executor.submit(() -> {
            try {
                // Sınıf değerini ayarla
                instance.setValue(trainingData.classIndex(), isHacking ? "hack" : "normal");
                
                // Eğitim verisine ekle
                trainingData.add(instance);
                
                // Buffer boyutunu kontrol et
                if (trainingData.numInstances() > TRAINING_BUFFER_SIZE) {
                    // Eski verileri temizle (FIFO)
                    while (trainingData.numInstances() > TRAINING_BUFFER_SIZE * 0.8) {
                        trainingData.delete(0);
                    }
                }
                
                // Belirli aralıklarla modeli yeniden eğit
                if (trainingData.numInstances() % 100 == 0) {
                    trainModel();
                }
                
            } catch (Exception e) {
                Logger.error("§c[ML] §7Eğitim verisi ekleme hatası: " + e.getMessage());
            }
        });
    }
    
    private void trainModel() throws Exception {
        if (trainingData.numInstances() < 10) return;
        
        Logger.info("§a[ML] §7Model yeniden eğitiliyor... (" + trainingData.numInstances() + " veri)");
        
        // Ensemble'ı eğit
        ensembleClassifier.buildClassifier(trainingData);
        
        Logger.info("§a[ML] §7Model eğitimi tamamlandı!");
    }
    
    private boolean shouldAddToTraining(PlayerBehaviorProfile profile, CheatDetectionResult result) {
        // Belirsiz durumları eğitime ekle
        return result.getConfidence() > 0.3 && result.getConfidence() < 0.9;
    }
    
    private String generateReason(PlayerBehaviorProfile profile, double confidence) {
        List<String> reasons = new ArrayList<>();
        
        // Combat şüpheleri
        if (profile.getCpsAverage() > 15) reasons.add("Yüksek CPS");
        if (profile.getCpsVariance() < 0.1) reasons.add("Tutarlı CPS");
        if (profile.getHitAccuracy() > 0.95) reasons.add("Yüksek doğruluk");
        if (profile.getHeadSnapAngle() > 30) reasons.add("Ani kafa hareketleri");
        if (profile.getReachDistance() > 3.2) reasons.add("Uzun erişim");
        
        // Movement şüpheleri
        if (profile.getSpeedVariance() < 0.05) reasons.add("Tutarlı hız");
        if (profile.getGroundTimeRatio() < 0.2) reasons.add("Az yerde kalma");
        if (profile.getFallDistance() == 0 && profile.getJumpPattern() > 0) reasons.add("Düşme yok");
        
        if (reasons.isEmpty()) {
            return "Makine öğrenmesi tespiti (%.2f güven)".formatted(confidence);
        }
        
        return String.join(", ", reasons) + " (%.2f güven)".formatted(confidence);
    }
    
    private void initializeFeatureWeights() {
        // Özellik ağırlıklarını başlat
        featureWeights.put("cps_average", 0.15);
        featureWeights.put("hit_accuracy", 0.12);
        featureWeights.put("reach_distance", 0.18);
        featureWeights.put("aim_consistency", 0.10);
        featureWeights.put("speed_variance", 0.08);
        featureWeights.put("ground_time_ratio", 0.07);
        featureWeights.put("block_break_speed", 0.06);
        featureWeights.put("head_snap_angle", 0.14);
        featureWeights.put("critical_hit_ratio", 0.10);
    }
    
    public void updatePlayerProfile(UUID playerUUID, PlayerData playerData) {
        PlayerBehaviorProfile profile = playerProfiles.computeIfAbsent(playerUUID, 
            k -> new PlayerBehaviorProfile(playerUUID));
        profile.updateProfile(playerData);
    }
    
    public PlayerBehaviorProfile getPlayerProfile(UUID playerUUID) {
        return playerProfiles.get(playerUUID);
    }
    
    public void removePlayerProfile(UUID playerUUID) {
        playerProfiles.remove(playerUUID);
    }
    
    public void shutdown() {
        executor.shutdown();
        Logger.info("§c[ML] §7Makine öğrenmesi motoru kapatıldı");
    }
    
    // İstatistikler
    public int getTrainingDataSize() {
        return trainingData != null ? trainingData.numInstances() : 0;
    }
    
    public int getActiveProfiles() {
        return playerProfiles.size();
    }
    
    public boolean isModelReady() {
        return ensembleClassifier != null;
    }
}