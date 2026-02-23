package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.view.View;

import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.adapter.files.PackageAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRentalDetailsBinding;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class RentalDetailsActivity extends ParentActivity implements PackageAdapter.setPackageClickList {

    private ActivityRentalDetailsBinding binding;
    private PackageAdapter adapter;
    private final ArrayList<HashMap<String, String>> packageList = new ArrayList<>();
    private MButton acceptBtn;

    private int selpos = 0;
    private String page_desc, imgUrl = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_rental_details);

        binding.pkgArea.setVisibility(View.GONE);
        addToClickHandler(binding.fareInfoArea);
        addToClickHandler(binding.toolbarInclude.backImgView);

        acceptBtn = ((MaterialRippleLayout) binding.acceptBtn).getChildView();
        acceptBtn.setId(Utils.generateViewId());
        acceptBtn.setAllCaps(false);
        addToClickHandler(acceptBtn);

        setLabel();
        getPackageDetails();


        binding.packageVtxt.setVisibility(View.GONE);
    }

    @SuppressLint("SetTextI18n")
    public void setLabel() {

        boolean eFly = getIntent().hasExtra("eFly") && getIntent().getBooleanExtra("eFly", false);

        if (getIntent().getStringExtra("eMoto") != null && !getIntent().getStringExtra("eMoto").equalsIgnoreCase("") && getIntent().getStringExtra("eMoto").equalsIgnoreCase("Yes")) {
            binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_MOTO_TITLE_TXT"));
        } else if (eFly) {
            binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_AIRCRAFT_TITLE_TXT"));
        } else if (getIntent().getBooleanExtra("isInterCity", false)) {
            binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_TXT"));
        } else {
            binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_A_CAR"));

        }

        binding.packageHtxt.setText(/*generalFunc.convertNumberWithRTL("3") + ". " + */generalFunc.retrieveLangLBl("", "LBL_SELECT_PACKAGE_TXT"));
        acceptBtn.setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_CONFIRM"));
        binding.fareTitletxt.setText(generalFunc.retrieveLangLBl("", "LBL_FARE_DETAILS_AND_RULES_TXT"));
        binding.fareMsgtxt.setText(generalFunc.retrieveLangLBl("", "LBL_FARE_DETAILS_DESCRIPTION_TXT"));

        String imgName = getImageName(getIntent().getStringExtra("vLogo"));
        if (Utils.checkText(imgName)) {
            imgUrl = CommonUtilities.SERVER_URL + "webimages/icons/VehicleType/" + getIntent().getStringExtra("iVehicleTypeId") + "/android/" + imgName;
        } else {
            imgUrl = CommonUtilities.SERVER_URL + "webimages/icons/DefaultImg/" + "hover_ic_car.png";
        }
        setVehicleDetailMarker();

    }

    private void getPackageDetails() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getRentalPackages");
        parameters.put("GeneralMemberId", generalFunc.getMemberId());
        parameters.put("iVehicleTypeId", getIntent().getStringExtra("iVehicleTypeId"));
        parameters.put("UserType", Utils.userType);

        //InterCity Stuffs
        if (getIntent().getBooleanExtra("isInterCity", false)) {
            parameters.put("tStartLatitude", getIntent().getStringExtra("sLat"));
            parameters.put("tStartLongitude", getIntent().getStringExtra("sLong"));
            parameters.put("tDestLatitude", getIntent().getStringExtra("eLat"));
            parameters.put("tDestLongitude", getIntent().getStringExtra("eLong"));
            parameters.put("tPickupDateTime", getIntent().getStringExtra("pickupDT"));
            parameters.put("tDestDateTime", getIntent().getStringExtra("dropoffDT"));
            parameters.put("eIsInterCity", "Yes");
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    page_desc = generalFunc.getJsonValue("page_desc", responseString);

                    JSONArray vehicleTypesArr = generalFunc.getJsonArray(Utils.message_str, responseString);
                    for (int i = 0; i < vehicleTypesArr.length(); i++) {

                        JSONObject obj_temp = generalFunc.getJsonObject(vehicleTypesArr, i);

                        HashMap<String, String> map = new HashMap<>();
                        map.put("iRentalPackageId", generalFunc.getJsonValueStr("iRentalPackageId", obj_temp));
                        map.put("vPackageName", generalFunc.getJsonValueStr("vPackageName", obj_temp));
                        map.put("fPrice", generalFunc.getJsonValueStr("fPrice", obj_temp));
                        map.put("fKiloMeter", generalFunc.getJsonValueStr("fKiloMeter", obj_temp));
                        map.put("fHour", generalFunc.getJsonValueStr("fHour", obj_temp));
                        map.put("fPricePerKM", generalFunc.getJsonValueStr("fPricePerKM", obj_temp));
                        map.put("fPricePerHour", generalFunc.getJsonValueStr("fPricePerHour", obj_temp));
                        map.put("fKiloMeter_LBL", generalFunc.getJsonValueStr("fKiloMeter_data", obj_temp));
                        map.put("isSelected", generalFunc.getJsonValueStr("isSelected", obj_temp));
                        map.put("isInterCity", String.valueOf(getIntent().getBooleanExtra("isInterCity", false)));
                        if (Utils.checkText(generalFunc.getJsonValueStr("isSelected", obj_temp)) && generalFunc.getJsonValueStr("isSelected", obj_temp).equalsIgnoreCase("yes")) {
                            selpos = i;
                        }
                        packageList.add(map);

                    }


                    if (!packageList.isEmpty()) {
                        adapter = new PackageAdapter(getActContext(), packageList);
                        adapter.itemPackageClick(RentalDetailsActivity.this);
                        binding.packageRecyclerView.setAdapter(adapter);
                        binding.pkgArea.setVisibility(View.VISIBLE);
                        binding.packageRecyclerView.smoothScrollToPosition(selpos);
                    }
                }
                if (packageList.isEmpty()) {
                    binding.bottomArea.setVisibility(View.GONE);
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }


    private String getImageName(@Nullable String vLogo) {
        String imageName;

        if (!Utils.checkText(vLogo)) {
            return "";
        }

        DisplayMetrics metrics = (getActContext().getResources().getDisplayMetrics());
        int densityDpi = (int) (metrics.density * 160f);
        imageName = switch (densityDpi) {
            case DisplayMetrics.DENSITY_LOW, DisplayMetrics.DENSITY_MEDIUM -> "mdpi_" + vLogo;
            case DisplayMetrics.DENSITY_HIGH, DisplayMetrics.DENSITY_TV -> "hdpi_" + vLogo;
            case DisplayMetrics.DENSITY_XHIGH, DisplayMetrics.DENSITY_280 -> "xhdpi_" + vLogo;
            case DisplayMetrics.DENSITY_XXXHIGH, DisplayMetrics.DENSITY_560 -> "xxxhdpi_" + vLogo;
            default -> "xxhdpi_" + vLogo;
        };

        return imageName;
    }

    private Context getActContext() {
        return RentalDetailsActivity.this;
    }

    @SuppressLint("NotifyDataSetChanged")
    @Override
    public void itemPackageClick(int position) {
        selpos = position;
        adapter.selPos(selpos);
        adapter.notifyDataSetChanged();
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == binding.toolbarInclude.backImgView.getId()) {
            getOnBackPressedDispatcher().onBackPressed();
        } else if (i == acceptBtn.getId()) {
            if (packageList != null && !packageList.isEmpty()) {
                Intent returnIntent = new Intent();
                returnIntent.putExtra("iRentalPackageId", packageList.get(selpos).containsKey("iRentalPackageId") ? packageList.get(selpos).get("iRentalPackageId") : "");
                setResult(Activity.RESULT_OK, returnIntent);
                finish();
            }
        } else if (i == binding.fareInfoArea.getId()) {
            if (packageList != null && !packageList.isEmpty()) {
                Bundle bn = new Bundle();
                HashMap<String, String> map = packageList.get(selpos);
                map.put("vVehicleType", getIntent().getStringExtra("vVehicleType"));
                map.put("page_desc", page_desc);
                bn.putBoolean("isInterCity", getIntent().getBooleanExtra("isInterCity", false));
                bn.putSerializable("data", map);
                new ActUtils(getActContext()).startActWithData(RentalInfoActivity.class, bn);
            }

        }


    }

    private void setVehicleDetailMarker() {
        binding.addressText.setText(getIntent().getStringExtra("address"));
        binding.vehicleTypeText.setText(getIntent().getStringExtra("vVehicleType"));
        binding.pickUpTitle.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOCATION_TXT"));

        new LoadImage.builder(LoadImage.bind(imgUrl), binding.carTypeImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
    }
}