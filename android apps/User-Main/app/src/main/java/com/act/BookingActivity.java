package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.act.deliverAll.TrackOrderActivity;
import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.dialogs.OpenListView;
import com.fragments.BiddingBookingFragment;
import com.fragments.NewBookingFragment;
import com.fragments.OrderFragment;
import com.general.files.ActUtils;
import com.general.files.MyApp;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class BookingActivity extends ParentActivity {


    MTextView titleTxt;
    ImageView backImgView;
    public ImageView filterImageview;

    int selTabPos = 0;
    private String eType;

    ArrayList<HashMap<String, String>> filterlist;
    ArrayList<HashMap<String, String>> subFilterlist;
    ArrayList<HashMap<String, String>> orderSubFilterlist;
    ArrayList<HashMap<String, String>> biddingSubFilterlist;

    public String selFilterType = "";
    public String selSubFilterType = "";
    public String selOrderSubFilterType = "";
    public String selBiddingSubFilterType = "";


    public int filterPosition = 0;
    public int subFilterPosition = 0;
    public int orderSubFilterPosition = 0;
    public int biddingSubFilterPosition = 0;

    public ViewPager appLogin_view_pager;
    public ArrayList<Fragment> fragmentList = new ArrayList<>();
    public ArrayList<String> titleList = new ArrayList<>();
    CharSequence[] titles;
    NewBookingFragment frag;
    OrderFragment orderFrag;
    BiddingBookingFragment biddingFrag;


    boolean isrestart = false;  // For Bookings
    boolean isRestart = false; // for OnGoing trips
    boolean fromNoti = false; // for OnGoing trips
    private boolean isOrder = false; // for Order
    private boolean isCustmNoti = false; // for custom notification


    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.acitity_booking);

        isrestart = getIntent().getBooleanExtra("isrestart", false);
        isOrder = getIntent().getBooleanExtra("isOrder", false);
        isCustmNoti = getIntent().getBooleanExtra("isCustmNoti", false);
        isRestart = getIntent().getBooleanExtra("isRestart", false);
        fromNoti = getIntent().getBooleanExtra("fromNoti", false);

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        filterImageview = (ImageView) findViewById(R.id.filterImageview);
        addToClickHandler(filterImageview);
        addToClickHandler(backImgView);
        eType = generalFunc.getJsonValueStr("eType", obj_userProfile);
        setLabels();

        appLogin_view_pager = (ViewPager) findViewById(R.id.appLogin_view_pager);
        TabLayout material_tabs = (TabLayout) findViewById(R.id.material_tabs);
        LinearLayout tabArea = (LinearLayout) findViewById(R.id.tabArea);


        if (ServiceModule.Taxi || ServiceModule.Delivery || ServiceModule.ServiceProvider) {
            titleList.add(generalFunc.retrieveLangLBl("", "LBL_BOOKING"));
            fragmentList.add(generateBookingFrag(Utils.Upcoming));
        }
        if (ServiceModule.isAnyDeliverAllOptionEnable()) {
            titleList.add(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS"));
            fragmentList.add(generateOrderFrag(Utils.Past));
        }
        if (ServiceModule.ServiceBid) {
            titleList.add(generalFunc.retrieveLangLBl("", "LBL_BIDDING"));
            fragmentList.add(generateBiddingFrag(Utils.Past));
        }

        if (titleList.size() == 1) {
            tabArea.setVisibility(View.GONE);
        }

        titles = titleList.toArray(new CharSequence[titleList.size()]);
        ViewPagerAdapter adapter = new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList);
        appLogin_view_pager.setAdapter(adapter);
        material_tabs.setupWithViewPager(appLogin_view_pager);

        if (isOrder) {
            focusFragment(1);
            Bundle bn = new Bundle();
            bn.putString("iOrderId", getIntent().getStringExtra("iOrderId"));
            bn.putString("iServiceId", getIntent().getStringExtra("iServiceId"));
            bn.putBoolean("fromNoti", fromNoti);
            new ActUtils(getActContext()).startActWithData(TrackOrderActivity.class, bn);
        }


        appLogin_view_pager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {
                Logger.d("onPageScrolled", "::" + position);

            }

            @Override
            public void onPageSelected(int position) {
                selTabPos = position;
                Logger.d("onPageScrolled", "::" + "onPageSelected");
                fragmentList.get(position).onResume();
                selFilterType = "";
            }

            @Override
            public void onPageScrollStateChanged(int state) {
                Logger.d("onPageScrolled", "::" + "onPageScrollStateChanged");
            }
        });


        if (getIntent().getBooleanExtra("isBid", false) || getIntent().getIntExtra("viewPos", 0) == 2) {
            new Handler().postDelayed(new Runnable() {
                @Override
                public void run() {
                    focusFragment(2);
                }
            }, 200);

        }


    }

    public void setFrag(int pos) {
        new Handler().postDelayed(() -> {
            if (pos == appLogin_view_pager.getCurrentItem()) {
                fragmentList.get(pos).onResume();
            } else {
                appLogin_view_pager.setCurrentItem(pos);
            }
        }, 200);

    }

    public void focusFragment(int pos) {
        appLogin_view_pager.setCurrentItem(pos);
    }

    private Fragment generateBiddingFrag(String past) {
        biddingFrag = new BiddingBookingFragment();
        Bundle bn = new Bundle();
        bn.putString("HISTORY_TYPE", "getBiddingPosts");
        biddingFrag.setArguments(bn);
        return biddingFrag;
    }

    private Fragment generateOrderFrag(String past) {
        orderFrag = new OrderFragment();
        Bundle bn = new Bundle();
        bn.putString("HISTORY_TYPE", "DisplayActiveOrder");
        orderFrag.setArguments(bn);
        return orderFrag;
    }

    private Fragment generateBookingFrag(String upcoming) {
        frag = new NewBookingFragment();
        Bundle bn = new Bundle();
        bn.putString("HISTORY_TYPE", "getMemberBookings");
        frag.setArguments(bn);

        return frag;
    }

    public NewBookingFragment getNewBookingFrag() {

        if (frag != null) {
            Bundle bn = new Bundle();
            bn.putString("HISTORY_TYPE", "getMemberBookings");
            frag.setArguments(bn);
            return frag;
        }
        return null;
    }

    public BiddingBookingFragment getBiddingFrag() {

        if (biddingFrag != null) {
            return biddingFrag;
        }
        return null;
    }


    public OrderFragment getOrderFrag() {

        if (orderFrag != null) {
            return orderFrag;
        }
        return null;
    }

    public void subFilterManage(ArrayList<HashMap<String, String>> filterlist, String type) {

        if (type.equalsIgnoreCase("Order")) {
            this.orderSubFilterlist = filterlist;
        } else if (type.equalsIgnoreCase("Bidding")) {
            this.biddingSubFilterlist = filterlist;
        } else {
            this.subFilterlist = filterlist;
        }
    }

    private void setLabels() {
        String menuMsgYourTrips = "";
        if (ServiceModule.isRideOnly()) {
            menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_YOUR_TRIPS");
        } else if (ServiceModule.isDeliveronly()) {
            menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_YOUR_DELIVERY");
        } else if (ServiceModule.isServiceProviderOnly()) {
            menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_YOUR_BOOKING");
        } else if (ServiceModule.isDeliverAllOnly()) {
            menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS");
        } else {
            menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_YOUR_BOOKING");
        }
        titleTxt.setText(menuMsgYourTrips);
    }

    @Override
    protected void onResume() {
        super.onResume();
    }

    public Context getActContext() {
        return BookingActivity.this;
    }

    @Override
    public void onBackPressed() {

        String vTripStatus = generalFunc.getJsonValueStr("vTripStatus", obj_userProfile);
        String DELIVERY_CATEGORY_ID = generalFunc.getJsonValueStr("DELIVERY_CATEGORY_ID", obj_userProfile);
        String DELIVERY_CATEGORY_NAME = generalFunc.getJsonValueStr("DELIVERY_CATEGORY_NAME", obj_userProfile);


        if (isOrder) {
            MyApp.getInstance().restartWithGetDataApp();
        } else if (isRestart) {
            if (ServiceModule.RideDeliveryUbexProduct) {
                if ((vTripStatus.equalsIgnoreCase("Active") ||
                        vTripStatus.equalsIgnoreCase("On Going Trip")) && !eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                    if (getIntent().hasExtra("isTripRunning")) {
                        MyApp.getInstance().restartWithGetDataApp();
                    }

                } else if (generalFunc.prefHasKey(Utils.isMultiTrackRunning) && generalFunc.retrieveValue(Utils.isMultiTrackRunning).equalsIgnoreCase("Yes")) {
                    MyApp.getInstance().restartWithGetDataApp();
                } else if (getIntent().getBooleanExtra("isRestart", false)) {

                    Bundle bn = new Bundle();
                    new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                    finishAffinity();
                } else {

                    super.onBackPressed();
                }
            } else if (ServiceModule.RideDeliveryProduct || ServiceModule.isDeliveronly()) {

                if ((vTripStatus.equalsIgnoreCase("Active") ||
                        vTripStatus.equalsIgnoreCase("On Going Trip")) && !eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                    if (getIntent().hasExtra("isTripRunning")) {
                        MyApp.getInstance().restartWithGetDataApp();
                    }

                } else if (generalFunc.prefHasKey(Utils.isMultiTrackRunning) && generalFunc.retrieveValue(Utils.isMultiTrackRunning).equalsIgnoreCase("Yes")) {
                    MyApp.getInstance().restartWithGetDataApp();
                } else if (getIntent().getBooleanExtra("isRestart", false)) {
                    Bundle bn = new Bundle();

                    if (generalFunc.getJsonValueStr("ENABLE_RIDE_DELIVERY_NEW_FLOW", obj_userProfile).equals("Yes")) {
                        if (generalFunc.getJsonValue("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile).equals("Yes")
                                || generalFunc.getJsonValue("ENABLE_APP_HOME_SCREEN_24", obj_userProfile).equals("Yes")) {
                            new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                        } else {
                            new ActUtils(getActContext()).startActWithData(RideDeliveryActivity.class, bn);
                        }
                    } else {


                        bn.putString("iVehicleCategoryId", DELIVERY_CATEGORY_ID);
                        bn.putString("vCategory", DELIVERY_CATEGORY_NAME);
                        new ActUtils(getActContext()).startActWithData(CommonDeliveryTypeSelectionActivity.class, bn);
                    }

                    finishAffinity();
                } else if (ServiceModule.isServiceProviderOnly()) {
                    Bundle bn = new Bundle();
                    new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                    finishAffinity();
                } else {

                    super.onBackPressed();
                }
            } else if (ServiceModule.isServiceProviderOnly()) {
                Bundle bn = new Bundle();
                new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                finishAffinity();
            } else {
                super.onBackPressed();
            }
        } else if (isrestart) {
            Bundle bn = new Bundle();

            if (ServiceModule.isServiceProviderOnly()) {
                new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
            } else if (ServiceModule.isDeliveronly()) {
                bn.putString("iVehicleCategoryId", DELIVERY_CATEGORY_ID);
                bn.putString("vCategory", DELIVERY_CATEGORY_NAME);


                if (generalFunc.getJsonValueStr("PACKAGE_TYPE", obj_userProfile).equalsIgnoreCase("STANDARD")) {
                    new ActUtils(getActContext()).startActWithData(MainActivity.class, bn);
                } else {
                    if (generalFunc.getJsonValueStr("ENABLE_RIDE_DELIVERY_NEW_FLOW", obj_userProfile).equals("Yes")) {
                        if (generalFunc.getJsonValue("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile).equals("Yes")
                                || generalFunc.getJsonValue("ENABLE_APP_HOME_SCREEN_24", obj_userProfile).equals("Yes")) {
                            new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                        } else {
                            new ActUtils(getActContext()).startActWithData(RideDeliveryActivity.class, bn);
                        }
                    } else {
                        new ActUtils(getActContext()).startActWithData(CommonDeliveryTypeSelectionActivity.class, bn);
                    }
                }

            } else if (ServiceModule.RideDeliveryProduct) {
                if (generalFunc.getJsonValueStr("ENABLE_RIDE_DELIVERY_NEW_FLOW", obj_userProfile).equals("Yes")) {
                    if (generalFunc.getJsonValue("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile).equals("Yes")
                            || generalFunc.getJsonValue("ENABLE_APP_HOME_SCREEN_24", obj_userProfile).equals("Yes")) {
                        new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                    } else {
                        new ActUtils(getActContext()).startActWithData(RideDeliveryActivity.class, bn);
                    }
                } else {
                    bn.putString("iVehicleCategoryId", DELIVERY_CATEGORY_ID);
                    bn.putString("vCategory", DELIVERY_CATEGORY_NAME);
                    new ActUtils(getActContext()).startActWithData(CommonDeliveryTypeSelectionActivity.class, bn);
                }
            } else {
                if (getIntent().getStringExtra("selType") != null) {
                    bn.putString("selType", getIntent().getStringExtra("selType"));
                    new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                } else {
                    new ActUtils(getActContext()).startActWithData(MainActivity.class, bn);
                }

            }
            finishAffinity();
        } else {
            super.onBackPressed();
        }
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.backImgView) {
            onBackPressed();
        } else if (id == R.id.filterImageview) {
            BuildType("Normal");
        }
    }


    public void BuildType(String type) {
        ArrayList<String> arrayList = populateSubArrayList(type);

        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("Select Type", "LBL_SELECT_TYPE"), arrayList, OpenListView.OpenDirection.BOTTOM, true, true, position -> {
            if (type.equalsIgnoreCase("Order")) {
                orderSubFilterPosition = position;
                selOrderSubFilterType = orderSubFilterlist.get(position).get("vSubFilterParam");
                getOrderFrag().filterTxt.setText(orderSubFilterlist.get(position).get("vTitle"));
            } else if (type.equalsIgnoreCase("History")) {
                subFilterPosition = position;
                selSubFilterType = subFilterlist.get(position).get("vSubFilterParam");
                getNewBookingFrag().filterTxt.setText(subFilterlist.get(position).get("vTitle"));
            } else if (type.equalsIgnoreCase("Bidding")) {

                biddingSubFilterPosition = position;
                selBiddingSubFilterType = biddingSubFilterlist.get(position).get("vSubFilterParam");
                getBiddingFrag().filterTxt.setText(biddingSubFilterlist.get(position).get("vTitle"));
            } else {
                filterPosition = position;
                selFilterType = filterlist.get(position).get("vFilterParam");
            }
            fragmentList.get(appLogin_view_pager.getCurrentItem()).onResume();

        }).show(populatePos(type), "vTitle");
    }

    private ArrayList<String> populateSubArrayList(String BuildType) {
        ArrayList<String> typeNameList = new ArrayList<>();
        ArrayList<HashMap<String, String>> filterArrayList;
        if (getNewBookingFrag() != null) {
            filterlist = getNewBookingFrag().filterlist;
        }
        if (BuildType.equalsIgnoreCase("Order")) {
            filterArrayList = orderSubFilterlist;
        } else if (BuildType.equalsIgnoreCase("Bidding")) {
            filterArrayList = biddingSubFilterlist;
        } else {
            filterArrayList = (BuildType.equalsIgnoreCase("History") ? subFilterlist : filterlist);
        }
        if (filterArrayList != null && filterArrayList.size() > 0) {
            for (int i = 0; i < filterArrayList.size(); i++) {
                typeNameList.add((filterArrayList.get(i).get("vTitle")));
            }
        }
        return typeNameList;
    }

    private int populatePos(String BuildType) {
        if (BuildType.equalsIgnoreCase("Order")) {
            return orderSubFilterPosition;
        } else if (BuildType.equalsIgnoreCase("Bidding")) {
            return biddingSubFilterPosition;
        } else {
            return (BuildType.equalsIgnoreCase("History") ? subFilterPosition : filterPosition);

        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.ASSIGN_DRIVER_CODE) {

            if (data != null && data.hasExtra("callGetDetail")) {
                MyApp.getInstance().restartWithGetDataApp();
            } else {
                String vTripStatus = generalFunc.getJsonValueStr("vTripStatus", obj_userProfile);
                boolean checkCond = (vTripStatus.equalsIgnoreCase("Active") ||
                        vTripStatus.equalsIgnoreCase("On Going Trip")) && !eType.equalsIgnoreCase(Utils.CabGeneralType_UberX);
//                if (ServiceModule.RideDeliveryUbexProduct) {
//                    if (checkCond) {
//
//                    } else {
//
//                        Bundle bn = new Bundle();
//                        new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
//                        finishAffinity();
//                    }
//                } else {
                if (!checkCond) {
                    Bundle bn = new Bundle();
                    new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                    finishAffinity();
                }
//                }
            }
        } else if (requestCode == Utils.LIVE_TRACK_REQUEST_CODE) {
            selSubFilterType = "";
        }

    }
}
