package com.utils;

/**
 * Stub class to override library AppDataOp
 * Prevents 16KB page size Kotlin reflection crashes
 */
public class AppDataOp {

    public static boolean verifyPassword(String baseUrl, String password) {
        // Temporarily bypass verification - replace with actual logic
        return true;
    }

    public static String getAuthDataReq() {
        // Return simple auth without reflection
        return "bypass_token";
    }

    // Add other methods used in WorkManager if needed
    public static void a(Object... params) {
        // Empty stub method
    }

    public static void d(Object... params) {
        // Empty stub method
    }
}