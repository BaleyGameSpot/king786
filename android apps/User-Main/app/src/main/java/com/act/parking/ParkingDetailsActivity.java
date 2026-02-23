package com.act.parking;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.appcompat.widget.Toolbar;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;

import com.activity.ParentActivity;
import com.adapter.ViewPager2Adapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityParkingDetailsBinding;
import com.act.parking.fragment.ParkingInformationFragment;
import com.act.parking.fragment.ParkingLocationFragment;
import com.act.parking.fragment.ParkingReviewsFragment;
import com.service.handler.ApiHandler;
import com.utils.JSONUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class ParkingDetailsActivity extends ParentActivity {
    public ActivityParkingDetailsBinding binding;
    private final ArrayList<Fragment> listOfFrag = new ArrayList<>();
    private ViewPager2Adapter mViewPager2Adapter;
    public ArrayList<HashMap<String, String>> photosData = new ArrayList<>();
    public ArrayList<HashMap<String, String>> reviewsData = new ArrayList<>();
    public Toolbar mToolbar;
    public JSONObject obj_msg;
    private String responseStr = "";
    public String instructions = "";
    MButton btn_type2;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_parking_details);
        initializeViews();
        getData();
    }

    private void getData() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "FetchParkingSpaceDetails");
        parameters.put("iParkingSpaceId", getIntent().getStringExtra("parkingSpacesId"));
        parameters.put("iDurationId", getIntent().getStringExtra("duration"));
        parameters.put("iParkingVehicleSizeId", getIntent().getStringExtra("iParkingVehicleSizeId"));
        parameters.put("ArrivalDate", getIntent().getStringExtra("ArrivalDate"));
        parameters.put("vLatitude", getIntent().getStringExtra("bookingLatitude"));
        parameters.put("vLongitude", getIntent().getStringExtra("bookingLongitude"));

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    responseStr = responseString;
                    photosData.clear();
                    reviewsData.clear();

                    JSONObject obj_data = JSONUtils.toJsonObj(responseString);
                    obj_msg = JSONUtils.getJSONObj(Utils.message_str, obj_data);
                    binding.tAddress.setText(generalFunc.getJsonValueStr("tAddress", obj_msg));
                    binding.parkingFrom.setText(generalFunc.getJsonValueStr("ParkingFromTitle", obj_msg));
                    binding.parkingUntil.setText(generalFunc.getJsonValueStr("ParkingToTitle", obj_msg));
                    instructions = generalFunc.getJsonValueStr("tInstructions", obj_msg);
                    binding.fromDate.setText(generalFunc.getJsonValueStr("ParkingFromDateTime", obj_msg));
                    binding.untilDate.setText(generalFunc.getJsonValueStr("ParkingToDateTime", obj_msg));
                    binding.totalDuration.setText(generalFunc.getJsonValueStr("Duration", obj_msg));
                    binding.totalDurationHTxt.setText(generalFunc.getJsonValueStr("DurationSubText", obj_msg));
                    binding.parkingFee.setText(generalFunc.getJsonValueStr("tPrice", obj_msg));
                    binding.parkingFeeHTxt.setText(generalFunc.getJsonValueStr("tPriceSubText", obj_msg));
                    binding.toDistance.setText(generalFunc.getJsonValueStr("distance", obj_msg));
                    binding.toDistanceHTxt.setText(generalFunc.getJsonValueStr("DistanceSubText", obj_msg));
                    binding.avgRating.setText("(" + generalFunc.getJsonValueStr("vAvgRating", obj_msg) + ")");
                    binding.ratingBar.setRating(GeneralFunctions.parseFloatValue(0, generalFunc.getJsonValueStr("vAvgRating", obj_msg)));

                    btn_type2 = ((MaterialRippleLayout) binding.btnType2).getChildView();
                    btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_PROCEED_TO_CHECKOUT_BTN_TXT"));
                    btn_type2.setId(Utils.generateViewId());
                    addToClickHandler(btn_type2);


                    JSONArray photosArray = generalFunc.getJsonArray("ParkingSpaceImages", obj_msg);

                    HashMap<String, String> photoMap = new HashMap<>();
                    for (int i = 0; i < photosArray.length(); i++) {
                        JSONObject photosObj = generalFunc.getJsonObject(photosArray, i);
                        photoMap.put("iParkingSpaceImageId", generalFunc.getJsonValueStr("iParkingSpaceImageId", photosObj));
                        photoMap.put("vImage", generalFunc.getJsonValueStr("vImage", photosObj));
                        photoMap.put("eFileType", generalFunc.getJsonValueStr("eFileType", photosObj));
                        photoMap.put("ThumbImage", generalFunc.getJsonValueStr("ThumbImage", photosObj));
                        photosData.add(photoMap);
                    }
                    HashMap<String, String> reviewsMap = new HashMap<>();
                    JSONArray reviewsArray = generalFunc.getJsonArray("ParkingSpaceReviews", obj_msg);
                    for (int i = 0; i < reviewsArray.length(); i++) {
                        JSONObject reviewsObj = generalFunc.getJsonObject(reviewsArray, i);
                        reviewsMap.put("vName", generalFunc.getJsonValueStr("vName", reviewsObj));
                        reviewsMap.put("Rating", generalFunc.getJsonValueStr("Rating", reviewsObj));
                        reviewsMap.put("Message", generalFunc.getJsonValueStr("Message", reviewsObj));
                        reviewsMap.put("vImage", generalFunc.getJsonValueStr("vImage", reviewsObj));
                        reviewsData.add(reviewsMap);
                    }

                    setParkingViewPager();

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void setParkingViewPager() {
        listOfFrag.add(new ParkingInformationFragment());
        listOfFrag.add(new ParkingReviewsFragment());
        listOfFrag.add(new ParkingLocationFragment());

        mViewPager2Adapter = new ViewPager2Adapter(getSupportFragmentManager(), this.getLifecycle(), listOfFrag);
        binding.viewPagerParkingDetails.setAdapter(mViewPager2Adapter);
        binding.viewPagerParkingDetails.setUserInputEnabled(false);
    }


    private void initializeViews() {
        mToolbar = findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SPACE_DETAILS_TITLE"));
        addToClickHandler(backImgView);
        addToClickHandler(binding.locationFrag);
        addToClickHandler(binding.reviewfrag);
        addToClickHandler(binding.infoFrag);
        binding.locationFrag.setText(generalFunc.retrieveLangLBl("", "LBL_LOCATION_FOR_FRONT"));
        binding.reviewfrag.setText(generalFunc.retrieveLangLBl("", "LBL_REVIEWS"));
        binding.infoFrag.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_INFORMATION_TAB_TXT"));
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.infoFrag.getId()) {
            setbtncolors(true, false, false);
            binding.viewPagerParkingDetails.setCurrentItem(0, true);

        } else if (i == binding.reviewfrag.getId()) {
            setbtncolors(false, true, false);
            binding.viewPagerParkingDetails.setCurrentItem(1, true);
        } else if (i == binding.locationFrag.getId()) {
            setbtncolors(false, false, true);
            binding.viewPagerParkingDetails.setCurrentItem(2, true);
        }
        if (i == btn_type2.getId()) {
            Bundle bn = new Bundle();
            bn.putString("responseStr", responseStr);
            bn.putSerializable("vehicleSizes", getIntent().getSerializableExtra("vehicleSizes"));
            new ActUtils(ParkingDetailsActivity.this).startActWithData(ReviewOrCancelParkingBookingActivity.class, bn);
        }
    }

    private void setbtncolors(boolean isInfo, boolean isReview, boolean isLocation) {

        int appTheme = getResources().getColor(R.color.appThemeColor_1);
        int gray = getResources().getColor(R.color.cardView23ProBG);
        int dark = getResources().getColor(R.color.text23Pro_Dark);


        binding.infoFrag.setTextColor(isInfo ? appTheme : dark);
        binding.infoFragIndicator.setBackgroundColor(isInfo ? appTheme : gray);
        binding.reviewfrag.setTextColor(isReview ? appTheme : dark);
        binding.reviewfragIndicator.setBackgroundColor(isReview ? appTheme : gray);
        binding.locationFrag.setTextColor(isLocation ? appTheme : dark);
        binding.locationFragIndicator.setBackgroundColor(isLocation ? appTheme : gray);

    }

}