package com.act.rideSharingPro;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.act.rideSharingPro.fragment.RideBookingFragment;
import com.act.rideSharingPro.fragment.RidePublishFragment;
import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.general.files.OpenMainProfile;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.main.R;
import com.utils.MyUtils;
import com.view.MTextView;

import java.util.ArrayList;

public class RideMyList extends ParentActivity {

    private final ArrayList<Fragment> fragmentList = new ArrayList<>();
    private RideBookingFragment bookingFrag;
    private RidePublishFragment publishFrag;
    public String vPublishParam = "", vBookingParam = "";
    public int vPublishPos = 0, vBookingPos = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_ride_my_list);

        initialization();
        setViewPager();
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_MY_RIDES_TXT"));
    }

    private void setViewPager() {
        ArrayList<String> titleList = new ArrayList<>();

        titleList.add(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PUBLISHED_RIDE_TXT"));
        fragmentList.add(ridePublishFrag());

        titleList.add(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_BOOKED_RIDE"));
        fragmentList.add(rideBookingFrag());

        TabLayout material_tabs = findViewById(R.id.material_tabs);
        ViewPager myRideViewPager = findViewById(R.id.myRideViewPager);

        ViewPagerAdapter adapter = new ViewPagerAdapter(getSupportFragmentManager(), titleList.toArray(new CharSequence[titleList.size()]), fragmentList);
        myRideViewPager.setAdapter(adapter);
        material_tabs.setupWithViewPager(myRideViewPager);
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
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == MyUtils.REFRESH_DATA_REQ_CODE && resultCode == Activity.RESULT_OK) {
            if (bookingFrag != null) {
                bookingFrag.GetBookingsRidesList(vBookingParam, false);
            }

            if (publishFrag != null) {
                publishFrag.getPublishRidesList(vPublishParam, false);
            }
        }
    }

    @Override
    public void onBackPressed() {
        if (getIntent().getBooleanExtra("isRestartApp", false)) {
            new OpenMainProfile(this, obj_userProfile.toString(), false, generalFunc).startProcess();
        } else {
            super.onBackPressed();
        }
    }

    public void pubNubMsgArrived(String finalMsg) {
        String msgType = generalFunc.getJsonValue("MsgType", finalMsg);
        if (msgType.equalsIgnoreCase("RideShareStartTrip") || msgType.equalsIgnoreCase("schedulePublishRideRide") || msgType.equalsIgnoreCase("RideShareEndTrip") || msgType.equalsIgnoreCase("RideSharePickup")) {
            generalFunc.showGeneralMessage("", generalFunc.getJsonValue("vTitle", finalMsg), i -> {
                if (bookingFrag != null) {
                    bookingFrag.GetBookingsRidesList(vBookingParam, false);
                }

                if (publishFrag != null) {
                    publishFrag.getPublishRidesList(vPublishParam, false);
                }
            });
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        }
    }
}