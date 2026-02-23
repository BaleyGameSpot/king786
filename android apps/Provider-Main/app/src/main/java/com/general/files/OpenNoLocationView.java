package com.general.files;

import android.app.Activity;
import android.content.Context;
import android.os.Handler;
import android.provider.Settings;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.fragment.app.Fragment;

import com.act.AddAddressActivity;
import com.act.DriverArrivedActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.WorkingtrekActivity;
import com.fragments.InactiveFragment;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.DesginNoLocatinViewBinding;
import com.utils.Logger;
import com.utils.Utils;

import java.util.List;

public class OpenNoLocationView {
    private static OpenNoLocationView currentInst;

    private View noLocView;
    private ViewGroup viewGroup;
    private Activity currentAct;

    private boolean isViewExecutionLocked = false;
    private boolean isAddviewCalled = false;

    public static OpenNoLocationView getInstance(Activity currentAct, ViewGroup viewGroup) {
        if (currentInst == null) {
            currentInst = new OpenNoLocationView();
        }
        currentInst.viewGroup = viewGroup;
        currentInst.currentAct = currentAct;
        return currentInst;
    }

    public void configView(boolean isFromNetwork) {
        if (MyApp.getInstance().getCurrentAct() != null) {
            currentAct = MyApp.getInstance().getCurrentAct();
        }
        if (viewGroup != null && currentAct != null) {

            if (isViewExecutionLocked) {
                return;
            }
            isViewExecutionLocked = true;

            closeView();

            if (currentAct instanceof MainActivity activity) {
                List<Fragment> fragmentsList = activity.getSupportFragmentManager().getFragments();

                for (int i = 0; i < fragmentsList.size(); i++) {
                    Fragment frag = fragmentsList.get(i);
                    if (frag instanceof InactiveFragment) {
                        isViewExecutionLocked = false;
                        return;
                    }
                }
            }
            if (currentAct instanceof MainActivity_22 activity) {
                List<Fragment> fragmentsList = activity.getSupportFragmentManager().getFragments();
                for (int i = 0; i < fragmentsList.size(); i++) {
                    Fragment frag = fragmentsList.get(i);
                    if (frag instanceof InactiveFragment) {
                        isViewExecutionLocked = false;
                        return;
                    }
                }
            }

            GeneralFunctions generalFunc = MyApp.getInstance().getGeneralFun(MyApp.getInstance().getCurrentAct());
            boolean isNetworkConnected = new InternetConnection(currentAct).isNetworkConnected();
            LayoutInflater inflater = (LayoutInflater) currentAct.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            DesginNoLocatinViewBinding binding = DesginNoLocatinViewBinding.inflate(inflater, null, false);

            //
            binding.settingBtn.setText(generalFunc.retrieveLangLBl("", "LBL_SETTINGS"));
            binding.RetryBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"));

            binding.settingBtn.setOnClickListener(v -> {
                if (isNetworkConnected) {
                    new ActUtils(MyApp.getInstance().getCurrentAct()).startActForResult(Settings.ACTION_LOCATION_SOURCE_SETTINGS, Utils.REQUEST_CODE_GPS_ON);
                } else {
                    new ActUtils(MyApp.getInstance().getCurrentAct()).startActForResult(Settings.ACTION_SETTINGS, Utils.REQUEST_CODE_NETWOEK_ON);
                }
            });

            binding.RetryBtn.setOnClickListener(v -> {
                configView(isFromNetwork);
            });

            if (!isNetworkConnected) {
                currentInst.noLocView = binding.getRoot();

                binding.noLocTitleTxt.setText(generalFunc.retrieveLangLBl("Internet Connection", "LBL_NO_INTERNET_TITLE"));
                binding.noLocMesageTxt.setText(generalFunc.retrieveLangLBl("Application requires internet connection to be enabled. Please check your network settings.", "LBL_NO_INTERNET_SUB_TITLE"));
                addView(noLocView, "NO_INTERNET");

                isViewExecutionLocked = false;
                if (currentAct instanceof WorkingtrekActivity activity) {
                    activity.internetConnection(false);
                }
                return;
            } else if (isFromNetwork) {
                if (currentAct instanceof DriverArrivedActivity activity) {
                    activity.internetIsBack();
                }
                if (currentAct instanceof WorkingtrekActivity activity) {
                    activity.internetIsBack();
                    activity.internetConnection(true);
                }
            }
        } else {
            Logger.e("AssertError", "ViewGroup OR Activity cannot be null");
        }
        isViewExecutionLocked = false;
    }

    private void addView(View noLocView, String type) {
        isAddviewCalled = true;
        closeView();
        currentInst.noLocView = noLocView;

        if (currentAct instanceof MainActivity activity) {
            if (type.equalsIgnoreCase("NO_LOCATION")) {
                activity.handleNoLocationDial();
            }
            ((RelativeLayout) (viewGroup.findViewById(R.id.containerView))).addView(noLocView);
        } else if (currentAct instanceof MainActivity_22 activity) {

            if (type.equalsIgnoreCase("NO_LOCATION")) {
                activity.handleNoLocationDial();
            }
            ((RelativeLayout) (viewGroup.findViewById(R.id.containerView))).addView(noLocView);
        } else {
            viewGroup.addView(noLocView);
        }
    }

    public void closeView() {
        if (noLocView != null) {
            try {
                if (currentAct instanceof MainActivity) {
                    ((RelativeLayout) (viewGroup.findViewById(R.id.containerView))).removeView(noLocView);
                } else if (currentAct instanceof MainActivity_22 activity) {
                    ((RelativeLayout) (viewGroup.findViewById(R.id.containerView))).removeView(noLocView);

                    if (isAddviewCalled) {
                        isAddviewCalled = false;
                        if (GetLocationUpdates.retrieveInstance() != null) {
                            GetLocationUpdates.getInstance().stopLocationUpdates(activity);
                            GetLocationUpdates.getInstance().destroyLocUpdates(activity);
                        }
                        GetLocationUpdates.getInstance().setTripStartValue(false, false, false, "");
                        new Handler().postDelayed(() -> GetLocationUpdates.getInstance().startLocationUpdates(activity, activity), 2000);
                    }


                } else if (currentAct instanceof AddAddressActivity activity) {
                    viewGroup.removeView(noLocView);

                    if (isAddviewCalled) {
                        isAddviewCalled = false;
                        if (GetLocationUpdates.retrieveInstance() != null) {
                            GetLocationUpdates.getInstance().stopLocationUpdates(activity);
                            GetLocationUpdates.getInstance().destroyLocUpdates(activity);
                        }
                        GetLocationUpdates.getInstance().setTripStartValue(false, false, false, "");
                        new Handler().postDelayed(() -> GetLocationUpdates.getInstance().startLocationUpdates(activity, activity), 2000);
                    }

                } else {
                    viewGroup.removeView(noLocView);
                }
                noLocView = null;
            } catch (Exception e) {
                Logger.e("ViewRemove", ":Exception:" + e.getMessage());
            }
        }
    }
}
