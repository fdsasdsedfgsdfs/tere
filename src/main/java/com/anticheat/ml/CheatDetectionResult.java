package com.anticheat.ml;

public class CheatDetectionResult {
    
    private final boolean isHacking;
    private final double confidence;
    private final String reason;
    
    public CheatDetectionResult(boolean isHacking, double confidence, String reason) {
        this.isHacking = isHacking;
        this.confidence = confidence;
        this.reason = reason;
    }
    
    public boolean isHacking() { return isHacking; }
    public double getConfidence() { return confidence; }
    public String getReason() { return reason; }
}