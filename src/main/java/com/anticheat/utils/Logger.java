package com.anticheat.utils;

import org.bukkit.Bukkit;

public class Logger {
    
    public static void info(String message) {
        Bukkit.getLogger().info(message);
    }
    
    public static void warn(String message) {
        Bukkit.getLogger().warning(message);
    }
    
    public static void error(String message) {
        Bukkit.getLogger().severe(message);
    }
    
    public static void debug(String message) {
        if (isDebugEnabled()) {
            Bukkit.getLogger().info("[DEBUG] " + message);
        }
    }
    
    private static boolean isDebugEnabled() {
        return true; // Config'den alınacak
    }
}