package com.act.rideSharingPro.fragmentHome;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.act.rideSharingPro.RideBookingRequestedActivity;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.fragment.RideBookingFragment;
import com.act.rideSharingPro.fragment.RidePublishFragment;
import com.adapter.files.ViewPagerAdapter;
import com.fragments.BaseFragment;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.main.R;
import com.utils.MyUtils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class RideSharingRidesFragment extends BaseFragment {

    @Nullable
    private RideSharingProHomeActivity mActivity;
    private View frgView;

    private ViewPager myRideViewPager;
    private final ArrayList<Fragment> fragmentList = new ArrayList<>();
    private RideBookingFragment bookingFrag;
    private RidePublishFragment publishFrag;
    public String vPublishParam = "", vBookingParam = "";
    public int vPublishPos = 0, vBookingPos = 0;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RideSharingProHomeActivity) {
            //TODO For better ram devices
            mActivity = (RideSharingProHomeActivity) requireActivity();
        } else {
            //TODO For slow ram devices
            mActivity = (RideSharingProHomeActivity) requireActivity();
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        frgView = inflater.inflate(R.layout.activity_ride_my_list, container, false);

        initialization();
        setViewPager();
        return frgView;
    }

    private void initialization() {
        assert mActivity != null;

        ImageView backImgView = frgView.findViewById(R.id.backImgView);
        backImgView.setVisibility(View.GONE);
        MTextView titleTxt = frgView.findViewById(R.id.titleTxt);
        titleTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_MY_RIDES_TXT"));
    }

    private void setViewPager() {
        assert mActivity != null;
        ArrayList<String> titleList = new ArrayList<>();

        titleList.add(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PUBLISHED_RIDE_TXT"));
        fragmentList.add(ridePublishFrag());

        titleList.add(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_BOOKED_RIDE"));
        fragmentList.add(rideBookingFrag());

        TabLayout material_tabs = frgView.findViewById(R.id.material_tabs);
        myRideViewPager = frgView.findViewById(R.id.myRideViewPager);

        ViewPagerAdapter adapter = new ViewPagerAdapter(mActivity.getSupportFragmentManager(), titleList.toArray(new CharSequence[titleList.size()]), fragmentList);
        myRideViewPager.setAdapter(adapter);
        material_tabs.setupWithViewPager(myRideViewPager);
    }

    public void setFrag(int pos) {
        new Handler().postDelayed(() -> {
            if (pos == myRideViewPager.getCurrentItem()) {
                fragmentList.get(pos).onResume();
            } else {
                myRideViewPager.setCurrentItem(pos);
            }
        }, 200);
    }

    private Fragment ridePublishFrag() {
        publishFrag = new RidePublishFragment();
        Bundle bn = new Bundle();
        bn.putString("TYPE_RIDE_PUBLISH", "GetPublishedRides");
        publishFrag.setArguments(bn);
        return publishFrag;
    }

    private Fragment rideBookingFrag() {
        bookingFrag = new RideBookingFragment();
        Bundle bn = new Bundle();
        bn.putString("TYPE_RIDE_BOOKING", "GetBookings");
        bookingFrag.setArguments(bn);
        return bookingFrag;
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == MyUtils.REFRESH_DATA_REQ_CODE && resultCode == Activity.RESULT_OK) {
            if (publishFrag != null) {
                publishFrag.getPublishRidesList(vPublishParam, false);
            }
            if (bookingFrag != null) {
                bookingFrag.GetBookingsRidesList(vBookingParam, false);
            }
        }
    }

    public void pubNubMsgArrived(String finalMsg) {
        assert mActivity != null;
        String msgType = mActivity.generalFunc.getJsonValue("MsgType", finalMsg);
        if (msgType.equalsIgnoreCase("AcceptPublishRide")
                || msgType.equalsIgnoreCase("CancelPublishRide")
                || msgType.equalsIgnoreCase("DeclinePublishRide")
                || msgType.equalsIgnoreCase("RideShareStartTrip")
                || msgType.equalsIgnoreCase("schedulePublishRideRide")
                || msgType.equalsIgnoreCase("RideShareEndTrip")
                || msgType.equalsIgnoreCase("RideSharePickup")) {

            mActivity.generalFunc.showGeneralMessage("", mActivity.generalFunc.getJsonValue("vTitle", finalMsg), i -> {
                if (publishFrag != null) {
                    publishFrag.getPublishRidesList(vPublishParam, false);
                }
                if (bookingFrag != null) {
                    bookingFrag.GetBookingsRidesList(vBookingParam, false);
                }
            });
        } else if (msgType.equalsIgnoreCase("RiderShareBooking")) {
            showRideShareBookingRequest(mActivity.generalFunc, finalMsg);
        }
    }

    private void showRideShareBookingRequest(GeneralFunctions generalFunc, String finalMsg) {
        if (MyApp.getInstance().getCurrentAct() instanceof RideBookingRequestedActivity) {
            return;
        }

        Bundle bn = new Bundle();
        bn.putSerializable("myRideDataHashMap", MyUtils.createHashMap(generalFunc, new HashMap<>(), generalFunc.getJsonObject("notiData", finalMsg)));
        bn.putBoolean("isFromRideShareRideFragment", true);
        if (mActivity != null) {
            mActivity.runOnUiThread(new Runnable() {
                @Override
                public void run() {
                    new ActUtils(mActivity).startActForResult(RideBookingRequestedActivity.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
                }
            });
        }
    }
}