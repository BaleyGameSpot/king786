package com.act;

import android.content.Context;
import android.location.Location;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.dialogs.OpenListView;
import com.fragments.BiddingBookingFragment;
import com.fragments.HistoryFragment;
import com.fragments.OrderFragment;
import com.general.files.GetLocationUpdates;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class BookingsActivity extends ParentActivity implements GetLocationUpdates.LocationUpdatesListener {

    MTextView titleTxt;
    ImageView backImgView;
    CharSequence[] titles;
    public Location userLocation;

    int selTabPos = 0;
    ArrayList<HashMap<String, String>> filterlist = new ArrayList<>();
    ArrayList<HashMap<String, String>> subFilterlist = new ArrayList<>();
    ArrayList<HashMap<String, String>> biddingSubFilterlist = new ArrayList<>();
    ArrayList<HashMap<String, String>> orderSubFilterlist = new ArrayList<>();

    public String selFilterType = "";
    public String selSubFilterType = "";
    public String biddingSelSubFilterType = "";
    public String selOrderSubFilterType = "";

    public int subFilterPosition = 0;
    public int orderSubFilterPosition = 0;
    public int biddingSubFilterPosition = 0;
    public int filterPosition = 0;

    public ImageView filterImageview;
    public ArrayList<Fragment> fragmentList = new ArrayList<>();
    ArrayList<String> titleList = new ArrayList<>();
    public ViewPager appLogin_view_pager;

    HistoryFragment frag;
    OrderFragment orderFrag;
    BiddingBookingFragment biddingfrag;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_history);

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        filterImageview = (ImageView) findViewById(R.id.filterImageview);
        addToClickHandler(backImgView);
        addToClickHandler(filterImageview);
        setLabels();
        appLogin_view_pager = (ViewPager) findViewById(R.id.appLogin_view_pager);
        TabLayout material_tabs = (TabLayout) findViewById(R.id.material_tabs);
        LinearLayout tablayoutArea = (LinearLayout) findViewById(R.id.tablayoutArea);
        LinearLayout headerArea = (LinearLayout) findViewById(R.id.headerArea);

        if (ServiceModule.bookingView()) {
            titleList.add(generalFunc.retrieveLangLBl("", "LBL_BOOKING"));
            fragmentList.add(generateBookingFrag());
        }
        if (ServiceModule.orderView()) {
            titleList.add(generalFunc.retrieveLangLBl("", "LBL_ORDERS_TAB_TXT"));
            fragmentList.add(generateOrderFrag());
        }
        if (ServiceModule.bidView()) {
            titleList.add(generalFunc.retrieveLangLBl("", "LBL_BIDDING_TXT"));
            fragmentList.add(generateBiddingFrag());
        }

        if (titleList.size() == 1) {
            tablayoutArea.setVisibility(View.GONE);
            headerArea.setPadding(0, 0, 0, 0);
        }

        titles = titleList.toArray(new CharSequence[titleList.size()]);

        ViewPagerAdapter adapter = new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList);
        appLogin_view_pager.setAdapter(adapter);
        if (getIntent().hasExtra("viewPos")) {
            setFrag(getIntent().getIntExtra("viewPos", 0));
        }
        material_tabs.setupWithViewPager(appLogin_view_pager);

        appLogin_view_pager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {
            }

            @Override
            public void onPageSelected(int position) {
                selTabPos = position;
                selFilterType = "";
                fragmentList.get(position).onResume();
            }

            @Override
            public void onPageScrollStateChanged(int state) {
            }
        });

    }

    public void filterManage(ArrayList<HashMap<String, String>> filterlist) {
        if (ServiceModule.IsTrackingProvider) {
            return;
        }
        this.filterlist = filterlist;
        if (filterlist.size() > 0 && (appLogin_view_pager != null && appLogin_view_pager.getCurrentItem() == 0)) {
            filterImageview.setVisibility(View.VISIBLE);
        } else {
            filterImageview.setVisibility(View.GONE);
        }
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

    @Override
    public void onLocationUpdate(Location location) {
        this.userLocation = location;
    }

    public void stopLocUpdates() {
        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
    }

    public void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_BOOKINGS"));
        if (ServiceModule.isRideOnly()) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_TRIPS"));
        } else if (ServiceModule.isDeliveronly()) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_DELIVERY"));
        } else if (ServiceModule.isServiceProviderOnly()) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_BOOKING"));
        } else if (ServiceModule.isDeliverAllOnly()) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS"));
        } else if (ServiceModule.IsTrackingProvider) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRIP"));
        }
    }

    public HistoryFragment generateBookingFrag() {
        frag = new HistoryFragment();
        Bundle bn = new Bundle();
        bn.putString("BOOKING_TYPE", "bookingHistory");
        if (getIntent().getStringExtra("isView") != null) {
            selSubFilterType = getIntent().getStringExtra("isView");
        }
        frag.setArguments(bn);
        return frag;
    }

    public OrderFragment generateOrderFrag() {
        orderFrag = new OrderFragment();
        Bundle bn = new Bundle();
        bn.putString("BOOKING_TYPE", "orderHistory");
        orderFrag.setArguments(bn);
        return orderFrag;
    }

    private Fragment generateBiddingFrag() {
        biddingfrag = new BiddingBookingFragment();
        Bundle bn = new Bundle();
        bn.putString("BOOKING_TYPE", "biddingHistory");
        biddingfrag.setArguments(bn);
        return biddingfrag;
    }

    public BiddingBookingFragment getBiddingFrag() {
        if (biddingfrag != null) {
            return biddingfrag;
        }
        return null;
    }

    public void setFrag(int pos) {
        if (pos == appLogin_view_pager.getCurrentItem()) {
            fragmentList.get(pos).onResume();
        } else {
            appLogin_view_pager.setCurrentItem(pos);
        }
    }

    public HistoryFragment getHistoryFrag() {

        if (frag != null) {
            return frag;
        }
        return null;
    }


    public OrderFragment getOrderFrag() {

        if (orderFrag != null) {
            return orderFrag;
        }
        return null;
    }


    public Context getActContext() {
        return BookingsActivity.this;
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == backImgView.getId()) {
            BookingsActivity.super.onBackPressed();
        } else if (i == filterImageview.getId()) {
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
                Logger.d("subFilterlist_", "" + subFilterlist.toString());
                selSubFilterType = subFilterlist.get(position).get("vSubFilterParam");
                getHistoryFrag().filterTxt.setText(subFilterlist.get(position).get("vTitle"));

                if (subFilterlist.get(position).get("vSubFilterParam").equalsIgnoreCase("past")) {
                    getHistoryFrag().calenderHeaderLayout.setVisibility(View.VISIBLE);

                } else {
                    getHistoryFrag().calenderHeaderLayout.setVisibility(View.GONE);
                }

                getHistoryFrag().isNextPageAvailable = false;
                getHistoryFrag().next_page_str = "";
            } else if (type.equalsIgnoreCase("Bidding")) {
                biddingSubFilterPosition = position;
                biddingSelSubFilterType = biddingSubFilterlist.get(position).get("vSubFilterParam");
                getBiddingFrag().filterTxt.setText(biddingSubFilterlist.get(position).get("vTitle"));

                if (biddingSubFilterlist.get(position).get("vSubFilterParam").equalsIgnoreCase("past")) {
                    getBiddingFrag().calenderHeaderLayout.setVisibility(View.VISIBLE);
                } else {
                    getBiddingFrag().calenderHeaderLayout.setVisibility(View.GONE);
                }

                getBiddingFrag().isNextPageAvailable = false;
                getBiddingFrag().next_page_str = "";
            } else {
                filterPosition = position;
                selFilterType = filterlist.get(position).get("vFilterParam");
            }
            fragmentList.get(appLogin_view_pager.getCurrentItem()).onResume();

        }).show(populatePos(type), "vTitle");

    }

    private ArrayList<String> populateSubArrayList(String BuildType) {
        ArrayList<String> typeNameList = new ArrayList<>();
        ArrayList<HashMap<String, String>> filterArrayList = BuildType.equalsIgnoreCase("Order") ? orderSubFilterlist : (BuildType.equalsIgnoreCase("History") ? subFilterlist : (BuildType.equalsIgnoreCase("Bidding") ? biddingSubFilterlist : filterlist));
        if (filterArrayList != null && filterArrayList.size() > 0) {
            for (int i = 0; i < filterArrayList.size(); i++) {
                typeNameList.add((filterArrayList.get(i).get("vTitle")));
            }
        }
        return typeNameList;
    }

    private int populatePos(String BuildType) {
        return BuildType.equalsIgnoreCase("Order") ? orderSubFilterPosition : (BuildType.equalsIgnoreCase("History") ? subFilterPosition : (BuildType.equalsIgnoreCase("Bidding") ? biddingSubFilterPosition : filterPosition));
    }

    public boolean checkCurrentFragment(int callFrag) {
        if (fragmentList != null && fragmentList.size() > 0) {
            String bookingType = fragmentList.get(appLogin_view_pager.getCurrentItem()).getArguments().getString("BOOKING_TYPE", "");
            if (callFrag == 1 && bookingType.equalsIgnoreCase("bookingHistory")) {
                return true;
            } else if (callFrag == 2 && bookingType.equalsIgnoreCase("orderHistory")) {
                return true;
            } else if (callFrag == 3 && bookingType.equalsIgnoreCase("biddingHistory")) {
                return true;
            }
        }
        return false;
    }
}
