package com.act;

import static android.text.Spanned.SPAN_INCLUSIVE_INCLUSIVE;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.graphics.Color;
import android.os.Bundle;
import android.text.SpannableString;
import android.text.TextUtils;
import android.text.style.AbsoluteSizeSpan;
import android.text.style.ForegroundColorSpan;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.appcompat.widget.AppCompatCheckBox;
import androidx.appcompat.widget.Toolbar;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.DefaultItemAnimator;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.deliverAll.VehicleSingleCheckListAdapter;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.CustomLinearLayoutManager;
import com.general.files.GeneralFunctions;
import com.general.files.PreferenceDailogJava;
import com.general.files.SetOnTouchList;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityAddVehicle24Binding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Objects;

public class AddVehicleActivity24 extends ParentActivity implements VehicleSingleCheckListAdapter.OnItemClickListener {

    private ActivityAddVehicle24Binding binding;
    JSONArray year_arr, vehicletypelist, maketypelist, vehicletypelist_DeliverAll;
    String LBL_MODEL, LBL_SELECT_MAKE, LBL_CHOOSE_MAKE, LBL_SELECT_YEAR, LBL_SELECT_MODEL, LBL_DELIVERY, LBL_RIDE, LBL_HEADER_RDU_FLY_RIDE, LBL_BTN_OK_TXT, LBL_SKIP_TXT, LBL_UPLOAD_DOC, LBL_CONTACT_US_TXT, LBL_AVAILABLE_FOR_RENTAL, ENABLE_EDIT_DRIVER_VEHICLE, LBL_DELIVERALL, iDriverVehicleId = "", iSelectedMakeId = "", iSelectedModelId = "", vRentalCarType = "", tempiDriverVehicleId = "", selectedCarTypes_DeliverAll = "";

    MTextView titleTxt;
    MButton submitVehicleBtn;
    ImageView backImgView;
    ArrayList<Boolean> carTypesStatusArr, rentalcarTypesStatusArr;
    VehicleSingleCheckListAdapter vehicleListAdapter;
    MaterialEditText makeBox, modelBox, yearBox, licencePlateBox, colorPlateBox;
    boolean ishandicapavilabel = false, ischildseatavilabel = false, iswheelchairavilabel = false;
    int iSelectedMakePosition = 0, iSelectedModelPosition = 0;
    int selMakePosition = -1;
    int selModelPosition = -1;
    int selYearPosition = -1;
    ArrayList<HashMap<String, String>> modelList = new ArrayList<>();

    ArrayList<HashMap<String, String>> makeList = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_add_vehicle24);

        SetData();
        removeInput();
        buildData();

    }

    private void buildData() {
        binding.loadingBar.setVisibility(View.VISIBLE);
        binding.contentArea.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getUserVehicleMakeDetails");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);
                if (isDataAvail) {
                    JSONObject message_obj = generalFunc.getJsonObject("message", responseStringObject);
                    year_arr = generalFunc.getJsonArray("year", message_obj);

                    vehicletypelist = generalFunc.getJsonArray("vehicletypelist", message_obj);
                    if (vehicletypelist != null && vehicletypelist.length() == 0) {
                        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                        generateAlert.setCancelable(false);
                        generateAlert.setBtnClickList(btn_id -> {
                            if (btn_id == 0) {
                                generateAlert.closeAlertBox();
                                Bundle bn = new Bundle();
                                bn.putBoolean("isContactus", false);
                                new ActUtils(getActContext()).setOkResult(bn);
                                backImgView.performClick();
                            } else if (btn_id == 1) {
                                Bundle bn = new Bundle();
                                bn.putBoolean("isContactus", true);
                                new ActUtils(getActContext()).setOkResult(bn);
                                backImgView.performClick();
                            }
                        });

                        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str_one, responseStringObject)));
                        generateAlert.setNegativeBtn(LBL_BTN_OK_TXT);
                        generateAlert.setPositiveBtn(LBL_CONTACT_US_TXT);

                        generateAlert.showAlertBox();
                    }

                    maketypelist = generalFunc.getJsonArray("carlist", message_obj);

                    buildServices(generalFunc.getJsonValueStr("IS_SHOW_VEHICLE_TYPE", message_obj));
                    buildMake();
                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                }
            } else {
                generalFunc.showError();
            }
            binding.loadingBar.setVisibility(View.GONE);
            binding.contentArea.setVisibility(View.VISIBLE);

        });
    }


    private void buildMake() {
        if (maketypelist == null || maketypelist.length() == 0) {
            return;
        }
        for (int i = 0; i < maketypelist.length(); i++) {
            JSONObject obj = generalFunc.getJsonObject(maketypelist, i);
            HashMap<String, String> hashMap = new HashMap<>();
            hashMap.put("iMakeId", generalFunc.getJsonValueStr("iMakeId", obj));
            hashMap.put("vMake", generalFunc.getJsonValueStr("vMake", obj));
            makeList.add(hashMap);

            if (!iSelectedMakeId.equals("") && iSelectedMakeId.equals(generalFunc.getJsonValueStr("iMakeId", obj))) {
                iSelectedMakePosition = i;
                selMakePosition = iSelectedMakePosition;
                makeBox.setText(generalFunc.getJsonValueStr("vMake", obj));
                modelBox.setEnabled(false);
                binding.mProgressBar.setVisibility(View.VISIBLE);
                buildModel();
                selModelPosition = -1;
            }
        }

    }


    private void buildModel() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getUserVehicleModelsDetails");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("iMakeId", iSelectedMakeId);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            binding.mProgressBar.setVisibility(View.GONE);
            modelBox.setEnabled(true);
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);
            if (responseStringObject != null) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);
                modelList.clear();
                if (isDataAvail) {
                    JSONArray msgArr = generalFunc.getJsonArray("message", responseStringObject);

                    if (msgArr == null || msgArr.length() == 0) {
                        return;
                    }
                    for (int i = 0; i < msgArr.length(); i++) {
                        JSONObject obj = generalFunc.getJsonObject(msgArr, i);
                        HashMap<String, String> hashMap = new HashMap<>();
                        hashMap.put("iModelId", generalFunc.getJsonValueStr("iModelId", obj));
                        hashMap.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj));
                        modelList.add(hashMap);
                        if (!iSelectedModelId.equals("") && iSelectedModelId.equals(generalFunc.getJsonValueStr("iModelId", obj))) {
                            selModelPosition = i;
                            modelBox.setText(generalFunc.getJsonValueStr("vTitle", obj));
                        }
                    }

                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                }
            } else {
                generalFunc.showError();
            }


        });
    }

    private void buildYear() {
        if (year_arr == null || year_arr.length() == 0) {
            return;
        }

        ArrayList<HashMap<String, String>> yearList = new ArrayList<>();

        for (int i = 0; i < year_arr.length(); i++) {

            HashMap<String, String> hashMap = new HashMap<>();
            hashMap.put("vTitle", (String) generalFunc.getValueFromJsonArr(year_arr, i));
            yearList.add(hashMap);

            if (Utils.getText(yearBox).equalsIgnoreCase(yearList.get(i).get("vTitle"))) {
                selYearPosition = i;
            }
        }

        OpenListView.getInstance(getActContext(), LBL_SELECT_YEAR, yearList, OpenListView.OpenDirection.CENTER, false, position -> {
            selYearPosition = position;
            yearBox.setText(yearList.get(position).get("vTitle"));

        }).show(selYearPosition, "vTitle");


    }

    @SuppressLint("NotifyDataSetChanged")
    private void buildServices(String IS_SHOW_VEHICLE_TYPE) {
        if (binding.serviceSelectArea.getChildCount() > 0) {
            binding.serviceSelectArea.removeAllViewsInLayout();
        }

        carTypesStatusArr = new ArrayList<>();
        rentalcarTypesStatusArr = new ArrayList<>();
        vehicletypelist_DeliverAll = new JSONArray();
        int position = 0;

        String[] vCarTypes = {};
        String[] vRentalCarType = {};


        String vCarType = getIntent().getStringExtra("vCarType");
        if (vCarType != null && !vCarType.isEmpty()) {
            vCarTypes = vCarType.split(",");
        }

        String vRentalCarType_ = getIntent().getStringExtra("vRentalCarType");
        if (vRentalCarType_ != null && !vRentalCarType_.isEmpty()) {
            vRentalCarType = vRentalCarType_.split(",");
        }

        if (!ServiceModule.isDeliverAllOnly()) {
            for (int i = 0; i < vehicletypelist.length(); i++) {
                JSONObject obj = generalFunc.getJsonObject(vehicletypelist, i);
                String eType = generalFunc.getJsonValueStr("eType", obj);

                if (eType.equalsIgnoreCase(Utils.eSystem_Type)) {
                    try {

                        obj.put("LBL_DELIVERALL", LBL_DELIVERALL);
                        obj.put("showTag", "Yes");

                        vehicletypelist_DeliverAll.put(position, obj);
                        carTypesStatusArr.add(false);
                        rentalcarTypesStatusArr.add(false);
                        position++;
                    } catch (JSONException e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                } else {
                    LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                    @SuppressLint("InflateParams") View view = inflater.inflate(R.layout.item_select_service_ride_del_design, null);

                    LinearLayout helperArea = view.findViewById(R.id.helperArea);
                    MTextView serviceNameTxtView = view.findViewById(R.id.serviceNameTxtView);
                    MTextView serviceTypeNameTxtView = view.findViewById(R.id.serviceTypeNameTxtView);
                    MTextView deliverhelperTxt = view.findViewById(R.id.deliverhelperTxt);
                    ImageView deliveryHelperimg = view.findViewById(R.id.deliveryHelperimg);
                    deliverhelperTxt.setText(generalFunc.retrieveLangLBl("", "LBL_REQ_DEL_HELPER"));
                    deliverhelperTxt.setOnClickListener(v -> {

                        PreferenceDailogJava preferenceDailogJava = new PreferenceDailogJava(getActContext());
                        preferenceDailogJava.showPreferenceDialog(generalFunc.retrieveLangLBl("Delivery Helper", "LBL_DEL_HELPER"), generalFunc.getJsonValueStr("tDeliveryHelperNoteDriver", obj), R.drawable.ic_exclamation, false,
                                generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), "", false);

                    });
                    deliveryHelperimg.setOnClickListener(v -> deliverhelperTxt.performClick());


                    LinearLayout rentalArea = view.findViewById(R.id.rentalArea);
                    CheckBox rentalchkBox = view.findViewById(R.id.rentalchkBox);
                    MTextView rentalTxtView = view.findViewById(R.id.rentalTxtView);
                    rentalTxtView.setText(LBL_AVAILABLE_FOR_RENTAL);


                    LinearLayout editarea = view.findViewById(R.id.editarea);
                    editarea.setVisibility(View.GONE);
                    serviceTypeNameTxtView.setText(generalFunc.getJsonValueStr("SubTitle", obj));

                    String text1 = generalFunc.getJsonValueStr("vVehicleType", obj);
                    String text2 = "";

                    if (IS_SHOW_VEHICLE_TYPE.equalsIgnoreCase("Yes")) {
                        String eTypeLable = (eType.equalsIgnoreCase("Delivery") || eType.equalsIgnoreCase("Deliver")) ? LBL_DELIVERY : eType.equalsIgnoreCase("Fly") ? LBL_HEADER_RDU_FLY_RIDE : LBL_RIDE;
                        text2 = " (" + eTypeLable + ")";
                    }

                    if (eType.equalsIgnoreCase("Ambulance")) {
                        text2 = " (" + generalFunc.retrieveLangLBl("", "LBL_VEHICLE_TYPE_AMBULANCE_TXT") + ")";
                    }
                    if (eType.equalsIgnoreCase("TaxiBid")) {
                        text2 = " (" + generalFunc.retrieveLangLBl("", "LBL_TAXI_BID_TXT") + ")";
                    }
                    if (eType.equalsIgnoreCase("InterCity")) {
                        text2 = " (" + generalFunc.retrieveLangLBl("", "LBL_INTERCITY_TXT") + ")";
                    }

                    SpannableString span1 = new SpannableString(text1);
                    span1.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(20, getActContext())), 0, text1.length(), SPAN_INCLUSIVE_INCLUSIVE);
                    span1.setSpan(new ForegroundColorSpan(Color.parseColor("#272727")), 0, text1.length(), 0);
                    SpannableString span2 = new SpannableString(text2);
                    span2.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(14, getActContext())), 0, text2.length(), SPAN_INCLUSIVE_INCLUSIVE);
                    span2.setSpan(new ForegroundColorSpan(Color.parseColor("#838383")), 0, text2.length(), 0);
                    CharSequence finalText = TextUtils.concat(span1, "", span2);

                    serviceNameTxtView.setText(finalText);

                    final AppCompatCheckBox chkBox = view.findViewById(R.id.chkBox);

                    String eRental = generalFunc.getJsonValueStr("eRental", obj);
                    boolean isRental = eRental != null && eRental.equalsIgnoreCase("yes");
                    String iVehicleTypeId = generalFunc.getJsonValueStr("iVehicleTypeId", obj);

                    if (vCarTypes.length > 0) {

                        String ischeck = generalFunc.getJsonValueStr("VehicleServiceStatus", obj);
                        if (ischeck.equalsIgnoreCase("true") || Arrays.asList(vCarTypes).contains(iVehicleTypeId)) {
                            chkBox.setChecked(true);
                            carTypesStatusArr.add(true);
                            if (isRental) {
                                rentalArea.setVisibility(View.VISIBLE);
                            }

                            if (generalFunc.getJsonValueStr("eDeliveryHelper", obj) != null && generalFunc.getJsonValueStr("eDeliveryHelper", obj).equalsIgnoreCase("Yes")) {
                                helperArea.setVisibility(View.VISIBLE);

                            }

                        } else {
                            if (generalFunc.getJsonValueStr("eDeliveryHelper", obj) != null && !generalFunc.getJsonValueStr("eDeliveryHelper", obj).equalsIgnoreCase("Yes")) {
                                helperArea.setVisibility(View.GONE);

                            }
                            carTypesStatusArr.add(false);
                        }
                    } else {
                        carTypesStatusArr.add(false);
                    }


                    if (vRentalCarType.length > 0) {

                        if (Arrays.asList(vRentalCarType).contains(iVehicleTypeId)) {

                            rentalchkBox.setChecked(true);
                            rentalcarTypesStatusArr.add(true);
                        } else {
                            rentalcarTypesStatusArr.add(false);
                        }
                    } else {
                        rentalcarTypesStatusArr.add(false);
                    }


                    final int finalI = i;
                    chkBox.setOnCheckedChangeListener((buttonView, isChecked) -> {
                        carTypesStatusArr.set(finalI, isChecked);
                        if (generalFunc.getJsonValueStr("eDeliveryHelper", obj) != null && generalFunc.getJsonValueStr("eDeliveryHelper", obj).equalsIgnoreCase("Yes")) {

                            helperArea.setVisibility(View.VISIBLE);


                        }

                        if (isRental) {

                            if (isChecked) {
                                rentalArea.setVisibility(View.VISIBLE);

                            } else {
                                helperArea.setVisibility(View.GONE);
                                rentalArea.setVisibility(View.GONE);
                                rentalchkBox.setChecked(false);
                            }
                        } else {
                            if (!isChecked) {
                                helperArea.setVisibility(View.GONE);
                            }
                            rentalArea.setVisibility(View.GONE);
                        }
                    });
                    rentalchkBox.setOnCheckedChangeListener((buttonView, isChecked) -> rentalcarTypesStatusArr.set(finalI, isChecked));
                    binding.serviceSelectArea.addView(view);
                }

            }
        } else {

            for (int i = 0; i < vehicletypelist.length(); i++) {
                JSONObject obj = generalFunc.getJsonObject(vehicletypelist, i);
                String eType = generalFunc.getJsonValueStr("eType", obj);
                if (eType.equalsIgnoreCase(Utils.eSystem_Type)) {
                    try {
                        obj.put("LBL_DELIVERALL", LBL_DELIVERALL);
                        obj.put("showTag", "No");
                        vehicletypelist_DeliverAll.put(position, obj);
                        position++;
                    } catch (JSONException e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                }
            }
        }


        if (vehicletypelist_DeliverAll.length() > 0 && ServiceModule.DeliverAll) {
            if (vehicleListAdapter == null) {
                if (ServiceModule.isDeliverAllOnly()) {
                    findViewById(R.id.deliverAllTitleText).setVisibility(View.GONE);
                }
                ((MTextView) findViewById(R.id.deliverAllTitleText)).setText(LBL_DELIVERALL);

                findViewById(R.id.deliverAllcarTypeArea).setVisibility(View.VISIBLE);
                RecyclerView.LayoutManager layoutManager = new CustomLinearLayoutManager(getActContext());
                binding.serviceSelectRecyclerView.setLayoutManager(layoutManager);
                binding.serviceSelectRecyclerView.setItemAnimator(new DefaultItemAnimator());
                vehicleListAdapter = new VehicleSingleCheckListAdapter(getActContext(), vCarTypes, generalFunc, vehicletypelist_DeliverAll);
                binding.serviceSelectRecyclerView.setAdapter(vehicleListAdapter);

                vehicleListAdapter.setOnItemClickListener(this);
                selectedCarTypes_DeliverAll = "";
                vehicleListAdapter.setSelectedPosition(-1);
                for (int i1 = 0; i1 < vehicletypelist_DeliverAll.length(); i1++) {
                    JSONObject obj1 = generalFunc.getJsonObject(vehicletypelist_DeliverAll, i1);
                    String ischeck = generalFunc.getJsonValueStr("VehicleServiceStatus", obj1);
                    String iVehicleTypeId = generalFunc.getJsonValueStr("iVehicleTypeId", obj1);
                    if (ischeck.equalsIgnoreCase("true") || Arrays.asList(vCarTypes).contains(iVehicleTypeId)) {
                        selectedCarTypes_DeliverAll = iVehicleTypeId;
                        vehicleListAdapter.setSelectedPosition(i1);
                        break;
                    }
                }
                vehicleListAdapter.notifyDataSetChanged();

                return;
            }
            vehicleListAdapter.notifyDataSetChanged();
        }
    }

    public Context getActContext() {
        return AddVehicleActivity24.this;
    }

    private void SetData() {
        Toolbar mToolbar = findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);

        titleTxt = findViewById(R.id.titleTxt);
        submitVehicleBtn = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        makeBox = findViewById(R.id.makeBox);
        modelBox = findViewById(R.id.modelBox);
        yearBox = findViewById(R.id.yearBox);
        licencePlateBox = findViewById(R.id.licencePlateBox);
        colorPlateBox = findViewById(R.id.colorPlateBox);
        backImgView = findViewById(R.id.backImgView);

        binding.mProgressBar.getIndeterminateDrawable().setColorFilter(getResources().getColor(R.color.appThemeColor_2), android.graphics.PorterDuff.Mode.SRC_IN);
        binding.mProgressBar.setIndeterminate(true);

        ENABLE_EDIT_DRIVER_VEHICLE = generalFunc.getJsonValueStr("ENABLE_EDIT_DRIVER_VEHICLE", obj_userProfile);

        LBL_MODEL = generalFunc.retrieveLangLBl("Model", "LBL_MODEL");
        LBL_SELECT_MAKE = generalFunc.retrieveLangLBl("Select Make", "LBL_SELECT_MAKE");
        LBL_CHOOSE_MAKE = generalFunc.retrieveLangLBl("Select Make", "LBL_CHOOSE_MAKE");
        LBL_SELECT_YEAR = generalFunc.retrieveLangLBl("Select Year", "LBL_SELECT_YEAR");
        LBL_SELECT_MODEL = generalFunc.retrieveLangLBl("Select Models", "LBL_SELECT_MODEL");
        LBL_DELIVERY = generalFunc.retrieveLangLBl("", "LBL_DELIVERY");
        LBL_RIDE = generalFunc.retrieveLangLBl("", "LBL_RIDE");
        LBL_HEADER_RDU_FLY_RIDE = generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_FLY_RIDE");
        LBL_BTN_OK_TXT = generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT");
        LBL_SKIP_TXT = generalFunc.retrieveLangLBl("", "LBL_SKIP_TXT");
        LBL_UPLOAD_DOC = generalFunc.retrieveLangLBl("", "LBL_UPLOAD_DOC");
        LBL_CONTACT_US_TXT = generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT");
        LBL_AVAILABLE_FOR_RENTAL = generalFunc.retrieveLangLBl("", "LBL_AVAILABLE_FOR_RENTAL");
        LBL_DELIVERALL = generalFunc.retrieveLangLBl("", "LBL_DELIVERALL");

        if (generalFunc.isRTLmode()) {
            modelBox.setPaddings(100, 0, 0, 0);
        } else {
            modelBox.setPaddings(0, 0, 100, 0);
        }

        if (ServiceModule.Taxi) {
            String isHadicap = generalFunc.getJsonValueStr("HANDICAP_ACCESSIBILITY_OPTION", obj_userProfile);
            String isChildSeatAvail = generalFunc.getJsonValueStr("CHILD_SEAT_ACCESSIBILITY_OPTION", obj_userProfile);
            String isWeelChairAvail = generalFunc.getJsonValueStr("WHEEL_CHAIR_ACCESSIBILITY_OPTION", obj_userProfile);

            if (isHadicap == null || !isHadicap.equalsIgnoreCase("Yes")) {
                binding.checkboxHandicap.setVisibility(View.GONE);
            } else {
                binding.checkboxHandicap.setVisibility(View.VISIBLE);
            }

            if (isChildSeatAvail == null || !isChildSeatAvail.equalsIgnoreCase("Yes")) {
                binding.checkboxChildSeat.setVisibility(View.GONE);
            } else {
                binding.checkboxChildSeat.setVisibility(View.VISIBLE);
            }

            if (isWeelChairAvail == null || !isWeelChairAvail.equalsIgnoreCase("Yes")) {
                binding.checkboxWheelChair.setVisibility(View.GONE);
            } else {
                binding.checkboxWheelChair.setVisibility(View.VISIBLE);
            }
        } else {
            binding.checkboxHandicap.setVisibility(View.GONE);
            binding.checkboxChildSeat.setVisibility(View.GONE);
            binding.checkboxWheelChair.setVisibility(View.GONE);

        }

        String iDriverVehicleId_ = getIntent().getStringExtra("iDriverVehicleId");
        if (iDriverVehicleId_ != null && !iDriverVehicleId_.equalsIgnoreCase("")) {
            iDriverVehicleId = iDriverVehicleId_;
            iSelectedMakeId = getIntent().getStringExtra("iMakeId");
            iSelectedModelId = getIntent().getStringExtra("iModelId");
            String vLicencePlate = getIntent().getStringExtra("vLicencePlate");
            String vColour = getIntent().getStringExtra("vColour");
            String iYear = getIntent().getStringExtra("iYear");
            String hadicap = ServiceModule.isDeliverAllOnly() ? "No" : getIntent().getStringExtra("eHandiCapAccessibility");
            String childseat = ServiceModule.isDeliverAllOnly() ? "No" : getIntent().getStringExtra("eChildAccessibility");
            String wheelchair = ServiceModule.isDeliverAllOnly() ? "No" : getIntent().getStringExtra("eWheelChairAccessibility");

            if (Objects.requireNonNull(hadicap).equalsIgnoreCase("yes")) {
                binding.checkboxHandicap.setChecked(true);
            }
            if (Objects.requireNonNull(childseat).equalsIgnoreCase("yes")) {
                binding.checkboxChildSeat.setChecked(true);
            }
            if (Objects.requireNonNull(wheelchair).equalsIgnoreCase("yes")) {
                binding.checkboxWheelChair.setChecked(true);
            }

            licencePlateBox.setText(Objects.requireNonNull(vLicencePlate).trim());
            colorPlateBox.setText(vColour);
            yearBox.setText(iYear);
        }

        if (getIntent().getStringExtra("isfrom") != null && Objects.requireNonNull(getIntent().getStringExtra("isfrom")).equalsIgnoreCase("edit")) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EDIT_VEHICLE"));
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_VEHICLE"));
        }

        submitVehicleBtn.setId(Utils.generateViewId());
        submitVehicleBtn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_SUBMIT_TXT"));

        LBL_MODEL = generalFunc.retrieveLangLBl("Model", "LBL_MODEL");
        LBL_SELECT_MAKE = generalFunc.retrieveLangLBl("Select Make", "LBL_SELECT_MAKE");
        LBL_CHOOSE_MAKE = generalFunc.retrieveLangLBl("Select Make", "LBL_CHOOSE_MAKE");
        LBL_SELECT_YEAR = generalFunc.retrieveLangLBl("Select Year", "LBL_SELECT_YEAR");
        LBL_SELECT_MODEL = generalFunc.retrieveLangLBl("Select Models", "LBL_SELECT_MODEL");
        LBL_DELIVERY = generalFunc.retrieveLangLBl("", "LBL_DELIVERY");
        LBL_RIDE = generalFunc.retrieveLangLBl("", "LBL_RIDE");
        LBL_HEADER_RDU_FLY_RIDE = generalFunc.retrieveLangLBl("", "LBL_HEADER_RDU_FLY_RIDE");
        LBL_BTN_OK_TXT = generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT");
        LBL_SKIP_TXT = generalFunc.retrieveLangLBl("", "LBL_SKIP_TXT");
        LBL_UPLOAD_DOC = generalFunc.retrieveLangLBl("", "LBL_UPLOAD_DOC");
        LBL_CONTACT_US_TXT = generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT");
        LBL_AVAILABLE_FOR_RENTAL = generalFunc.retrieveLangLBl("", "LBL_AVAILABLE_FOR_RENTAL");


        makeBox.setBothText(generalFunc.retrieveLangLBl("Make", "LBL_MAKE"));
        modelBox.setBothText(LBL_MODEL);
        yearBox.setBothText(generalFunc.retrieveLangLBl("Year", "LBL_YEAR"));
        licencePlateBox.setBothText(generalFunc.retrieveLangLBl("Licence", "LBL_LICENCE_PLATE_TXT"));
        colorPlateBox.setBothText(generalFunc.retrieveLangLBl("Color", "LBL_COLOR_TXT"));

        binding.checkboxHandicap.setText(generalFunc.retrieveLangLBl("Handicap accessibility available?", "LBL_HANDICAP_QUESTION"));
        binding.checkboxChildSeat.setText(generalFunc.retrieveLangLBl("", "LBL_CHILD_SEAT_QUESTION"));
        binding.checkboxWheelChair.setText(generalFunc.retrieveLangLBl("", "LBL_WHEEL_CHAIR_ADD_VEHICLES"));
        addToClickHandler(backImgView);
        addToClickHandler(submitVehicleBtn);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }

    }


    @SuppressLint("ClickableViewAccessibility")
    private void removeInput() {
        Utils.removeInput(makeBox);
        Utils.removeInput(modelBox);
        Utils.removeInput(yearBox);

        makeBox.setOnTouchListener(new SetOnTouchList());
        modelBox.setOnTouchListener(new SetOnTouchList());
        yearBox.setOnTouchListener(new SetOnTouchList());

        addToClickHandler(makeBox);
        addToClickHandler(modelBox);
        addToClickHandler(yearBox);

    }

    @Override
    public void onItemClickList(int position) {

        if (position == -1) {
            selectedCarTypes_DeliverAll = "";
        } else {
            JSONObject obj = generalFunc.getJsonObject(vehicletypelist_DeliverAll, position);

            selectedCarTypes_DeliverAll = generalFunc.getJsonValueStr("iVehicleTypeId", obj);
        }

    }

    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(AddVehicleActivity24.this);
        if (i == R.id.backImgView) {
            AddVehicleActivity24.super.onBackPressed();

        } else if (i == R.id.makeBox) {
            OpenListView.getInstance(getActContext(), LBL_SELECT_MAKE, makeList, OpenListView.OpenDirection.CENTER, false, position -> {
                selMakePosition = position;
                selModelPosition = -1;
                modelBox.setText("");
                iSelectedModelId = "";
                modelBox.setBothText(LBL_MODEL);
                makeBox.setText(makeList.get(position).get("vMake"));
                iSelectedMakeId = makeList.get(position).get("iMakeId");
                iSelectedMakePosition = selMakePosition;
                modelBox.setEnabled(false);
                binding.mProgressBar.setVisibility(View.VISIBLE);
                buildModel();
            }).show(selMakePosition, "vMake");


        } else if (i == R.id.modelBox) {
            if (iSelectedMakeId.isEmpty()) {
                generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), LBL_CHOOSE_MAKE);
            } else {
                OpenListView.getInstance(getActContext(), LBL_SELECT_MODEL, modelList, OpenListView.OpenDirection.CENTER, false, position -> {
                    selModelPosition = position;
                    modelBox.setText(modelList.get(position).get("vTitle"));
                    iSelectedModelId = modelList.get(position).get("iModelId");
                    iSelectedModelPosition = selModelPosition;
                }).show(selModelPosition, "vTitle");
            }

        } else if (i == R.id.yearBox) {
            buildYear();

        } else if (i == submitVehicleBtn.getId()) {
            checkData();
        }
    }

    public void checkData() {

        if (iSelectedMakeId.isEmpty()) {
            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), LBL_CHOOSE_MAKE);
            return;
        }
        if (iSelectedModelId.isEmpty()) {
            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_CHOOSE_VEHICLE_MODEL"));
            return;
        }

        if (Utils.getText(yearBox).isEmpty()) {
            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_CHOOSE_YEAR"));
            return;
        }
        if (Utils.getText(licencePlateBox).isEmpty()) {
            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("Please add your car's licence plate no.", "LBL_ADD_LICENCE_PLATE"));
            return;
        }

        boolean isCarTypeSelected = false;
        String carTypes;

        carTypes = selectedCarTypes_DeliverAll;

        if (Utils.checkText(carTypes)) {
            isCarTypeSelected = true;
        }

        for (int i = 0; i < carTypesStatusArr.size(); i++) {
            if (carTypesStatusArr.get(i)) {
                isCarTypeSelected = true;

                JSONObject obj = generalFunc.getJsonObject(vehicletypelist, i);

                String iVehicleTypeId = generalFunc.getJsonValueStr("iVehicleTypeId", obj);

                carTypes = carTypes.isEmpty() ? iVehicleTypeId : (carTypes + "," + iVehicleTypeId);
            }
        }

        if (!isCarTypeSelected) {
            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl(".", "LBL_SELECT_CAR_TYPE"));
            return;
        }

        ishandicapavilabel = binding.checkboxHandicap.isChecked();

        ischildseatavilabel = binding.checkboxChildSeat.isChecked();

        iswheelchairavilabel = binding.checkboxWheelChair.isChecked();

        if (iDriverVehicleId.isEmpty()) {
            if (ENABLE_EDIT_DRIVER_VEHICLE != null && ENABLE_EDIT_DRIVER_VEHICLE.equalsIgnoreCase("No")) {

                try {

                    GenerateAlertBox editVehicleConfirmDialog = new GenerateAlertBox(getActContext());
                    editVehicleConfirmDialog.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_COMFIRM_ADD_VEHICLE"));
                    editVehicleConfirmDialog.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                    editVehicleConfirmDialog.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT"));
                    editVehicleConfirmDialog.setCancelable(false);
                    String finalCarTypes = carTypes;
                    editVehicleConfirmDialog.setBtnClickList(btn_id -> {
                        if (btn_id == 0) {
                            editVehicleConfirmDialog.closeAlertBox();
                        } else {
                            addVehicle(iSelectedMakeId, iSelectedModelId, finalCarTypes);
                        }
                    });
                    editVehicleConfirmDialog.showAlertBox();
                } catch (Exception e) {
                    addVehicle(iSelectedMakeId, iSelectedModelId, carTypes);
                }
            } else {
                addVehicle(iSelectedMakeId, iSelectedModelId, carTypes);
            }
        } else {
            addVehicle(iSelectedMakeId, iSelectedModelId, carTypes);
        }
    }

    public void addVehicle(String iMakeId, String iModelId, String vCarType) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateDriverVehicle");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("iMakeId", iMakeId);
        parameters.put("iModelId", iModelId);
        parameters.put("iYear", Utils.getText(yearBox));
        parameters.put("vLicencePlate", Utils.getText(licencePlateBox));
        parameters.put("vCarType", vCarType);
        parameters.put("eAddedDeliverVehicle", ServiceModule.DeliverAll && Utils.checkText(selectedCarTypes_DeliverAll) ? "Yes" : "No");

        for (int i = 0; i < rentalcarTypesStatusArr.size(); i++) {
            if (rentalcarTypesStatusArr.get(i)) {

                JSONObject obj = generalFunc.getJsonObject(vehicletypelist, i);

                String iVehicleTypeId = generalFunc.getJsonValueStr("iVehicleTypeId", obj);

                vRentalCarType = vRentalCarType.isEmpty() ? iVehicleTypeId : (vRentalCarType + "," + iVehicleTypeId);
            }
        }
        parameters.put("vRentalCarType", vRentalCarType);
        parameters.put("iDriverVehicleId", iDriverVehicleId);
        parameters.put("vColor", Utils.getText(colorPlateBox));

        parameters.put("HandiCap", ishandicapavilabel ? "Yes" : "No");
        parameters.put("ChildAccess", ischildseatavilabel ? "Yes" : "No");
        parameters.put("WheelChair", iswheelchairavilabel ? "Yes" : "No");

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

                    if (responseStringObject != null) {
                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

                        if (isDataAvail) {

                            try {
                                if (generalFunc.getJsonValue("VehicleInsertId", responseStringObject) != null) {
                                    tempiDriverVehicleId = generalFunc.getJsonValueStr("VehicleInsertId", responseStringObject);
                                }
                            } catch (Exception ignored) {

                            }

                            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                            generateAlert.setCancelable(false);
                            generateAlert.setBtnClickList(btn_id -> {
                                if (iDriverVehicleId.isEmpty()) {
                                    if (btn_id == 0) {
                                        generateAlert.closeAlertBox();
                                        Bundle bn = new Bundle();
                                        bn.putBoolean("isUploadDoc", false);
                                        bn.putString("iDriverVehicleId", tempiDriverVehicleId);
                                        new ActUtils(getActContext()).setOkResult(bn);
                                        backImgView.performClick();
                                    } else if (btn_id == 1) {
                                        Bundle bn = new Bundle();
                                        bn.putString("PAGE_TYPE", "vehicle");
                                        bn.putString("vLicencePlate", Utils.getText(licencePlateBox));
                                        bn.putString("eStatus", generalFunc.getJsonValueStr("VehicleStatus", responseStringObject));
                                        bn.putString("vMake", Utils.getText(makeBox));
                                        bn.putString("iDriverVehicleId", generalFunc.getJsonValueStr("VehicleInsertId", responseStringObject));
                                        bn.putString("vCarType", vCarType);
                                        bn.putString("iMakeId", iMakeId);
                                        bn.putString("iYear", Utils.getText(yearBox));
                                        bn.putString("iModelId", iModelId);
                                        bn.putString("vColour", Utils.getText(colorPlateBox));
                                        Bundle passBn = new Bundle();
                                        passBn.putBoolean("isUploadDoc", false);
                                        passBn.putString("iDriverVehicleId", tempiDriverVehicleId);
                                        new ActUtils(getActContext()).setOkResult(passBn);
                                        new ActUtils(getApplicationContext()).startActWithDataNewTask(ListOfDocumentActivity.class, bn);
                                        finish();

                                        iDriverVehicleId = tempiDriverVehicleId;
                                    }
                                } else {
                                    if (btn_id == 0) {
                                        generateAlert.closeAlertBox();
                                        Bundle bn = new Bundle();
                                        bn.putBoolean("isUploadDoc", false);
                                        bn.putString("iDriverVehicleId", tempiDriverVehicleId);
                                        new ActUtils(getActContext()).setOkResult(bn);
                                        backImgView.performClick();
                                    } else if (btn_id == 1) {
                                        generateAlert.closeAlertBox();
                                        Bundle bn = new Bundle();
                                        bn.putBoolean("isUploadDoc", false);
                                        bn.putString("iDriverVehicleId", tempiDriverVehicleId);
                                        new ActUtils(getActContext()).setOkResult(bn);
                                        backImgView.performClick();
                                    }
                                }
                            });
                            if (iDriverVehicleId.isEmpty()) {
                                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                                generateAlert.setNegativeBtn(LBL_SKIP_TXT);
                                generateAlert.setPositiveBtn(LBL_UPLOAD_DOC);
                            } else {
                                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                                generateAlert.setPositiveBtn(LBL_BTN_OK_TXT);
                            }

                            generateAlert.showAlertBox();

                        } else {
                            String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObject);
                            if (!iDriverVehicleId.isEmpty() && message.equalsIgnoreCase("LBL_EDIT_VEHICLE_DISABLED")) {
                                GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                                alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", message));
                                alertBox.setPositiveBtn(LBL_BTN_OK_TXT);
                                alertBox.setNegativeBtn(LBL_CONTACT_US_TXT);
                                alertBox.setBtnClickList(btn_id -> {
                                    if (btn_id == 0) {
                                        alertBox.closeAlertBox();
                                        new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                                    } else {
                                        alertBox.closeAlertBox();
                                    }
                                });
                                alertBox.showAlertBox();
                            } else {
                                generalFunc.showGeneralMessage("",
                                        generalFunc.retrieveLangLBl("", message));
                            }
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }
}