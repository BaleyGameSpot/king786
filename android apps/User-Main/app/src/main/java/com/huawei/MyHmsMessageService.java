package com.huawei;

import android.content.Context;
import android.os.PowerManager;
import android.util.Log;

import com.general.files.FireTripStatusMsg;
import com.general.files.MyApp;
import com.huawei.hms.push.HmsMessageService;
import com.huawei.hms.push.RemoteMessage;

import java.util.Arrays;

public class MyHmsMessageService extends HmsMessageService {
    private static final String TAG = "PushDemoLog";

    @Override
    public void onNewToken(String token) {
        super.onNewToken(token);
        Log.d(TAG, "receive token:" + token);
    }

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        super.onMessageReceived(remoteMessage);

        Log.d(TAG, "Data: " + remoteMessage.getData());

        if (remoteMessage == null || remoteMessage.getData().trim().equalsIgnoreCase("")) {
            return;
        }

        String message = remoteMessage.getData();

        try {
//            PowerManager powerManager = (PowerManager) MyApp.getInstance().getCurrentAct().getSystemService(Context.POWER_SERVICE);
//            boolean isScreenOn = powerManager.isScreenOn();
            new FireTripStatusMsg(MyApp.getInstance() != null ? MyApp.getInstance().getCurrentAct() : getApplicationContext()).fireTripMsg(message);
        } catch (Exception e) {
            new FireTripStatusMsg(MyApp.getInstance() != null ? MyApp.getInstance().getCurrentAct() : getApplicationContext()).fireTripMsg(message);
        }

    }
}