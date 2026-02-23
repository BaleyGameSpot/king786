package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.os.Bundle;
import android.text.Editable;
import android.text.InputType;
import android.text.TextWatcher;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RatingBar;
import android.widget.RelativeLayout;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;

import com.AudioRecord.RecordingItem;
import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.general.features.SafetyTools;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.DividerView;
import com.view.ErrorView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.File;
import java.util.HashMap;

public class CollectPaymentActivity extends ParentActivity {

    MTextView titleTxt;
    ImageView backImgView, interCityImagedest, imagedest;
    ProgressBar loading;
    ErrorView errorView;
    MButton btn_type2;
    ImageView editCommentImgView;
    MTextView commentBox;
    MTextView generalCommentTxt;

    int submitBtnId;

    String appliedComment = "";
    LinearLayout container;
    LinearLayout fareDetailDisplayArea, interCityDestarea;

    RatingBar ratingBar;
    String iTripId_str;

    HashMap<String, String> data_trip;
    AlertDialog collectPaymentFailedDialog = null;
    private View squareImgView;

    MTextView additionalchargeHTxt, matrialfeeHTxt, miscfeeHTxt, discountHTxt, interCityDestAddressHTxt, interCityDestAddressTxt, intercityPickupDt, intercityReachedDt, intercityEndDt, sourceAddressTxt;
    MaterialEditText timatrialfeeVTxt, miscfeeVTxt, discountVTxt;
    MTextView matrialfeeCurrancyTxt, miscfeeCurrancyTxt, discountCurrancyTxt;
    ImageView discounteditImgView, miseeditImgView, matrialeditImgView;
    MTextView dateVTxt;
    MTextView totalFareTxt, cartypeTxt;

    MTextView promoAppliedVTxt;
    MTextView walletNoteTxt;
    MTextView thanksNoteTxt, orderTxt;
    public MTextView sourceAddressHTxt;
    public MTextView destAddressHTxt;
    public MTextView destAddressTxt;
    LinearLayout destarea, upperDateTimelayout;
    DividerView dashImage;
    LinearLayout PayTypeArea;
    LinearLayout btnarea;
    RelativeLayout main_content;
    private boolean isBiddingView;
    private String isVideoCall = "";
    private String eType;
    @Nullable
    public SafetyTools mSafetyTools;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_collect_payment);

        isBiddingView = getIntent().getBooleanExtra("isBiddingView", false);
        main_content = findViewById(R.id.main_content);
        titleTxt = findViewById(R.id.titleTxt);
        thanksNoteTxt = findViewById(R.id.thanksNoteTxt);
        destarea = findViewById(R.id.destarea);
        orderTxt = findViewById(R.id.orderTxt);
        imagedest = findViewById(R.id.imagedest);
        dashImage = findViewById(R.id.dashImage);
        sourceAddressHTxt = findViewById(R.id.sourceAddressHTxt);
        destAddressHTxt = findViewById(R.id.destAddressHTxt);
        sourceAddressTxt = findViewById(R.id.sourceAddressTxt);
        destAddressTxt = findViewById(R.id.destAddressTxt);
        backImgView = findViewById(R.id.backImgView);
        loading = findViewById(R.id.loading);
        errorView = findViewById(R.id.errorView);
        editCommentImgView = findViewById(R.id.editCommentImgView);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        commentBox = findViewById(R.id.commentBox);
        generalCommentTxt = findViewById(R.id.generalCommentTxt);
        container = findViewById(R.id.container);
        fareDetailDisplayArea = findViewById(R.id.fareDetailDisplayArea);
        ratingBar = findViewById(R.id.ratingBar);
        PayTypeArea = findViewById(R.id.PayTypeArea);
        btnarea = findViewById(R.id.btnarea);
        dateVTxt = findViewById(R.id.dateVTxt);
        promoAppliedVTxt = findViewById(R.id.promoAppliedVTxt);
        walletNoteTxt = findViewById(R.id.walletNoteTxt);
        additionalchargeHTxt = findViewById(R.id.additionalchargeHTxt);
        matrialfeeHTxt = findViewById(R.id.matrialfeeHTxt);
        miscfeeHTxt = findViewById(R.id.miscfeeHTxt);
        discountHTxt = findViewById(R.id.discountHTxt);
        timatrialfeeVTxt = findViewById(R.id.timatrialfeeVTxt);
        miscfeeVTxt = findViewById(R.id.miscfeeVTxt);
        discountVTxt = findViewById(R.id.discountVTxt);
        matrialfeeCurrancyTxt = findViewById(R.id.matrialfeeCurrancyTxt);
        miscfeeCurrancyTxt = findViewById(R.id.miscfeeCurrancyTxt);
        discountCurrancyTxt = findViewById(R.id.discountCurrancyTxt);
        discounteditImgView = findViewById(R.id.discounteditImgView);
        miseeditImgView = findViewById(R.id.miseeditImgView);
        matrialeditImgView = findViewById(R.id.matrialeditImgView);
        cartypeTxt = findViewById(R.id.cartypeTxt);
        totalFareTxt = findViewById(R.id.totalFareTxt);
        addToClickHandler(discounteditImgView);
        addToClickHandler(miscfeeCurrancyTxt);
        addToClickHandler(discountCurrancyTxt);
        timatrialfeeVTxt.setInputType(InputType.TYPE_NUMBER_FLAG_DECIMAL | InputType.TYPE_CLASS_NUMBER);
        miscfeeVTxt.setInputType(InputType.TYPE_NUMBER_FLAG_DECIMAL | InputType.TYPE_CLASS_NUMBER);
        discountVTxt.setInputType(InputType.TYPE_NUMBER_FLAG_DECIMAL | InputType.TYPE_CLASS_NUMBER);
        discountVTxt.setShowClearButton(false);
        miscfeeVTxt.setShowClearButton(false);
        timatrialfeeVTxt.setShowClearButton(false);
        discountVTxt.addTextChangedListener(new setOnAddTextListner());
        miscfeeVTxt.addTextChangedListener(new setOnAddTextListner());
        timatrialfeeVTxt.addTextChangedListener(new setOnAddTextListner());
        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);
        addToClickHandler(editCommentImgView);
        backImgView.setVisibility(View.GONE);


        interCityDestAddressHTxt = (MTextView) findViewById(R.id.interCityDestAddressHTxt);
        sourceAddressTxt = (MTextView) findViewById(R.id.sourceAddressTxt);
        interCityDestAddressTxt = (MTextView) findViewById(R.id.interCityDestAddressTxt);
        intercityEndDt = (MTextView) findViewById(R.id.intercityEndDt);
        intercityReachedDt = (MTextView) findViewById(R.id.intercityReachedDt);
        intercityPickupDt = (MTextView) findViewById(R.id.intercityPickupDt);

        interCityDestarea = (LinearLayout) findViewById(R.id.interCityDestarea);
        upperDateTimelayout = (LinearLayout) findViewById(R.id.upperDateTimelayout);

        interCityImagedest = findViewById(R.id.interCityImagedest);
        imagedest = findViewById(R.id.imagedest);
        squareImgView = findViewById(R.id.squareImgView);

        setLabels();

        data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
        if (isBiddingView) {
            getFareBidding();
        } else {
            getFare();
        }

        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) titleTxt.getLayoutParams();
        params.setMargins(Utils.dipToPixels(getActContext(), 15), 0, 0, 0);
        titleTxt.setLayoutParams(params);


        if (savedInstanceState != null) {
            // Restore value of members from saved state
            String restratValue_str = savedInstanceState.getString("RESTART_STATE");

            if (restratValue_str != null && !restratValue_str.equals("") && restratValue_str.trim().equals("true")) {
                generalFunc.restartApp();
            }
        }

        GetLocationUpdates.getInstance().setTripStartValue(false, false, false, "");

    }


    public Context getActContext() {
        return CollectPaymentActivity.this;
    }

    public void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Your Trip", "LBL_PAY_SUMMARY"));
        commentBox.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_COMMENT_TXT"));
        promoAppliedVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DIS_APPLIED"));
        btn_type2.setText(generalFunc.retrieveLangLBl("COLLECT PAYMENT", "LBL_COLLECT_PAYMENT"));
        ((MTextView) findViewById(R.id.detailsTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CHARGES_TXT"));


        additionalchargeHTxt.setText(generalFunc.retrieveLangLBl("ADDITIONAL CHARGES", "LBL_ADDITONAL_CHARGE_HINT"));
        matrialfeeHTxt.setText(generalFunc.retrieveLangLBl("Material fee", "LBL_MATERIAL_FEE"));
        miscfeeHTxt.setText(generalFunc.retrieveLangLBl("Misc fee", "LBL_MISC_FEE"));
        discountHTxt.setText(generalFunc.retrieveLangLBl("Provider Discount", "LBL_PROVIDER_DISCOUNT"));

        dateVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MYTRIP_Trip_Date"));
        totalFareTxt.setText(generalFunc.retrieveLangLBl("", "LBL_Total_Fare"));
        discountVTxt.setText("0.0");
        miscfeeVTxt.setText("0.0");
        timatrialfeeVTxt.setText("0.0");


    }

    public void showCommentBox() {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        builder.setTitle(generalFunc.retrieveLangLBl("", "LBL_ADD_COMMENT_HEADER_TXT"));

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.input_box_view, null);
        builder.setView(dialogView);

        final MaterialEditText input = dialogView.findViewById(R.id.editBox);

        input.setSingleLine(false);
        input.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        input.setMaxLines(5);
        if (!appliedComment.equals("")) {
            input.setText(appliedComment);
        }
        builder.setPositiveButton("OK", (dialog, which) -> {
            if (Utils.getText(input).trim().equals("") && appliedComment.equals("")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_ENTER_PROMO"));
            } else if (Utils.getText(input).trim().equals("") && !appliedComment.equals("")) {
                appliedComment = "";
                commentBox.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_COMMENT_TXT"));
                generalFunc.showGeneralMessage("", "Your comment has been removed.");
            } else {
                appliedComment = Utils.getText(input);
                commentBox.setText(appliedComment);
            }
        });
        builder.setNegativeButton("Cancel", (dialog, which) -> dialog.cancel());

        builder.show();
    }

    public void getFare() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (container.getVisibility() == View.VISIBLE) {
            container.setVisibility(View.GONE);
            findViewById(R.id.PayTypeArea).setVisibility(View.GONE);
        }
        if (loading.getVisibility() != View.VISIBLE) {
            loading.setVisibility(View.VISIBLE);
            main_content.setVisibility(View.GONE);
            PayTypeArea.setVisibility(View.GONE);
            btnarea.setVisibility(View.GONE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "displayFare");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            closeLoader();
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                responseString = generalFunc.getJsonValue(Utils.message_str, responseString);
                if (responseString != null && !responseString.equals("")) {
                    String FormattedTripDate = generalFunc.getJsonValue("tTripRequestDateOrig", responseString);
                    String FareSubTotal = generalFunc.getJsonValue("FareSubTotal", responseString);
                    String eIsInterCity = generalFunc.getJsonValue("eIsInterCity", responseString);
                    String eRoundTrip = generalFunc.getJsonValue("eRoundTrip", responseString);
                    String vTripPaymentMode = generalFunc.getJsonValue("vTripPaymentMode", responseString);
                    String fDiscount = generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("fDiscount", responseString));
                    String CurrencySymbol = generalFunc.getJsonValue("CurrencySymbol", responseString);
                    String PaymentPerson = generalFunc.getJsonValue("PaymentPerson", responseString);
                    String ePaymentBy = generalFunc.getJsonValue("ePaymentBy", responseString);
                    String button_lbl = generalFunc.getJsonValue("OutstandingLabel", responseString);
                    String OutstandingDescDriver = generalFunc.getJsonValue("OutstandingDescDriver", responseString);

                    String vServiceDetailTitle = generalFunc.getJsonValue("vServiceDetailTitle", responseString);
                    isVideoCall = generalFunc.getJsonValue("isVideoCall", responseString);

                    if (!OutstandingDescDriver.equalsIgnoreCase("")) {
                        generalCommentTxt.setText(generalFunc.retrieveLangLBl("", OutstandingDescDriver));
                        generalCommentTxt.setVisibility(View.VISIBLE);
                    }

                    if (!button_lbl.equalsIgnoreCase("")) {
                        btn_type2.setText(generalFunc.retrieveLangLBl("", button_lbl));
                    }
                    cartypeTxt.setText(vServiceDetailTitle);
                    cartypeTxt.setVisibility(View.VISIBLE);

                    iTripId_str = generalFunc.getJsonValue("iTripId", responseString);


                    if (generalFunc.getJsonValue("eWalletAmtAdjusted", responseString).equalsIgnoreCase("Yes")) {
                        walletNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WALLET_AMT_ADJUSTED") + " " + generalFunc.getJsonValue("fWalletAmountAdjusted", responseString));
                    }

                    if (!fDiscount.equals("") && !fDiscount.equals("0") && !fDiscount.equals("0.00")) {
                        ((MTextView) findViewById(R.id.promoAppliedTxt)).setText(CurrencySymbol + generalFunc.convertNumberWithRTL(fDiscount));
                        (findViewById(R.id.promoView)).setVisibility(View.VISIBLE);
                    } else {
                        ((MTextView) findViewById(R.id.promoAppliedTxt)).setText("--");
                    }


                    String collectMoneytxt = "";
                    String deductedcard = "";

                    eType = generalFunc.getJsonValue("eType", responseString);

                    if (eType.equals(Utils.CabGeneralType_UberX)) {
                        dateVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_JOB_REQ_DATE") + ": ");
                        collectMoneytxt = generalFunc.retrieveLangLBl("Please collect money from rider", "LBL_COLLECT_MONEY_FRM_USER");
                        deductedcard = generalFunc.retrieveLangLBl("", "LBL_DEDUCTED_USER_CARD");
                        if (vTripPaymentMode.equals("Wallet")) {
                            deductedcard = generalFunc.retrieveLangLBl("", "LBL_DEDUCTED_USER_WALLET");
                        }

                        if (isBiddingView) {
                            sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_LOCATION_TXT"));
                        } else {
                            sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_JOB_LOCATION_TXT"));
                        }
                        destAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DEST_LOCATION"));

                        if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
                            sourceAddressHTxt.setVisibility(View.GONE);
                            sourceAddressTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIDEO_CONSULT_AT_YOUR_LOC"));

                            ImageView srcimage = findViewById(R.id.srcimage);
                            srcimage.setVisibility(View.INVISIBLE);

                            ImageView imgVideoConsult = findViewById(R.id.imgVideoConsult);
                            imgVideoConsult.setVisibility(View.VISIBLE);
                        }
                    } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                        dateVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_DATE_TXT") + ": ");
                        collectMoneytxt = generalFunc.retrieveLangLBl("Please collect money from rider", "LBL_COLLECT_MONEY_FRM_RECIPIENT");

                        if (eType.equals(Utils.eType_Multi_Delivery)) {
                            if (Utils.checkText(PaymentPerson)) {
                                collectMoneytxt = generalFunc.retrieveLangLBl("Paid By", "LBL_PAID_BY_TXT") + " : " + PaymentPerson;
                            }
                            btn_type2.setText(generalFunc.retrieveLangLBl("Confirm Delivery", "LBL_CONFIRM_DELIVERY_TXT"));
                        }

                        deductedcard = generalFunc.retrieveLangLBl("", "LBL_DEDUCTED_SENDER_CARD");
                        if (vTripPaymentMode.equals("Wallet")) {
                            deductedcard = generalFunc.retrieveLangLBl("", "LBL_DEDUCTED_SENDER_WALLET");
                        }
                        sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SENDER_LOCATION"));
                        destAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECEIVER_LOCATION"));

                    } else {
                        dateVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRIP_DATE_TXT") + ": ");
                        collectMoneytxt = generalFunc.retrieveLangLBl("Please collect money from rider", "LBL_COLLECT_MONEY_FRM_RIDER");
                        deductedcard = generalFunc.retrieveLangLBl("", "LBL_DEDUCTED_RIDER_CARD");
                        if (vTripPaymentMode.equals("Wallet")) {
                            deductedcard = generalFunc.retrieveLangLBl("", "LBL_DEDUCTED_RIDER_WALLET");
                        }
                        sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICK_UP_LOCATION"));
                        destAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DEST_LOCATION"));
                    }

                    if (vTripPaymentMode.equals("Cash")) {
                        ((MTextView) findViewById(R.id.payTypeTxt)).setText(
                                generalFunc.retrieveLangLBl("", "LBL_CASH_PAYMENT_TXT"));

                        String pay_str = "";
                        if (Utils.getText(generalCommentTxt).length() > 0) {
                            pay_str = generalCommentTxt.getText().toString() + "\n" + collectMoneytxt;
                        } else {
                            pay_str = collectMoneytxt;
                        }

                        if (eType.equals(Utils.eType_Multi_Delivery)) {
                            if (Utils.checkText(PaymentPerson)) {
                                pay_str = generalFunc.retrieveLangLBl("Paid By", "LBL_PAID_BY_TXT") + " : " + PaymentPerson;
                            }
                        }

                        generalCommentTxt.setText(pay_str);
                        generalCommentTxt.setVisibility(View.VISIBLE);

                    } else if (vTripPaymentMode.equals("Wallet")) {

                        ((MTextView) findViewById(R.id.payTypeTxt)).setText(
                                generalFunc.retrieveLangLBl("Pay by Wallet", "LBL_PAY_BY_WALLET_TXT"));
                        ((ImageView) findViewById(R.id.payTypeImg)).setImageResource(R.drawable.ic_menu_wallet);
                        generalCommentTxt.setText(deductedcard);
                        generalCommentTxt.setVisibility(View.VISIBLE);
                    } else {


                        ((MTextView) findViewById(R.id.payTypeTxt)).setText(
                                generalFunc.retrieveLangLBl("", "LBL_CARD_PAYMENT"));
                        ((ImageView) findViewById(R.id.payTypeImg)).setImageResource(R.mipmap.ic_card_new);
                        generalCommentTxt.setText(deductedcard);
                        generalCommentTxt.setVisibility(View.VISIBLE);

                    }

                    if (ePaymentBy.equals("Organization")) {
                        ((MTextView) findViewById(R.id.payTypeTxt)).setText(
                                generalFunc.retrieveLangLBl("", "LBL_ORGANIZATION"));

                        generalCommentTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MONEY_PAID_ORGANIZATION"));
                        generalCommentTxt.setVisibility(View.VISIBLE);
                        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                        ((ImageView) findViewById(R.id.payTypeImg)).setImageResource(R.drawable.ic_business_pay);
                        ((ImageView) findViewById(R.id.payTypeImg)).setColorFilter(getResources().getColor(R.color.appThemeColor_1), PorterDuff.Mode.SRC_IN);

                    }

                    String headerLable = "", noVal = "";
                    if (eType.equals(Utils.CabGeneralType_UberX)) {
                        headerLable = generalFunc.retrieveLangLBl("", "LBL_THANKS_TXT");
                        noVal = generalFunc.retrieveLangLBl("", "LBL_SERVICES") + " #";

                    } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                        headerLable = generalFunc.retrieveLangLBl("", "LBL_THANKS_TXT");
                        noVal = generalFunc.retrieveLangLBl("", "LBL_DELIVERY") + " #";

                    } else {
                        headerLable = generalFunc.retrieveLangLBl("", "LBL_THANKS_TXT");
                        noVal = generalFunc.retrieveLangLBl("", "LBL_RIDE") + " #";
                    }

                    thanksNoteTxt.setText(headerLable);
                    orderTxt.setText(noVal + "" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vRideNo", responseString)));
                    sourceAddressTxt.setText(generalFunc.getJsonValue("tSaddress", responseString));
                    destAddressTxt.setText(generalFunc.getJsonValue("tDaddress", responseString));

                    if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
                        sourceAddressTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIDEO_CONSULT_AT_YOUR_LOC"));
                    }

                    if (generalFunc.getJsonValue("tDaddress", responseString).equalsIgnoreCase("")) {
                        destAddressTxt.setVisibility(View.GONE);
                        destAddressHTxt.setVisibility(View.GONE);
                        destarea.setVisibility(View.GONE);
                        dashImage.setVisibility(View.GONE);
                        imagedest.setVisibility(View.GONE);
                    }

                    if (Utils.checkText(eIsInterCity) && eIsInterCity.equalsIgnoreCase("yes") && Utils.checkText(eRoundTrip) && eRoundTrip.equalsIgnoreCase("Yes")) {
                        destAddressTxt.setText(generalFunc.getJsonValue("tDropAddress", responseString));
                        interCityDestAddressTxt.setText(generalFunc.getJsonValue("tReturnAddress", responseString));

                        intercityPickupDt.setText(generalFunc.getJsonValue("FormattedTripDate", responseString));
                        intercityReachedDt.setText(generalFunc.getJsonValue("tReachedDateTimeFormatted", responseString));
                        intercityEndDt.setText(generalFunc.getJsonValue("tEndDateFormatted", responseString));

                        squareImgView.setVisibility(View.VISIBLE);
                        imagedest.setVisibility(View.GONE);

                        interCityImagedest.setVisibility(View.VISIBLE);
                        interCityDestarea.setVisibility(View.VISIBLE);
                        intercityPickupDt.setVisibility(View.VISIBLE);
                        intercityReachedDt.setVisibility(View.VISIBLE);
                        intercityEndDt.setVisibility(View.VISIBLE);
                        upperDateTimelayout.setVisibility(View.GONE);
                        interCityDestAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_RETURN_LOCATION"));
                    }
                    ((MTextView) findViewById(R.id.dateTxt)).setText(generalFunc.getJsonValue("tDisplayDateTime", responseString));
                    //((MTextView) findViewById(R.id.dateTxt)).setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(FormattedTripDate, Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));
                    ((MTextView) findViewById(R.id.fareTxt)).setText(generalFunc.convertNumberWithRTL(FareSubTotal));

                    container.setVisibility(View.VISIBLE);
                    findViewById(R.id.PayTypeArea).setVisibility(View.VISIBLE);
                    boolean FareDetailsArrNew = generalFunc.isJSONkeyAvail("FareDetailsNewArr", responseString);

                    JSONArray FareDetailsArrNewObj = null;
                    if (FareDetailsArrNew) {
                        FareDetailsArrNewObj = generalFunc.getJsonArray("FareDetailsNewArr", responseString);
                    }


                    if (FareDetailsArrNewObj != null)
                        addFareDetailLayout(FareDetailsArrNewObj);

                } else {
                    generateErrorView();
                }
            } else {
                generateErrorView();
            }

            if (Utils.checkText(eType) && eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) && !MyApp.getInstance().isGetDetailCall && generalFunc.getJsonValueStr("ENABLE_SAFETY_TOOLS", obj_userProfile).equalsIgnoreCase("Yes")) {
                String mFileName = "/safetyTools-" + iTripId_str + "-audio.wav";
                String filePath = getCacheDir() + mFileName;
                if (new File(filePath).exists()) {
                    RecordingItem recordingItem = new RecordingItem();
                    recordingItem.setFilePath(filePath);
                    recordingItem.setName(mFileName);
                    if (Utils.checkText(recordingItem.getName())) {
                        SafetyTools.getInstance().initiate(getActContext(), generalFunc, iTripId_str, eType);
                        SafetyTools.getInstance().audioRecordingAvailableDialog(recordingItem);
                    }
                }
            }
        });

    }

    private void addFareDetailLayout(JSONArray jobjArray) {
        if (fareDetailDisplayArea.getChildCount() > 0) {
            fareDetailDisplayArea.removeAllViewsInLayout();
        }
        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                String data = jobject.names().getString(0);
                addFareDetailRow(data, jobject.get(data).toString(), (jobjArray.length() - 1) == i ? true : false);
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

    }

    private void addFareDetailRow(String rName, String rValue, boolean isLast) {
        View convertView;
        if (rName.equalsIgnoreCase("eDisplaySeperator")) {
            convertView = new View(getActContext());
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, Utils.dipToPixels(getActContext(), 1));
            params.setMarginStart(Utils.dipToPixels(getActContext(), 10));
            params.setMarginEnd(Utils.dipToPixels(getActContext(), 10));
            convertView.setBackgroundColor(Color.parseColor("#dedede"));
            convertView.setLayoutParams(params);
        } else {
            LayoutInflater infalInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = infalInflater.inflate(R.layout.design_fare_deatil_row, null);

            convertView.setLayoutParams(new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT));

            convertView.setMinimumHeight(Utils.dipToPixels(getActContext(), 40));

            MTextView titleHTxt = convertView.findViewById(R.id.titleHTxt);
            MTextView titleVTxt = convertView.findViewById(R.id.titleVTxt);

            titleHTxt.setText(generalFunc.convertNumberWithRTL(rName));
            titleVTxt.setText(generalFunc.convertNumberWithRTL(rValue));

            if (!Utils.checkText(rValue)) {
                titleHTxt.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
                titleVTxt.setVisibility(View.GONE);
            }

            if (isLast) {
                titleHTxt.setTextColor(getResources().getColor(R.color.black));
                titleHTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);

                titleHTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);
                titleVTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));
            }
        }

        if (convertView != null) {
            fareDetailDisplayArea.addView(convertView);
        }
    }

    public void collectPayment(String isCollectCash) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CollectPayment");
        parameters.put("iTripId", iTripId_str);
        if (!isCollectCash.equals("")) {
            parameters.put("isCollectCash", isCollectCash);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                    if (responseStringObj != null) {

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {

                            Bundle bn = new Bundle();
                            bn.putSerializable("TRIP_DATA", data_trip);
                            try {
                                if (data_trip.get("eHailTrip").equalsIgnoreCase("Yes") || data_trip.get("eBookingFrom").equalsIgnoreCase("Kiosk")) {
                                    generalFunc.saveGoOnlineInfo();
                                    //  MyApp.getInstance().restartWithGetDataApp();
                                    MyApp.getInstance().refreshView(this, responseString);

                                } else {
                                    new ActUtils(getActContext()).startActWithData(TripRatingActivity.class, bn);
                                }
                            } catch (Exception e) {
                                new ActUtils(getActContext()).startActWithData(TripRatingActivity.class, bn);
                            }
                        } else {
                            buildPaymentCollectFailedMessage(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)),
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str_one, responseStringObj)));

                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }


    public void buildPaymentCollectFailedMessage(String msg, String btnStr) {

        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext(), R.style.StackedAlertDialogStyle);
        builder.setTitle("");
        builder.setCancelable(false);

        builder.setMessage(msg);

        builder.setPositiveButton(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"), (dialog, which) -> {
            collectPaymentFailedDialog.dismiss();
            if (isBiddingView) {
                collectPaymentBidding("");
            } else {
                collectPayment("");
            }
        });
        builder.setNegativeButton(btnStr, (dialog, which) -> {
            collectPaymentFailedDialog.dismiss();
            if (isBiddingView) {
                collectPaymentBidding("true");
            } else {
                collectPayment("true");
            }
        });

        collectPaymentFailedDialog = builder.create();
        collectPaymentFailedDialog.setCancelable(false);
        collectPaymentFailedDialog.setCanceledOnTouchOutside(false);
        collectPaymentFailedDialog.show();
    }

    public void closeLoader() {
        if (loading.getVisibility() == View.VISIBLE) {
            loading.setVisibility(View.GONE);
            main_content.setVisibility(View.VISIBLE);
            PayTypeArea.setVisibility(View.VISIBLE);
            btnarea.setVisibility(View.VISIBLE);
        }
    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> {
            if (isBiddingView) {
                getFareBidding();
            } else {
                getFare();
            }
        });
    }

    @Override
    public void onBackPressed() {
        return;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(CollectPaymentActivity.this);
        if (i == submitBtnId) {
            if (isBiddingView) {
                collectPaymentBidding("");
            } else {
                collectPayment("");
            }
        } else if (i == editCommentImgView.getId()) {
            showCommentBox();
        } else if (i == discounteditImgView.getId()) {
            discountVTxt.setEnabled(true);

        } else if (i == miscfeeCurrancyTxt.getId()) {
            miscfeeVTxt.setEnabled(true);
        } else if (i == discountCurrancyTxt.getId()) {
            timatrialfeeVTxt.setEnabled(true);

        }


    }


    public class setOnAddTextListner implements TextWatcher {

        @Override
        public void beforeTextChanged(CharSequence s, int start, int count, int after) {
        }

        @Override
        public void onTextChanged(CharSequence s, int start, int before, int count) {
        }

        @Override
        public void afterTextChanged(Editable s) {
        }
    }

    //Bidding view
    @SuppressLint("SetTextI18n")
    private void getFareBidding() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (container.getVisibility() == View.VISIBLE) {
            container.setVisibility(View.GONE);
            findViewById(R.id.PayTypeArea).setVisibility(View.GONE);
        }
        if (loading.getVisibility() != View.VISIBLE) {
            loading.setVisibility(View.VISIBLE);
            main_content.setVisibility(View.GONE);
            PayTypeArea.setVisibility(View.GONE);
            btnarea.setVisibility(View.GONE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "displayFareBiddingService");
        parameters.put("iBiddingPostId", data_trip.get("TripId"));
        parameters.put("PAGE_MODE", "Display");

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {
                    JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                    if (responseStringObj != null) {

                        closeLoader();

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {

                            String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);

                            ((MTextView) findViewById(R.id.fareTxt)).setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("FareSubTotal", message)));

                            thanksNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_THANKS_BIDING_SERVICE_TXT"));
                            orderTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TASK_TXT") + " #" + "" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vBiddingPostNo", message)));

                            ((MTextView) findViewById(R.id.dateTxt)).setText(generalFunc.getJsonValue("tDisplayDateTime", message));
                            //((MTextView) findViewById(R.id.dateTxt)).setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("dBiddingDate", message), Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));

                            sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_LOCATION_TXT"));
                            sourceAddressTxt.setText(generalFunc.getJsonValue("tSaddress", message));

                            destAddressTxt.setVisibility(View.GONE);
                            destAddressHTxt.setVisibility(View.GONE);
                            destarea.setVisibility(View.GONE);
                            dashImage.setVisibility(View.GONE);
                            imagedest.setVisibility(View.GONE);

                            cartypeTxt.setText(generalFunc.getJsonValue("vServiceDetailTitle", message));
                            cartypeTxt.setVisibility(View.VISIBLE);

                            if (generalFunc.isJSONkeyAvail("FareDetailsNewArr", message)) {
                                addFareDetailLayout(generalFunc.getJsonArray("FareDetailsNewArr", message));
                            }

                            PayTypeArea.setVisibility(View.VISIBLE);
                            String vBiddingPaymentMode = generalFunc.getJsonValue("vBiddingPaymentMode", message);
                            if (vBiddingPaymentMode.equals("Wallet")) {
                                ((MTextView) findViewById(R.id.payTypeTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_PAY_BY_WALLET_TXT"));
                                ((ImageView) findViewById(R.id.payTypeImg)).setImageResource(R.drawable.ic_menu_wallet);
                                generalCommentTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DEDUCTED_USER_WALLET"));
                                generalCommentTxt.setVisibility(View.VISIBLE);

                            } else if (vBiddingPaymentMode.equals("Cash")) {
                                ((MTextView) findViewById(R.id.payTypeTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CASH_PAYMENT_TXT"));
                                ((ImageView) findViewById(R.id.payTypeImg)).setImageResource(R.drawable.ic_cash_new);

                                generalCommentTxt.setText(generalFunc.retrieveLangLBl("", "LBL_COLLECT_MONEY_FRM_USER"));
                                generalCommentTxt.setVisibility(View.VISIBLE);
                            } else {
                                ((MTextView) findViewById(R.id.payTypeTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CARD_PAYMENT"));
                                ((ImageView) findViewById(R.id.payTypeImg)).setImageResource(R.mipmap.ic_card_new);
                                generalCommentTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DEDUCTED_USER_CARD"));
                                generalCommentTxt.setVisibility(View.VISIBLE);
                            }

                            container.setVisibility(View.VISIBLE);
                        } else {
                            generateErrorView();
                        }
                    } else {
                        generateErrorView();
                    }
                });

    }

    private void collectPaymentBidding(String isCollectCash) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CollectPaymentBiddingService");
        parameters.put("iBiddingPostId", data_trip.get("TripId"));
        if (!isCollectCash.equals("")) {
            parameters.put("isCollectCash", isCollectCash);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                    if (responseStringObj != null) {

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {

                            Bundle bn = new Bundle();
                            bn.putSerializable("TRIP_DATA", data_trip);
                            new ActUtils(getActContext()).startActWithData(TripRatingActivity.class, bn);
                        } else {
                            buildPaymentCollectFailedMessage(generalFunc.retrieveLangLBl("",
                                            generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)),
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str_one, responseStringObj)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }
}