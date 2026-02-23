package com.general.files;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

import com.general.PermissionHandlers;

/**
 * Created by Admin on 23-11-2016.
 */
public class GpsReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(Context context, Intent intent) {
        PermissionHandlers.getInstance().checkPermissions();
    }
}