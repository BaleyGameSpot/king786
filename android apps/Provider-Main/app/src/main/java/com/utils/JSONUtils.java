package com.utils;

import org.json.JSONObject;

public class JSONUtils {

    public static JSONObject toJsonObj(String response) {
        try {
            return new JSONObject(response);
        } catch (Exception ignored) {

        }

        return null;
    }

    public static JSONObject getJSONObj(String key, String data) {
        try {
            JSONObject obj = new JSONObject(data);
            return obj.getJSONObject(key);
        } catch (Exception ignored) {

        }

        return null;
    }

    public static JSONObject getJSONObj(String key, JSONObject obj) {
        try {
            return obj.getJSONObject(key);
        } catch (Exception ignored) {

        }

        return null;
    }

    public static boolean isKeyExist(String key, String response) {
        try {
            JSONObject obj = new JSONObject(response);

            if (obj.has(key)) {
                return true;
            }
        } catch (Exception ignored) {

        }

        return false;
    }

    public static boolean isKeyExist(String key, JSONObject obj) {
        try {
            if (obj.has(key)) {
                return true;
            }
        } catch (Exception ignored) {

        }

        return false;
    }
}
