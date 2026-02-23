package com.general.files;

import android.app.Activity;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.view.ViewGroup;

import com.act.RideDeliveryActivity;
import com.act.UberXHomeActivity;
import com.buddyverse.main.R;

/**
 * Created by Admin on 23-11-2016.
 */
public class GpsReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(Context context, Intent intent) {
        checkGPSSettings();
    }

    private void checkGPSSettings() {
        Activity currentActivity = MyApp.getInstance().getCurrentAct();

        if (currentActivity != null) {
            if (currentActivity instanceof UberXHomeActivity || MyApp.getInstance().isCurrentActByConfigView()) {

                ViewGroup viewGroup;
                if (currentActivity instanceof UberXHomeActivity act) {

                    viewGroup = currentActivity.findViewById(R.id.MainArea);
                    if (act.homeDynamic_23_fragment != null) {
                        viewGroup = act.homeDynamic_23_fragment.binding.screen23MainArea;
                        act.homeDynamic_23_fragment.onResume();
                    } else if (act.homeDynamic_24_fragment != null) {
                        viewGroup = act.homeDynamic_24_fragment.binding.screen23MainArea;
                        act.homeDynamic_24_fragment.onResume();
                    }

                } else if (currentActivity instanceof RideDeliveryActivity) {
                    viewGroup = currentActivity.findViewById(R.id.MainLayout);

                } else {
                    viewGroup = currentActivity.findViewById(android.R.id.content);
                }
                OpenNoLocationView.getInstance(currentActivity, viewGroup).configView(false);
            }
        }
    }
}