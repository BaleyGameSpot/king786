package com.act;

import android.app.Activity;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.view.ViewGroup;

import com.general.files.MyApp;
import com.general.files.OpenNoLocationView;

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

            if (MyApp.getInstance().driverArrivedAct == null && MyApp.getInstance().activeTripAct == null) {
                MainActivity mainAct = MyApp.getInstance().mainAct;
                MainActivity_22 main22Act = null;
                if (MyApp.getInstance().getCurrentAct() instanceof MainActivity_22) {
                    main22Act = (MainActivity_22) MyApp.getInstance().getCurrentAct();
                }

                if (mainAct != null) {
                    ViewGroup viewGroup = mainAct.findViewById(android.R.id.content);
                    handleNetworkView(mainAct, viewGroup);
                }
                if (main22Act != null) {
                    ViewGroup viewGroup = main22Act.findViewById(android.R.id.content);
                    handleNetworkView(mainAct, viewGroup);
                }
            } else {
                Activity finalActivity = currentActivity;
                if (MyApp.getInstance().activeTripAct != null) {
                    finalActivity = MyApp.getInstance().activeTripAct;

                    MyApp.getInstance().activeTripAct.manageLoader();
                } else if (MyApp.getInstance().driverArrivedAct != null) {
                    finalActivity = MyApp.getInstance().driverArrivedAct;
                }
                ViewGroup viewGroup = finalActivity.findViewById(android.R.id.content);
                handleNetworkView(finalActivity, viewGroup);
            }
        }
    }

    private void handleNetworkView(Activity activity, ViewGroup viewGroup) {
        try {
            OpenNoLocationView.getInstance(activity, viewGroup).configView(true);
        } catch (Exception e) {

        }
    }
}