package com.general.files;

import android.content.Context;
import android.graphics.RectF;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;

import com.act.CommonDeliveryTypeSelectionActivity;
import com.act.RideDeliveryActivity;
import com.act.UberXHomeActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity;
import com.act.deliverAll.ServiceHomeActivity;
import com.activity.ParentActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.BottomBarBinding;
import com.model.ServiceModule;
import com.utils.Utils;
import com.view.MTextView;
import com.view.bottombar.BottomBarItem;
import com.view.bottombar.OnItemSelectedListener;

import java.util.ArrayList;
import java.util.Objects;

public class AddBottomBarNew {

    @NonNull
    private final BottomBarBinding binding;
    private final Context mContext;
    private final GeneralFunctions generalFunc;

    private final boolean isNewHome_23, isNewHome_24;
    private int appThemeColor1, deSelectedColor;

    public AddBottomBarNew(Context mContext, GeneralFunctions generalFunc, LinearLayout parentView, boolean isNewHome_23, boolean isNewHome_24) {
        LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        this.binding = BottomBarBinding.inflate(inflater, parentView, false);
        parentView.addView(binding.getRoot());

        this.mContext = mContext;
        this.generalFunc = generalFunc;

        this.isNewHome_23 = isNewHome_23;
        this.isNewHome_24 = isNewHome_24;

        //
        setBottomView();
    }

    private void setBottomView() {
        binding.homeArea.setOnClickListener(new setOnClickList());
        binding.servicesArea.setOnClickListener(new setOnClickList());
        binding.rentItemPostListArea.setOnClickListener(new setOnClickList());
        binding.historyArea.setOnClickListener(new setOnClickList());
        binding.walletArea.setOnClickListener(new setOnClickList());
        binding.profileArea.setOnClickListener(new setOnClickList());

        binding.bottomBar.setVisibility(View.GONE);
        binding.bottomArea.setVisibility(View.GONE);
        if (isNewHome_24) {
            binding.bottomBar.setVisibility(View.VISIBLE);
            ArrayList<BottomBarItem> bottomBarItems = new ArrayList<>();
            bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HOME_BOTTOM_MENU"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_home_24)), new RectF(), 0));

            if (ServiceModule.isDeliverAllOnly()) {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_booking)), new RectF(), 0));
            } else if (ServiceModule.isOnlyBuySellRentEnable()) {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_BUY_SELL_RENT_POST_TXT"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_booking)), new RectF(), 0));
            } else if (ServiceModule.isRideOnly() || ServiceModule.RideDeliveryProduct || ServiceModule.isCubeXApp) {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_SERVICES"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_services)), new RectF(), 0));
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_BOOKINGS"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_booking)), new RectF(), 0));
            } else {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_BOOKINGS"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_booking)), new RectF(), 0));
            }

            if (ServiceModule.isRideOnly() || ServiceModule.RideDeliveryProduct || ServiceModule.isCubeXApp) {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_ACCOUNT_TXT"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_profile_24)), new RectF(), 0));
            } else {
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_WALLET"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_wallet)), new RectF(), 0));
                bottomBarItems.add(new BottomBarItem(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_PROFILE"), "", Objects.requireNonNull(ContextCompat.getDrawable(mContext, R.drawable.ic_profile_24)), new RectF(), 0));
            }
            binding.bottomBar.initBottomBar(bottomBarItems);
            binding.bottomBar.setOnItemSelectedListener((OnItemSelectedListener) pos -> {
                if (MyApp.getInstance().getCurrentAct() instanceof ParentActivity) {
                    ((ParentActivity) MyApp.getInstance().getCurrentAct()).onBottomTabSelected(pos);
                }
                if (mContext instanceof UberXHomeActivity activity && (pos == 1 && ServiceModule.isRideOnly() || ServiceModule.RideDeliveryProduct || ServiceModule.isCubeXApp)) {
                    activity.serviceAreaClickHandle();
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

            binding.rentItemPostListArea.setVisibility(View.GONE);
            binding.servicesArea.setVisibility(View.GONE);

            binding.homeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_BOTTOM_MENU"));
            binding.historyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_BOOKINGS"));
            binding.walletTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_WALLET"));
            binding.profileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_PROFILE"));

            if (ServiceModule.isDeliverAllOnly()) {
                binding.historyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS"));
            }
            if (ServiceModule.isRideOnly() || ServiceModule.RideDeliveryProduct || ServiceModule.isCubeXApp) {
                binding.servicesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICES"));
                binding.profileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ACCOUNT_TXT"));
                binding.servicesArea.setVisibility(View.VISIBLE);
                binding.walletArea.setVisibility(View.GONE);
            }
            if (ServiceModule.isOnlyBuySellRentEnable()) {
                binding.historyArea.setVisibility(View.GONE);
                binding.rentItemPostListArea.setVisibility(View.VISIBLE);
                binding.postText.setText(generalFunc.retrieveLangLBl("", "LBL_BUY_SELL_RENT_POST_TXT"));
            }

            manageBottomMenu(binding.homeTxt);
        }
        binding.homeArea.performClick();
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

        if (selTextView.getId() == binding.servicesTxt.getId()) {
            binding.servicesTxt.setTextColor(appThemeColor1);
            binding.servicesImg.setColorFilter(appThemeColor1, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.servicesImg.setImageResource(R.drawable.ic_services);
        } else {
            binding.servicesTxt.setTextColor(deSelectedColor);
            binding.servicesImg.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.servicesImg.setImageResource(R.drawable.ic_services);
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
        if (selTextView.getId() == binding.postText.getId()) {
            binding.postText.setTextColor(appThemeColor1);
            binding.postImg.setColorFilter(appThemeColor1, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.postImg.setImageResource(R.drawable.ic_booking_fill);
        } else {
            binding.postText.setTextColor(deSelectedColor);
            binding.postImg.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.postImg.setImageResource(R.drawable.ic_booking);
        }
    }

    public void manualClickView(int pos) {
        if (isNewHome_24) {
            binding.bottomBar.setSelectedItem(pos);
        } else if (isNewHome_23) {
            if (pos == 1) {
                if (ServiceModule.isOnlyBuySellRentEnable()) {
                    binding.rentItemPostListArea.performClick();
                } else if (ServiceModule.isRideOnly() || ServiceModule.RideDeliveryProduct || ServiceModule.isCubeXApp) {
                    binding.servicesArea.performClick();
                } else {
                    binding.historyArea.performClick();
                }
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
                if (generalFunc.prefHasKey(Utils.isMultiTrackRunning) && generalFunc.retrieveValue(Utils.isMultiTrackRunning).equalsIgnoreCase("Yes")) {
                    MyApp.getInstance().restartWithGetDataApp();
                } else {
                    if (mContext instanceof UberXHomeActivity activity) {
                        activity.setBottomFrg(0);
                    } else if (mContext instanceof FoodDeliveryHomeActivity activity) {
                        activity.manageHome();
                    } else if (mContext instanceof CommonDeliveryTypeSelectionActivity activity) {
                        activity.manageHome();
                    } else if (mContext instanceof ServiceHomeActivity activity) {
                        activity.manageHome();
                    } else if (mContext instanceof RideDeliveryActivity activity) {
                        activity.manageHome();
                    }
                }

            } else if (view.getId() == binding.servicesArea.getId() || view.getId() == binding.rentItemPostListArea.getId()) {
                manageBottomMenu(binding.servicesTxt);
                if (mContext instanceof UberXHomeActivity activity) {
                    activity.setBottomFrg(1);
                }

            } else if (view.getId() == binding.historyArea.getId()) {
                manageBottomMenu(binding.historyTxt);
                if (Utils.checkText(generalFunc.getMemberId())) {
                    if (mContext instanceof UberXHomeActivity activity) {
                        if (ServiceModule.isOnlyBuySellRentEnable() || ServiceModule.isRideOnly() || ServiceModule.RideDeliveryProduct || ServiceModule.isCubeXApp) {
                            activity.setBottomFrg(2);
                        } else {
                            activity.setBottomFrg(1);
                        }
                    } else if (mContext instanceof FoodDeliveryHomeActivity activity) {
                        activity.openHistoryFragment();
                    } else if (mContext instanceof CommonDeliveryTypeSelectionActivity activity) {
                        activity.openHistoryFragment();
                    } else if (mContext instanceof ServiceHomeActivity activity) {
                        activity.openHistoryFragment();
                    } else if (mContext instanceof RideDeliveryActivity activity) {
                        activity.openHistoryFragment();
                    }
                } else {
                    manageWithoutSignIn();
                }

            } else if (view.getId() == binding.walletArea.getId()) {
                manageBottomMenu(binding.walletTxt);
                if (Utils.checkText(generalFunc.getMemberId())) {
                    if (mContext instanceof UberXHomeActivity activity) {
                        activity.setBottomFrg(2);
                    } else if (mContext instanceof FoodDeliveryHomeActivity activity) {
                        activity.openWalletFragment();
                    } else if (mContext instanceof CommonDeliveryTypeSelectionActivity activity) {
                        activity.openWalletFragment();
                    } else if (mContext instanceof ServiceHomeActivity activity) {
                        activity.openWalletFragment();
                    } else if (mContext instanceof RideDeliveryActivity activity) {
                        activity.openWalletFragment();
                    }
                } else {
                    manageWithoutSignIn();
                }

            } else if (view.getId() == binding.profileArea.getId()) {
                manageBottomMenu(binding.profileTxt);
                if (mContext instanceof UberXHomeActivity activity) {
                    activity.setBottomFrg(3);
                } else if (mContext instanceof FoodDeliveryHomeActivity activity) {
                    activity.openProfileFragment();
                } else if (mContext instanceof CommonDeliveryTypeSelectionActivity activity) {
                    activity.openProfileFragment();
                } else if (mContext instanceof ServiceHomeActivity activity) {
                    activity.openProfileFragment();
                } else if (mContext instanceof RideDeliveryActivity activity) {
                    activity.openProfileFragment();
                }
            }
        }
    }

    private void manageWithoutSignIn() {
        if (mContext instanceof FoodDeliveryHomeActivity activity) {
            activity.openProfileFragment();
        } else if (mContext instanceof CommonDeliveryTypeSelectionActivity activity) {
            activity.openProfileFragment();
        } else if (mContext instanceof ServiceHomeActivity activity) {
            activity.openProfileFragment();
        }
    }
}