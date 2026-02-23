package com.huawei;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.util.Log;

import com.general.files.MyApp;
import com.huawei.hms.push.HmsMessaging;

public class BootAlarmReceiver extends BroadcastReceiver {
    @Override
    public void onReceive(Context context, Intent intent) {
        Log.e("BootAlarm","Received");
        if (MyApp.getInstance().isHMSOnly()) {
            Log.e("BootAlarm","Received::Enable");
            HmsMessaging.getInstance(context).setAutoInitEnabled(true);
        }
    }
}
