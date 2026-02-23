package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.os.Bundle;
import android.text.Editable;
import android.text.SpannableStringBuilder;
import android.text.TextWatcher;
import android.text.style.ForegroundColorSpan;
import android.view.Gravity;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.ApplyCouponAdapter;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.ManageScroll;
import com.general.files.SpacesItemDecoration;
import com.google.android.material.appbar.AppBarLayout;
import com.google.android.material.appbar.CollapsingToolbarLayout;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 03-07-18.
 */

public class CouponActivity extends ParentActivity implements ApplyCouponAdapter.OnItemClickListener {

    MTextView titleTxt;
    MTextView noDataTxt;
    LinearLayout headerArea;
    ImageView backImgView;
    EditText inputCouponCode;
    //MButton btn_type2;
    MTextView couponHTxt;
    ProgressBar loading_apply_coupon;
    MTextView applyBtn;

    RecyclerView applyCouponRecyclerView;
    ErrorView errorView;
    ArrayList<HashMap<String, String>> list = new ArrayList<>();
    ApplyCouponAdapter applyCouponAdapter;

    String required_str = "";

    String appliedCouponCode = "", mSearchText = "";

    int selpos = -1;
    AppBarLayout app_bar_layout;
    CollapsingToolbarLayout collapsing_toolbar;
    boolean isRemoved = false;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_apply_coupon);

        collapsing_toolbar = findViewById(R.id.collapsing_toolbar);
        app_bar_layout = (AppBarLayout) findViewById(R.id.app_bar_layout_coupon);
        app_bar_layout.addOnOffsetChangedListener((appBarLayout, i) -> {
            int maxScroll = appBarLayout.getTotalScrollRange();
            float percentage = (float) Math.abs(i) / (float) maxScroll;
            if (percentage > 0.70f) {

                couponHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_COUPONS"));
                couponHTxt.setGravity(Gravity.START);
                couponHTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
            } else {
                couponHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_COUPON_FROM_LIST"));
                couponHTxt.setGravity(Gravity.CENTER);
                couponHTxt.setTypeface(SystemFont.FontStyle.LIGHT.font);
            }

        });
        loading_apply_coupon = (ProgressBar) findViewById(R.id.loading_apply_coupon);
        applyCouponRecyclerView = (RecyclerView) findViewById(R.id.applyCouponRecyclerView);

        headerArea = findViewById(R.id.headerArea);

        noDataTxt = (MTextView) findViewById(R.id.noDataTxt);
        errorView = (ErrorView) findViewById(R.id.errorView);
        inputCouponCode = (EditText) findViewById(R.id.inputCouponCode);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        couponHTxt = (MTextView) findViewById(R.id.couponHTxt);
        applyBtn = (MTextView) findViewById(R.id.applyBtn);
        applyBtn.setAlpha(0.5F);
        applyBtn.setEnabled(false);
        inputCouponCode.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
            }

            @Override
            public void afterTextChanged(Editable s) {
                mSearchText = s.toString();
                if (mSearchText.length() == 0) {
                    applyBtn.setAlpha(0.5F);
                    applyBtn.setEnabled(false);
                } else {
                    applyBtn.setAlpha(1.0F);
                    applyBtn.setEnabled(true);
                }
            }
        });
        LinearLayoutManager mLayoutManager = new LinearLayoutManager(getActContext(), LinearLayoutManager.VERTICAL, false);
        mLayoutManager.setSmoothScrollbarEnabled(true);
        applyCouponRecyclerView.setLayoutManager(mLayoutManager);

        addToClickHandler(applyBtn);
        addToClickHandler(backImgView);

        if (getIntent().getStringExtra("CouponCode") != null) {
            appliedCouponCode = getIntent().getStringExtra("CouponCode");
        }

        setAdapter();
        setLabels();
        getCouponList();
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
            ((ImageView) findViewById(R.id.imageView)).setRotation(180);
            applyBtn.setBackgroundDrawable(ContextCompat.getDrawable(this, R.drawable.right_radius_rtl));
        }
    }

    private void setAdapter() {
        applyCouponAdapter = new ApplyCouponAdapter(list, generalFunc, appliedCouponCode, applyCouponRecyclerView, this);
        applyCouponRecyclerView.setAdapter(applyCouponAdapter);
        applyCouponRecyclerView.addItemDecoration(new SpacesItemDecoration(1, getResources().getDimensionPixelSize(R.dimen._12sdp), false));
    }

    private void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY_COUPON"));
        applyBtn.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY"));
        couponHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_COUPON_FROM_LIST"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");

        inputCouponCode.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_COUPON_CODE"));
    }

    private Context getActContext() {
        return CouponActivity.this;
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getCouponList() {
        noDataTxt.setVisibility(View.GONE);
        headerArea.setVisibility(View.VISIBLE);
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (loading_apply_coupon.getVisibility() != View.VISIBLE) {
            loading_apply_coupon.setVisibility(View.VISIBLE);
        }

        app_bar_layout.setVisibility(View.GONE);

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DisplayCouponList");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        if (getIntent().hasExtra("vSourceLatitude")) {
            parameters.put("vSourceLatitude", getIntent().getStringExtra("vSourceLatitude"));
            parameters.put("vSourceLongitude", getIntent().getStringExtra("vSourceLongitude"));

        }
        if (getIntent().hasExtra("vDestLatitude")) {
            parameters.put("vDestLatitude", getIntent().getStringExtra("vDestLatitude"));
            parameters.put("vDestLongitude", getIntent().getStringExtra("vDestLongitude"));
        }
        if (getIntent().hasExtra("tDeliveryLocations")) {
            parameters.put("tDeliveryLocations", getIntent().getStringExtra("tDeliveryLocations"));
        }

        if (getIntent().getBooleanExtra("isVideoConsultEnable", false)) {
            parameters.put("eForVideoConsultation", "Yes");
        }

        if (getIntent().hasExtra("eSystem")) {
            parameters.put("iCompanyId", getIntent().getStringExtra("iCompanyId"));
            parameters.put("iUserAddressId", getIntent().getStringExtra("iUserAddressId"));
            parameters.put("eType", Utils.eSystem_Type);
            parameters.put("eSystemType", "");
            parameters.put("eTakeAway", getIntent().getStringExtra("eTakeAway"));
            parameters.put("eSystem", Utils.eSystem_Type);
        }
        parameters.put("eFly", getIntent().getBooleanExtra("eFly", false) ? "Yes" : "No");

        if (getIntent().hasExtra("eType")) {
            parameters.put("eType", getIntent().getStringExtra("eType"));
        }
        if (getIntent().hasExtra("isTaxiBid")) {
            parameters.put("isTaxiBid", getIntent().getStringExtra("isTaxiBid"));
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            app_bar_layout.setVisibility(View.VISIBLE);
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {

                list.clear();
                closeLoader();

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    String vCurrency = generalFunc.getJsonValueStr("vCurrency", responseObj);
                    String vSymbol = generalFunc.getJsonValueStr("vSymbol", responseObj);

                    JSONArray arr_rides = generalFunc.getJsonArray(Utils.message_str, responseObj);

                    if (arr_rides != null && arr_rides.length() > 0) {
                        for (int i = 0; i < arr_rides.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);

                            HashMap<String, String> map = new HashMap<>();
                            String eValidityType = generalFunc.getJsonValueStr("eValidityType", obj_temp);
                            String dExpiryDate = generalFunc.getJsonValueStr("dExpiryDate", obj_temp);
                            String eType = generalFunc.getJsonValueStr("eType", obj_temp);
                            map.put("iCouponId", generalFunc.getJsonValueStr("iCouponId", obj_temp));
                            map.put("vCouponCode", generalFunc.getJsonValueStr("vCouponCode", obj_temp));
                            map.put("tDescription", generalFunc.getJsonValueStr("tDescription", obj_temp));
                            map.put("fDiscount", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fDiscount", obj_temp)));
                            map.put("fDiscountSymbol", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fDiscountSymbol", obj_temp)));
                            map.put("eType", eType);
                            map.put("eValidityType", eValidityType);
                            map.put("dActiveDate", generalFunc.getJsonValueStr("dActiveDate", obj_temp));
//                            map.put("dExpiryDate", dExpiryDate+ " 00:00:00");
                            map.put("iUsageLimit", generalFunc.getJsonValueStr("iUsageLimit", obj_temp));
                            map.put("iUsed", generalFunc.getJsonValueStr("iUsed", obj_temp));
                            map.put("eStatus", generalFunc.getJsonValueStr("eStatus", obj_temp));
                            map.put("eFreeDelivery", generalFunc.getJsonValueStr("eFreeDelivery", obj_temp));
                            if (!eValidityType.equalsIgnoreCase("PERMANENT")) {

                                map.put("dExpiryDate", dExpiryDate);

//                                map.put("dExpiryDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dExpiryDate, Utils.OriginalDateFormate, CommonUtilities.OriginalDateFormate)));
                            }

                            map.put("vSymbol", vSymbol);
                            map.put("vCurrency", vCurrency);
                            map.put("isdetailsView", "No");
                            map.put("LBL_USE_AND_SAVE_LBL", generalFunc.retrieveLangLBl("", "LBL_USE_AND_SAVE_LBL"));
                            map.put("LBL_REMOVE_TEXT", generalFunc.retrieveLangLBl("", "LBL_REMOVE_TEXT"));
                            map.put("LBL_APPLY", generalFunc.retrieveLangLBl("", "LBL_APPLY"));
                            map.put("LBL_VALID_TILL_TXT", generalFunc.retrieveLangLBl("", "LBL_VALID_TILL_TXT"));
                            map.put("LBL_USE_CODE_TXT", generalFunc.retrieveLangLBl("", "LBL_USE_CODE_TXT"));
                            map.put("LBL_PROMOCODE_DISCOUNT_TYPE_TXT", generalFunc.retrieveLangLBl("", "LBL_PROMOCODE_DISCOUNT_TYPE_TXT"));

                            map.put("LBL_PROMOCODE_TAP_TO_APPLY_TXT", generalFunc.retrieveLangLBl("", "LBL_PROMOCODE_TAP_TO_APPLY_TXT"));

                            if (generalFunc.getJsonValueStr("vCouponCode", obj_temp).equals(appliedCouponCode)) {
                                selpos = i;
                            }
                            list.add(map);
                        }
                    }

                    if (list.size() == 0) {
                        noDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        noDataTxt.setVisibility(View.VISIBLE);
                        headerArea.setVisibility(View.GONE);
                        applyCouponRecyclerView.setVisibility(View.GONE);
                        Utils.showSoftKeyboard((Activity) getActContext(), inputCouponCode);
                        new ManageScroll(collapsing_toolbar).scroll(false);
                    } else {
                        new ManageScroll(collapsing_toolbar).scroll(true);
                    }


                    if (selpos == -1 && !appliedCouponCode.equalsIgnoreCase("")) {
                        inputCouponCode.setText(appliedCouponCode);
                        applyBtn.setText(generalFunc.retrieveLangLBl("", "LBL_REMOVE_TEXT"));
                    }

                    applyCouponAdapter.notifyDataSetChanged();
                    applyCouponRecyclerView.scrollToPosition(selpos == -1 ? 0 : selpos);
                } else {
                    if (list.size() == 0) {
                        noDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        noDataTxt.setVisibility(View.VISIBLE);
                        headerArea.setVisibility(View.GONE);
                        applyCouponRecyclerView.setVisibility(View.GONE);
                        Utils.showSoftKeyboard((Activity) getActContext(), inputCouponCode);
                        new ManageScroll(collapsing_toolbar).scroll(false);
                    } else {
                        new ManageScroll(collapsing_toolbar).scroll(true);
                    }
                }
            } else {
                generateErrorView();
            }
        });
    }

    private void closeLoader() {
        if (loading_apply_coupon.getVisibility() == View.VISIBLE) {
            loading_apply_coupon.setVisibility(View.GONE);
        }
    }

    private void generateErrorView() {
        closeLoader();
        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(this::getCouponList);
    }

    @Override
    public void onItemClickList(View v, int position) {
        Utils.hideKeyboard(getActContext());
        checkPromoCode(list.get(position).get("vCouponCode"));
    }

    @Override
    public void onItemClickListRemoveCode(View v, int position, String string) {
        Utils.hideKeyboard(getActContext());
        if (string.equals("useCode")) {
            removePromoCode();
        }
    }

    private void checkCouponCode() {
        if (!appliedCouponCode.equalsIgnoreCase("") && appliedCouponCode.equalsIgnoreCase(Utils.getText(inputCouponCode))) {
            removePromoCode();
            return;
        }

        boolean couponCodeEntered = Utils.checkText(inputCouponCode) || setErrorData();
        if (!couponCodeEntered) {
            return;
        }
        checkPromoCode(inputCouponCode.getText().toString().trim());
    }

    private boolean setErrorData() {
        ForegroundColorSpan foregroundColorSpan = new ForegroundColorSpan(getActContext().getResources().getColor(R.color.white));
        SpannableStringBuilder spannableStringBuilder = new SpannableStringBuilder(required_str);
        spannableStringBuilder.setSpan(foregroundColorSpan, 0, required_str.length(), 0);
        inputCouponCode.setError(spannableStringBuilder);
        return false;
    }

    private void checkPromoCode(final String promoCode) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CheckPromoCode");
        parameters.put("PromoCode", promoCode);
        parameters.put("iUserId", generalFunc.getMemberId());
        if (getIntent().hasExtra("eSystem")) {
            parameters.put("iCompanyId", getIntent().getStringExtra("iCompanyId"));
            parameters.put("iUserAddressId", getIntent().getStringExtra("iUserAddressId"));
            parameters.put("eType", Utils.eSystem_Type);
            parameters.put("eSystemType", "");
            parameters.put("eSystem", Utils.eSystem_Type);
        }

        parameters.put("isRemovePromocode", isRemoved ? "Yes" : "No");


        if (getIntent().getBooleanExtra("isVideoConsultEnable", false)) {
            parameters.put("eForVideoConsultation", "Yes");
        }

        parameters.put("eFly", getIntent().getBooleanExtra("eFly", false) ? "Yes" : "No");

        if (getIntent().hasExtra("eType")) {
            parameters.put("eType", getIntent().getStringExtra("eType"));
        }

        if (getIntent().hasExtra("vSourceLatitude")) {
            parameters.put("vSourceLatitude", getIntent().getStringExtra("vSourceLatitude"));
            parameters.put("vSourceLongitude", getIntent().getStringExtra("vSourceLongitude"));

            parameters.put("eTakeAway", getIntent().getStringExtra("eTakeAway"));
            if (getIntent().hasExtra("tDeliveryLocations")) {
                parameters.put("tDeliveryLocations", getIntent().getStringExtra("tDeliveryLocations"));
            }
        }

        if (getIntent().hasExtra("vDestLatitude")) {
            parameters.put("vDestLatitude", getIntent().getStringExtra("vDestLatitude"));
            parameters.put("vDestLongitude", getIntent().getStringExtra("vDestLongitude"));
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                String action = generalFunc.getJsonValue(Utils.action_str, responseString);
                if (action.equals("1")) {

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();

                        Bundle bundle = new Bundle();
                        bundle.putString("CouponCode", isRemoved ? "" : promoCode);
                        new ActUtils(getActContext()).setOkResult(bundle);
                        finish();
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void removePromoCode() {
        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DELETE_CONFIRM_COUPON_MSG"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
            if (buttonId == 1) {
                appliedCouponCode = "";
                isRemoved = true;
                checkPromoCode(list.get(selpos).get("vCouponCode"));
            }
        });
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            CouponActivity.super.onBackPressed();
        } else if (i == applyBtn.getId()) {
            checkCouponCode();
        }
    }
}