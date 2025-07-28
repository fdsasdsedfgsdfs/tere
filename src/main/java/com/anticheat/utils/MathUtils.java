package com.anticheat.utils;

import java.util.List;

public class MathUtils {
    
    public static double calculateVariance(List<Double> values) {
        if (values.size() < 2) return 0.0;
        
        double mean = values.stream().mapToDouble(Double::doubleValue).average().orElse(0);
        double variance = values.stream()
            .mapToDouble(val -> Math.pow(val - mean, 2))
            .average()
            .orElse(0);
        
        return variance;
    }
    
    public static double calculateStandardDeviation(List<Double> values) {
        return Math.sqrt(calculateVariance(values));
    }
    
    public static double calculateMean(List<Double> values) {
        return values.stream().mapToDouble(Double::doubleValue).average().orElse(0);
    }
}