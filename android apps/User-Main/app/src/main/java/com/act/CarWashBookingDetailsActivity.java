package com.act;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RadioButton;
import android.widget.ScrollView;

import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.content.ContextCompat;

import com.activity.ParentActivity;
import com.dialogs.RequestNearestCab;
import com.general.files.ActUtils;
import com.general.files.CovidDialog;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.RecurringTask;
import com.buddyverse.main.R;
import com.realmModel.CarWashCartData;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Locale;
import java.util.Objects;

import io.realm.Realm;
import io.realm.RealmResults;

public class CarWashBookingDetailsActivity extends ParentActivity {

    private static final int ADD_ADDRESS_REQ_CODE = 165;
    private static final int SEL_ADDRESS_REQ_CODE = 155;

    ImageView backImgView;
    MTextView titleTxt;
    LinearLayout itemContainer, itemChargeContainer;
    MTextView subtotalHTxt, subtotalVTxt;
    MTextView serviceTxt, driverNameTxt, bookDateHTxt, bookDateVTxt, booktimeHTxt, booktimeVTxt;
    View couponCodeArea;
    MTextView applyCouponHTxt;
    LinearLayout promocodeArea;
    MTextView promocodeappliedHTxt, promocodeappliedVTxt, appliedPromoHTxtView;
    ImageView couponCodeImgView, couponCodeCloseImgView;
    private String appliedPromoCode = "";
    ArrayList<String> iVehicleTypeIdList;
    ArrayList<String> fVehicleTypeQtyList;
    LinearLayout addressArea, locationArea;
    MTextView locationHTxt;

    boolean iscash = true;
    public MButton btn_type2_now, btn_type_later;
    MTextView selLocTxt;
    boolean isUserLocation = true;
    String totalAddressCount = "";
    String iUserAddressId = "";
    String iTempUserId = "";
    String vServiceAddress = "";
    String vLatitude = "", vLongitude = "";
    MTextView userAddressTxt;
    public RequestNearestCab requestNearestCab;
    GenerateAlertBox reqSentErrorDialog = null;
    String bookingtype = Utils.CabReqType_Now;
    String SelectDate = "", sdate = "", Stime = "";
    boolean eWalletDebitAllow = false;
    androidx.appcompat.app.AlertDialog outstanding_dialog;

    View containerView;
    View mProgressBar;

    boolean clickable = false;
    boolean isPayNow = false;
    LinearLayout btn_type2Area, reschedulearea;

    String provider_away_str = "";
    RecurringTask allCabRequestTask;
    androidx.appcompat.app.AlertDialog alertDialog_surgeConfirm;
    RealmResults<CarWashCartData> realmCartList;
    public String eWalletIgnore = "No";
    public String ePaymentBy = "Passenger";

    String LBL_CHANGE = "";
    String SYSTEM_PAYMENT_FLOW = "";
    String APP_PAYMENT_METHOD = "";
    String APP_PAYMENT_MODE = "";
    MTextView serviceHTxt, chargeHTxt;
    RadioButton userLocRadioBtn, providerLocRadioBtn;
    LinearLayout AddAddressArea, EditAddressArea;
    MTextView addAddressHTxt, addAddressBtn, changeBtn, seldriverNameTxt, ratingTxt;
    AppCompatImageView addChangeLocImgView;
    ImageView driverImg;
    private static final int WEBVIEWPAYMENT = 001;
    private boolean isVideoConsultEnable;
    private String mReqMsgCode;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_car_wash_booking_details);


        getUserProfileJson();

        isVideoConsultEnable = getIntent().getBooleanExtra("isVideoConsultEnable", false);
        if (isVideoConsultEnable) {
            isUserLocation = false;
        }

        driverImg = (ImageView) findViewById(R.id.driverImg);
        new CreateRoundedView(getResources().getColor(R.color.white), (int) getResources().getDimension(R.dimen._30sdp), 0,
                0, driverImg);
        seldriverNameTxt = (MTextView) findViewById(R.id.seldriverNameTxt);
        ratingTxt = (MTextView) findViewById(R.id.ratingTxt);
//        btnView = (View) findViewById(R.id.btnView);
        userLocRadioBtn = (RadioButton) findViewById(R.id.userLocRadioBtn);
        providerLocRadioBtn = (RadioButton) findViewById(R.id.providerLocRadioBtn);
        serviceHTxt = (MTextView) findViewById(R.id.serviceHTxt);
        chargeHTxt = (MTextView) findViewById(R.id.chargeHTxt);
        itemContainer = (LinearLayout) findViewById(R.id.itemContainer);
        itemChargeContainer = (LinearLayout) findViewById(R.id.itemChargeContainer);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        subtotalHTxt = (MTextView) findViewById(R.id.subtotalHTxt);
        subtotalVTxt = (MTextView) findViewById(R.id.subtotalVTxt);
        serviceTxt = (MTextView) findViewById(R.id.serviceTxt);
        driverNameTxt = (MTextView) findViewById(R.id.driverNameTxt);
        bookDateHTxt = (MTextView) findViewById(R.id.bookDateHTxt);
        bookDateVTxt = (MTextView) findViewById(R.id.bookDateVTxt);
        booktimeHTxt = (MTextView) findViewById(R.id.booktimeHTxt);
        booktimeVTxt = (MTextView) findViewById(R.id.booktimeVTxt);
        applyCouponHTxt = (MTextView) findViewById(R.id.applyCouponHTxt);

        locationHTxt = (MTextView) findViewById(R.id.locationHTxt);
        addressArea = (LinearLayout) findViewById(R.id.addressArea);
        locationArea = (LinearLayout) findViewById(R.id.locationArea);
        couponCodeArea = findViewById(R.id.couponCodeArea);
        addToClickHandler(couponCodeArea);
        promocodeArea = (LinearLayout) findViewById(R.id.promocodeArea);
        promocodeappliedHTxt = (MTextView) findViewById(R.id.promocodeappliedHTxt);
        promocodeappliedVTxt = (MTextView) findViewById(R.id.promocodeappliedVTxt);
        appliedPromoHTxtView = (MTextView) findViewById(R.id.appliedPromoHTxtView);
        couponCodeImgView = (ImageView) findViewById(R.id.couponCodeImgView);
        couponCodeCloseImgView = (ImageView) findViewById(R.id.couponCodeCloseImgView);
        userAddressTxt = (MTextView) findViewById(R.id.userAddressTxt);

        btn_type2Area = (LinearLayout) findViewById(R.id.btn_type2Area);
        reschedulearea = (LinearLayout) findViewById(R.id.reschedulearea);
        AddAddressArea = (LinearLayout) findViewById(R.id.AddAddressArea);
        EditAddressArea = (LinearLayout) findViewById(R.id.EditAddressArea);
        addAddressHTxt = (MTextView) findViewById(R.id.addAddressHTxt);
        addAddressBtn = (MTextView) findViewById(R.id.addAddressBtn);
        changeBtn = (MTextView) findViewById(R.id.changeBtn);
        addChangeLocImgView = findViewById(R.id.addChangeLocImgView);
        addToClickHandler(changeBtn);
        addToClickHandler(addChangeLocImgView);

        containerView = findViewById(R.id.containerView);
        mProgressBar = findViewById(R.id.mProgressBar);

        btn_type2Area.setVisibility(View.VISIBLE);
        userAddressTxt.setVisibility(View.VISIBLE);

        selLocTxt = (MTextView) findViewById(R.id.selLocTxt);


        changeBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CHANGE"));
        addAddressBtn.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_ADDRESS_TXT"));
        addAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_SERVICE_LOCATION"));
        userLocRadioBtn.setText(generalFunc.retrieveLangLBl("", "LBL_AT_USER_LOCATION"));
        providerLocRadioBtn.setText(generalFunc.retrieveLangLBl("", "LBL_AT_PROVIDER_LOCATION"));
        serviceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICES"));
        chargeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHARGES_TXT"));
        selLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_LOCATION"));
        btn_type2_now = ((MaterialRippleLayout) findViewById(R.id.btn_type2_now)).getChildView();
        btn_type_later = ((MaterialRippleLayout) findViewById(R.id.btn_type_later)).getChildView();
        addToClickHandler(btn_type2_now);
        addToClickHandler(btn_type_later);
        addToClickHandler(addAddressBtn);

        appliedPromoHTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));
        btn_type2_now.setText(generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW"));
        btn_type_later.setText(generalFunc.retrieveLangLBl("", "LBL_BOOK_LATER"));
        locationHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_BOOKING_LOCATION"));

        subtotalHTxt.setText(generalFunc.retrieveLangLBl("SubTotal", "LBL_SUBTOTAL_TXT"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BOOKING_DETAILS_TXT"));
        bookDateHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BOOKING_DATE"));
        booktimeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BOOKING_TIME"));
        applyCouponHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY_PROMO_TXT"));

        LBL_CHANGE = generalFunc.retrieveLangLBl("", "LBL_CHANGE");


        driverNameTxt.setText(getIntent().getStringExtra("name"));

        ratingTxt.setText(getIntent().getStringExtra("average_rating"));


        String image_url = CommonUtilities.PROVIDER_PHOTO_PATH + getIntent().getStringExtra("iDriverId") + "/" + getIntent().getStringExtra("driver_img");

        new LoadImage.builder(LoadImage.bind(image_url), driverImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        seldriverNameTxt.setText(getIntent().getStringExtra("name"));
        serviceTxt.setText(getIntent().getStringExtra("serviceName"));
        booktimeVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NOW"));


        if (isUserLocation) {
            userLocRadioBtn.setChecked(true);
        } else {
            providerLocRadioBtn.setChecked(true);
        }

        userLocRadioBtn.setOnClickListener(view -> {
            isUserLocation = true;
            locationArea.setVisibility(View.VISIBLE);
            setLocationView();
            ((ScrollView) findViewById(R.id.scrollView)).post(() -> ((ScrollView) findViewById(R.id.scrollView)).fullScroll(ScrollView.FOCUS_DOWN));
        });

        providerLocRadioBtn.setOnClickListener(view -> {
            locationArea.setVisibility(View.GONE);
            isUserLocation = false;
            setLocationView();


        });


        bookDateVTxt.setText(generalFunc.getCurrentdate());
        realmCartList = getCartData();
        if (realmCartList != null && realmCartList.size() > 0) {
            for (int i = 0; i < realmCartList.size(); i++) {
                iVehicleTypeIdList.add(realmCartList.get(i).getCategoryListItem().getiVehicleTypeId());
                fVehicleTypeQtyList.add(realmCartList.get(i).getItemCount());
            }
        }

        if (generalFunc.isRTLmode()) {
            couponCodeImgView.setRotation(180);
            backImgView.setRotation(180);
        }

        double distance = Utils.CalculationByLocation(GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("latitude")), GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("longitude")), GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("vProviderLatitude")), GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("vProviderLongitude")), "");

        if (generalFunc.getJsonValueStr("eUnit", obj_userProfile).equals("KMs")) {
            provider_away_str = String.format(Locale.ENGLISH, "%.2f", (float) distance) + " " + generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT") + " " + generalFunc.retrieveLangLBl("", "LBL_AWAY");
        } else {
            provider_away_str = String.format(Locale.ENGLISH, "%.2f", (float) (distance * 0.621371)) + " " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT") + " " + generalFunc.retrieveLangLBl("", "LBL_AWAY");
        }

        getDetails();
    }

    private void getUserProfileJson() {


        APP_PAYMENT_MODE = generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile);
        SYSTEM_PAYMENT_FLOW = generalFunc.getJsonValueStr("SYSTEM_PAYMENT_FLOW", obj_userProfile);
        APP_PAYMENT_METHOD = generalFunc.getJsonValueStr("APP_PAYMENT_METHOD", obj_userProfile);
    }

    public void setPromoCode() {
        if (appliedPromoCode.equalsIgnoreCase("")) {
            defaultPromoView();
        } else {
            appliedPromoView();
        }
    }

    public void defaultPromoView() {
        promocodeArea.setVisibility(View.GONE);
        appliedPromoHTxtView.setVisibility(View.GONE);

        couponCodeCloseImgView.setVisibility(View.GONE);
        couponCodeImgView.setVisibility(View.VISIBLE);

        applyCouponHTxt.setTextColor(Color.parseColor("#333333"));
        applyCouponHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY_PROMO_TXT"));

        promocodeappliedHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));
    }

    public void appliedPromoView() {
        appliedPromoHTxtView.setVisibility(View.VISIBLE);
        applyCouponHTxt.setText(appliedPromoCode);
        applyCouponHTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        addToClickHandler(couponCodeCloseImgView);
        couponCodeCloseImgView.setVisibility(View.VISIBLE);
        couponCodeImgView.setVisibility(View.GONE);
        appliedPromoHTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));
    }

    public Context getActContext() {
        return CarWashBookingDetailsActivity.this;
    }

    public void addChargesView(JSONArray chargeArray) {
        if (chargeArray == null || chargeArray.length() == 0) {
            return;
        }
        if (itemChargeContainer.getChildCount() > 0) {
            itemChargeContainer.removeAllViewsInLayout();
        }

        for (int i = 0; i < chargeArray.length(); i++) {

            JSONObject jobject = generalFunc.getJsonObject(chargeArray, i);
            String rName = generalFunc.getJsonValueStr("Title", jobject);
            if (generalFunc.getJsonValueStr("eDisplaySeperator", jobject).equalsIgnoreCase("Yes")) {
                try {
                    rName = Objects.requireNonNull(jobject.names()).getString(0);
                } catch (JSONException e) {
                    throw new RuntimeException(e);
                }
            }
            String rValue = generalFunc.getJsonValueStr("Amount", jobject);
            itemChargeContainer.addView(MyUtils.addFareDetailRow(this, generalFunc, rName, rValue, (chargeArray.length() - 1) == i));
        }

    }

    public void addItemView(JSONArray itemArray) {


        if (itemArray == null || itemArray.length() == 0) {
            return;
        }


        if (itemContainer.getChildCount() > 0) {
            itemContainer.removeAllViewsInLayout();
        }


        for (int i = 0; i < itemArray.length(); i++) {
            JSONObject data = generalFunc.getJsonObject(itemArray, i);

            LayoutInflater topinginflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            View editCartView = topinginflater.inflate(R.layout.item_uberxcheckout_row, null);
            MTextView itemNameTxtView = (MTextView) editCartView.findViewById(R.id.itemNameTxtView);
            itemNameTxtView.setSelected(true);
            LinearLayout mainView = (LinearLayout) editCartView.findViewById(R.id.mainView);
            MTextView itemMenuNameTxtView = (MTextView) editCartView.findViewById(R.id.itemMenuNameTxtView);
            itemMenuNameTxtView.setSelected(true);
            MTextView itemPriceTxtView = (MTextView) editCartView.findViewById(R.id.itemPriceTxtView);
            MTextView itemstrikePriceTxtView = (MTextView) editCartView.findViewById(R.id.itemstrikePriceTxtView);
            MTextView QTYNumberTxtView = (MTextView) editCartView.findViewById(R.id.QTYNumberTxtView);
            ImageView cancelImg = (ImageView) editCartView.findViewById(R.id.cancelImg);
            MTextView hourTxt = (MTextView) editCartView.findViewById(R.id.hourTxt);
            LinearLayout layoutShape = (LinearLayout) editCartView.findViewById(R.id.layoutShape);
            itemPriceTxtView.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("Amount", data)));
            itemstrikePriceTxtView.setVisibility(View.GONE);
            itemNameTxtView.setText(generalFunc.getJsonValueStr("Title", data));
            itemMenuNameTxtView.setVisibility(View.GONE);
            hourTxt.setText("/" + generalFunc.retrieveLangLBl("", "LBL_HOUR_TXT"));


            if (generalFunc.isRTLmode()) {
                layoutShape.setBackgroundResource(R.drawable.ic_shape_rtl);
            }

            if (generalFunc.getJsonValueStr("Amount", data).equalsIgnoreCase("")) {
                itemPriceTxtView.setVisibility(View.GONE);
                hourTxt.setVisibility(View.GONE);

            }

            if (generalFunc.getJsonValueStr("eFareType", data).equalsIgnoreCase("Hourly")) {
                hourTxt.setVisibility(View.VISIBLE);
            } else {
                hourTxt.setVisibility(View.GONE);
            }

            QTYNumberTxtView.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("Qty", data)));

            cancelImg.setTag(i);

            cancelImg.setOnClickListener(view -> {
                try {


                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        if (btn_id == 1) {
                            Realm realm = MyApp.getRealmInstance();
                            realm.beginTransaction();
                            CarWashCartData carWashCartData = realmCartList.get((Integer) cancelImg.getTag());
                            if (carWashCartData != null) {
                                carWashCartData.deleteFromRealm();
                            }
                            realm.commitTransaction();

                            realmCartList = getCartData();


                            if (realmCartList.size() == 0) {
                                onBackPressed();
                            }
                            getDetails();

                        }
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("Are you sure want to delete", "LBL_DELETE_CONFIRM_MSG"));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
                    generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("cANCEL", "LBL_CANCEL_TXT"));
                    generateAlert.showAlertBox();


                } catch (Exception e) {
                    Logger.e("TestCrash", "::" + e.toString());
                }

            });

            itemContainer.addView(editCartView);
        }
    }


    public void getDetails() {

        containerView.setVisibility(View.GONE);
        mProgressBar.setVisibility(View.VISIBLE);

        JSONArray orderedItemArr = new JSONArray();
        if (realmCartList != null && realmCartList.size() > 0) {
            try {
                for (int i = 0; i < realmCartList.size(); i++) {
                    CarWashCartData posData = realmCartList.get(i);
                    JSONObject object = new JSONObject();
                    object.put("iVehicleTypeId", posData.getCategoryListItem().getiVehicleTypeId());
                    object.put("fVehicleTypeQty", posData.getItemCount());
                    object.put("tUserComment", posData.getSpecialInstruction());
                    orderedItemArr.put(object);
                }
            } catch (Exception e) {
                Logger.e("TestCrash", "::" + e.toString());
            }
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getVehicleTypeFareDetails");
        parameters.put("SelectedCabType", Utils.CabGeneralType_UberX);
        parameters.put("iVehicleTypeId", android.text.TextUtils.join(",", iVehicleTypeIdList));
        parameters.put("fVehicleTypeQty", android.text.TextUtils.join(",", fVehicleTypeQtyList));
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("vCouponCode", appliedPromoCode.trim());
        parameters.put("OrderDetails", orderedItemArr.toString());
        parameters.put("iDriverId", getIntent().getStringExtra("iDriverId"));
        parameters.put("vSelectedLatitude", getIntent().getStringExtra("latitude"));
        parameters.put("vSelectedLongitude", getIntent().getStringExtra("longitude"));
        parameters.put("vSelectedAddress", getIntent().getStringExtra("address"));
        parameters.put("iUserAddressId", iUserAddressId);
        parameters.put("eForVideoConsultation", isVideoConsultEnable ? "Yes" : "No");

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail) {

                    if (generalFunc.getJsonValue("eEnableServiceAtProviderLocation", responseString).equalsIgnoreCase("Yes")) {
                        addressArea.setVisibility(View.VISIBLE);
                    } else {
                        addressArea.setVisibility(View.GONE);
                    }

                    String vAvailability = generalFunc.getJsonValue("vAvailability", responseString);
                    String vScheduleAvailability = generalFunc.getJsonValue("vScheduleAvailability", responseString);

                    if (vAvailability.equalsIgnoreCase("No")) {
                        btn_type2Area.setVisibility(View.GONE);
                    } else {
                        btn_type2Area.setVisibility(View.VISIBLE);
                        btn_type2_now.setText(generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW"));
                        addToClickHandler(btn_type2_now);
                    }
                    if (vScheduleAvailability.equalsIgnoreCase("No")) {
                        reschedulearea.setVisibility(View.GONE);
                    } else {
                        reschedulearea.setVisibility(View.VISIBLE);
                    }

                    if (vAvailability.equalsIgnoreCase("No") && vScheduleAvailability.equalsIgnoreCase("No")) {
                        btn_type2Area.setVisibility(View.VISIBLE);
                        btn_type2_now.setText(generalFunc.retrieveLangLBl("", "LBL_PROVIDER_NOT_AVAILABLE_NOTE"));
                        btn_type2_now.setOnClickListener(null);
                    }

                    totalAddressCount = generalFunc.getJsonValue("totalAddressCount", responseString);
                    vServiceAddress = generalFunc.getJsonValue("vServiceAddress", responseString);
                    vLatitude = generalFunc.getJsonValue("vServiceAddressLatitude", responseString);
                    vLongitude = generalFunc.getJsonValue("vServiceAddressLongitude", responseString);
                    iUserAddressId = generalFunc.getJsonValue("iUserAddressId", responseString);
                    iTempUserId = generalFunc.getJsonValue("iUserAddressId", responseString);

                    userAddressTxt.setText(vServiceAddress);

                    if ((GeneralFunctions.parseIntegerValue(0, totalAddressCount) >= 1) && Utils.checkText(vServiceAddress)) {
                        AddAddressArea.setVisibility(View.GONE);
                        EditAddressArea.setVisibility(View.VISIBLE);
                    } else {
                        EditAddressArea.setVisibility(View.GONE);
                        AddAddressArea.setVisibility(View.VISIBLE);
                    }

                    addItemView(generalFunc.getJsonArray("items", responseString));
                    addChargesView(generalFunc.getJsonArray("vehiclePriceTypeArrCubex", responseString));

                    containerView.setVisibility(View.VISIBLE);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message", responseString)), true);
                }

                mProgressBar.setVisibility(View.GONE);

                if (isVideoConsultEnable) {
                    locationArea.setVisibility(View.GONE);
                }

            } else {
                generalFunc.showError(true);
            }
        });
        exeWebServer.setCancelAble(false);
    }

    public RealmResults<CarWashCartData> getCartData() {
        try {
            iVehicleTypeIdList = new ArrayList<>();
            fVehicleTypeQtyList = new ArrayList<>();
            Realm realm = MyApp.getRealmInstance();
            return realm.where(CarWashCartData.class).findAll();
        } catch (Exception e) {
            Logger.d("RealmException", "::" + e.toString());
        }
        return null;
    }


    public void setLocationView() {
        if (isUserLocation) {
            userAddressTxt.setVisibility(View.VISIBLE);
            selLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_LOCATION"));
            userAddressTxt.setText(vServiceAddress);
            iUserAddressId = iTempUserId;
        } else {
            iUserAddressId = "";
            userAddressTxt.setVisibility(View.VISIBLE);

            selLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AT_PROVIDER_LOCATION"));
            userAddressTxt.setText(provider_away_str);
        }
    }


    public void OpenCardPaymentAct() {
        Bundle bn = new Bundle();
        new ActUtils(getActContext()).startActForResult(CardPaymentActivity.class, bn, Utils.CARD_PAYMENT_REQ_CODE);
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == couponCodeArea.getId()) {
            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                generalFunc.showMessage(applyCouponHTxt, generalFunc.retrieveLangLBl("", "LBL_PROMO_CODE_LOGIN_HINT"));
            } else {
                Bundle bn = new Bundle();
                bn.putString("CouponCode", appliedPromoCode);
                bn.putString("eType", Utils.CabGeneralType_UberX);
                bn.putString("vSourceLatitude", getIntent().getStringExtra("latitude"));
                bn.putString("vSourceLongitude", getIntent().getStringExtra("longitude"));
                bn.putString("vDestLatitude", vLatitude);
                bn.putString("vDestLongitude", vLongitude);
                bn.putString("eTakeAway", "No");
                bn.putBoolean("isVideoConsultEnable", isVideoConsultEnable ? true : false);
                new ActUtils(getActContext()).startActForResult(CouponActivity.class, bn, Utils.SELECT_COUPON_REQ_CODE);
            }
        } else if (i == R.id.couponCodeCloseImgView) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DELETE_CONFIRM_COUPON_MSG"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                if (buttonId == 1) {
                    appliedPromoCode = "";
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_COUPON_REMOVE_SUCCESS"));
                    getDetails();
                    setPromoCode();
                }
            });
        } else if (i == R.id.addAddressBtn) {
            Bundle bn = new Bundle();
            bn.putString("iCompanyId", "-1");
            bn.putString("iUserAddressId", iUserAddressId);
            bn.putString("latitude", getIntent().getStringExtra("latitude"));
            bn.putString("longitude", getIntent().getStringExtra("longitude"));
            bn.putString("address", getIntent().getStringExtra("address"));
            new ActUtils(getActContext()).startActForResult(AddAddressActivity.class, bn, ADD_ADDRESS_REQ_CODE);
        } else if (view == btn_type2_now) {
            if (isUserLocation && !Utils.checkText(iUserAddressId)) {
                generalFunc.showMessage(addressArea, generalFunc.retrieveLangLBl("", "LBL_PLEASE_SELECT_SERVICE_LOCATION_MSG"));
                return;
            }

            bookingtype = Utils.CabReqType_Now;
            Bundle bn = new Bundle();

            String url = generalFunc.getJsonValueStr("PAYMENT_MODE_URL", obj_userProfile) + "&eType=" + Utils.CabGeneralType_UberX;


            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();
            if (isVideoConsultEnable) {
                url = url + "&eForVideoConsultation=" + "Yes";
            }
            url = url + "&iDriverId=" + getIntent().getStringExtra("iDriverId");

            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);

        } else if (view == btn_type_later) {
            if (isUserLocation && !Utils.checkText(iUserAddressId)) {
                generalFunc.showMessage(addressArea, generalFunc.retrieveLangLBl("", "LBL_PLEASE_SELECT_SERVICE_LOCATION_MSG"));
                return;
            }
            bookingtype = Utils.CabReqType_Later;
            Bundle bn = new Bundle();

            String url = generalFunc.getJsonValueStr("PAYMENT_MODE_URL", obj_userProfile) + "&eType=" + Utils.CabGeneralType_UberX;


            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();
            if (isVideoConsultEnable) {
                url = url + "&eForVideoConsultation=" + "Yes";
            }
            url = url + "&iDriverId=" + getIntent().getStringExtra("iDriverId");

            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);

        } else if (view == addChangeLocImgView) {
            Bundle bn = new Bundle();
            bn.putString("iCompanyId", "-1");
            bn.putString("iUserAddressId", iUserAddressId);
            bn.putString("latitude", getIntent().getStringExtra("latitude"));
            bn.putString("longitude", getIntent().getStringExtra("longitude"));
            bn.putString("address", getIntent().getStringExtra("address"));
            if (GeneralFunctions.parseIntegerValue(0, totalAddressCount) >= 1) {
                bn.putString("iDriverId", getIntent().getStringExtra("iDriverId"));
                bn.putBoolean("isUfx", true);
                new ActUtils(getActContext()).startActForResult(ListAddressActivity.class, bn, SEL_ADDRESS_REQ_CODE);
            } else {
                new ActUtils(getActContext()).startActForResult(AddAddressActivity.class, bn, ADD_ADDRESS_REQ_CODE);
            }

        }
    }


    public void getOutStandingAmout(String clicked, String paymentType) {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "checkSurgePrice");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.userType);
        parameters.put("SelectedCarTypeID", android.text.TextUtils.join(",", iVehicleTypeIdList));
        parameters.put("ePaymentmethod", isCashSelected ? "cash" : "card");
        if (!SelectDate.trim().equals("")) {
            parameters.put("SelectedTime", SelectDate);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equals("")) {
                openSurgeConfirmDialog(responseString, paymentType);
            } else {
                generalFunc.showError();
            }
        });
    }

    public void openSurgeConfirmDialog(String message, String paymentType) {

        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        builder.setTitle("");
        builder.setCancelable(false);
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.surge_confirm_design, null);
        builder.setView(dialogView);

        MTextView payableAmountTxt;
        MTextView payableTxt;

        if (generalFunc.getJsonValue("SurgePrice", message) != null && generalFunc.getJsonValue("SurgePrice", message).equalsIgnoreCase("")) {
            checkOutStandingAmount(message, paymentType);
            return;
        }

        ((MTextView) dialogView.findViewById(R.id.headerMsgTxt)).setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, message)));
        ((MTextView) dialogView.findViewById(R.id.surgePriceTxt)).setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("SurgePrice", message)));

        ((MTextView) dialogView.findViewById(R.id.tryLaterTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_TRY_LATER"));

        payableTxt = (MTextView) dialogView.findViewById(R.id.payableTxt);
        payableAmountTxt = (MTextView) dialogView.findViewById(R.id.payableAmountTxt);
        payableTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PAYABLE_AMOUNT"));

        payableAmountTxt.setVisibility(View.GONE);
        payableTxt.setVisibility(View.VISIBLE);

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_SURGE"));
        btn_type2.setId(Utils.generateViewId());

        btn_type2.setOnClickListener(view -> {
            alertDialog_surgeConfirm.dismiss();
            checkOutStandingAmount(message, paymentType);
        });

        (dialogView.findViewById(R.id.tryLaterTxt)).setOnClickListener(view -> {
            alertDialog_surgeConfirm.dismiss();
        });

        alertDialog_surgeConfirm = builder.create();
        alertDialog_surgeConfirm.setCancelable(false);
        alertDialog_surgeConfirm.setCanceledOnTouchOutside(false);
        LayoutDirection.setLayoutDirection(alertDialog_surgeConfirm);
        alertDialog_surgeConfirm.show();
    }

    public void checkOutStandingAmount(String message, String paymentType) {
        String fOutStandingAmount = "";
        if (Utils.checkText(message)) {
            fOutStandingAmount = generalFunc.getJsonValue("fOutStandingAmount", message);
        }

        boolean isDataAvail = GeneralFunctions.parseDoubleValue(0.0, fOutStandingAmount) > 0;

        if (iscash && isDataAvail) {
            outstandingDialog(message, paymentType);
        } else if (!iscash && isDataAvail) {

            outstandingDialog(message, paymentType);


        } else {

            if (paymentType.equals(Utils.CabReqType_Now)) {
                sendRequestToDrivers(false);
            } else {
                bookScheduleRide();
            }
        }

    }

    String ShowAdjustTripBtn;
    String ShowPayNow;
    String ShowContactUsBtn;

    public void outstandingDialog(String data, String paymentType) {
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dailog_outstanding, null);

        final MTextView outStandingTitle = (MTextView) dialogView.findViewById(R.id.outStandingTitle);
        final MTextView outStandingValue = (MTextView) dialogView.findViewById(R.id.outStandingValue);
        final MTextView cardtitleTxt = (MTextView) dialogView.findViewById(R.id.cardtitleTxt);
        final MTextView adjustTitleTxt = (MTextView) dialogView.findViewById(R.id.adjustTitleTxt);
        final LinearLayout cardArea = (LinearLayout) dialogView.findViewById(R.id.cardArea);
        final LinearLayout adjustarea = (LinearLayout) dialogView.findViewById(R.id.adjustarea);
        final MTextView adjustSubTitleTxt = dialogView.findViewById(R.id.adjustSubTitleTxt);
        final MTextView adjustTripMessageTxt = dialogView.findViewById(R.id.adjustTripMessageTxt);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.imgCardPayNow)).setRotationY(180);
            (dialogView.findViewById(R.id.imgAdjustInTrip)).setRotationY(180);
        }
        outStandingTitle.setText(generalFunc.retrieveLangLBl("", "LBL_OUTSTANDING_AMOUNT_TXT"));
        outStandingValue.setText(generalFunc.getJsonValue("fOutStandingAmountWithSymbol", data));
        cardtitleTxt.setText(generalFunc.retrieveLangLBl("Pay Now", "LBL_PAY_NOW"));
        adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in Your trip", "LBL_ADJUST_OUT_AMT_JOB_TXT"));
        adjustSubTitleTxt.setText(generalFunc.retrieveLangLBl("Outstanding amount will be added in invoice total amount.", "LBL_OUTSTANDING_AMOUNT_ADDED_INVOICE_NOTE"));
        ShowAdjustTripBtn = generalFunc.getJsonValue("ShowAdjustTripBtn", data);
        ShowAdjustTripBtn = (ShowAdjustTripBtn == null || ShowAdjustTripBtn.isEmpty()) ? "No" : ShowAdjustTripBtn;
        ShowPayNow = generalFunc.getJsonValue("ShowPayNow", data);
        ShowPayNow = (ShowPayNow == null || ShowPayNow.isEmpty()) ? "No" : ShowPayNow;
        ShowContactUsBtn = generalFunc.getJsonValue("ShowContactUsBtn", data);
        ShowContactUsBtn = (ShowContactUsBtn == null || ShowContactUsBtn.isEmpty()) ? "No" : ShowContactUsBtn;


        if (ShowPayNow.equalsIgnoreCase("Yes") && ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.VISIBLE);
        } else if (ShowPayNow.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        } else if (ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            /*String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", data);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;
            }*/
            adjustarea.setVisibility(View.VISIBLE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
        } else {
            adjustarea.setVisibility(View.GONE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
            adjustTripMessageTxt.setVisibility(View.VISIBLE);
            //String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", data);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                adjustTripMessageTxt.setText(outstanding_restriction_label);
            }
        }
        /*else if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {

            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", data);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 1) {
                        new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                    }
                });
                generateAlert.setContentMessage("", outstanding_restriction_label);
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_OK"));
                if (ShowContactUsBtn != null && ShowContactUsBtn.equalsIgnoreCase("Yes")) {
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
                }
                generateAlert.showAlertBox();
                return;
            }
        }*/

        final LinearLayout contactUsArea = dialogView.findViewById(R.id.contactUsArea);
        contactUsArea.setVisibility(View.GONE);
        ShowContactUsBtn = generalFunc.getJsonValueStr("ShowContactUsBtn", obj_userProfile);
        if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
            MTextView contactUsTxt = dialogView.findViewById(R.id.contactUsTxt);
            contactUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
            contactUsArea.setVisibility(View.VISIBLE);
            contactUsArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(ContactUsActivity.class));
            /*if (generalFunc.isRTLmode()) {
                (dialogView.findViewById(R.id.contactUsArrow)).setRotationY(180);
            }*/
        }

        cardArea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            clickable = false;

            isPayNow = true;
            getUserProfileJson();
            getUserProfileJson();

            callOutStandingPayAmout();

        });

        adjustarea.setOnClickListener(v -> {


            outstanding_dialog.dismiss();
            /*String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", data);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {

                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;

            }*/

            clickable = false;
            if (generalFunc.getJsonValueStr("eWalletBalanceAvailable", obj_userProfile).equalsIgnoreCase("Yes")) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 1) {
                        eWalletDebitAllow = true;

                        if (paymentType.equals(Utils.CabReqType_Now)) {
                            sendRequestToDrivers(false);
                        } else {
                            bookScheduleRide();
                        }

                    } else {
                        eWalletDebitAllow = false;
                        if (paymentType.equals(Utils.CabReqType_Now)) {
                            sendRequestToDrivers(false);
                        } else {
                            bookScheduleRide();
                        }
                    }
                });

                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_WALLET_BALANCE_EXIST_JOB").replace("####", generalFunc.getJsonValueStr("user_available_balance", obj_userProfile)));

                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
                generateAlert.showAlertBox();

            } else {
                eWalletDebitAllow = false;
                if (paymentType.equalsIgnoreCase(Utils.CabReqType_Now)) {
                    sendRequestToDrivers(false);
                } else {
                    bookScheduleRide();
                }
            }
        });

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1)));
        btn_type2.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        btn_type2.setOnClickListener(v -> {
            clickable = false;
            outstanding_dialog.dismiss();
        });

        builder.setView(dialogView);
        outstanding_dialog = builder.create();
        LayoutDirection.setLayoutDirection(outstanding_dialog);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.cardimagearrow)).setRotationY(180);
            (dialogView.findViewById(R.id.adjustimagearrow)).setRotationY(180);
        }
        outstanding_dialog.setCancelable(false);
        outstanding_dialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        outstanding_dialog.show();
    }

    public void callOutStandingPayAmout() {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "ChargePassengerOutstandingAmount");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                    getUserProfileJson();

                    clickable = false;
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                } else {
                    if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LOW_WALLET_AMOUNT")) {
                        String walletMsg = "";
                        String low_balance_content_msg = generalFunc.getJsonValue("low_balance_content_msg", responseString);

                        if (low_balance_content_msg != null && !low_balance_content_msg.equalsIgnoreCase("")) {
                            walletMsg = low_balance_content_msg;
                        } else {
                            walletMsg = generalFunc.retrieveLangLBl("", "LBL_WALLET_LOW_AMOUNT_MSG_TXT");
                        }
                        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BALANCE"), walletMsg, generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"), button_Id -> {
                            if (button_Id == 1) {

                                new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                            } else {

                            }
                        });
                    } else {

                        Bundle bn = new Bundle();
                        bn.putString("url", generalFunc.getJsonValue("OUTSTANDING_PAYMENT_URL", responseString));
                        bn.putBoolean("isApiCall", true);
                        new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
                    }

                }


            } else {

                if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LBL_OUTSTANDING_AMOUT_ALREADY_PAID_TXT")) {
                    String message = generalFunc.getJsonValue(Utils.message_str_one, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                    getUserProfileJson();
                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            }

        });
        exeWebServer.setCancelAble(false);

    }


    private void sendRequestToDrivers(boolean isRetry) {

        try {
            if (requestNearestCab == null) {
                requestNearestCab = new RequestNearestCab(getActContext(), generalFunc);
                requestNearestCab.run();
            }
            if (allCabRequestTask != null) {
                allCabRequestTask.stopRepeatingTask();
                allCabRequestTask = null;
            }
            int interval = GeneralFunctions.parseIntegerValue(30, generalFunc.getJsonValue("RIDER_REQUEST_ACCEPT_TIME", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
            allCabRequestTask = new RecurringTask((interval + 5) * 1000);
            allCabRequestTask.startRepeatingTask();
            allCabRequestTask.setTaskRunListener(instance -> {
                setRetryReqBtn(true, false);
                allCabRequestTask.stopRepeatingTask();
            });
        } catch (Exception e) {
            Logger.e("TestCrash", "::" + e.toString());
        }

        JSONArray orderedItemArr = new JSONArray();
        if (realmCartList != null && realmCartList.size() > 0) {
            try {
                for (int i = 0; i < realmCartList.size(); i++) {
                    JSONObject object = new JSONObject();
                    object.put("iVehicleTypeId", realmCartList.get(i).getCategoryListItem().getiVehicleTypeId());
                    object.put("fVehicleTypeQty", realmCartList.get(i).getItemCount());
                    object.put("tUserComment", realmCartList.get(i).getSpecialInstruction());
                    orderedItemArr.put(object);
                }
            } catch (Exception e) {
                Logger.e("TestCrash", "::" + e.toString());
            }
        }

        HashMap<String, Object> requestCabData = new HashMap<String, Object>();
        requestCabData.put("type", "sendRequestToDrivers");
        requestCabData.put("userId", generalFunc.getMemberId());

        if (Utils.checkText(mReqMsgCode)) {
            requestCabData.put("ReqMsgCode", mReqMsgCode);
        }

        requestCabData.put("CashPayment", "" + iscash);
        requestCabData.put("PickUpAddress", vServiceAddress);
        requestCabData.put("eType", Utils.CabGeneralType_UberX);
        requestCabData.put("driverIds", getIntent().getStringExtra("iDriverId"));
        requestCabData.put("eWalletDebitAllow", eWalletDebitAllow ? "Yes" : "No");
        requestCabData.put("eWalletIgnore", eWalletIgnore);
        requestCabData.put("ePaymentBy", ePaymentBy);

        requestCabData.put("PromoCode", appliedPromoCode.trim());

        requestCabData.put("OrderDetails", orderedItemArr.toString());

        if (isVideoConsultEnable) {
            requestCabData.put("isVideoCall", "Yes");
            requestCabData.put("eServiceLocation", "Driver");
        }
        if (isRetry) {
            requestCabData.put("isRetry", "Yes");
        } else {
            requestCabData.put("isRetry", "");
        }

        if (isUserLocation) {
            requestCabData.put("eServiceLocation", "Passenger");
            requestCabData.put("iUserAddressId", iUserAddressId);
        } else {
            requestCabData.put("eServiceLocation", "Driver");
        }

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), requestCabData, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                generalFunc.sendHeartBeat();

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (!isDataAvail) {
                    String message_str = generalFunc.getJsonValue(Utils.message_str, responseString);
                    Bundle bn = new Bundle();
                    bn.putString("msg", "" + message_str);

                    String message = message_str;

                    if (message.equals("SESSION_OUT")) {
                        closeRequestDialog(false);
                        MyApp.getInstance().notifySessionTimeOut();
                        Utils.runGC();
                        return;
                    }
                    if (message.equalsIgnoreCase("LOW_WALLET_AMOUNT")) {

                        closeRequestDialog(false);


                        String walletMsg = "";
                        String low_balance_content_msg = generalFunc.getJsonValue("low_balance_content_msg", responseString);

                        if (low_balance_content_msg != null && !low_balance_content_msg.equalsIgnoreCase("")) {
                            walletMsg = low_balance_content_msg;
                        } else {
                            walletMsg = generalFunc.retrieveLangLBl("", "LBL_WALLET_LOW_AMOUNT_MSG_TXT");
                        }
                        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BALANCE"), walletMsg, generalFunc.getJsonValue("IS_RESTRICT_TO_WALLET_AMOUNT", responseString).equalsIgnoreCase("No") ? generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN") :
                                generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"), generalFunc.getJsonValue("IS_RESTRICT_TO_WALLET_AMOUNT", responseString).equalsIgnoreCase("NO") ? generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT") :
                                "", button_Id -> {
                            if (button_Id == 1) {

                                new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                            } else if (button_Id == 0) {
                                if (generalFunc.getJsonValue("IS_RESTRICT_TO_WALLET_AMOUNT", responseString).equalsIgnoreCase("No")) {

                                    requestNearestCab = null;
                                    eWalletDebitAllow = true;
                                    eWalletIgnore = "Yes";
                                    sendRequestToDrivers(false);
                                }
                            }
                        });

                        return;

                    }


                    if (message_str.equals("DO_EMAIL_PHONE_VERIFY") ||
                            message_str.equals("DO_PHONE_VERIFY") ||
                            message_str.equals("DO_EMAIL_VERIFY")) {
                        closeRequestDialog(true);
                        accountVerificationAlert(generalFunc.retrieveLangLBl("", "LBL_ACCOUNT_VERIFY_ALERT_RIDER_TXT"), bn);
                    } else if (!message_str.equalsIgnoreCase("")) {
                        if (generalFunc.getJsonValue("isShowContactUs", responseString) != null && generalFunc.getJsonValue("isShowContactUs", responseString).equalsIgnoreCase("Yes")) {
                            closeRequestDialog(false);
                            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                            generateAlert.setCancelable(false);
                            generateAlert.setBtnClickList(btn_id -> {
                                if (btn_id == 1) {
                                    Intent intent = new Intent(CarWashBookingDetailsActivity.this, ContactUsActivity.class);
                                    intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
                                    startActivity(intent);
                                }
                            });

                            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", message_str));
                            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
                            generateAlert.showAlertBox();
                        } else {
                            closeRequestDialog(false);
                            if (message.equals("NO_CARS")) {
                                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_PROVIDERS_AVAIL_TXT"), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), null);
                                return;
                            }
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message_str), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), null);
                        }
                    } else {
                        closeRequestDialog(false);
                        buildMessage(generalFunc.retrieveLangLBl("", "LBL_REQUEST_FAILED_PROCESS"), generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), true);
                    }

                } else {
                    mReqMsgCode = generalFunc.getJsonValue("ReqMsgCode", responseString);
                }
            } else {
                if (reqSentErrorDialog != null) {
                    reqSentErrorDialog.closeAlertBox();
                    reqSentErrorDialog = null;
                }

                InternetConnection intConnection = new InternetConnection(getActContext());

                reqSentErrorDialog = generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", intConnection.isNetworkConnected() ? "LBL_TRY_AGAIN_TXT" : "LBL_NO_INTERNET_TXT"), generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"), buttonId -> {
                    if (buttonId == 1) {
                        sendRequestToDrivers(false);
                    } else {
                        closeRequestDialog(true);
                        MyApp.getInstance().restartWithGetDataApp();
                    }
                });
            }
        });
        exeWebServer.setCancelAble(false);

        generalFunc.sendHeartBeat();
    }

    public void showBookingAlert(String message, boolean isongoing) {

        /*SuccessDialog.showSuccessDialog(getActContext(), generalFunc.retrieveLangLBl("Booking Successful", "LBL_BOOKING_ACCEPTED"), message, isongoing ? generalFunc.retrieveLangLBl("", "LBL_VIEW_ON_GOING_TRIPS") : generalFunc.retrieveLangLBl("Done", "LBL_VIEW_BOOKINGS"), generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"), false, () -> {
            if (isongoing) {
                Bundle bn = new Bundle();
                bn.putBoolean("isRestart", true);
                new ActUtils(getActContext()).startActForResult(BookingActivity.class, bn, Utils.ASSIGN_DRIVER_CODE);
            } else {
                Bundle bn = new Bundle();
                bn.putBoolean("isrestart", true);
                if (getIntent().getStringExtra("selType") != null) {
                    bn.putString("selType", getIntent().getStringExtra("selType"));
                }
                new ActUtils(getActContext()).startActWithData(BookingActivity.class, bn);
            }

        }, () -> {
            Bundle bn = new Bundle();
            new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
            finishAffinity();
        });*/

        CustomDialog customDialog = new CustomDialog(this, generalFunc);
        if (isVideoConsultEnable) {
            customDialog.setDetails(generalFunc.retrieveLangLBl("Booking Successful", "LBL_VIDEO_CONSULT_BOOKING_SUCCESSFUL_TITLE"), generalFunc.retrieveLangLBl("Booking Successful", "LBL_VIDEO_CONSULT_BOOKING_SUCCESSFUL_DESC"), generalFunc.retrieveLangLBl("", "LBL_VIEW_DETAILS"), generalFunc.retrieveLangLBl("Ok", "LBL_OK"), false, R.drawable.ic_correct_2, false, 1, true);

        } else {
            customDialog.setDetails(generalFunc.retrieveLangLBl("Booking Successful", "LBL_BOOKING_SUCCESSFUL_TITLE"), message, isongoing ? generalFunc.retrieveLangLBl("", "LBL_VIEW_ON_GOING_TRIPS") : generalFunc.retrieveLangLBl("Done", "LBL_VIEW_BOOKINGS"), generalFunc.retrieveLangLBl("Ok", "LBL_OK"), false, R.drawable.ic_correct_2, false, 1, true);

        }
        customDialog.createDialog();
        customDialog.setPositiveButtonClick(() -> {
            if (isongoing) {
                Bundle bn = new Bundle();
                bn.putBoolean("isRestart", true);
                new ActUtils(getActContext()).startActForResult(BookingActivity.class, bn, Utils.ASSIGN_DRIVER_CODE);
            } else {
                Bundle bn = new Bundle();
                bn.putBoolean("isrestart", true);
                if (getIntent().getStringExtra("selType") != null) {
                    bn.putString("selType", getIntent().getStringExtra("selType"));
                }
                new ActUtils(getActContext()).startActWithData(BookingActivity.class, bn);
            }
        });
        customDialog.setNegativeButtonClick(() -> {
            Bundle bn = new Bundle();
            new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
            finishAffinity();
        });
        customDialog.show();

    }

    public void pubNubMsgArrived(final String message) {
        try {
            String driverMsg = generalFunc.getJsonValue("Message", message);
            String isDecline = generalFunc.getJsonValue("isDecline", message);
            if (driverMsg.equals("CabRequestAccepted")) {
                closeRequestDialog(false);
                Realm realm = MyApp.getRealmInstance();
                realm.beginTransaction();
                realm.delete(CarWashCartData.class);
                realm.commitTransaction();
                showBookingAlert(generalFunc.retrieveLangLBl("", "LBL_ONGOING_TRIP_TXT"), true);
            } else if (driverMsg.equals("TripRequestCancel")) {
                if (requestNearestCab != null) {
                    Boolean isDeclineFromDriver = false;
                    if (isDecline.equalsIgnoreCase("Yes")) {
                        isDeclineFromDriver = true;
                    }
                    setRetryReqBtn(true, isDeclineFromDriver);
                    allCabRequestTask.stopRepeatingTask();
                }


            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }


    public void retryReqBtnPressed(String driverIds, String cabRequestedJson, boolean isRetry) {
        sendRequestToDrivers(isRetry);
        setRetryReqBtn(false, false);
    }

    public void setRetryReqBtn(boolean isVisible, boolean isDeclineFromDriver) {
        if (isVisible) {
            if (requestNearestCab != null) {
                //requestNearestCab.setVisibilityOfRetryArea(View.VISIBLE);
                requestNearestCab.setVisibleBottomArea(View.VISIBLE, isDeclineFromDriver);
            }
        } else {
            if (requestNearestCab != null) {
                //requestNearestCab.setVisibilityOfRetryArea(View.GONE);
                requestNearestCab.setInVisibleBottomArea(View.GONE);
            }
        }
    }

    public void accountVerificationAlert(String message, final Bundle bn) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 1) {
                generateAlert.closeAlertBox();
//                bn.putString("msg", "DO_PHONE_VERIFY");
                (new ActUtils(getActContext())).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_INFO_REQ_CODE);
            } else if (btn_id == 0) {
                generateAlert.closeAlertBox();
            }
        });
        generateAlert.setContentMessage("", message);
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_CANCEL_TRIP_TXT"));
        generateAlert.showAlertBox();

    }

    public void buildMessage(String message, String positiveBtn, final boolean isRestart) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            generateAlert.closeAlertBox();
            if (isRestart) {
                generalFunc.restartApp();
            }
        });
        generateAlert.setContentMessage("", message);
        generateAlert.setPositiveBtn(positiveBtn);
        generateAlert.showAlertBox();
    }


    public void closeRequestDialog(boolean isSetDefault) {
        if (requestNearestCab != null) {
            requestNearestCab.dismissDialog();
            requestNearestCab = null;
        }
    }

    public void bookScheduleRide() {

        JSONArray orderedItemArr = new JSONArray();
        if (realmCartList != null && realmCartList.size() > 0) {
            try {
                for (int i = 0; i < realmCartList.size(); i++) {
                    JSONObject object = new JSONObject();
                    object.put("iVehicleTypeId", realmCartList.get(i).getCategoryListItem().getiVehicleTypeId());
                    object.put("fVehicleTypeQty", realmCartList.get(i).getItemCount());
                    object.put("tUserComment", realmCartList.get(i).getSpecialInstruction());
                    orderedItemArr.put(object);
                }
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "ScheduleARide");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("CashPayment", "" + iscash);
        parameters.put("PickUpAddress", vServiceAddress);
        parameters.put("eType", Utils.CabGeneralType_UberX);
        parameters.put("driverIds", getIntent().getStringExtra("iDriverId"));
        parameters.put("eWalletDebitAllow", eWalletDebitAllow ? "Yes" : "No");
        parameters.put("ePaymentBy", ePaymentBy);

        parameters.put("PromoCode", appliedPromoCode.trim());

        parameters.put("OrderDetails", orderedItemArr.toString());


        if (isVideoConsultEnable) {
            parameters.put("isVideoCall", "Yes");
            parameters.put("eServiceLocation", "Driver");
        } else if (isUserLocation) {
            parameters.put("eServiceLocation", "Passenger");
            parameters.put("iUserAddressId", iUserAddressId);
        } else {
            parameters.put("eServiceLocation", "Driver");
        }

        parameters.put("SelectedDriverId", getIntent().getStringExtra("iDriverId"));
        parameters.put("CashPayment", "" + iscash);
        parameters.put("PromoCode", appliedPromoCode);
        parameters.put("eType", Utils.CabGeneralType_UberX);
        parameters.put("scheduleDate", SelectDate);
        parameters.put("eWalletIgnore", eWalletIgnore);
        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                String message_str = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (message_str.equals("DO_EMAIL_PHONE_VERIFY") || message_str.equals("DO_PHONE_VERIFY") || message_str.equals("DO_EMAIL_VERIFY")) {
                    Bundle bn = new Bundle();
                    bn.putString("msg", "" + message_str);
                    accountVerificationAlert(generalFunc.retrieveLangLBl("", "LBL_ACCOUNT_VERIFY_ALERT_RIDER_TXT"), bn);
                    return;
                }

                String action = generalFunc.getJsonValue(Utils.action_str, responseString);

                if (action.equals("1")) {
                    showBookingAlert(generalFunc.retrieveLangLBl("", message_str));
                } else {
                    String message = message_str;
                    if (message.equalsIgnoreCase("LOW_WALLET_AMOUNT")) {

                        closeRequestDialog(false);

                        String walletMsg = "";
                        String low_balance_content_msg = generalFunc.getJsonValue("low_balance_content_msg", responseString);

                        if (low_balance_content_msg != null && !low_balance_content_msg.equalsIgnoreCase("")) {
                            walletMsg = low_balance_content_msg;
                        } else {
                            walletMsg = generalFunc.retrieveLangLBl("", "LBL_WALLET_LOW_AMOUNT_MSG_TXT");
                        }


                        String LBL_CANCEL_TXT = generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT");
                        String IS_RESTRICT_TO_WALLET_AMOUNT = generalFunc.getJsonValue("IS_RESTRICT_TO_WALLET_AMOUNT", responseString);

                        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BALANCE"), walletMsg, IS_RESTRICT_TO_WALLET_AMOUNT.equalsIgnoreCase("No") ? generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN") :
                                LBL_CANCEL_TXT, generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"), IS_RESTRICT_TO_WALLET_AMOUNT.equalsIgnoreCase("NO") ? LBL_CANCEL_TXT :
                                "", button_Id -> {
                            if (button_Id == 1) {

                                new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                            } else if (button_Id == 0) {
                                if (IS_RESTRICT_TO_WALLET_AMOUNT.equalsIgnoreCase("No")) {
                                    requestNearestCab = null;
                                    eWalletDebitAllow = true;
                                    eWalletIgnore = "Yes";
                                    bookScheduleRide();
                                }
                            }
                        });

                        return;
                    }

                    String isShowContactUs = generalFunc.getJsonValue("isShowContactUs", responseString);
                    if (isShowContactUs != null && isShowContactUs.equalsIgnoreCase("Yes")) {
                        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                        generateAlert.setCancelable(false);
                        generateAlert.setBtnClickList(btn_id -> {
                            if (btn_id == 1) {
                                Intent intent = new Intent(CarWashBookingDetailsActivity.this, ContactUsActivity.class);
                                intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
                                startActivity(intent);
                            }
                        });

                        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", message_str));
                        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));

                        generateAlert.showAlertBox();
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message_str));
                    }
                }

            } else {
                generalFunc.showError();
            }
        });
        exeWebServer.setCancelAble(false);
    }


    public void showBookingAlert(String message) {

        /*SuccessDialog.showSuccessDialog(getActContext(), generalFunc.retrieveLangLBl("Booking Successful", "LBL_BOOKING_ACCEPTED"), message, generalFunc.retrieveLangLBl("Done", "LBL_VIEW_BOOKINGS"), generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"), false, () -> {

            clearReleamData();

            Bundle bn = new Bundle();
            bn.putBoolean("isrestart", true);
            bn.putString("selType", Utils.CabGeneralType_UberX);
            new ActUtils(getActContext()).startActWithData(BookingActivity.class, bn);
            finish();

        }, () -> {

            clearReleamData();

            Bundle bn = new Bundle();
            new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
            finishAffinity();
        });*/

        CustomDialog customDialog = new CustomDialog(this, generalFunc);
        if (isVideoConsultEnable) {
            customDialog.setDetails(generalFunc.retrieveLangLBl("Booking Successful", "LBL_BOOKING_ACCEPTED"), message, generalFunc.retrieveLangLBl("Done", "LBL_VIEW_BOOKINGS"), generalFunc.retrieveLangLBl("Ok", "LBL_OK"), false, R.drawable.ic_correct_2, false, 1, true);

        } else {
            customDialog.setDetails(generalFunc.retrieveLangLBl("Booking Successful", "LBL_BOOKING_ACCEPTED"), message, generalFunc.retrieveLangLBl("Done", "LBL_VIEW_BOOKINGS"), generalFunc.retrieveLangLBl("Ok", "LBL_OK"), false, R.drawable.ic_correct_2, false, 1, true);

        }
        customDialog.createDialog();
        customDialog.setPositiveButtonClick(() -> {
            clearReleamData();

            Bundle bn = new Bundle();
            bn.putBoolean("isrestart", true);
            bn.putString("selType", Utils.CabGeneralType_UberX);
            new ActUtils(getActContext()).startActWithData(BookingActivity.class, bn);
            finish();
        });
        customDialog.setNegativeButtonClick(() -> {
            clearReleamData();

            Bundle bn = new Bundle();
            new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
            finishAffinity();
        });
        customDialog.show();
    }

    private void clearReleamData() {
        try {
            Realm realm = MyApp.getRealmInstance();
            realm.beginTransaction();
            realm.delete(CarWashCartData.class);
            realm.commitTransaction();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    boolean isCashSelected = true;


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK && requestCode == WEBVIEWPAYMENT) {
            if (bookingtype.equalsIgnoreCase(Utils.CabReqType_Now)) {
                String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValueStr("ENABLE_SAFETY_CHECKLIST", obj_userProfile);
                if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes") && generalFunc.getJsonValueStr("ENABLE_SAFETY_FEATURE_UFX", obj_userProfile).equalsIgnoreCase("Yes") && !isVideoConsultEnable) {
                    Bundle bn = new Bundle();
                    bn.putBoolean("isDeliverNow", true);
                    bn.putString("URL", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_INFO_URL", obj_userProfile));
                    bn.putString("LBL_CURRENT_PERSON_LIMIT", "");
                    new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                    ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
                } else {

                    getOutStandingAmout("", bookingtype);
                }

            } else {
                Bundle bundle = new Bundle();
                bundle.putString("iDriverId", getIntent().getStringExtra("iDriverId"));
                new ActUtils(getActContext()).startActForResult(ScheduleDateSelectActivity.class, bundle, Utils.SCHEDULE_REQUEST_CODE);
            }

        } else if (requestCode == Utils.SELECT_COUPON_REQ_CODE && resultCode == RESULT_OK) {

            String couponCode = data.getStringExtra("CouponCode");

            if (couponCode == null) {
                couponCode = "";
            }

            appliedPromoCode = couponCode;
            setPromoCode();
            getDetails();
        } else if (requestCode == SEL_ADDRESS_REQ_CODE) {

            if (resultCode == RESULT_OK) {
                if (!iUserAddressId.equalsIgnoreCase(data.getStringExtra("addressId"))) {
                    appliedPromoCode = "";
                    setPromoCode();
                }
                iUserAddressId = data.getStringExtra("addressId");
                iTempUserId = data.getStringExtra("addressId");
                vLatitude = data.getStringExtra("vLatitude");
                vLongitude = data.getStringExtra("vLongitude");
                vServiceAddress = data.getStringExtra("address");
                userAddressTxt.setText(vServiceAddress);
                ((ScrollView) findViewById(R.id.scrollView)).post(() -> ((ScrollView) findViewById(R.id.scrollView)).fullScroll(ScrollView.FOCUS_DOWN));
            }

            getDetails();

        } else if (requestCode == ADD_ADDRESS_REQ_CODE && resultCode == RESULT_OK) {
            getUserProfileJson();
            appliedPromoCode = "";
            setPromoCode();
            getDetails();
        } else if (requestCode == Utils.SCHEDULE_REQUEST_CODE && resultCode == RESULT_OK) {

            SelectDate = data.getStringExtra("SelectDate");
            sdate = data.getStringExtra("Sdate");
            Stime = data.getStringExtra("Stime");

            booktimeVTxt.setText(Stime);
            bookDateVTxt.setText(sdate);
            bookingtype = Utils.CabReqType_Later;
            String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValueStr("ENABLE_SAFETY_CHECKLIST", obj_userProfile);
            if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes") && generalFunc.getJsonValueStr("ENABLE_SAFETY_FEATURE_UFX", obj_userProfile).equalsIgnoreCase("Yes") && !isVideoConsultEnable) {
                Bundle bn = new Bundle();
                bn.putBoolean("isDeliverNow", false);
                bn.putString("URL", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_INFO_URL", obj_userProfile));
                bn.putString("LBL_CURRENT_PERSON_LIMIT", "");
                new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
            } else {

                getOutStandingAmout("", Utils.CabReqType_Later);
            }
        } else if (requestCode == Utils.SELECT_ORGANIZATION_PAYMENT_CODE) {

            if (resultCode == RESULT_OK) {
                if (data.getSerializableExtra("data").equals("")) {


                    if (data.getBooleanExtra("isCash", false)) {
                        isCashSelected = true;
                        iscash = true;
                    } else {
                        isCashSelected = false;
                        iscash = false;
                    }
                    if (data.getBooleanExtra("isWallet", false)) {
                        eWalletDebitAllow = true;

                    } else {

                        eWalletDebitAllow = false;
                    }

                    if (bookingtype.equalsIgnoreCase(Utils.CabReqType_Now)) {
                        String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValueStr("ENABLE_SAFETY_CHECKLIST", obj_userProfile);
                        if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes") && generalFunc.getJsonValueStr("ENABLE_SAFETY_FEATURE_UFX", obj_userProfile).equalsIgnoreCase("Yes") && !isVideoConsultEnable) {
                            Bundle bn = new Bundle();
                            bn.putBoolean("isDeliverNow", true);
                            bn.putString("URL", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_INFO_URL", obj_userProfile));
                            bn.putString("LBL_CURRENT_PERSON_LIMIT", "");
                            new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                            ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
                        } else {

                            getOutStandingAmout("", bookingtype);
                        }

                    } else {
                        Bundle bundle = new Bundle();
                        bundle.putString("iDriverId", getIntent().getStringExtra("iDriverId"));
                        new ActUtils(getActContext()).startActForResult(ScheduleDateSelectActivity.class, bundle, Utils.SCHEDULE_REQUEST_CODE);
                    }


                }

            }
        } else if (requestCode == 77 && resultCode == RESULT_OK && data != null) {
            if (data.getBooleanExtra("isDeliverNow", false)) {
                getOutStandingAmout("", bookingtype);
            } else {
                getOutStandingAmout("", Utils.CabReqType_Later);

            }
        }
    }
}
