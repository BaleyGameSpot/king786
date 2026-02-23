package com.general.files;

import android.content.Context;
import android.graphics.RectF;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;

import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.deliverAll.LiveTaskListActivity;
import com.activity.ParentActivity;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.BottomBarBinding;
import com.model.ServiceModule;
import com.view.bottombar.BottomBarItem;
import com.view.bottombar.OnItemSelectedListener;
import com.view.MTextView;

import org.json.JSONObject;

import java.util.ArrayList;

public class AddBottomBar {

    @NonNull
    private final BottomBarBinding binding;
    private final Context mContext;
    private final GeneralFunctions generalFunc;

    private final boolean isNewHome_23, isNewHome_24;
    private int appThemeColor1, deSelectedColor;


    public AddBottomBar(Context mContext, GeneralFunctions generalFunc, JSONObject obj_userProfile, LinearLayout parentView) {
        LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        this.binding = BottomBarBinding.inflate(inflater, parentView, false);
        parentView.addView(binding.getRoot());

        this.mContext = mContext;
        this.generalFunc = generalFunc;

        isNewHome_23 = generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile).equalsIgnoreCase("Yes");

        isNewHome_24 = generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_24", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_24", obj_userProfile).equalsIgnoreCase("Yes");

        //
        setBottomView();
    }

    private void setBottomView() {
        binding.homeArea.setOnClickListener(new setOnClickList());
        binding.historyArea.setOnClickListener(new setOnClickList());
        binding.walletArea.setOnClickListener(new setOnClickList());
        binding.profileArea.setOnClickListener(new setOnClickList());

        binding.bottomBar.setVisibility(View.GONE);
        binding.bottomArea.setVisibility(View.GONE);
        if (isNewHome_24) {
            binding.bottomBar.setVisibility(View.VISIBLE);

            ArrayList<BottomBarItem> bottomBarItems = new ArrayList<>();
            bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HOME_BOTTOM_MENU"), "", ContextCompat.getDrawable(mContext, R.drawable.ic_home_24), new RectF(), 0));

            if (ServiceModule.isDeliverAllOnly()) {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS_TXT"), "", ContextCompat.getDrawable(mContext, R.drawable.ic_booking), new RectF(), 0));
            } else if (ServiceModule.IsTrackingProvider) {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_TRIP"), "", ContextCompat.getDrawable(mContext, R.drawable.ic_booking), new RectF(), 0));
            } else {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_BOOKINGS"), "", ContextCompat.getDrawable(mContext, R.drawable.ic_booking), new RectF(), 0));
            }

            bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_WALLET"), "", ContextCompat.getDrawable(mContext, R.drawable.ic_wallet), new RectF(), 0));

            bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_PROFILE"), "", ContextCompat.getDrawable(mContext, R.drawable.ic_profile_24), new RectF(), 0));

            binding.bottomBar.initBottomBar(bottomBarItems);

            binding.bottomBar.setOnItemSelectedListener((OnItemSelectedListener) pos -> {
                if (MyApp.getInstance().getCurrentAct() instanceof ParentActivity) {
                    ((ParentActivity) MyApp.getInstance().getCurrentAct()).onBottomTabSelected(pos);
                }
                return true;
            });

        } else {
            binding.bottomArea.setVisibility(View.VISIBLE);

            int color = R.color.appThemeColor_1;
            int deSelectColor = R.color.homedeSelectColor;
            if (isNewHome_23 && ServiceModule.isRideOnly()) {
                color = R.color.homeSelectColor_23;
                deSelectColor = R.color.homeDeSelectColor_23;
            }
            appThemeColor1 = ContextCompat.getColor(mContext, color);
            deSelectedColor = ContextCompat.getColor(mContext, deSelectColor);

            binding.homeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_BOTTOM_MENU"));
            if (ServiceModule.isDeliverAllOnly()) {
                binding.historyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS_TXT"));
            } else if (ServiceModule.IsTrackingProvider) {
                binding.historyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRIP"));
            } else {
                binding.historyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_BOOKINGS"));
            }
            binding.walletTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_WALLET"));
            binding.profileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_PROFILE"));

            manageBottomMenu(binding.homeTxt);
        }
        binding.homeArea.performClick();
    }

    public void setBottomFrg(int pos) {
        if (pos == 0) {
            binding.homeArea.performClick();
        } else if (pos == 1) {
            binding.historyArea.performClick();
        } else if (pos == 2) {
            binding.walletArea.performClick();
        } else if (pos == 3) {
            binding.profileArea.performClick();
        }
    }

    private void manageBottomMenu(MTextView selTextView) {
        //manage Select deselect Bottom Menu
        if (selTextView.getId() == binding.homeTxt.getId()) {
            binding.homeTxt.setTextColor(appThemeColor1);
            binding.homeImg.setColorFilter(appThemeColor1, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.homeImg.setImageResource(R.drawable.ic_home_fill);
        } else {
            binding.homeTxt.setTextColor(deSelectedColor);
            binding.homeImg.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.homeImg.setImageResource(R.drawable.ic_home);
        }

        if (selTextView.getId() == binding.historyTxt.getId()) {
            binding.historyTxt.setTextColor(appThemeColor1);
            binding.bookingImg.setColorFilter(appThemeColor1, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.bookingImg.setImageResource(R.drawable.ic_booking_fill);
        } else {
            binding.historyTxt.setTextColor(deSelectedColor);
            binding.bookingImg.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.bookingImg.setImageResource(R.drawable.ic_booking);
        }

        if (selTextView.getId() == binding.walletTxt.getId()) {
            binding.walletTxt.setTextColor(appThemeColor1);
            binding.walletImg.setColorFilter(appThemeColor1, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.walletImg.setImageResource(R.drawable.ic_wallet_fill);
        } else {
            binding.walletTxt.setTextColor(deSelectedColor);
            binding.walletImg.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.walletImg.setImageResource(R.drawable.ic_wallet);
        }

        if (selTextView.getId() == binding.profileTxt.getId()) {
            binding.profileTxt.setTextColor(appThemeColor1);
            binding.profileImg.setColorFilter(appThemeColor1, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.profileImg.setImageResource(R.drawable.ic_profile_fill);
        } else {
            binding.profileTxt.setTextColor(deSelectedColor);
            binding.profileImg.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.profileImg.setImageResource(R.drawable.ic_profile);
        }
    }

    public void manualClickView(int pos) {
        if (isNewHome_24) {
            binding.bottomBar.setSelectedItem(pos);
        } else if (isNewHome_23) {
            if (pos == 1) {
                binding.historyArea.performClick();
            } else if (pos == 3) {
                binding.profileArea.performClick();
            }
        }
    }

    private class setOnClickList implements View.OnClickListener {

        @Override
        public void onClick(View view) {

            if (view.getId() == binding.homeArea.getId()) {
                manageBottomMenu(binding.homeTxt);
                if (mContext instanceof MainActivity activity) {
                    activity.manageHome();
                } else if (mContext instanceof MainActivity_22 activity) {
                    activity.manageHome();
                } else if (mContext instanceof LiveTaskListActivity activity) {
                    activity.manageHome();
                }

            } else if (view.getId() == binding.historyArea.getId()) {
                manageBottomMenu(binding.historyTxt);
                if (mContext instanceof MainActivity activity) {
                    activity.openBookingFrgament();
                } else if (mContext instanceof MainActivity_22 activity) {
                    activity.openBookingFrgament();
                } else if (mContext instanceof LiveTaskListActivity activity) {
                    activity.openBookingFrgament();
                }

            } else if (view.getId() == binding.walletArea.getId()) {
                manageBottomMenu(binding.walletTxt);
                if (mContext instanceof MainActivity activity) {
                    activity.openWalletFrgament();
                } else if (mContext instanceof MainActivity_22 activity) {
                    activity.openWalletFrgament();
                } else if (mContext instanceof LiveTaskListActivity activity) {
                    activity.openWalletFragment();
                }

            } else if (view.getId() == binding.profileArea.getId()) {
                manageBottomMenu(binding.profileTxt);
                if (mContext instanceof MainActivity activity) {
                    activity.openProfileFragment();
                } else if (mContext instanceof MainActivity_22 activity) {
                    activity.openProfileFragment();
                } else if (mContext instanceof LiveTaskListActivity activity) {
                    activity.openProfileFragment();
                }

            }
        }
    }
}