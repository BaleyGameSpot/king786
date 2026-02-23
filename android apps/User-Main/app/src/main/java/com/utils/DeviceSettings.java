package com.utils;

import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Build;

import androidx.core.app.ActivityCompat;
import androidx.core.app.NotificationManagerCompat;

import com.general.files.MyApp;

public class DeviceSettings {
    public static boolean isForegroundLocationEnabled() {
        if (MyApp.getInstance() == null || MyApp.getInstance().getCurrentAct() == null) {
            return false;
        }
        return (ActivityCompat.checkSelfPermission(MyApp.getInstance().getCurrentAct(),
                Manifest.permission.ACCESS_COARSE_LOCATION) == PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(MyApp.getInstance().getCurrentAct(),
                Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED);
    }

    public static boolean isBackgroundLocationEnabled() {
        if (true){
            return true;
        }
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.Q) {
            return true;
        }
        if (MyApp.getInstance() == null || MyApp.getInstance().getCurrentAct() == null) {
            return false;
        }
        return ActivityCompat.checkSelfPermission(MyApp.getInstance().getCurrentAct(),
                Manifest.permission.ACCESS_BACKGROUND_LOCATION) == PackageManager.PERMISSION_GRANTED;
    }

    public static boolean isAllLocationEnable() {
        return isForegroundLocationEnabled() && isBackgroundLocationEnabled();
    }

    public static boolean isNotificationEnable() {
        if (MyApp.getInstance() == null || MyApp.getInstance().getCurrentAct() == null) {
            return false;
        }
        return NotificationManagerCompat.from(MyApp.getInstance().getCurrentAct()).areNotificationsEnabled();
    }
}