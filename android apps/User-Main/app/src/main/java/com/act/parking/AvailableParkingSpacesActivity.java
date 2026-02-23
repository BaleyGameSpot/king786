package com.act.parking;

import android.graphics.drawable.ColorDrawable;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.appcompat.widget.Toolbar;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;

import com.act.parking.fragment.ParkingListFragment;
import com.act.parking.fragment.ParkingListMapFragment;
import com.activity.ParentActivity;
import com.adapter.ViewPager2Adapter;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityAvailableParkingSpacesBinding;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.HashMap;

public class AvailableParkingSpacesActivity extends ParentActivity {
    public Toolbar mToolbar;
    public ActivityAvailableParkingSpacesBinding binding;
    private ViewPager2Adapter mViewPager2Adapter;
    private final ArrayList<Fragment> listOfFrag = new ArrayList<>();
    public ArrayList<HashMap<String, String>> listData = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_available_parking_spaces);
        initializeViews();
        fetchAvailableParkingSpaces();

    }

    private void setParkingViewPager() {
        listOfFrag.add(new ParkingListFragment());
        listOfFrag.add(new ParkingListMapFragment());

        mViewPager2Adapter = new ViewPager2Adapter(getSupportFragmentManager(), this.getLifecycle(), listOfFrag);
        binding.parkingViewPager.setAdapter(mViewPager2Adapter);
        binding.parkingViewPager.setUserInputEnabled(false);
    }

    private void initializeViews() {
        mToolbar = findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SPACE_TXT"));
        addToClickHandler(backImgView);
        addToClickHandler(binding.listParking);
        addToClickHandler(binding.mapViewParking);
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == R.id.listParking) {
            setbtncolors(true, false);
            binding.parkingViewPager.setCurrentItem(0, true);
        } else if (i == R.id.mapViewParking) {
            setbtncolors(false, true);
            binding.parkingViewPager.setCurrentItem(1, true);
        }
    }

    private void fetchAvailableParkingSpaces() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "FetchAvailableParkingSpace");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iParkingVehicleSizeId", getIntent().getStringExtra("parkingId"));
        parameters.put("iDurationId", getIntent().getStringExtra("duration"));
        parameters.put("ArrivalDate", getIntent().getStringExtra("dateTime"));
        parameters.put("vLatitude", getIntent().getStringExtra("bookingLatitude"));
        parameters.put("vLongitude", getIntent().getStringExtra("bookingLongitude"));

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    listData.clear();
                    JSONArray dataArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    MyUtils.createArrayListJSONArray(generalFunc, listData, dataArray);
                    setParkingViewPager();

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void setbtncolors(boolean isList, boolean isMap) {

        binding.listParking.setColorFilter(ContextCompat.getColor(this, isList ? R.color.appThemeColor_1 : R.color.text23Pro_Dark), android.graphics.PorterDuff.Mode.SRC_IN);
        binding.mapViewParking.setColorFilter(ContextCompat.getColor(this, isMap ? R.color.appThemeColor_1 : R.color.text23Pro_Dark), android.graphics.PorterDuff.Mode.SRC_IN);
        binding.listIndicator.setBackground(new ColorDrawable(getResources().getColor(isList ? R.color.appThemeColor_1 : R.color.view23ProBG)));
        binding.mapIndicator.setBackground(new ColorDrawable(getResources().getColor(isMap ? R.color.appThemeColor_1 : R.color.view23ProBG)));

    }
}