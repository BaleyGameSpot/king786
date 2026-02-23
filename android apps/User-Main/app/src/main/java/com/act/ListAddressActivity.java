package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.adapter.files.AddressListAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityListAddressBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class ListAddressActivity extends ParentActivity implements AddressListAdapter.ItemClickListener {

    private ActivityListAddressBinding binding;
    private ImageView backImgView, rightImgView;
    private MTextView titleTxt;
    private AddressListAdapter mAddressListAdapter;
    private final ArrayList<HashMap<String, String>> mAddressList = new ArrayList<>();
    private String type = "", quantity = "0", SelectedVehicleTypeId = "";
    private boolean isDeleted = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_list_address);

        type = getIntent().getStringExtra("type");
        SelectedVehicleTypeId = getIntent().getStringExtra("SelectedVehicleTypeId");
        quantity = getIntent().getStringExtra("Quantity");

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        rightImgView = (ImageView) findViewById(R.id.rightImgView);
        rightImgView.setVisibility(View.VISIBLE);
        addToClickHandler(backImgView);
        addToClickHandler(rightImgView);

        if (getIntent().hasExtra("iCompanyId") || getIntent().getBooleanExtra("isBid", false)) {
            rightImgView.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_add_circle));
        }
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        setLabel();

        mAddressListAdapter = new AddressListAdapter(mAddressList, this);
        binding.rvAddressList.setAdapter(mAddressListAdapter);
    }

    private void setLabel() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_DELIVERY_ADDRESS"));
        if (getIntent().getBooleanExtra("isBid", false)) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_BIDDING_ADDRESS"));
        }
        if (getIntent().getBooleanExtra("isUfx", false)) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_SERVICE_ADDRESS"));
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        getAddressDetail();
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getAddressDetail() {
        binding.loader.setVisibility(View.VISIBLE);
        binding.noAddressTxt.setVisibility(View.GONE);
        mAddressList.clear();
        mAddressListAdapter.notifyDataSetChanged();

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DisplayUserAddress");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("eUserType", Utils.app_type);

        if (getIntent().getBooleanExtra("isGenie", false)) {
            parameters.put("eCatType", "Genie");
        }

        if (getIntent().hasExtra("iDriverId")) {
            parameters.put("iDriverId", getIntent().getStringExtra("iDriverId"));
        }

        if (getIntent().hasExtra("eSystem")) {
            parameters.put("eSystem", getIntent().getStringExtra("eSystem"));
        }

        if (getIntent().hasExtra("iCompanyId")) {
            if (!getIntent().getStringExtra("iCompanyId").equalsIgnoreCase("-1")) {
                HashMap<String, String> data = new HashMap<>();
                data.put(Utils.SELECT_LATITUDE, "");
                data.put(Utils.SELECT_LONGITUDE, "");
                data = generalFunc.retrieveValue(data);
                parameters.put("PassengerLat", data.get(Utils.SELECT_LATITUDE));
                parameters.put("PassengerLon", data.get(Utils.SELECT_LONGITUDE));
                parameters.put("iCompanyId", getIntent().getStringExtra("iCompanyId"));
            }
        }

        ApiHandler.execute(getActContext(), parameters, false, false, generalFunc, responseString -> {

            binding.noAddressTxt.setVisibility(View.GONE);
            binding.loader.setVisibility(View.GONE);

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    JSONArray message_arr = generalFunc.getJsonArray("message", responseString);

                    if (message_arr != null && message_arr.length() > 0) {
                        mAddressList.clear();

                        String iSelectedUserAddressId = "";

                        String iUserAddressId = getIntent().getStringExtra("iUserAddressId");
                        String addressid = getIntent().getStringExtra("addressid");

                        if (iUserAddressId != null && !iUserAddressId.equalsIgnoreCase("") && !iUserAddressId.equalsIgnoreCase("0") && !iUserAddressId.equalsIgnoreCase("-1")) {
                            iSelectedUserAddressId = iUserAddressId;
                        } else if (addressid != null && !addressid.equalsIgnoreCase("") && !addressid.equalsIgnoreCase("0") && !addressid.equalsIgnoreCase("-1")) {
                            iSelectedUserAddressId = addressid;
                        }

                        for (int i = 0; i < message_arr.length(); i++) {
                            JSONObject addr_obj = generalFunc.getJsonObject(message_arr, i);

                            HashMap<String, String> map = new HashMap<>();
                            map.put("vServiceAddress", generalFunc.getJsonValueStr("vServiceAddress", addr_obj));
                            map.put("iUserAddressId", generalFunc.getJsonValueStr("iUserAddressId", addr_obj));
                            map.put("vBuildingNo", generalFunc.getJsonValueStr("vBuildingNo", addr_obj));
                            map.put("vLandmark", generalFunc.getJsonValueStr("vLandmark", addr_obj));
                            map.put("vAddressType", generalFunc.getJsonValueStr("vAddressType", addr_obj));
                            map.put("vLatitude", generalFunc.getJsonValueStr("vLatitude", addr_obj));
                            map.put("vLongitude", generalFunc.getJsonValueStr("vLongitude", addr_obj));
                            map.put("eStatus", generalFunc.getJsonValueStr("eStatus", addr_obj));
                            map.put("eLocationAvailable", generalFunc.getJsonValueStr("eLocationAvailable", addr_obj));


                            if (iSelectedUserAddressId.equalsIgnoreCase(map.get("iUserAddressId"))) {
                                map.put("isSelected", "true");
                            } else {
                                map.put("isSelected", "false");
                            }

                            map.put("LBL_SERVICE_NOT_AVAIL_ADDRESS_RESTRICT", generalFunc.retrieveLangLBl("", "LBL_SERVICE_NOT_AVAIL_ADDRESS_RESTRICT"));
                            mAddressList.add(map);
                        }
                        mAddressListAdapter.notifyDataSetChanged();

                    }
                } else {
                    binding.noAddressTxt.setVisibility(View.VISIBLE);
                    if (getIntent().hasExtra("iCompanyId") || getIntent().getBooleanExtra("isBid", false)) {
                        binding.noAddressTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO_USER_ADDRESS_FOUND"));
                    } else {
                        binding.noAddressTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO_ADDRESS_TXT"));
                        finish();
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void checkUserAddressRestriction(String iAddressId, String selectedVehicleTypeId, final String type, final int position) {
        final Bundle bundle = new Bundle();

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "Checkuseraddressrestriction");
        parameters.put("iUserAddressId", iAddressId);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iSelectVehicalId", selectedVehicleTypeId);
        parameters.put("eUserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    HashMap<String, String> data = mAddressList.get(position);
                    if (type.equalsIgnoreCase(Utils.CabReqType_Later)) {
                        bundle.putString("latitude", data.get("vLatitude"));
                        bundle.putString("longitude", data.get("vLongitude"));
                        bundle.putString("address", data.get("vServiceAddress"));
                        bundle.putString("iUserAddressId", data.get("iUserAddressId"));
                        bundle.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
                        bundle.putString("SelectvVehicleType", getIntent().getStringExtra("SelectvVehicleType"));
                        bundle.putString("SelectvVehiclePrice", getIntent().getStringExtra("SelectvVehiclePrice"));
                        String vAddressType = data.get("vAddressType");
                        String tempAdd;
                        if (vAddressType != null && !vAddressType.equals("")) {
                            tempAdd = vAddressType + "\n" + data.get("vBuildingNo") + ", " + data.get("vLandmark") + "\n" + data.get("vServiceAddress");
                        } else {
                            tempAdd = data.get("vBuildingNo") + ", " + data.get("vLandmark") + "\n" + data.get("vServiceAddress");
                        }
                        bundle.putString("address", tempAdd);
                        bundle.putString("Quantity", quantity);
                        bundle.putString("Quantityprice", getIntent().getStringExtra("Quantityprice"));
                        bundle.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
                        bundle.putBoolean("isWalletShow", getIntent().getBooleanExtra("isWalletShow", false));
                        new ActUtils(getActContext()).startActWithData(ScheduleDateSelectActivity.class, bundle);

                    } else {
                        bundle.putBoolean("isufx", true);
                        bundle.putString("latitude", data.get("vLatitude"));
                        bundle.putString("longitude", data.get("vLongitude"));
                        bundle.putString("address", data.get("vServiceAddress"));
                        bundle.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
                        bundle.putString("iUserAddressId", data.get("iUserAddressId"));
                        bundle.putString("SelectvVehicleType", getIntent().getStringExtra("SelectvVehicleType"));
                        bundle.putString("SelectvVehiclePrice", getIntent().getStringExtra("SelectvVehiclePrice"));
                        bundle.putString("Quantity", quantity);
                        bundle.putString("Quantityprice", getIntent().getStringExtra("Quantityprice"));
                        bundle.putString("type", Utils.CabReqType_Now);
                        bundle.putString("Sdate", "");
                        bundle.putString("Stime", "");
                        bundle.putBoolean("isWalletShow", getIntent().getBooleanExtra("isWalletShow", false));
                        new ActUtils(getActContext()).startActWithData(MainActivity.class, bundle);
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), i -> finish());
                }
            }
        });
    }

    private void removeAddressApi(String iAddressId) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DeleteUserAddressDetail");
        parameters.put("iUserAddressId", iAddressId);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("eUserType", Utils.app_type);
        if (getIntent().hasExtra("iCompanyId")) {

            HashMap<String, String> data = new HashMap<>();
            data.put(Utils.SELECT_LATITUDE, "");
            data.put(Utils.SELECT_LONGITUDE, "");
            data = generalFunc.retrieveValue(data);

            parameters.put("PassengerLat", data.get(Utils.SELECT_LATITUDE));
            parameters.put("PassengerLon", data.get(Utils.SELECT_LONGITUDE));
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();

                        if (getIntent().hasExtra("iCompanyId") && !getIntent().getStringExtra("iCompanyId").equalsIgnoreCase("-1")) {
                            String addressid = getIntent().getStringExtra("addressid");
                            if (addressid != null) {
                                if (addressid.equalsIgnoreCase(iAddressId)) {
                                    isDeleted = true;
                                }
                            }

                            String ToTalAddress = getIntent().hasExtra("ToTalAddress") ? getIntent().getStringExtra("ToTalAddress") : generalFunc.getJsonValue("ToTalAddress", responseString);
                            if ((ToTalAddress != null && ToTalAddress.equalsIgnoreCase("0"))) {
                                Intent returnIntent = new Intent();
                                returnIntent.putExtra("ToTalAddress", ToTalAddress);
                                setResult(Activity.RESULT_OK, returnIntent);
                                finish();
                            } else {
                                getAddressDetail();
                            }
                        } else {

                            String userprofileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                            if (generalFunc.getJsonValue("ToTalAddress", userprofileJson).equalsIgnoreCase("0")) {
                                finish();
                            } else {
                                getAddressDetail();
                            }


                        }
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));

                    generateAlert.showAlertBox();


                } else {

                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));

                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private Context getActContext() {
        return ListAddressActivity.this;
    }

    @Override
    public void setOnClick(int position) {
        HashMap<String, String> data = mAddressList.get(position);

        if (getIntent().hasExtra("iCompanyId") || getIntent().getBooleanExtra("isBid", false)) {
            Intent returnIntent = new Intent();

            String address;
            String vAddressType = data.get("vAddressType");

            if (vAddressType != null && !vAddressType.equalsIgnoreCase("")) {
                address = vAddressType + "," + data.get("vBuildingNo") + ", " + data.get("vLandmark") + ", " + data.get("vServiceAddress");
            } else {
                address = data.get("vBuildingNo") + ", " + data.get("vLandmark") + ", " + data.get("vServiceAddress");
            }

            returnIntent.putExtra("address", address);
            returnIntent.putExtra("addressId", data.get("iUserAddressId"));
            returnIntent.putExtra("vLatitude", data.get("vLatitude"));
            returnIntent.putExtra("vLongitude", data.get("vLongitude"));
            returnIntent.putExtra("ToTalAddress", "1");
            setResult(RESULT_OK, returnIntent);
            finish();
        } else {
            if (type.equals(Utils.CabReqType_Later)) {
                checkUserAddressRestriction(data.get("iUserAddressId"), SelectedVehicleTypeId, Utils.CabReqType_Later, position);
            } else {
                checkUserAddressRestriction(data.get("iUserAddressId"), SelectedVehicleTypeId, Utils.CabReqType_Now, position);
            }
        }
    }

    @Override
    public void setOnDeleteClick(final int position) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            generateAlert.closeAlertBox();
            if (btn_id == 1) {
                removeAddressApi(mAddressList.get(position).get("iUserAddressId"));
            }
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("Are you sure want to delete", "LBL_DELETE_CONFIRM_MSG"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("cANCEL", "LBL_CANCEL_TXT"));
        generateAlert.showAlertBox();
    }

    @Override
    public void onBackPressed() {
        if (getIntent().hasExtra("iCompanyId")) {
            Intent returnIntent = new Intent();
            returnIntent.putExtra("isDeleted", isDeleted);
            setResult(Activity.RESULT_CANCELED, returnIntent);
            finish();
        } else {
            super.onBackPressed();
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == backImgView.getId()) {
            ListAddressActivity.super.onBackPressed();
        } else if (i == rightImgView.getId()) {
            Bundle bundle = new Bundle();
            bundle.putBoolean("isufx", true);
            bundle.putString("latitude", getIntent().getStringExtra("latitude"));
            bundle.putString("longitude", getIntent().getStringExtra("longitude"));
            bundle.putString("address", getIntent().getStringExtra("address"));
            bundle.putString("type", type);

            bundle.putString("Quantity", quantity);
            bundle.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
            bundle.putString("Quantityprice", getIntent().getStringExtra("Quantityprice"));
            if (getIntent().hasExtra("iCompanyId")) {
                bundle.putString("iCompanyId", getIntent().getStringExtra("iCompanyId"));

            }
            bundle.putBoolean("isWalletShow", getIntent().getBooleanExtra("isWalletShow", false));
            bundle.putBoolean("isGenie", getIntent().getBooleanExtra("isGenie", false));
            if (getIntent().getBooleanExtra("isGenie", false)) {
                bundle.putBoolean("isGenieAddress", true);
            }
            bundle.putBoolean("isBid", getIntent().getBooleanExtra("isBid", false));
            new ActUtils(getActContext()).startActWithData(AddAddressActivity.class, bundle);
        }
    }
}