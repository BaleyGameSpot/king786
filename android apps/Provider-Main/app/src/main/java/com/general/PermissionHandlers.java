package com.general;

import android.animation.Animator;
import android.content.Context;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.LinearLayout;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.widget.NestedScrollView;
import androidx.fragment.app.Fragment;
import androidx.viewpager2.widget.ViewPager2;

import com.act.MainActivity_22;
import com.adapter.files.permissions.PermissionViewPager2Adapter;
import com.airbnb.lottie.LottieAnimationView;
import com.fragments.permissions.DeviceBatterySaverFragment;
import com.fragments.permissions.DeviceDrawOverlayFragment;
import com.fragments.permissions.DeviceGPSFragment;
import com.fragments.permissions.DeviceLocationFragment;
import com.fragments.permissions.DeviceNotificationFragment;
import com.fragments.permissions.DevicePermissionListFragment;
import com.fragments.permissions.DeviceSetUpCompleteFragment;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.buddyverse.providers.R;
import com.utils.DeviceSettings;
import com.view.GenerateAlertBox;

import java.util.ArrayList;
import java.util.Objects;

public class PermissionHandlers {

    public static String BATTERY_SETTINGS_KEY = "BATTERY_SETTINGS";
    static PermissionHandlers instance = null;
    private final GeneralFunctions generalFunc;
    private final ArrayList<Fragment> listOfFrag = new ArrayList<>();

    public PermissionHandlers() {
        this.generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
    }

    public static PermissionHandlers getInstance() {
        if (instance == null) {
            instance = new PermissionHandlers();
        }
        return instance;
    }

    public void initiatePermissionHandler() {
        listOfFrag.clear();

        listOfFrag.add(new DevicePermissionListFragment());

        if (!DeviceSettings.isDeviceGPSEnabled()) {
            listOfFrag.add(new DeviceGPSFragment());
        }

        if (!DeviceSettings.isForegroundLocationEnabled() || !DeviceSettings.isBackgroundLocationEnabled()) {
            listOfFrag.add(new DeviceLocationFragment());
        }

        if (!DeviceSettings.isNotificationEnable()) {
            listOfFrag.add(new DeviceNotificationFragment());
        }

        if (!DeviceSettings.isDrawOverlayEnable()) {
            listOfFrag.add(new DeviceDrawOverlayFragment());
        }

        if (!DeviceSettings.isBatterySaverDisabled()) {
            listOfFrag.add(new DeviceBatterySaverFragment());
        }

        if (listOfFrag.size() <= 2) {
            listOfFrag.remove(0);
        }

        if (listOfFrag.size() > 0) {

            listOfFrag.add(new DeviceSetUpCompleteFragment());

            if (getCurrentAct() != null) {
                PermissionViewPager2Adapter adapter = new PermissionViewPager2Adapter(((AppCompatActivity) getCurrentAct()).getSupportFragmentManager(), ((AppCompatActivity) getCurrentAct()).getLifecycle(), listOfFrag);

                ViewPager2 viewPager2 = getPermissionPager();

                if (viewPager2 != null) {
                    viewPager2.setAdapter(adapter);
                    viewPager2.setVisibility(View.VISIBLE);
                    viewPager2.setUserInputEnabled(false);

                    if (MyApp.getInstance().getCurrentAct() instanceof MainActivity_22) {
                        MainActivity_22 mainActivity_22 = (MainActivity_22) MyApp.getInstance().getCurrentAct();
                        mainActivity_22.goOnlineOffline(false, false);
                    }
                }
            }

        } else {
            setVisibility(View.GONE);
        }
    }

    public void checkPermissions() {
        ViewPager2 viewPager2 = getPermissionPager();
        if (viewPager2 != null && viewPager2.getVisibility() == View.VISIBLE) {
            return;
        }
        initiatePermissionHandler();
    }

    private Context getCurrentAct() {
        return MyApp.getInstance().getCurrentAct();
    }

    @Nullable
    private ViewPager2 getPermissionPager() {
        try {
            return getCurrentAct() != null ? ((AppCompatActivity) getCurrentAct()).
                    findViewById(R.id.permissionPagerView) : null;
        } catch (Exception e) {
            return null;
        }
    }

    public void setVisibility(int VISIBILITY_FLAGS) {
        ViewPager2 myPage = getPermissionPager();
        if (myPage != null) {
            myPage.setVisibility(VISIBILITY_FLAGS);
        }
    }

    public boolean getVisibilityPager() {
        ViewPager2 myPage = getPermissionPager();
        if (myPage != null) {
            return myPage.getVisibility() == View.VISIBLE;
        }
        return false;
    }

    public void setPageNext() {
        if (getPermissionPager() != null) {
            if (Objects.requireNonNull(getPermissionPager()).getCurrentItem() == (listOfFrag.size() - 1)) {
                boolean isAllPermissionApproved = DeviceSettings.isAllLocationEnable() &&
                        DeviceSettings.isNotificationEnable() &&
                        DeviceSettings.isDrawOverlayEnable() &&
                        DeviceSettings.isBatterySaverDisabled();

                if (!isAllPermissionApproved) {
                    initiatePermissionHandler();
                } else {
//                    MyApp.getInstance().forceDestroyServices();
                    GetLocationUpdates.getInstance().continueExecution();
                    new OpenMainProfile(MyApp.getInstance().getCurrentAct(), true, generalFunc).startProcess();
                }
            } else {
                Objects.requireNonNull(getPermissionPager()).setCurrentItem(Objects.requireNonNull(getPermissionPager()).getCurrentItem() + 1);
            }
        }
    }


    public void setShadowView(View view) {
        NestedScrollView nestedScrollView = view.findViewById(R.id.nestedScrollView);
        View shadowHeaderViewTop = view.findViewById(R.id.shadowHeaderViewTop);
        View shadowHeaderViewBottom = view.findViewById(R.id.shadowHeaderViewBottom);
        shadowHeaderViewTop.setVisibility(View.INVISIBLE);
        shadowHeaderViewBottom.setVisibility(View.INVISIBLE);

        nestedScrollView.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {
            if (scrollY > oldScrollY) {
                shadowHeaderViewTop.setVisibility(View.VISIBLE);
                shadowHeaderViewBottom.setVisibility(View.VISIBLE);
            }
            if (scrollY < oldScrollY) {
                shadowHeaderViewTop.setVisibility(View.VISIBLE);
                shadowHeaderViewBottom.setVisibility(View.VISIBLE);
            }

            if (scrollY == 0) {
                shadowHeaderViewTop.setVisibility(View.INVISIBLE);
            }

            if (scrollY == Math.round((v.getChildAt(0).getMeasuredHeight() - (v.getMeasuredHeight())))) {
                shadowHeaderViewBottom.setVisibility(View.INVISIBLE);
            }
        });
    }

    public void openSuccessPermissionDialogView() {
        GenerateAlertBox alert = new GenerateAlertBox(getCurrentAct());
        alert.setCustomView(R.layout.permission_success_dialog);

        LinearLayout llArea = (LinearLayout) alert.getView(R.id.llArea);
        LottieAnimationView animationView = (LottieAnimationView) alert.getView(R.id.lottieAnimationView);
        if (animationView != null) {
            animationView.setAnimation(R.raw.permission_success);
            animationView.addAnimatorListener(new Animator.AnimatorListener() {
                @Override
                public void onAnimationStart(Animator animation) {

                }

                @Override
                public void onAnimationEnd(Animator animation) {
                    alert.closeAlertBox();
                    setPageNext();
                }

                @Override
                public void onAnimationCancel(Animator animation) {
                }

                @Override
                public void onAnimationRepeat(Animator animation) {
                }
            });

            Animation anim = AnimationUtils.loadAnimation(MyApp.getInstance().getCurrentAct(), R.anim.zoom_out_permission);
            llArea.setAnimation(anim);

            new Handler(Looper.getMainLooper()).postDelayed(() -> {
                animationView.playAnimation();
                animationView.setRepeatCount(0);
            }, 500);
            alert.showAlertBox();

            alert.alertDialog.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        }
    }
}