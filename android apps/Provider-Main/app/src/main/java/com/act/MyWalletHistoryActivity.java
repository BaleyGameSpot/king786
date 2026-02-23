package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.dialogs.FilterDateRangeDialog;
import com.dialogs.OpenBottomListView;
import com.fragments.WalletFragment;
import com.general.DatePicker;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.providers.R;
import com.utils.DateTimeUtils;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Locale;

/**
 * Created by Admin on 04-11-2016.
 */
public class MyWalletHistoryActivity extends ParentActivity {

    MTextView titleTxt;
    ImageView filterImageView;
    CharSequence[] titles;
    boolean isFilterShow = false;

    private String selectedDateRange = "", selectedStartDate = "", selectedEndDate = "";
    private FilterDateRangeDialog mPreferenceDailog;

    private final ArrayList<HashMap<String, String>> filterTypeList = new ArrayList<>();
    private int selFilterPosition = -1;

    ArrayList<Fragment> fragmentList = new ArrayList<>();
    ViewPagerAdapter adapter;
    ViewPager appLogin_view_pager;
    TabLayout material_tabs;
    private String SELECTED_DATE = "";

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_mywallet_history);

        isFilterShow = getIntent().getBooleanExtra("seeAll", false);

        titleTxt = findViewById(R.id.titleTxt);
        ImageView backImgView = findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        filterImageView = findViewById(R.id.filterImageview);
        addToClickHandler(filterImageView);
        setLabels();
        if (isFilterShow) {
            filterImageView.setVisibility(View.VISIBLE);
        }

        appLogin_view_pager = findViewById(R.id.appLogin_view_pager);
        material_tabs = findViewById(R.id.material_tabs);

        titles = new CharSequence[]{generalFunc.retrieveLangLBl("", "LBL_ALL"), generalFunc.retrieveLangLBl("", "LBL_CREDIT"), generalFunc.retrieveLangLBl("", "LBL_DEBIT")};
        material_tabs.setVisibility(View.VISIBLE);
        fragmentList.add(generateWalletFrag(Utils.Wallet_all));
        fragmentList.add(generateWalletFrag(Utils.Wallet_credit));
        fragmentList.add(generateWalletFrag(Utils.Wallet_debit));

        adapter = new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList);
        appLogin_view_pager.setAdapter(adapter);
        material_tabs.setupWithViewPager(appLogin_view_pager);

        initCalender();
    }

    private void initCalender() {
        Date var3 = Utils.convertStringToDate(DateTimeUtils.DayFormatEN, generalFunc.getJsonValueStr("RegistrationDate", obj_userProfile));
        Calendar registrationDateCal = Calendar.getInstance();
        registrationDateCal.setTime(var3);

        Calendar regisDateCal = (Calendar) registrationDateCal.clone();


        @SuppressLint("SimpleDateFormat") SimpleDateFormat serverDateFormat = new SimpleDateFormat(DateTimeUtils.DayFormatEN);
        mPreferenceDailog = new FilterDateRangeDialog(getActContext(), generalFunc, regisDateCal);
        mPreferenceDailog.setCalendarListener((startDate, endDate) -> {
            mPreferenceDailog.dialogDismiss();
            int rangeListPosition = getPosition("range");
            if (startDate != null && endDate != null && rangeListPosition != -1) {
                selFilterPosition = rangeListPosition;
                selectedStartDate = serverDateFormat.format(startDate.getTime());
                selectedEndDate = serverDateFormat.format(endDate.getTime());
            }
            resetData();
        });
    }

    private int getPosition(String dateRange) {
        int position = -1;
        for (int i = 0; i < filterTypeList.size(); i++) {
            if (filterTypeList.get(i).get("dateRange").equalsIgnoreCase(dateRange)) {
                position = i;
                break;
            }
        }
        return position;
    }

    private void resetData() {
        fragmentList.clear();
        fragmentList.add(generateWalletFrag(Utils.Wallet_all));
        fragmentList.add(generateWalletFrag(Utils.Wallet_credit));
        fragmentList.add(generateWalletFrag(Utils.Wallet_debit));
        adapter = new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList);
        appLogin_view_pager.setAdapter(adapter);
        material_tabs.setupWithViewPager(appLogin_view_pager);
    }

    private WalletFragment generateWalletFrag(String bookingType) {
        WalletFragment frag = new WalletFragment();
        Bundle bn = new Bundle();
        bn.putString("ListType", bookingType);
        bn.putString("dateRange", selectedDateRange);
        bn.putString("startDate", selectedStartDate);
        bn.putString("endDate", selectedEndDate);
        frag.setArguments(bn);
        return frag;
    }

    private void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_Transaction_HISTORY"));
    }

    public Context getActContext() {
        return MyWalletHistoryActivity.this;
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.backImgView) {
            MyWalletHistoryActivity.super.onBackPressed();
        } else if (id == R.id.filterImageview) {
            openFilterDialog();
        }
    }


    private void openFilterDialog() {
        buildFilterList();

        OpenBottomListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("Select Date Range", "LBL_SELECT_DATE_RANGE"), filterTypeList, OpenBottomListView.OpenDirection.CENTER, true, position -> {
            selectedDateRange = filterTypeList.get(position).get("dateRange");
            if (selectedDateRange.equals("range")) {
                openCalenderView();
            } else {
                selFilterPosition = position;
                resetData();
            }
        }, true, generalFunc.retrieveLangLBl("", ""), true).show(selFilterPosition, "vName");
    }

    private void openCalenderView() {
        //select 7 days logic
        //Calendar startCalendar = (Calendar) Calendar.getInstance().clone();
        //mPreferenceDailog.showPreferenceDialog(startCalendar, Calendar.getInstance());

        Calendar now = Calendar.getInstance(MyUtils.getLocale());
        SimpleDateFormat date_format = new SimpleDateFormat(DateTimeUtils.DayFormatEN, Locale.US);
        SELECTED_DATE = date_format.format(now.getTime());

        Date mSelectedCal = Utils.convertStringToDate(DateTimeUtils.DayFormatEN, generalFunc.getJsonValueStr("RegistrationDate", obj_userProfile));
        Calendar registrationDate = Calendar.getInstance();
        registrationDate.setTime(mSelectedCal);

        DatePicker.showRange(getActContext(), generalFunc, registrationDate, Calendar.getInstance(), SELECTED_DATE, null,
                (sYear, sMonthOfYear, sDayOfMonth, eYear, eMonthOfYear, eDayOfMonth) -> {
                    SimpleDateFormat date_format1 = new SimpleDateFormat(DateTimeUtils.DayFormatEN, Locale.US);
                    int rangeListPosition = getPosition("range");
                    if (rangeListPosition != -1) {
                        try {
                            Date calStart = date_format1.parse(sYear + "-" + sMonthOfYear + "-" + sDayOfMonth);
                            Date calEnd = date_format1.parse(eYear + "-" + eMonthOfYear + "-" + eDayOfMonth);
                            if (calStart != null && calEnd != null) {
                                selFilterPosition = rangeListPosition;
                                selectedStartDate = date_format1.format(calStart.getTime());
                                selectedEndDate = date_format1.format(calEnd.getTime());
                                resetData();
                            }
                        } catch (ParseException e) {
                            Logger.e("Exception", "::" + e.getMessage());
                        }
                    }
                });
    }

    private void buildFilterList() {
        filterTypeList.clear();

        HashMap<String, String> filte1 = new HashMap<>();
        filte1.put("vName", generalFunc.retrieveLangLBl("Today", "LBL_TODAY_TXT"));
        filte1.put("isShowForward", "No");
        filte1.put("dateRange", "today");
        filterTypeList.add((HashMap<String, String>) filte1.clone());
        filte1.put("vName", generalFunc.retrieveLangLBl("This Week", "LBL_THIS_WEEK_TXT"));
        filte1.put("isShowForward", "No");
        filte1.put("dateRange", "week");
        filterTypeList.add((HashMap<String, String>) filte1.clone());
        filte1.put("vName", generalFunc.retrieveLangLBl("This Month", "LBL_THIS_MONTH"));
        filte1.put("isShowForward", "No");
        filte1.put("dateRange", "month");
        filterTypeList.add((HashMap<String, String>) filte1.clone());

        Date strDate = Utils.convertStringToDate(DateTimeUtils.DayFormatEN, generalFunc.getJsonValueStr("RegistrationDate", obj_userProfile));
        Date crtDate = Utils.convertStringToDate(DateTimeUtils.DayFormatEN, Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, new Date()));
        if (!crtDate.equals(strDate)) {
            filte1.put("vName", generalFunc.retrieveLangLBl("Select Date Range", "LBL_SELECT_DATE_RANGE"));
            filte1.put("isShowForward", "Yes");
            filte1.put("dateRange", "range");
            filterTypeList.add((HashMap<String, String>) filte1.clone());
        }
    }
}