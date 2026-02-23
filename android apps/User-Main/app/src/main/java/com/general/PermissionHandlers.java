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

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.widget.NestedScrollView;
import androidx.fragment.app.Fragment;
import androidx.viewpager2.widget.ViewPager2;

import com.adapter.ViewPager2Adapter;
import com.airbnb.lottie.LottieAnimationView;
import com.fragments.permissions.DeviceLocationFragment;
import com.fragments.permissions.DeviceNotificationFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentPermissionsInfoBinding;
import com.utils.DeviceSettings;
import com.view.GenerateAlertBox;

import java.util.ArrayList;
import java.util.Objects;

public class PermissionHandlers {

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

        if (!DeviceSettings.isForegroundLocationEnabled() || !DeviceSettings.isBackgroundLocationEnabled()) {
            listOfFrag.add(new DeviceLocationFragment());
        }

        if (!DeviceSettings.isNotificationEnable()) {
            listOfFrag.add(new DeviceNotificationFragment());
        }

        if (!listOfFrag.isEmpty()) {

            if (getCurrentAct() != null) {
                ViewPager2Adapter adapter = new ViewPager2Adapter(((AppCompatActivity) getCurrentAct()).getSupportFragmentManager(), ((AppCompatActivity) getCurrentAct()).getLifecycle(), listOfFrag);

                ViewPager2 viewPager2 = getPermissionPager();

                if (viewPager2 != null) {
                    viewPager2.setAdapter(adapter);
                    viewPager2.setVisibility(View.VISIBLE);
                    viewPager2.setUserInputEnabled(false);
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
                        DeviceSettings.isNotificationEnable();

                if (!isAllPermissionApproved) {
                    initiatePermissionHandler();
                } else {
                    new OpenMainProfile(MyApp.getInstance().getCurrentAct(), "", true, generalFunc).startProcess();
                }
            } else {
                Objects.requireNonNull(getPermissionPager()).setCurrentItem(Objects.requireNonNull(getPermissionPager()).getCurrentItem() + 1);
            }
        }
    }

    public void setShadowView(@NonNull FragmentPermissionsInfoBinding binding) {
        binding.shadowHeaderViewTop.setVisibility(View.INVISIBLE);
        binding.shadowHeaderViewBottom.setVisibility(View.INVISIBLE);

        binding.nestedScrollView.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {
            if (scrollY > oldScrollY) {
                binding.shadowHeaderViewTop.setVisibility(View.VISIBLE);
                binding.shadowHeaderViewBottom.setVisibility(View.VISIBLE);
            }
            if (scrollY < oldScrollY) {
                binding.shadowHeaderViewTop.setVisibility(View.VISIBLE);
                binding.shadowHeaderViewBottom.setVisibility(View.VISIBLE);
            }

            if (scrollY == 0) {
                binding.shadowHeaderViewTop.setVisibility(View.INVISIBLE);
            }

            if (scrollY == Math.round((v.getChildAt(0).getMeasuredHeight() - (v.getMeasuredHeight())))) {
                binding.shadowHeaderViewBottom.setVisibility(View.INVISIBLE);
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
                public void onAnimationStart(@NonNull Animator animation) {

                }

                @Override
                public void onAnimationEnd(@NonNull Animator animation) {
                    alert.closeAlertBox();
                    setPageNext();
                }

                @Override
                public void onAnimationCancel(@NonNull Animator animation) {
                }

                @Override
                public void onAnimationRepeat(@NonNull Animator animation) {
                }
            });

            Animation anim = AnimationUtils.loadAnimation(MyApp.getInstance().getCurrentAct(), R.anim.zoom_out_permission);
            llArea.setAnimation(anim);

            new Handler(Looper.getMainLooper()).postDelayed(() -> {
                animationView.playAnimation();
                animationView.setRepeatCount(0);
            }, 500);
            alert.showAlertBox();

            Objects.requireNonNull(alert.alertDialog.getWindow()).setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        }
    }
}