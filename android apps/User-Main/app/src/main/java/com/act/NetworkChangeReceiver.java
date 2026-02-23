package com.act;

import android.app.Activity;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.view.ViewGroup;

import com.general.files.MyApp;
import com.general.files.OpenNoLocationView;
import com.buddyverse.main.R;

/**
 * Created by Admin on 31-08-2017.
 */

public class NetworkChangeReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(Context context, Intent intent) {
        checkNetworkSettings();
    }

    private void checkNetworkSettings() {
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