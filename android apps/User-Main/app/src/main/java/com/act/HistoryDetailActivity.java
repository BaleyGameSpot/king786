package com.act;

import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.graphics.drawable.ColorDrawable;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.text.InputFilter;
import android.text.InputType;
import android.text.TextUtils;
import android.text.method.ScrollingMovementMethod;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.cardview.widget.CardView;

import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.CustomHorizontalScrollView;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OnSwipeTouchListener;
import com.general.files.SetOnTouchList;
import com.general.files.SlideAnimationUtil;
import com.buddyverse.main.R;
import com.kyleduo.switchbutton.SwitchButton;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.DividerView;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;

public class HistoryDetailActivity extends ParentActivity {


    private MTextView titleTxt, tipTitleText, tipDescTitleText, tipDescHintTitleText, subTitleTxt, cartypeTxt, ufxratingDriverHTxt, dropOffHTxt, interCityDestAddressHTxt, pickUpVTxt, interCityDestAddressTxt, dropOffVTxt, tipHTxt, tipamtTxt, tipmsgTxt, vReasonTitleTxt, favDriverTitleTxt, tripRatingTitleTxt, safetyRatingTitleTxt, tipAmountText1, tipAmountText2, tipAmountText3, tipAmountOtherText, interCityEndDateTime, interCityReachedDateTime, interCityDateTime, tripdateVTxt;
    private ImageView backImgView, receiptImgView, helpTxt, tipPluseImage, driverImageview, iv_tip_help;
    private LinearLayout fareDetailDisplayArea, beforeServiceArea, afterServiceArea, tipArea, safetyRatingArea, lineArea, interCityDropOffLayout, favArea, rideRatingArea, tipCardArea, tipAmountArea1, tipAmountArea2, tipAmountArea3, tipAmountAreaOther, addTipArea, amountArea, dropToLocation;

    private String before_serviceImg_url = "", after_serviceImg_url = "", isRatingDone = "", tripData = "", senderImage, CurrencySymbol = "", tipAmount = "", ShowSafetyRating = "", isVideoCall = "";
    private MButton btn_type2, giveTipArea;
    private AppCompatImageView imagemarkerdest2;
    private View squareImgView;
    private SimpleRatingBar ufxratingBar, safetyRatingBar;
    private int rateBtnId, width, width_, width_D, submitBtnId;
    private MaterialEditText commentBox;
    private Dialog signatureImageDialog;

    private ProgressBar loading;
    private ErrorView errorView;
    private RelativeLayout container;

    private CardView viewReqServicesArea;

    private SwitchButton favSwitch;
    /* Fav Driver variable declaration end */ FrameLayout paymentMainArea;
    private CustomHorizontalScrollView hScrollView;
    private boolean isAnimated = false;
    private EditText tipAmountBox;
    private AlertDialog giveTipAlertDialog;
    private static final int WEBVIEWPAYMENT = 001;
    private DividerView dividerView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_history_detail);

        helpTxt = (ImageView) findViewById(R.id.helpTxt);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        subTitleTxt = (MTextView) findViewById(R.id.subTitleTxt);
        receiptImgView = (ImageView) findViewById(R.id.receiptImgView);
        commentBox = (MaterialEditText) findViewById(R.id.commentBox);
        vReasonTitleTxt = (MTextView) findViewById(R.id.vReasonTitleTxt);
        viewReqServicesArea = (CardView) findViewById(R.id.viewReqServicesArea);
        addToClickHandler(receiptImgView);
        paymentMainArea = findViewById(R.id.paymentMainArea);
        interCityDropOffLayout = findViewById(R.id.interCityDropOffLayout);
        dividerView = findViewById(R.id.interCityaboveLine);
        tipPluseImage = (ImageView) findViewById(R.id.tipPluseImage);

        commentBox.setHint(generalFunc.retrieveLangLBl("", "LBL_WRITE_COMMENT_HINT_TXT"));

        MyUtils.editBoxMultiLine(commentBox);


        backImgView = (ImageView) findViewById(R.id.backImgView);
        fareDetailDisplayArea = (LinearLayout) findViewById(R.id.fareDetailDisplayArea);
        afterServiceArea = (LinearLayout) findViewById(R.id.afterServiceArea);
        beforeServiceArea = (LinearLayout) findViewById(R.id.beforeServiceArea);
        cartypeTxt = (MTextView) findViewById(R.id.cartypeTxt);

        ufxratingDriverHTxt = (MTextView) findViewById(R.id.ufxratingDriverHTxt);
        dropOffHTxt = (MTextView) findViewById(R.id.dropOffHTxt);
        imagemarkerdest2 = (AppCompatImageView) findViewById(R.id.imagemarkerdest2);
        dropOffVTxt = (MTextView) findViewById(R.id.dropOffVTxt);
        squareImgView = findViewById(R.id.squareImgView);
        pickUpVTxt = (MTextView) findViewById(R.id.pickUpVTxt);
        interCityDestAddressHTxt = (MTextView) findViewById(R.id.interCityDestAddressHTxt);
        interCityDestAddressTxt = (MTextView) findViewById(R.id.interCityDestAddressTxt);
        ufxratingBar = (SimpleRatingBar) findViewById(R.id.ufxratingBar);
        dropToLocation = (LinearLayout) findViewById(R.id.dropToLocation);
        interCityEndDateTime = (MTextView) findViewById(R.id.interCityEndDateTime);
        interCityReachedDateTime = (MTextView) findViewById(R.id.interCityReachedDateTime);
        interCityDateTime = (MTextView) findViewById(R.id.interCityDateTime);
        tripdateVTxt = (MTextView) findViewById(R.id.tripdateVTxt);


        tipHTxt = (MTextView) findViewById(R.id.tipHTxt);
        tipamtTxt = (MTextView) findViewById(R.id.tipamtTxt);
        tipmsgTxt = (MTextView) findViewById(R.id.tipmsgTxt);

        loading = (ProgressBar) findViewById(R.id.loading);
        errorView = (ErrorView) findViewById(R.id.errorView);
        container = (RelativeLayout) findViewById(R.id.container);

        tipArea = (LinearLayout) findViewById(R.id.tipArea);
        driverImageview = (ImageView) findViewById(R.id.driverImgView);


        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        rateBtnId = Utils.generateViewId();
        btn_type2.setId(rateBtnId);
        addToClickHandler(btn_type2);
        addToClickHandler(viewReqServicesArea);
        getMemberBookings();
        commentBox.setTextColor(getResources().getColor(R.color.mdtp_transparent_black));

        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }

        addToClickHandler(backImgView);
        addToClickHandler(subTitleTxt);
        addToClickHandler(afterServiceArea);
        addToClickHandler(beforeServiceArea);
        addToClickHandler(helpTxt);
//        commentBox.setOnTouchListener((v, event) -> {
//            ((NestedScrollView) findViewById(R.id.scrollContainer)).requestDisallowInterceptTouchEvent(true);
//            return false;
//        });

        tipCardArea = (LinearLayout) findViewById(R.id.tipCardArea);
        tipCardArea.setVisibility(View.GONE);
        tipUI();
    }

    private void tipUI() {
        tipCardArea = (LinearLayout) findViewById(R.id.tipCardArea);
        tipAmountOtherText = (MTextView) findViewById(R.id.tipAmountTextOther);
        tipAmountAreaOther = (LinearLayout) findViewById(R.id.tipAmountAreaOther);

        tipAmountText1 = (MTextView) findViewById(R.id.tipAmountText1);
        tipAmountArea1 = (LinearLayout) findViewById(R.id.tipAmountArea1);

        tipAmountText2 = (MTextView) findViewById(R.id.tipAmountText2);
        tipAmountArea2 = (LinearLayout) findViewById(R.id.tipAmountArea2);

        tipAmountText3 = (MTextView) findViewById(R.id.tipAmountText3);
        tipAmountArea3 = (LinearLayout) findViewById(R.id.tipAmountArea3);

        addToClickHandler(tipAmountArea1);
        addToClickHandler(tipAmountArea2);
        addToClickHandler(tipAmountArea3);
        addToClickHandler(tipAmountAreaOther);

        tipTitleText = (MTextView) findViewById(R.id.tipTitleText);
        iv_tip_help = (ImageView) findViewById(R.id.iv_tip_help);
        addToClickHandler(iv_tip_help);

        tipDescTitleText = (MTextView) findViewById(R.id.tipDescTitleText);
        tipDescHintTitleText = (MTextView) findViewById(R.id.tipDescHintTitleText);

        tipAmountBox = (EditText) findViewById(R.id.tipAmountBox);
        addTipArea = (LinearLayout) findViewById(R.id.addTipArea);
        amountArea = (LinearLayout) findViewById(R.id.amountArea);
        giveTipArea = ((MaterialRippleLayout) findViewById(R.id.giveTipArea)).getChildView();
        submitBtnId = Utils.generateViewId();
        giveTipArea.setId(submitBtnId);
        addToClickHandler(giveTipArea);

        tipAmountBox.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        tipAmountBox.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});

        tipTitleText.setText(generalFunc.retrieveLangLBl("", "LBL_TIP_TITLE_TXT"));
        tipDescTitleText.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_TIP_DESCRIPTION_TXT"));
        tipDescHintTitleText.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_TIP_HOW_WORKS_TXT"));
        tipAmountBox.setHint(generalFunc.retrieveLangLBl("", "LBL_TIP_AMOUNT_ENTER_TITLE"));
        giveTipArea.setText(generalFunc.retrieveLangLBl("", "LBL_GIVE_TIP"));

        addToClickHandler(tipDescHintTitleText);
        tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));

        CurrencySymbol = generalFunc.getJsonValueStr("CurrencySymbol", obj_userProfile);
        String tipAMount1 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("RIDE_TIP_AMOUNT_1", obj_userProfile));
        String tipAMount2 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("RIDE_TIP_AMOUNT_2", obj_userProfile));
        String tipAMount3 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("RIDE_TIP_AMOUNT_3", obj_userProfile));
        boolean eReverseSymbolEnable = generalFunc.retrieveValue("eReverseSymbolEnable").equalsIgnoreCase("Yes");
        tipAmountText1.setTag(tipAMount1);
        tipAmountText1.setText(eReverseSymbolEnable ? tipAMount1 + " " + CurrencySymbol : CurrencySymbol + tipAMount1);
        tipAmountText2.setTag(tipAMount2);
        tipAmountText2.setText(eReverseSymbolEnable ? tipAMount2 + " " + CurrencySymbol : CurrencySymbol + tipAMount2);
        tipAmountText3.setTag(tipAMount3);
        tipAmountText3.setText(eReverseSymbolEnable ? tipAMount3 + " " + CurrencySymbol : CurrencySymbol + tipAMount3);
        tipAmountOtherText.setTag("");

    }

    private void setSelected(MTextView selectedTextAreaTxtView, LinearLayout selectedArea) {
        tipAmount = selectedTextAreaTxtView.getTag().toString();
        addTipArea.setVisibility(selectedArea == tipAmountAreaOther ? View.VISIBLE : View.GONE);
        resetErrorTxt();
        tipAmountArea1.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea1 ? R.drawable.button_background : R.drawable.button_normal_background));
        tipAmountArea2.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea2 ? R.drawable.button_background : R.drawable.button_normal_background));
        tipAmountArea3.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea3 ? R.drawable.button_background : R.drawable.button_normal_background));
        tipAmountAreaOther.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountAreaOther ? R.drawable.button_background : R.drawable.button_normal_background));

        if (selectedArea == tipAmountAreaOther && Utils.checkText(Utils.getText(tipAmountBox))) {
            tipAmountOtherText.setTag(Utils.getText(tipAmountBox));
            tipAmount = Utils.getText(tipAmountBox);
            tipAmountOtherText.setText(CurrencySymbol + Utils.getText(tipAmountBox));
            addTipArea.setVisibility(View.GONE);
            resetErrorTxt();
        } else if (selectedArea != tipAmountAreaOther) {
            tipAmountOtherText.setTag("");

            tipAmountBox.setText("");
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        } else if ((selectedArea == tipAmountAreaOther && !Utils.checkText(Utils.getText(tipAmountBox))) && Utils.checkText(tipAmount)) {
            tipAmountOtherText.setTag("");
            tipAmountBox.setText("");
            tipAmount = "";
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        } else {
            tipAmountBox.setText("");
            tipAmountOtherText.setTag("");
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        }

        if (selectedTextAreaTxtView == tipAmountText1) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText1.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountText2) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText2.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountText3) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText3.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountOtherText) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountOtherText.setTextColor(getResources().getColor(R.color.black));
        }
    }

    private void resetErrorTxt() {
        amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrectwithdesign));
    }

    private void getMemberBookings() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (container.getVisibility() == View.VISIBLE) {
            container.setVisibility(View.GONE);
            paymentMainArea.setVisibility(View.GONE);
        }
        if (loading.getVisibility() != View.VISIBLE) {
            loading.setVisibility(View.VISIBLE);
        }


        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getMemberBookings");
        parameters.put("memberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("memberType", Utils.app_type);
        if (getIntent().hasExtra("iTripId")) {
            parameters.put("iTripId", getIntent().getExtras().getString("iTripId"));
        }
        if (getIntent().hasExtra("iCabBookingId")) {
            parameters.put("iCabBookingId", getIntent().getExtras().getString("iCabBookingId"));
        }


        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {
                closeLoader();

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    JSONArray arr_rides = generalFunc.getJsonArray(Utils.message_str, responseObj);


                    if (arr_rides != null && arr_rides.length() > 0) {
                        for (int i = 0; i < arr_rides.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);
                            HashMap<String, String> map = new HashMap<String, String>();

                            ShowSafetyRating = generalFunc.getJsonValueStr("ShowSafetyRating", obj_temp);
                            map.put("tTripRequestDateOrig", generalFunc.getJsonValueStr("tTripRequestDateOrig", obj_temp));
                            map.put("tTripRequestDisplayDate", generalFunc.getJsonValueStr("tTripRequestDisplayDate", obj_temp));
                            map.put("tTripRequestDisplayTime", generalFunc.getJsonValueStr("tTripRequestDisplayTime", obj_temp));
                            map.put("tTripRequestDisplayDateTime", generalFunc.getJsonValueStr("tTripRequestDisplayDateTime", obj_temp));

                            map.put("CurrencySymbol", generalFunc.getJsonValueStr("CurrencySymbol", obj_temp));
                            map.put("tSaddress", generalFunc.getJsonValueStr("tSaddress", obj_temp));
                            map.put("tDaddress", generalFunc.getJsonValueStr("tDaddress", obj_temp));
                            map.put("vRideNo", generalFunc.getJsonValueStr("vRideNo", obj_temp));
                            map.put("eFly", generalFunc.getJsonValueStr("eFly", obj_temp));

                            map.put("is_rating", generalFunc.getJsonValueStr("is_rating", obj_temp));
                            map.put("iTripId", generalFunc.getJsonValueStr("iTripId", obj_temp));
                            senderImage = generalFunc.getJsonValueStr("vSignImage", obj_temp);

                            if (Utils.checkText(senderImage)) {
                                findViewById(R.id.signArea).setVisibility(View.VISIBLE);
                                addToClickHandler(findViewById(R.id.signArea));
                            }

                            if (generalFunc.getJsonValueStr("eType", obj_temp).equalsIgnoreCase("deliver") || generalFunc.getJsonValue("eType", obj_temp).equals(Utils.eType_Multi_Delivery)) {
                                map.put("eType", generalFunc.retrieveLangLBl("Delivery", "LBL_DELIVERY"));
                                map.put("LBL_PICK_UP_LOCATION", generalFunc.retrieveLangLBl("Sender Location", "LBL_SENDER_LOCATION"));
                                map.put("LBL_DEST_LOCATION", generalFunc.retrieveLangLBl("Receiver's Location", "LBL_RECEIVER_LOCATION"));
                            } else {
                                map.put("LBL_PICK_UP_LOCATION", generalFunc.retrieveLangLBl("", "LBL_PICK_UP_LOCATION"));
                                map.put("eType", generalFunc.getJsonValueStr("eType", obj_temp));
                                map.put("LBL_DEST_LOCATION", generalFunc.retrieveLangLBl("", "LBL_DEST_LOCATION"));
                            }
                            map.put("eFareType", generalFunc.getJsonValueStr("eFareType", obj_temp));

                            if (generalFunc.getJsonValueStr("eCancelled", obj_temp).equals("Yes")) {
                                map.put("iActive", generalFunc.retrieveLangLBl("", "LBL_CANCELED_TXT"));
                            } else {
                                if (generalFunc.getJsonValueStr("iActive", obj_temp).equals("Canceled")) {
                                    map.put("iActive", generalFunc.retrieveLangLBl("", "LBL_CANCELED_TXT"));
                                } else if (generalFunc.getJsonValueStr("iActive", obj_temp).equals("Finished")) {
                                    map.put("iActive", generalFunc.retrieveLangLBl("", "LBL_FINISHED_TXT"));
                                } else {
                                    map.put("iActive", generalFunc.getJsonValueStr("iActive", obj_temp));
                                }
                            }

                            if (generalFunc.retrieveValue(Utils.APP_DESTINATION_MODE).equalsIgnoreCase(Utils.NONE_DESTINATION)) {
                                map.put("DESTINATION", "No");
                            } else {
                                map.put("DESTINATION", "Yes");
                            }


                            map.put("JSON", obj_temp.toString());
                            isVideoCall = generalFunc.getJsonValueStr("isVideoCall", obj_temp);

                            if (generalFunc.getJsonValueStr("eType", obj_temp).equals(Utils.CabGeneralType_UberX) && !generalFunc.getJsonValueStr("eFareType", obj_temp).equalsIgnoreCase(Utils.CabFaretypeRegular)) {

                                map.put("SelectedVehicle", generalFunc.getJsonValueStr("carTypeName", obj_temp));
                                map.put("SelectedCategory", generalFunc.getJsonValueStr("vVehicleCategory", obj_temp));


                            }
                            map.put("moreServices", generalFunc.getJsonValueStr("moreServices", obj_temp));
                            if (generalFunc.getJsonValueStr("eFareType", obj_temp).equalsIgnoreCase(Utils.CabFaretypeFixed) && generalFunc.getJsonValueStr("moreServices", obj_temp).equalsIgnoreCase("No")) {
                                map.put("SelectedCategory", generalFunc.getJsonValueStr("vCategory", obj_temp));

                            }

//                            map.put("LBL_BOOKING_NO", generalFunc.retrieveLangLBl("Delivery No", "LBL_DELIVERY_NO"));
                            map.put("LBL_CANCEL_BOOKING", generalFunc.retrieveLangLBl("Cancel Delivery", "LBL_CANCEL_DELIVERY"));
                            map.put("LBL_BOOKING_NO", generalFunc.retrieveLangLBl("", "LBL_BOOKING"));
                            map.put("LBL_Status", generalFunc.retrieveLangLBl("", "LBL_Status"));
                            map.put("LBL_JOB_LOCATION_TXT", generalFunc.retrieveLangLBl("", "LBL_JOB_LOCATION_TXT"));

                            String paymentDoneByDetail = generalFunc.getJsonValueStr("PaymentPerson", obj_temp);

                            if (Utils.checkText(paymentDoneByDetail)) {
                                ((MTextView) findViewById(R.id.recipientTxt)).setVisibility(View.VISIBLE);
                                ((MTextView) findViewById(R.id.recipientTxt)).setText(" " + generalFunc.retrieveLangLBl("Paid By", "LBL_PAID_BY_TXT") + " " + paymentDoneByDetail);
                            }


                            if (map != null) {
                                tripData = map.get("JSON");
                                setLabels(tripData);
                                setData(tripData);
                            }

                            if (ShowSafetyRating.equalsIgnoreCase("Yes")) {
                                DisplayMetrics displayMetrics = new DisplayMetrics();
                                getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
                                width = displayMetrics.widthPixels;
                                width_D = (int) (width * 0.349);
                                width_ = (int) (width * 0.397);
                                safetyRatingBar = findViewById(R.id.safetyRatingBar);
                                tripRatingTitleTxt = findViewById(R.id.tripRatingTitleTxt);
                                safetyRatingTitleTxt = findViewById(R.id.safetyRatingTitleTxt);
                                safetyRatingTitleTxt.setText(generalFunc.retrieveLangLBl("Safety Rating", "LBL_SAFETY_RATING_TXT"));
                                tripRatingTitleTxt.setText(generalFunc.retrieveLangLBl("Trip Rating", "LBL_TRIP_RATING_TXT"));
                                safetyRatingTitleTxt.setVisibility(View.VISIBLE);
                                tripRatingTitleTxt.setVisibility(View.VISIBLE);

                                hScrollView = (CustomHorizontalScrollView) findViewById(R.id.hScrollView);
                                rideRatingArea = (LinearLayout) findViewById(R.id.rideRatingArea);
                                lineArea = (LinearLayout) findViewById(R.id.lineArea);
                                safetyRatingArea = (LinearLayout) findViewById(R.id.ratingArea);
                                lineArea.setOnTouchListener(new SetOnTouchList());

                                hScrollView.setOnTouchListener(new SetOnTouchList());
                                addToClickHandler(hScrollView);

                                ufxratingBar.setOnRatingBarChangeListener(new SimpleRatingBar.OnRatingBarChangeListener() {
                                    @Override
                                    public void onRatingChanged(SimpleRatingBar simpleRatingBar, float rating, boolean fromUser) {

                                        if (safetyRatingArea.getVisibility() == View.GONE) {
                                            showSafetyRatingArea(true);

                                        }
                                    }
                                });
                                hScrollView.setOnTouchListener(new OnSwipeTouchListener(getActContext()) {
                                    public void onSwipeTop() {

                                    }

                                    public void onSwipeRight() {
                                        scrollWithanimation();
                                    }

                                    public void onSwipeLeft() {
                                        scrollWithanimation();
                                    }

                                    public void onSwipeBottom() {

                                    }

                                });
                            }


                            if (generalFunc.getJsonValueStr("ENABLE_TIP_MODULE", obj_temp).equalsIgnoreCase("Yes")) {
                                tipCardArea.setVisibility(View.VISIBLE);
                            } else {
                                tipCardArea.setVisibility(View.GONE);
                            }
                        }
                    }


                } else {
                    generateErrorView();
                }
            } else {
                generateErrorView();
            }
        });

    }

    private void showSafetyRatingArea(boolean show) {
        if (show) {

            if (generalFunc.isRTLmode()) {
                SlideAnimationUtil.slideInFromLeft(getActContext(), safetyRatingArea);
            } else {
                SlideAnimationUtil.slideOutToRight(getActContext(), safetyRatingArea);
            }

            if (!isAnimated) {
                hScrollView.setScrollingEnabled(true);

                ufxratingBar.animate().translationXBy(generalFunc.isRTLmode() ? width_ : -width_);
                rideRatingArea.animate().translationXBy(generalFunc.isRTLmode() ? width_ : -width_);

                ufxratingBar.setIndicator(true);
                ufxratingBar.setFocusable(false);

                safetyRatingArea.setVisibility(View.VISIBLE);
                safetyRatingArea.animate().translationXBy(generalFunc.isRTLmode() ? width_D : -width_D);

                isAnimated = true;

                hScrollView.setScrollingEnabled(false);
            }

        } else {
//

            if (generalFunc.isRTLmode()) {
                SlideAnimationUtil.slideInFromRight(getActContext(), ufxratingBar);
                SlideAnimationUtil.slideInFromRight(getActContext(), rideRatingArea);
            } else {
                SlideAnimationUtil.slideOutToLeft(getActContext(), ufxratingBar);
                SlideAnimationUtil.slideOutToLeft(getActContext(), rideRatingArea);
            }

            if (isAnimated) {
                hScrollView.setScrollingEnabled(true);

                ufxratingBar.animate().translationXBy(generalFunc.isRTLmode() ? -width_ : width_);
                rideRatingArea.animate().translationXBy(generalFunc.isRTLmode() ? -width_ : width_);

                ufxratingBar.setIndicator(false);
                ufxratingBar.setFocusable(true);

                safetyRatingArea.animate().translationXBy(generalFunc.isRTLmode() ? -width_D : width_D);
                safetyRatingArea.setVisibility(View.GONE);

                isAnimated = false;

                hScrollView.setScrollingEnabled(false);
            }


        }
        if (show) {
            addToClickHandler(lineArea);
        } else {
            lineArea.setOnClickListener(null);
        }

    }

    private void scrollWithanimation() {
        if (!ufxratingBar.isFocusable()) {
            lineArea.performClick();
        } else {
            showSafetyRatingArea(true);
        }
    }

    public void closeLoader() {
        if (loading.getVisibility() == View.VISIBLE) {
            loading.setVisibility(View.GONE);
        }

        if (container.getVisibility() == View.GONE) {
            container.setVisibility(View.VISIBLE);
            paymentMainArea.setVisibility(View.VISIBLE);
        }
    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }

        if (container.getVisibility() == View.VISIBLE) {
            container.setVisibility(View.GONE);
            paymentMainArea.setVisibility(View.GONE);
        }

        errorView.setOnRetryListener(() -> getMemberBookings());
    }


    String headerLable = "", noVal = "", driverhVal = "";

    public void setLabels(String tripData) {
        String eType = generalFunc.getJsonValue("eType", tripData);
        /*Multi related new lable*/
        ((MTextView) findViewById(R.id.viewSingleDeliveryTitleTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_DETAILS"));

        ((MTextView) findViewById(R.id.passengerSignTxt)).setText(generalFunc.retrieveLangLBl("View Signature", "LBL_VIEW_MULTI_SENDER_SIGN"));
        ((MTextView) findViewById(R.id.viewDeliveryDetails)).setText(generalFunc.retrieveLangLBl("View Delivery Details", "LBL_VIEW_DELIVERY_DETAILS"));
        titleTxt.setText(generalFunc.retrieveLangLBl("RECEIPT", "LBL_RECEIPT_HEADER_TXT"));
        subTitleTxt.setText(generalFunc.retrieveLangLBl("GET RECEIPT", "LBL_GET_RECEIPT_TXT"));
        ((MTextView) findViewById(R.id.viewReqServicesTxtView)).setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_REQUESTED_SERVICES"));

        boolean eFly = generalFunc.getJsonValue("eFly", tripData).equalsIgnoreCase("Yes");

        if (eType.equals(Utils.CabGeneralType_UberX)) {
            headerLable = generalFunc.retrieveLangLBl("", "LBL_THANKS_TXT");
            noVal = generalFunc.retrieveLangLBl("", "LBL_SERVICES");
            driverhVal = generalFunc.retrieveLangLBl("", "LBL_SERVICE_PROVIDER_TXT");
        } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
            headerLable = generalFunc.retrieveLangLBl("", "LBL_THANKS_TXT");
            noVal = generalFunc.retrieveLangLBl("", "LBL_DELIVERY");
            driverhVal = generalFunc.retrieveLangLBl("", "LBL_CARRIER");
        } else {
            headerLable = generalFunc.retrieveLangLBl("", "LBL_THANKS_TXT");
            noVal = generalFunc.retrieveLangLBl("", eFly ? "LBL_HEADER_RDU_FLY_RIDE" : "LBL_RIDE");
            driverhVal = generalFunc.retrieveLangLBl("", "LBL_DRIVER");
        }

        ((MTextView) findViewById(R.id.headerTxt)).setText(generalFunc.retrieveLangLBl("", headerLable));
        ((MTextView) findViewById(R.id.rideNoHTxt)).setText(noVal);

        String pickupHval = "";

        if (eType.equals(Utils.CabGeneralType_UberX)) {
            pickupHval = generalFunc.retrieveLangLBl("", "LBL_JOB_LOCATION_TXT");
            if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
                ((MTextView) findViewById(R.id.pickUpAddressVTxt)).setText(generalFunc.retrieveLangLBl("Video Consultation", "LBL_VIDEO_CONSULT_AT_YOUR_LOC"));
            }
        } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
            pickupHval = generalFunc.retrieveLangLBl("", "LBL_SENDER_LOCATION");
        } else {
            pickupHval = generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOCATION_TXT");
        }

        ((MTextView) findViewById(R.id.pickUpHTxt)).setText(pickupHval);
        ((MTextView) findViewById(R.id.pickUpAddressHTxt)).setText(pickupHval);

        if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
            dropOffHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_DETAILS_TXT"));
        } else {
            dropOffHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DEST_LOCATION"));
        }

        interCityDestAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_RETURN_LOCATION"));
        ((MTextView) findViewById(R.id.chargesHTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CHARGES_TXT"));
        btn_type2.setText(generalFunc.retrieveLangLBl("Rate", "LBL_RATE_DRIVER_TXT"));

        if (eType.equals(Utils.eType_Multi_Delivery)) {
            ufxratingDriverHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RATE_HEADING_CARRIER"));
        } else {
            ufxratingDriverHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RATE_HEADING_DRIVER_TXT"));
        }

        tipHTxt.setText(generalFunc.retrieveLangLBl("Tip Amount", "LBL_TIP_AMOUNT"));
        tipmsgTxt.setText(generalFunc.retrieveLangLBl("Thank you for giving tip for this trip.", "LBL_TIP_INFO_SHOW_RIDER"));


    }

    public void setData(String tripData) {
        String eType = generalFunc.getJsonValue("eType", tripData);
        if (generalFunc.getJsonValue("vReasonTitle", tripData) != null && !generalFunc.getJsonValue("vReasonTitle", tripData).equalsIgnoreCase("")) {
            vReasonTitleTxt.setVisibility(View.VISIBLE);
            vReasonTitleTxt.setText(generalFunc.getJsonValue("vReasonTitle", tripData));
        }

        String vImage = generalFunc.getJsonValue("vImage", tripData);
        if (vImage == null || vImage.equals("") || vImage.equals("NONE")) {
            (driverImageview).setImageResource(R.mipmap.ic_no_pic_user);
        } else {

            new LoadImage.builder(LoadImage.bind(vImage), driverImageview).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
        }


        if (generalFunc.retrieveValue(Utils.ENABLE_FAVORITE_DRIVER_MODULE_KEY).equalsIgnoreCase("Yes")) {

            /*Fav Driver Feature Start*/

            favSwitch = (SwitchButton) findViewById(R.id.favSwitch);
            favArea = (LinearLayout) findViewById(R.id.favArea);
            favArea.setVisibility(View.VISIBLE);
            favDriverTitleTxt = (MTextView) findViewById(R.id.favDriverTitleTxt);
            if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                favDriverTitleTxt.setText(generalFunc.retrieveLangLBl("Save as Favourite Carrier?", "LBL_SAVE_FAVOUTITE_CARRIER"));
            } else {
                favDriverTitleTxt.setText(generalFunc.retrieveLangLBl("save as Favorite Driver", "LBL_SAVE_AS_FAV_DRIVER"));
            }

            favSwitch.setOnCheckedChangeListener((compoundButton, b) -> {

                if (b) {
                    favSwitch.setThumbColorRes(R.color.Green);
                } else {
                    favSwitch.setThumbColorRes(android.R.color.holo_red_dark);
                }


            });

            String eFavDriver = generalFunc.getJsonValue("eFavDriver", tripData);
            favSwitch.setChecked(eFavDriver.equalsIgnoreCase("Yes"));

            /*Fav Driver Feature End*/

        }


        ((MTextView) findViewById(R.id.rideNoVTxt)).setText("#" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vRideNo", tripData)));
        if (generalFunc.getJsonValue("eChargeViewShow", tripData) != null && generalFunc.getJsonValue("eChargeViewShow", tripData).equalsIgnoreCase("No")) {
            if (!generalFunc.getJsonValue("iActive", tripData).equalsIgnoreCase("Canceled")) {
                findViewById(R.id.headerTxt).setVisibility(View.GONE);
            }
            findViewById(R.id.chargeArea).setVisibility(View.GONE);
            findViewById(R.id.paymentarea).setVisibility(View.GONE);
            findViewById(R.id.helpTxt).setVisibility(View.GONE);
            ((MTextView) findViewById(R.id.rideNoVTxt)).setText("#" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vRideNo", tripData)));
            LinearLayout.LayoutParams txtParam = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            txtParam.setMargins(2, 10, 2, 0);
            ((MTextView) findViewById(R.id.rideNoVTxt)).setLayoutParams(txtParam);
            ((MTextView) findViewById(R.id.rideNoHTxt)).setLayoutParams(txtParam);

        }


        ((MTextView) findViewById(R.id.nameDriverVTxt)).setText(generalFunc.getJsonValue("vName", generalFunc.getJsonValue("DriverDetails", tripData)) + " " + generalFunc.getJsonValue("vLastName", generalFunc.getJsonValue("DriverDetails", tripData)) + " (" + driverhVal + ")");
        //tripdateVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("dBooking_dateOrig", tripData), Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));
        //interCityDateTime.setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("dBooking_dateOrig", tripData), Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));
        tripdateVTxt.setText(generalFunc.getJsonValue("tDisplayDateTime", tripData));
        interCityDateTime.setText(generalFunc.getJsonValue("tTripRequestDisplayDateTime", tripData));

        pickUpVTxt.setText(generalFunc.getJsonValue("tSaddress", tripData));
        ((MTextView) findViewById(R.id.pickUpAddressVTxt)).setText(generalFunc.getJsonValue("tSaddress", tripData));
        if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
            ((MTextView) findViewById(R.id.pickUpAddressVTxt)).setText(generalFunc.retrieveLangLBl("Video Consultation", "LBL_VIDEO_CONSULT_AT_YOUR_LOC"));
            ((MTextView) findViewById(R.id.pickUpAddressHTxt)).setVisibility(View.GONE);
            ((ImageView) findViewById(R.id.videoImg)).setVisibility(View.VISIBLE);
            ((ImageView) findViewById(R.id.loactionImg)).setVisibility(View.GONE);
        }

        if (eType.equals("Deliver")) {

            (findViewById(R.id.viewDeliveryDetailsArea)).setVisibility(View.VISIBLE);
            (findViewById(R.id.addressArea)).setVisibility(View.GONE);
            (findViewById(R.id.sourceLocCardArea)).setVisibility(View.VISIBLE);
            ((MTextView) findViewById(R.id.viewSingleDeliveryTitleTxt)).setVisibility(View.VISIBLE);
            ((MTextView) findViewById(R.id.viewSingleDeliveryDetails)).setVisibility(View.VISIBLE);
            ((MTextView) findViewById(R.id.viewDeliveryDetails)).setVisibility(View.GONE);
            ((MTextView) findViewById(R.id.viewSingleDeliveryDetails)).setText(generalFunc.retrieveLangLBl("", "LBL_RECEIVER_NAME") + ": " + generalFunc.getJsonValue("vReceiverName", tripData) + "\n\n" + generalFunc.retrieveLangLBl("", "LBL_RECEIVER_LOCATION") + ": " + generalFunc.getJsonValue("tDaddress", tripData) + "\n\n" + generalFunc.retrieveLangLBl("", "LBL_PACKAGE_TYPE_TXT") + ": " + generalFunc.getJsonValue("PackageType", tripData) + "\n\n" + generalFunc.retrieveLangLBl("", "LBL_PACKAGE_DETAILS") + ": " + generalFunc.getJsonValue("tPackageDetails", tripData));
        } else {
            dropOffVTxt.setText(generalFunc.getJsonValue("tDaddress", tripData));
        }

        if (Utils.checkText(generalFunc.getJsonValue("tDaddress", tripData))) {
            dropOffVTxt.setText(generalFunc.getJsonValue("tDaddress", tripData));
            (findViewById(R.id.destarea)).setVisibility(View.VISIBLE);
            (findViewById(R.id.aboveLine)).setVisibility(View.VISIBLE);
        } else {
            if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
                (findViewById(R.id.addressArea)).setVisibility(View.GONE);
                (findViewById(R.id.sourceLocCardArea)).setVisibility(View.VISIBLE);
            } else {
                (findViewById(R.id.addressArea)).setVisibility(View.VISIBLE);
                (findViewById(R.id.sourceLocCardArea)).setVisibility(View.GONE);
            }
        }

        if (Utils.checkText(generalFunc.getJsonValue("eIsInterCity", tripData)) && generalFunc.getJsonValue("eIsInterCity", tripData).equalsIgnoreCase("Yes") &&
                Utils.checkText(generalFunc.getJsonValue("eRoundTrip", tripData)) && generalFunc.getJsonValue("eRoundTrip", tripData).equalsIgnoreCase("Yes")) {

            dropOffVTxt.setText(generalFunc.getJsonValue("tDropAddress", tripData));
            interCityDestAddressTxt.setText(generalFunc.getJsonValue("tReturnAddress", tripData));


            squareImgView.setVisibility(View.VISIBLE);
            dividerView.setVisibility(View.VISIBLE);
            interCityDropOffLayout.setVisibility(View.VISIBLE);
            dropToLocation.setPadding(0, 0, 0, getResources().getDimensionPixelSize(R.dimen._10sdp));
            imagemarkerdest2.setVisibility(View.GONE);

            tripdateVTxt.setVisibility(View.GONE);
            interCityReachedDateTime.setText(generalFunc.getJsonValue("tReachedDateTimeFormatted", tripData));
            interCityEndDateTime.setText(generalFunc.getJsonValue("tEndDateFormatted", tripData));

            interCityDateTime.setVisibility(View.VISIBLE);
            interCityReachedDateTime.setVisibility(View.VISIBLE);
            interCityEndDateTime.setVisibility(View.VISIBLE);
        }


        if (!generalFunc.getJsonValue("fTipPrice", tripData).equals("0") && !generalFunc.getJsonValue("fTipPrice", tripData).equals("0.0") && !generalFunc.getJsonValue("fTipPrice", tripData).equals("0.00") && !generalFunc.getJsonValue("fTipPrice", tripData).equals("")) {
            tipArea.setVisibility(View.VISIBLE);
            tipamtTxt.setText(generalFunc.getJsonValue("fTipPrice", tripData));

        } else {
            tipArea.setVisibility(View.GONE);
        }

        cartypeTxt.setText(generalFunc.getJsonValue("vServiceDetailTitle", tripData));

        String trip_status_str = generalFunc.getJsonValue("iActive", tripData);

        isRatingDone = generalFunc.getJsonValue("is_rating", tripData);

        if (isRatingDone.equalsIgnoreCase("No") && trip_status_str.contains("Finished")) {
            findViewById(R.id.rateDriverArea).setVisibility(View.VISIBLE);
            findViewById(R.id.rateCardDriverArea).setVisibility(View.VISIBLE);
        } else {
            findViewById(R.id.rateDriverArea).setVisibility(View.GONE);
            findViewById(R.id.rateCardDriverArea).setVisibility(View.GONE);
        }

        if (trip_status_str.contains("Canceled")) {
            findViewById(R.id.ratingview).setVisibility(View.GONE);

            String cancelLable = "";
            String cancelableReason = generalFunc.getJsonValue("vCancelReason", tripData);

            if (generalFunc.getJsonValue("eCancelledBy", tripData).equalsIgnoreCase("DRIVER")) {

                if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_PREFIX_DELIVERY_CANCEL_DRIVER_TXT");
                } else if (eType.equals(Utils.CabGeneralType_UberX)) {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_PREFIX_JOB_CANCEL_PROVIDER_TXT");
                } else {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_PREFIX_TRIP_CANCEL_DRIVER_TXT");
                }

            } else if (generalFunc.getJsonValue("eCancelledBy", tripData).equalsIgnoreCase("ADMIN")) {

                if (eType.equals(Utils.CabGeneralType_UberX)) {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_CANCELED_JOB_ADMIN");
                } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_ADMIN_CANCELED_DELIVERY_TXT");
                } else {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_ADMIN_CANCELED_TRIP_TXT");
                }
            } else {
                if (eType.equals(Utils.CabGeneralType_UberX)) {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_CANCELED_JOB");
                } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_CANCELED_DELIVERY_TXT");
                } else {
                    cancelLable = generalFunc.retrieveLangLBl("", "LBL_CANCELED_TRIP_TXT");
                }
            }


            (findViewById(R.id.cancelReasonArea)).setVisibility(View.VISIBLE);
            (findViewById(R.id.tripStatusArea)).setVisibility(View.GONE);
            ((MTextView) findViewById(R.id.vReasonHTxt)).setText(cancelLable);
            ((MTextView) findViewById(R.id.vReasonVTxt)).setText(cancelableReason);

            if (!generalFunc.getJsonValue("tDaddress", tripData).equals("")) {
                (findViewById(R.id.destarea)).setVisibility(View.VISIBLE);
                (findViewById(R.id.aboveLine)).setVisibility(View.VISIBLE);
            }

        } else if (trip_status_str.contains("Finished")) {

            String finishLable = "";
            if (eType.equals(Utils.CabGeneralType_UberX)) {
                finishLable = generalFunc.retrieveLangLBl("", "LBL_FINISHED_JOB_TXT");
            } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                finishLable = generalFunc.retrieveLangLBl("", "LBL_FINISHED_DELIVERY_TXT");
            } else {
                finishLable = generalFunc.retrieveLangLBl("", "LBL_FINISHED_TRIP_TXT");
            }

            ((MTextView) findViewById(R.id.tripStatusTxt)).setText(generalFunc.retrieveLangLBl("", finishLable));

            if (!generalFunc.getJsonValue("tDaddress", tripData).equals("")) {
                (findViewById(R.id.destarea)).setVisibility(View.VISIBLE);
                (findViewById(R.id.aboveLine)).setVisibility(View.VISIBLE);
            }

            // subTitleTxt.setVisibility(View.VISIBLE);
            receiptImgView.setVisibility(View.VISIBLE);
        } else {
            ((MTextView) findViewById(R.id.tripStatusTxt)).setText(trip_status_str);

        }

        if (generalFunc.getJsonValue("vTripPaymentMode", tripData).equals("Cash")) {
            ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.drawable.ic_cash_payment_pro);
            ((MTextView) findViewById(R.id.paymentTypeTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CASH_PAYMENT_TXT"));
        } else {
            ((MTextView) findViewById(R.id.paymentTypeTxt)).setText(generalFunc.retrieveLangLBl("Card Payment", "LBL_CARD_PAYMENT"));
            ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.mipmap.ic_card_new);

        }

        if (generalFunc.getJsonValue("ePayWallet", tripData).equals("Yes")) {
            ((MTextView) findViewById(R.id.paymentTypeTxt)).setText(generalFunc.retrieveLangLBl("Paid By Wallet", "LBL_PAID_VIA_WALLET"));
            ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.drawable.ic_menu_wallet);
        }


        if (generalFunc.getJsonValue("vTripPaymentMode", tripData).equalsIgnoreCase("Organization")) {
            ((MTextView) findViewById(R.id.paymentTypeTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_PAYMENT_BY_TXT") + " " + generalFunc.getJsonValue("OrganizationName", tripData));
            ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.drawable.ic_business_pay);
            ((ImageView) findViewById(R.id.paymentTypeImgeView)).setColorFilter(getResources().getColor(R.color.appThemeColor_1), PorterDuff.Mode.SRC_IN);
        }

        if (generalFunc.getJsonValue("eCancelled", tripData).equals("Yes")) {

            String cancelledLable = "";
            String cancelableReason = generalFunc.getJsonValue("vCancelReason", tripData);

            if (generalFunc.getJsonValue("eCancelledBy", tripData).equalsIgnoreCase("DRIVER")) {

                if (eType.equals(Utils.CabGeneralType_UberX)) {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_PREFIX_JOB_CANCEL_PROVIDER_TXT") + " " + cancelableReason;
                } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_PREFIX_DELIVERY_CANCEL_DRIVER_TXT") + " " + cancelableReason;
                } else {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_PREFIX_TRIP_CANCEL_DRIVER_TXT") + " " + cancelableReason;
                }

            } else if (generalFunc.getJsonValue("eCancelledBy", tripData).equalsIgnoreCase("Admin")) {

                if (eType.equals(Utils.CabGeneralType_UberX)) {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_CANCELED_JOB_ADMIN") + " " + cancelableReason;
                } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_ADMIN_CANCELED_DELIVERY_TXT") + " " + cancelableReason;
                } else {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_ADMIN_CANCELED_TRIP_TXT") + " " + cancelableReason;
                }

            } else {

                if (eType.equals(Utils.CabGeneralType_UberX)) {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_CANCELED_JOB");
                } else if (eType.equals("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_CANCELED_DELIVERY_TXT");
                } else {
                    cancelledLable = generalFunc.retrieveLangLBl("", "LBL_CANCELED_TRIP_TXT");
                }
            }

            ((MTextView) findViewById(R.id.tripStatusTxt)).setText(cancelledLable);
        }
        ((MTextView) findViewById(R.id.Rating)).setText(generalFunc.getJsonValue("TripRating", tripData));
        if (generalFunc.getJsonValue("TripRating", tripData).equalsIgnoreCase("") || generalFunc.getJsonValue("TripRating", tripData).equalsIgnoreCase("0")) {
            ((MTextView) findViewById(R.id.Rating)).setVisibility(View.GONE);
            ((ImageView) findViewById(R.id.ratingImg)).setVisibility(View.GONE);
        }

        if (eType.equalsIgnoreCase("UberX") && generalFunc.getJsonValueStr("SERVICE_PROVIDER_FLOW", obj_userProfile).equalsIgnoreCase("Provider")) {
            viewReqServicesArea.setVisibility(View.VISIBLE);
        }

        if (eType.equalsIgnoreCase("UberX") || generalFunc.getJsonValue("eFareType", tripData).equalsIgnoreCase("Fixed")) {
            findViewById(R.id.service_area).setVisibility(View.GONE);
            findViewById(R.id.serviceHTxt).setVisibility(View.GONE);
            findViewById(R.id.photoArea).setVisibility(View.VISIBLE);

            ((MTextView) findViewById(R.id.beforeImgHTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_BEFORE_SERVICE"));
            ((MTextView) findViewById(R.id.afterImgHTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_AFTER_SERVICE"));

            if (!TextUtils.isEmpty(generalFunc.getJsonValue("vBeforeImage", tripData))) {
                findViewById(R.id.beforeServiceArea).setVisibility(View.VISIBLE);
                before_serviceImg_url = generalFunc.getJsonValue("vBeforeImage", tripData);

                String vBeforeImage = Utils.getResizeImgURL(getActContext(), before_serviceImg_url, getResources().getDimensionPixelSize(R.dimen.before_after_img_size), getResources().getDimensionPixelSize(R.dimen.before_after_img_size));

                displayPic(vBeforeImage, (ImageView) findViewById(R.id.iv_before_img), "before");

                findViewById(R.id.iv_before_img).setOnClickListener(v -> (new ActUtils(getActContext())).openURL(before_serviceImg_url));
            } else {
                findViewById(R.id.beforeServiceArea).setVisibility(View.GONE);
            }

            if (!TextUtils.isEmpty(generalFunc.getJsonValue("vAfterImage", tripData))) {
                findViewById(R.id.afterServiceArea).setVisibility(View.VISIBLE);
                after_serviceImg_url = generalFunc.getJsonValue("vAfterImage", tripData);

                String vAfterImage = Utils.getResizeImgURL(getActContext(), after_serviceImg_url, getResources().getDimensionPixelSize(R.dimen.before_after_img_size), getResources().getDimensionPixelSize(R.dimen.before_after_img_size));
                displayPic(vAfterImage, (ImageView) findViewById(R.id.iv_after_img), "after");

                findViewById(R.id.iv_after_img).setOnClickListener(v -> (new ActUtils(getActContext())).openURL(after_serviceImg_url));
            } else {
                findViewById(R.id.afterServiceArea).setVisibility(View.GONE);
            }

            if (TextUtils.isEmpty(generalFunc.getJsonValue("vBeforeImage", tripData)) && TextUtils.isEmpty(generalFunc.getJsonValue("vAfterImage", tripData))) {

                findViewById(R.id.photoArea).setVisibility(View.GONE);

            }
            pickUpVTxt.setText(generalFunc.getJsonValue("tSaddress", tripData));

        } else {
            if (!generalFunc.getJsonValue("tDaddress", tripData).equals("")) {
                (findViewById(R.id.destarea)).setVisibility(View.VISIBLE);
                (findViewById(R.id.aboveLine)).setVisibility(View.VISIBLE);
            }
            findViewById(R.id.service_area).setVisibility(View.GONE);
            findViewById(R.id.serviceHTxt).setVisibility(View.GONE);
            findViewById(R.id.photoArea).setVisibility(View.GONE);
        }

        /*Show Multi Delivery Details*/
        if (eType.equals(Utils.eType_Multi_Delivery)) {

            (findViewById(R.id.addressArea)).setVisibility(View.GONE);
            (findViewById(R.id.sourceLocCardArea)).setVisibility(View.VISIBLE);
            (findViewById(R.id.viewDeliveryDetailsArea)).setVisibility(View.VISIBLE);

            addToClickHandler((findViewById(R.id.viewDeliveryDetailsArea)));
        }

        boolean FareDetailsArrNew = generalFunc.isJSONkeyAvail("HistoryFareDetailsNewArr", tripData);

        JSONArray FareDetailsArrNewObj = null;
        if (FareDetailsArrNew == true) {
            FareDetailsArrNewObj = generalFunc.getJsonArray("HistoryFareDetailsNewArr", tripData);
        }
        if (FareDetailsArrNewObj != null) {
            addFareDetailLayout(FareDetailsArrNewObj);
        }
    }

    /*Start of Multi Delivery Data*/

    public void showSignatureImage(String Name, String image_url, boolean isSender) {
        signatureImageDialog = new Dialog(getActContext(), R.style.Theme_Dialog);
        signatureImageDialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        signatureImageDialog.getWindow().setBackgroundDrawable(new ColorDrawable(android.graphics.Color.TRANSPARENT));

        signatureImageDialog.setContentView(R.layout.multi_show_sign_design);

        final ProgressBar LoadingProgressBar = ((ProgressBar) signatureImageDialog.findViewById(R.id.LoadingProgressBar));

        ((MTextView) signatureImageDialog.findViewById(R.id.nameTxt)).setText(" " + Name);

        if (isSender) {
            ((MTextView) signatureImageDialog.findViewById(R.id.passengerDTxt)).setText(generalFunc.retrieveLangLBl("Sender Signature", "LBL_SENDER_SIGN"));
            ((MTextView) signatureImageDialog.findViewById(R.id.nameTxt)).setVisibility(View.GONE);

        } else {
            ((MTextView) signatureImageDialog.findViewById(R.id.passengerDTxt)).setText(generalFunc.retrieveLangLBl("Receiver Signature", "LBL_RECEIVER_SIGN"));
            ((MTextView) signatureImageDialog.findViewById(R.id.nameTxt)).setVisibility(View.VISIBLE);

        }

        if (Utils.checkText(image_url)) {

            new LoadImage.builder(LoadImage.bind(image_url), ((ImageView) signatureImageDialog.findViewById(R.id.passengerImgView))).setPicassoListener(new LoadImage.PicassoListener() {
                @Override
                public void onSuccess() {
                    LoadingProgressBar.setVisibility(View.GONE);
                    ((ImageView) signatureImageDialog.findViewById(R.id.passengerImgView)).setVisibility(View.VISIBLE);
                }

                @Override
                public void onError() {
                    LoadingProgressBar.setVisibility(View.VISIBLE);
                    ((ImageView) signatureImageDialog.findViewById(R.id.passengerImgView)).setVisibility(View.GONE);
                }
            }).build();


        } else {
            LoadingProgressBar.setVisibility(View.VISIBLE);
            ((ImageView) signatureImageDialog.findViewById(R.id.passengerImgView)).setVisibility(View.GONE);

        }
        (signatureImageDialog.findViewById(R.id.cancelArea)).setOnClickListener(view -> {

            if (signatureImageDialog != null) {
                signatureImageDialog.dismiss();
            }
        });

        signatureImageDialog.setCancelable(false);
        signatureImageDialog.setCanceledOnTouchOutside(false);
        LayoutDirection.setLayoutDirection(signatureImageDialog);
        signatureImageDialog.show();

    }

    /*End of Multi Delivery Data*/

    public void displayPic(String image_url, ImageView view, final String imgType) {

        new LoadImage.builder(LoadImage.bind(image_url), view).setPlaceholderImagePath(R.mipmap.ic_no_icon).setPicassoListener(new LoadImage.PicassoListener() {
            @Override
            public void onSuccess() {
                if (imgType.equalsIgnoreCase("before")) {
                    findViewById(R.id.before_loading).setVisibility(View.GONE);
                    findViewById(R.id.iv_before_img).setVisibility(View.VISIBLE);
                } else if (imgType.equalsIgnoreCase("after")) {
                    findViewById(R.id.after_loading).setVisibility(View.GONE);
                    findViewById(R.id.iv_after_img).setVisibility(View.VISIBLE);
                }
            }

            @Override
            public void onError() {
                if (imgType.equalsIgnoreCase("before")) {
                    findViewById(R.id.before_loading).setVisibility(View.VISIBLE);
                    findViewById(R.id.iv_before_img).setVisibility(View.GONE);
                } else if (imgType.equalsIgnoreCase("after")) {
                    findViewById(R.id.after_loading).setVisibility(View.VISIBLE);
                    findViewById(R.id.iv_after_img).setVisibility(View.GONE);

                }
            }
        }).build();


    }

    private void addFareDetailLayout(JSONArray jobjArray) {

        if (fareDetailDisplayArea.getChildCount() > 0) {
            fareDetailDisplayArea.removeAllViewsInLayout();
        }

        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                //addFareDetailRow(jobject.names().getString(0), jobject.get(jobject.names().getString(0)).toString(), (jobjArray.length() - 1) == i ? true : false);


                String rName = jobject.names().getString(0);
                String rValue = jobject.get(rName).toString();
                fareDetailDisplayArea.addView(MyUtils.addFareDetailRow(getActContext(), generalFunc, rName, rValue, (jobjArray.length() - 1) == i));
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

    }

    private void addFareDetailRow(String row_name, String row_value, boolean isLast) {
        View convertView = null;
        if (row_name.equalsIgnoreCase("eDisplaySeperator")) {
            convertView = new View(getActContext());
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, Utils.dipToPixels(getActContext(), 1));
            params.setMargins(0, 0, 0, (int) getResources().getDimension(R.dimen._5sdp));
            convertView.setBackgroundColor(Color.parseColor("#dedede"));
            convertView.setLayoutParams(params);
        } else {
            LayoutInflater infalInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = infalInflater.inflate(R.layout.design_fare_deatil_row, null);

            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
            params.setMargins(0, (int) getResources().getDimension(R.dimen._10sdp), 0, isLast ? (int) getResources().getDimension(R.dimen._10sdp) : 0);
            convertView.setLayoutParams(params);

            MTextView titleHTxt = (MTextView) convertView.findViewById(R.id.titleHTxt);
            MTextView titleVTxt = (MTextView) convertView.findViewById(R.id.titleVTxt);

            titleHTxt.setText(generalFunc.convertNumberWithRTL(row_name));
            titleVTxt.setText(generalFunc.convertNumberWithRTL(row_value));

            if (isLast) {
                // CALCULATE individual fare & show
                titleHTxt.setTextColor(getResources().getColor(R.color.black));
                titleHTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);

                titleHTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);
                titleVTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));

            }

            fareDetailDisplayArea.addView(convertView);
        }
    }


    public void sendReceipt() {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getReceipt");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iTripId", generalFunc.getJsonValue("iTripId", tripData));

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                generalFunc.showMessage(generalFunc.getCurrentView(HistoryDetailActivity.this), generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            } else {
                generalFunc.showError();
            }
        });

    }

    public Context getActContext() {
        return HistoryDetailActivity.this;
    }

    public void submitRating(boolean isCollectTip) {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "submitRating");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iGeneralUserId", generalFunc.getMemberId());
        parameters.put("tripID", generalFunc.getJsonValue("iTripId", tripData));
        parameters.put("rating", "" + ufxratingBar.getRating());
        parameters.put("message", Utils.getText(commentBox));
        parameters.put("UserType", Utils.app_type);
        if (favSwitch != null) {
            parameters.put("eFavDriver", favSwitch.isChecked() ? "Yes" : "No");
        }
        if (ShowSafetyRating.equalsIgnoreCase("Yes")) {
            parameters.put("safetyRating", "" + safetyRatingBar.getRating());
        }

        parameters.put("fAmount", tipAmount);
        if (isCollectTip) {
            parameters.put("isCollectTip", "Yes");
        } else {
            parameters.put("isCollectTip", "No");
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    String msg = "";
                    if (isCollectTip) {
                        msg = generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString));
                    } else {
                        msg = generalFunc.retrieveLangLBl("", "LBL_TRIP_RATING_FINISHED_TXT");
                    }

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(true);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        if (isCollectTip) {
                            getMemberBookings();
                        } else {
                            Intent returnIntent = new Intent();
                            setResult(Activity.RESULT_OK, returnIntent);
                            finish();
                        }

                    });
                    generateAlert.setContentMessage("", msg);
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                    generateAlert.setCancelable(false);


                } else {
                    if (generalFunc.getJsonValue("isCollectTip", responseString) != null && generalFunc.getJsonValue("isCollectTip", responseString).equalsIgnoreCase("Yes")) {
                        Bundle bn = new Bundle();
                        bn.putString("url", generalFunc.getJsonValue(Utils.message_str, responseString));
                        bn.putBoolean("handleResponse", true);
                        new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
                    } else {
                        resetRatingData();
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void resetRatingData() {
        commentBox.setText("");
        ((MTextView) findViewById(R.id.Rating)).setText("0");
        ((MTextView) findViewById(R.id.Rating)).setVisibility(View.GONE);
        ((ImageView) findViewById(R.id.ratingImg)).setVisibility(View.GONE);

    }

    @Override
    public void onBackPressed() {
        if (getIntent().getBooleanExtra("isRestart", false)) {
            MyApp.getInstance().restartWithGetDataApp();
        } else {
            super.onBackPressed();
        }
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        Bundle bn = new Bundle();

        int id = view.getId();
        if (id == submitBtnId) {
            if (addTipArea.getVisibility() == View.VISIBLE) {
                if (!Utils.checkText(tipAmountBox)) {
                    amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrecterrorwithdesign));
                    generalFunc.showMessage(amountArea, generalFunc.retrieveLangLBl("", "LBL_SELECT_TIP_AMOUNT_TXT"));
                    return;
                } else {
                    resetErrorTxt();
                }

                if (GeneralFunctions.parseDoubleValue(0, tipAmountBox.getText().toString()) > 0) {
                    resetErrorTxt();
                    setSelected(tipAmountOtherText, tipAmountAreaOther);
                    submitRating(true);

                } else {
                    amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrecterrorwithdesign));
                    generalFunc.showMessage(amountArea, generalFunc.retrieveLangLBl("", "LBL_ENTER_GRATER_ZERO_VALUE_TXT"));
                }
            } else {
                if (!Utils.checkText(tipAmount)) {
                    generalFunc.showMessage(amountArea, generalFunc.retrieveLangLBl("", "LBL_SELECT_TIP_AMOUNT_TXT"));
                    return;
                }
                submitRating(true);
            }
            return;
        }

        if (id == R.id.backImgView) {
            onBackPressed();
        } else if (id == R.id.receiptImgView) {
            sendReceipt();
        } else if (id == R.id.beforeServiceArea) {
            new ActUtils(getActContext()).openURL(before_serviceImg_url);
        } else if (id == R.id.afterServiceArea) {
            new ActUtils(getActContext()).openURL(after_serviceImg_url);
        } else if (id == R.id.viewReqServicesArea) {
            bn.putString("iTripId", generalFunc.getJsonValue("iTripId", tripData));
            new ActUtils(getActContext()).startActWithData(MoreServiceInfoActivity.class, bn);
        } else if (id == R.id.viewDeliveryDetailsArea) {
            bn.putString("iTripId", generalFunc.getJsonValue("iTripId", tripData));
            new ActUtils(getActContext()).startActWithData(ViewMultiDeliveryDetailsActivity.class, bn);
        } else if (id == R.id.helpTxt) {
            bn.putString("iTripId", generalFunc.getJsonValue("iTripId", tripData));
            new ActUtils(getActContext()).startActWithData(HelpMainCategory23Pro.class, bn);
        } else if (id == R.id.signArea) {//new ActUtils(getActContext()).startActWithData(UberXSelectServiceActivity.class, bundle);
            showSignatureImage(generalFunc.getJsonValue("vName", tripData) + " " + generalFunc.getJsonValue("vLastName", tripData), senderImage, true);
        } else if (id == R.id.lineArea) {
            showSafetyRatingArea(false);
        } else if (id == R.id.hScrollView) {
            if (!ufxratingBar.isFocusable()) {
                lineArea.performClick();
            }
        } else if (id == R.id.tipAmountArea1) {
            setSelected(tipAmountText1, tipAmountArea1);
        } else if (id == R.id.tipAmountArea2) {
            setSelected(tipAmountText2, tipAmountArea2);
        } else if (id == R.id.tipAmountArea3) {
            setSelected(tipAmountText3, tipAmountArea3);
        } else if (id == R.id.tipAmountAreaOther) {
            setSelected(tipAmountOtherText, tipAmountAreaOther);
        } else if (id == R.id.tipDescHintTitleText || id == R.id.iv_tip_help) {
            showTipInfoDialog(getActContext().getResources().getDrawable(R.drawable.ic_save_money), "LBL_TIP_TXT");
        }

        if (id == rateBtnId) {
            if (((SimpleRatingBar) findViewById(R.id.ufxratingBar)).getRating() <= 0.0) {
                generalFunc.showMessage(generalFunc.getCurrentView(HistoryDetailActivity.this), generalFunc.retrieveLangLBl("", "LBL_ERROR_RATING_DIALOG_TXT"));
                return;
            }
            if (safetyRatingArea != null && safetyRatingArea.getVisibility() == View.VISIBLE && safetyRatingBar.getRating() < 0.5) {
                generalFunc.showMessage(generalFunc.getCurrentView(HistoryDetailActivity.this), generalFunc.retrieveLangLBl("", "LBL_ERROR_SAFETY_RATING_DIALOG_TXT"));
                return;
            }
            submitRating(false);
        }
    }

    private void showTipInfoDialog(Drawable drawable, String LBL_DATA) {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.design_tip_help, null);
        builder.setView(dialogView);

        final ImageView iamage_source = (ImageView) dialogView.findViewById(R.id.iamage_source);
        if (drawable != null) {
            iamage_source.setImageDrawable(drawable);
        }
        final MTextView okTxt = (MTextView) dialogView.findViewById(R.id.okTxt);
        final MTextView titileTxt = (MTextView) dialogView.findViewById(R.id.titileTxt);
        final MTextView msgTxt = (MTextView) dialogView.findViewById(R.id.msgTxt);
        titileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TIP_TITLE_TXT"));
        okTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        msgTxt.setText("" + generalFunc.retrieveLangLBl("", LBL_DATA));
        msgTxt.setMovementMethod(new ScrollingMovementMethod());
        okTxt.setOnClickListener(v -> giveTipAlertDialog.dismiss());
        giveTipAlertDialog = builder.create();
        giveTipAlertDialog.setCancelable(true);
        LayoutDirection.setLayoutDirection(giveTipAlertDialog);
        giveTipAlertDialog.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
        giveTipAlertDialog.show();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK && requestCode == WEBVIEWPAYMENT) {
            getMemberBookings();
        }
    }


}
