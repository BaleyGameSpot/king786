package com.fragments;

import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.adapter.files.ViewPagerAdapter;
import com.dialogs.OpenListView;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class MyBookingFragment extends BaseFragment {

    public GeneralFunctions generalFunc;
    MTextView titleTxt;
    ImageView backImgView;
    String userProfileJson;
    public ImageView filterImageview;

    int selTabPos = 0;
    public ArrayList<HashMap<String, String>> filterlist;
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

    ViewPager appLogin_view_pager;
    ArrayList<Fragment> fragmentList = new ArrayList<>();
    ArrayList<String> titleList = new ArrayList<>();
    CharSequence[] titles;
    NewBookingFragment frag;
    BiddingBookingFragment biddingfrag;
    OrderFragment orderFrag;

    View view;
    ViewPagerAdapter adapter;
    TabLayout material_tabs;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {

        if (view != null) {
            return view;
        }

        view = inflater.inflate(R.layout.acitity_booking, container, false);

        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());

        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

        titleTxt = (MTextView) view.findViewById(R.id.titleTxt);
        backImgView = (ImageView) view.findViewById(R.id.backImgView);
        backImgView.setVisibility(View.GONE);
        filterImageview = (ImageView) view.findViewById(R.id.filterImageview);
        addToClickHandler(filterImageview);
        addToClickHandler(backImgView);
        setLabels();

        appLogin_view_pager = (ViewPager) view.findViewById(R.id.appLogin_view_pager);
        material_tabs = (TabLayout) view.findViewById(R.id.material_tabs);
        LinearLayout tabArea = (LinearLayout) view.findViewById(R.id.tabArea);


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
        adapter = new ViewPagerAdapter(getActivity().getSupportFragmentManager(), titles, fragmentList);
        appLogin_view_pager.setAdapter(adapter);
        material_tabs.setupWithViewPager(appLogin_view_pager);
        appLogin_view_pager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {
            }

            @Override
            public void onPageSelected(int position) {
                selTabPos = position;
                fragmentList.get(position).onResume();
                filterPosition = 0;
                selFilterType = "";

            }

            @Override
            public void onPageScrollStateChanged(int state) {
            }

        });
        return view;
    }

    public void setFrag(int pos) {
        if (pos == appLogin_view_pager.getCurrentItem()) {
            fragmentList.get(pos).onResume();
        } else {
            appLogin_view_pager.setCurrentItem(pos);
        }

    }

    public Fragment generateOrderFrag(String past) {
        orderFrag = new OrderFragment();
        Bundle bn = new Bundle();
        bn.putString("HISTORY_TYPE", "DisplayActiveOrder");

        orderFrag.setArguments(bn);
        return orderFrag;
    }

    private Fragment generateBiddingFrag(String past) {
        biddingfrag = new BiddingBookingFragment();
        Bundle bn = new Bundle();
        bn.putString("HISTORY_TYPE", "getBiddingPosts");
        biddingfrag.setArguments(bn);
        return biddingfrag;
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

        if (biddingfrag != null) {
            Bundle bn = new Bundle();
            bn.putString("HISTORY_TYPE", "getBiddingPosts");
            biddingfrag.setArguments(bn);
            return biddingfrag;
        }
        return null;
    }

    public OrderFragment getOrderFrag() {

        if (orderFrag != null) {
            Bundle bn = new Bundle();
            bn.putString("HISTORY_TYPE", "DisplayActiveOrder");
            orderFrag.setArguments(bn);
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


    public Context getActContext() {
        return getActivity();
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.backImgView) {
            getActivity().onBackPressed();
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
                if (getOrderFrag() != null) {
                    getOrderFrag().filterTxt.setText(orderSubFilterlist.get(position).get("vTitle"));
                }
            } else if (type.equalsIgnoreCase("History")) {
                subFilterPosition = position;
                selSubFilterType = subFilterlist.get(position).get("vSubFilterParam");
                if (getNewBookingFrag() != null) {
                    getNewBookingFrag().filterTxt.setText(subFilterlist.get(position).get("vTitle"));
                }
            } else if (type.equalsIgnoreCase("Bidding")) {

                biddingSubFilterPosition = position;
                selBiddingSubFilterType = biddingSubFilterlist.get(position).get("vSubFilterParam");
                if (getBiddingFrag() != null) {
                    getBiddingFrag().filterTxt.setText(biddingSubFilterlist.get(position).get("vTitle"));
                }
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


}
