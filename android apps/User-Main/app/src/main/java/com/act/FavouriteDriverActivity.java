package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;

import androidx.appcompat.app.AlertDialog;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.dialogs.OpenListView;
import com.fragments.FavDriverFragment;
import com.general.files.ActUtils;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class FavouriteDriverActivity extends ParentActivity {

    MTextView titleTxt;
    CharSequence[] titles;
    boolean isrestart = false;

    String TAB1 = "ALL", TAB2 = "FAV", set = "";
    ImageView filterImageview;
    int selTabPos = 0;
    ArrayList<HashMap<String, String>> filterlist;
    AlertDialog list_type;
    public String selFilterType = "";
    ArrayList<Fragment> fragmentList = new ArrayList<>();
    ViewPager appLogin_view_pager;
    ArrayList<Integer> selList = new ArrayList();
    Button pos, neg;
    public int filterPosition = -1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_favorite_driver);

        isrestart = getIntent().getBooleanExtra("isrestart", false);

        titleTxt = findViewById(R.id.titleTxt);
        filterImageview = findViewById(R.id.filterImageview);
        addToClickHandler(filterImageview);
        ImageView backImgView = findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        setLabels();
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }


        appLogin_view_pager = (ViewPager) findViewById(R.id.appLogin_view_pager);
        TabLayout material_tabs = (TabLayout) findViewById(R.id.material_tabs);

        String TAB1_LBL = generalFunc.retrieveLangLBl("", "LBL_ALL_FAV_DRIVERS");
        String TAB2_LBL = generalFunc.retrieveLangLBl("", "LBL_FAV_TXT");

        if (!Utils.checkText(set)) {
            titles = new CharSequence[]{TAB1_LBL, TAB2_LBL};
            material_tabs.setVisibility(View.VISIBLE);
            fragmentList.add(generateFavDriverFrag(TAB1));
            fragmentList.add(generateFavDriverFrag(TAB2));
        } else if (set.equalsIgnoreCase(TAB1)) {
            titles = new CharSequence[]{TAB1_LBL};
            fragmentList.add(generateFavDriverFrag(TAB1));
            material_tabs.setVisibility(View.GONE);
        } else if (set.equalsIgnoreCase(TAB2)) {
            titles = new CharSequence[]{TAB2_LBL};
            fragmentList.add(generateFavDriverFrag(TAB1));
            material_tabs.setVisibility(View.GONE);
        }

        ViewPagerAdapter adapter = new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList);
        appLogin_view_pager.setAdapter(adapter);
        material_tabs.setupWithViewPager(appLogin_view_pager);


        if (isrestart) {
            appLogin_view_pager.setCurrentItem(1);
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
                //selFilterType = "";
            }

            @Override
            public void onPageScrollStateChanged(int state) {
                Logger.d("onPageScrolled", "::" + "onPageScrollStateChanged");
            }
        });

    }


    public FavDriverFragment getFavDriverFrag() {

        if (favDriverFragment != null) {
            return favDriverFragment;
        }
        return null;
    }

    FavDriverFragment favDriverFragment = null;

    public FavDriverFragment generateFavDriverFrag(String bookingType) {
        FavDriverFragment frag = new FavDriverFragment();
        Bundle bn = new Bundle();
        bn.putString("TAB_TYPE", bookingType);

        favDriverFragment = frag;

        frag.setArguments(bn);


        return frag;
    }

    public void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Favorite Drivers", "LBL_FAV_DRIVERS_TITLE_TXT"));
        if (ServiceModule.isRideOnly() ||
                ServiceModule.isDeliveronly() || ServiceModule.RideDeliveryProduct) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAV_DRIVER"));
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAV_PROVIDER"));
        }
    }


    public void BuildType() {
        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_TYPE"), filterlist, OpenListView.OpenDirection.BOTTOM, true, position -> {

            filterPosition = position;
            selFilterType = filterlist.get(position).get("vFilterParam");
            if (fragmentList.size() == 2) {
                ((FavDriverFragment) fragmentList.get(0)).applyfilter();
                ((FavDriverFragment) fragmentList.get(1)).applyfilter();
            } else {
                ((FavDriverFragment) fragmentList.get(appLogin_view_pager.getCurrentItem())).applyfilter();
            }


        }).show(filterPosition, "vTitle");
    }


    public boolean isContainTrue(boolean[] checkedETypes) {
        selList = new ArrayList();
        if (checkedETypes != null) {
            for (int i = 0; i < checkedETypes.length; i++) {
                if (checkedETypes[i])
                    selList.add(i);
            }
        }
        return false;
    }

    public void filterManage(ArrayList<HashMap<String, String>> filterlist) {
        this.filterlist = filterlist;
        runOnUiThread(() -> filterImageview.setVisibility(View.VISIBLE));
    }

    @Override
    public void onBackPressed() {
        if (isrestart) {
            Bundle bn = new Bundle();
            new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
            finishAffinity();
        } else {
            super.onBackPressed();
        }
    }

    public Context getActContext() {
        return FavouriteDriverActivity.this;
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();

    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.backImgView) {
            FavouriteDriverActivity.super.onBackPressed();
        } else if (id == R.id.filterImageview) {
            BuildType();
        }
    }


    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

    }

    public void updateFavDriver(String tab_type) {
        if (tab_type.equalsIgnoreCase(TAB1)) {
            ((FavDriverFragment) fragmentList.get(1)).getFavDriverList(false);
        }
    }
}
