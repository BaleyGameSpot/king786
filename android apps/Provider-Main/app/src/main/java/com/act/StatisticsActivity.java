package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.fragments.StatisticsFragment;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;


public class StatisticsActivity extends ParentActivity {

    MTextView titleTxt;
    ImageView backImgView;
    CharSequence[] titles;
    ArrayList<Fragment> fragmentList = new ArrayList<>();

    LinearLayout tablayoutArea, toolbar_layout;

    boolean isadded = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_statistics);
        titleTxt = findViewById(R.id.titleTxt);
        backImgView = findViewById(R.id.backImgView);
        tablayoutArea = findViewById(R.id.tablayoutArea);
        toolbar_layout = findViewById(R.id.toolbar_layout);

        addToClickHandler(backImgView);


        ViewPager appLogin_view_pager = findViewById(R.id.appLogin_view_pager);
        TabLayout material_tabs = findViewById(R.id.material_tabs);

        titles = new CharSequence[]{generalFunc.retrieveLangLBl("Trip", "LBL_TRIP_TXT")};

        material_tabs.setVisibility(View.VISIBLE);
        if (generalFunc.retrieveValue(Utils.ONLYDELIVERALL_KEY).equalsIgnoreCase("No")) {
            fragmentList.add(generateStatisticsFrag(Utils.MENU_TRIP_STATISTICS));
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STATISTICS"));
        }


        if (ServiceModule.isAnyDeliverAllOptionEnable()) {
            isadded = true;
            fragmentList.add(generateStatisticsFrag(Utils.MENU_ORDER_STATISTICS));
            tablayoutArea.setVisibility(View.VISIBLE);
            titles = new CharSequence[]{generalFunc.retrieveLangLBl("Trip", "LBL_TRIP_TXT"), generalFunc.retrieveLangLBl("Order", "LBL_ORDER")};
            toolbar_layout.setBackgroundColor(getResources().getColor(R.color.appThemeColor_1));
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STATISTICS"));
        }

        //   if (generalFunc.retrieveValue(Utils.ONLYDELIVERALL_KEY).equalsIgnoreCase("Yes")) {
        if (ServiceModule.isDeliverAllOnly()) {
            titles = new CharSequence[]{generalFunc.retrieveLangLBl("Order", "LBL_ORDER")};
            fragmentList.clear();
            tablayoutArea.setVisibility(View.GONE);
            fragmentList.add(generateStatisticsFrag(Utils.MENU_ORDER_STATISTICS));
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STATISTICS"));
        }

        if (titles.length == 1) {
            tablayoutArea.setVisibility(View.GONE);
            toolbar_layout.setPadding(0, 0, 0, 0);
        }

        ViewPagerAdapter adapter = new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList);
        appLogin_view_pager.setAdapter(adapter);
        material_tabs.setupWithViewPager(appLogin_view_pager);
        appLogin_view_pager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

            }

            @Override
            public void onPageSelected(int position) {
                fragmentList.get(position).onResume();
            }

            @Override
            public void onPageScrollStateChanged(int state) {

            }
        });


    }


    public StatisticsFragment generateStatisticsFrag(int type) {
        StatisticsFragment frag = new StatisticsFragment();
        Bundle bn = new Bundle();
        bn.putInt("type", type);
        frag.setArguments(bn);
        return frag;
    }


    public Context getActContext() {
        return StatisticsActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(StatisticsActivity.this);

        if (i == R.id.backImgView) {
            StatisticsActivity.super.onBackPressed();
        }
    }


}
