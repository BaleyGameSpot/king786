package com.general.files;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

import com.utils.Logger;

public class ButtonReceiver extends BroadcastReceiver {
    @Override
    public void onReceive(Context context, Intent intent) {
        Logger.d("ButtonReceiver","called::"+intent.getStringExtra("name"));

    }
}
