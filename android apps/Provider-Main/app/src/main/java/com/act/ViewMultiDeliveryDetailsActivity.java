package com.act;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.res.Resources;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.RectF;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.os.Parcelable;
import android.os.PersistableBundle;
import android.text.Editable;
import android.text.InputType;
import android.text.TextWatcher;
import android.util.AttributeSet;
import android.util.Log;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.inputmethod.EditorInfo;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.Toolbar;
import androidx.recyclerview.widget.DefaultItemAnimator;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.ViewMultiDeliveryDetailRecyclerAdapter;
import com.fontanalyzer.SystemFont;
import com.general.PermissionHandler;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.MyScrollView;
import com.general.files.UploadProfileImage;
import com.buddyverse.providers.R;
import com.model.ChatMsgHandler;
import com.model.Delivery_Data;
import com.model.Trip_Status;
import com.mukesh.OtpView;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.ErrorView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;

/**
 * Created by Admin on 03-11-2017.
 */

public class ViewMultiDeliveryDetailsActivity extends ParentActivity implements ViewMultiDeliveryDetailRecyclerAdapter.OnItemClickList {

    private RecyclerView deliveryDetailSummuryRecyclerView;
    private LinearLayout signatureArea, mainArea, buttonArea, totalfareTitleTxtLayout;
    private LinearLayout verificationCodeArea;
    private LinearLayout mainSignCodeArea;
    private MTextView submitCodeBtn;
    private MaterialEditText verificationCodeBox;
    private MTextView submitBtn, cancelBtn, clearBtn, signatureTxt;
    private ViewMultiDeliveryDetailRecyclerAdapter deliveryDetailSummaryAdapter;

    private MTextView paymentDetailsTitleTxt, paymentTypeTitleTxt, payByTitleTxt, totalfareTitleTxt, senderDetailsTitleTxt, phoneTitleTxt;
    private MTextView titleTxt;
    private MTextView paymentTypeTxt, payByTxt, totalfareTxt;
    private MTextView senderNameValTxt, senderPhoneValTxt;
    private SelectableRoundedImageView userProfileImgView;
    private ImageView backImgView;

    private ProgressBar loading;
    private ErrorView errorView;

    ArrayList<Trip_Status> recipientDetailList = new ArrayList<>();
    String data_message;
    String last_trip_data = "";
    String riderImage = "";
    String iUserId = "";

    String vImage = "";
    String vName = "";

    private String DELIVERY_VERIFICATION_METHOD;

    //Signature
    private boolean isSignatureView = false;

    signature mSignature;
    Bitmap bitmap;
    private LinearLayout mContent;
    private LinearLayout mView;

    private static final String IMAGE_DIRECTORY_NAME = "Temp";
    private boolean noSign = false;

    MyScrollView scrollView;

    androidx.appcompat.app.AlertDialog collectPaymentFailedDialog = null;
    private Parcelable recyclerViewState;
    private boolean isIndividualFare = false;
    HashMap<String, String> data_trip;
    Dialog alert_showFare_detail;
    JSONObject last_trip_fare_data;

    String filePath = "";

    Toolbar toolbar;
    MTextView callTxt, message_text;
    private Dialog signatureImageDialog;
    private LinearLayout subMainArea;
    private Dialog dialog_verify_via_otp;
    boolean isOtpVerified = false;
    boolean isOtpVerificationDenied = false;

    int activeScrollPos = 0;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_multi_delivery_details);
        if (getIntent().hasExtra("TRIP_DATA")) {
            this.data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
            vName = data_trip.get("PName");
        }


        init();
        setLables();
        setView();
        getTripDeliveryLocations();

        String OPEN_CHAT = generalFunc.retrieveValue(ChatMsgHandler.OPEN_CHAT);
        if (Utils.checkText(OPEN_CHAT)) {
            JSONObject OPEN_CHAT_DATA_OBJ = generalFunc.getJsonObject(OPEN_CHAT);
            if (OPEN_CHAT_DATA_OBJ != null) {
                ChatMsgHandler.performAction(OPEN_CHAT_DATA_OBJ.toString());
            }
        }

    }

    private String getOutputMediaFilePath() {

        // External sdcard location
        File mediaStorageDir;
        if (android.os.Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            mediaStorageDir = new File(getExternalFilesDir(Environment.DIRECTORY_PICTURES), IMAGE_DIRECTORY_NAME); // NOSONAR
        } else {
            mediaStorageDir = new File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES), IMAGE_DIRECTORY_NAME); // NOSONAR
        }
        // Create the storage directory if it does not exist
        if (!mediaStorageDir.exists()) {
            if (!mediaStorageDir.mkdirs()) {
                return null;
            }
        }

        // Create a media file name
        String timeStamp = new SimpleDateFormat("yyyyMMdd_HHmmss", MyUtils.getLocale()).format(new Date());

        return mediaStorageDir.getPath() + File.separator + "IMG_" + timeStamp + ".jpg";
    }


    @SuppressLint("SetTextI18n")
    private void setData(String sender_message, JSONObject responseString) {
        data_message = sender_message;

        senderNameValTxt.setText(generalFunc.getJsonValue("vName", sender_message));

        String vTripPaymentMode = generalFunc.getJsonValue("vTripPaymentMode", sender_message);
        String payType = Utils.checkText(vTripPaymentMode) ? vTripPaymentMode : generalFunc.getJsonValue("ePayType", sender_message);
        paymentTypeTxt.setText(payType);

        payByTxt.setText("" + generalFunc.getJsonValue("PaymentPerson", responseString));
        totalfareTxt.setText("" + generalFunc.getJsonValue("DriverPaymentAmount", responseString));

        if (generalFunc.isJSONkeyAvail("FareDetailsNewArr", last_trip_fare_data.toString())) {
            addToClickHandler(totalfareTitleTxtLayout);
        }

        String ePaymentBy = generalFunc.getJsonValueStr("ePaymentBy", responseString);
        if (ePaymentBy.equalsIgnoreCase("Individual")) {
            isIndividualFare = true;
            ((MTextView) findViewById(R.id.indifareTxt)).setText("" + generalFunc.getJsonValue("Fare_Payable", responseString));
            ((MTextView) findViewById(R.id.indifareTitleTxt)).setText("" + generalFunc.retrieveLangLBl("Payable amount", "LBL_MULTI_PAYBALE_AMOUNT") + ":");
            ((LinearLayout) findViewById(R.id.totalFareArea)).setBackgroundColor(getActContext().getResources().getColor(R.color.appThemeColor_bg_parent_1));
            findViewById(R.id.indiFareArea).setVisibility(View.VISIBLE);
        }
        totalfareTxt.setText("" + generalFunc.getJsonValue("DriverPaymentAmount", responseString));
        senderPhoneValTxt.setText("+" + generalFunc.getJsonValue("vCode", data_message) + " " + generalFunc.getJsonValue("vMobile", sender_message));
        riderImage = generalFunc.getJsonValue("vImage", sender_message);
        iUserId = generalFunc.getJsonValue("iUserId", sender_message);
        vName = generalFunc.getJsonValue("vName", sender_message);
        String image_url = CommonUtilities.SERVER_URL_PHOTOS + "upload/Passenger/" + generalFunc.getJsonValue("iUserId", sender_message) + "/"
                + riderImage;

        vImage = image_url;
        new LoadImage.builder(LoadImage.bind(image_url), userProfileImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

    }


    @SuppressLint("SetTextI18n")
    private void setLables() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Delivery Details", "LBL_DELIVERY_DETAILS"));
        paymentDetailsTitleTxt.setText(generalFunc.retrieveLangLBl("PAYMENT DETAIL", "LBL_PAYMENT_HEADER_TXT"));
        paymentTypeTitleTxt.setText(generalFunc.retrieveLangLBl("Payment Type", "LBL_PAYMENT_TYPE_TXT") + ":");
        payByTitleTxt.setText(generalFunc.retrieveLangLBl("Pay By", "LBL_MULTI_PAY_BY_TXT") + ":");
        totalfareTitleTxt.setText(generalFunc.retrieveLangLBl("Total Fare", "LBL_TOTAL_TXT") + ":");
        senderDetailsTitleTxt.setText(generalFunc.retrieveLangLBl("Sender Details", "LBL_MULTI_SENDER_DETAILS_TXT"));
        phoneTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PHONE"));
        callTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_TXT"));
        message_text.setText(generalFunc.retrieveLangLBl("", "LBL_MESSAGE_TXT"));

        clearBtn.setText(generalFunc.retrieveLangLBl("Clear", "LBL_MULTI_CLEAR_TXT"));
        submitBtn.setText(generalFunc.retrieveLangLBl("Submit", "LBL_BTN_SUBMIT_TXT"));
        submitCodeBtn.setText(generalFunc.retrieveLangLBl("Submit", "LBL_BTN_SUBMIT_TXT"));
        cancelBtn.setText(generalFunc.retrieveLangLBl("Cancel", "LBL_BTN_CANCEL_TXT"));
        signatureTxt.setText(generalFunc.retrieveLangLBl("Signature", "LBL_SIGN_ABOVE"));

        String contentMsg = generalFunc.retrieveLangLBl("Please enter the confirmation code.", "LBL_MULTI_VERIFICATION_CODE_MSG_TXT");
        ((MTextView) (findViewById(R.id.contentMsgTxt))).setText(contentMsg);
        verificationCodeBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_CONFIRMATION_CODE"), generalFunc.retrieveLangLBl("", "LBL_CONFIRMATION_CODE"));
        verificationCodeBox.setImeOptions(EditorInfo.IME_ACTION_DONE);
        verificationCodeBox.setInputType(InputType.TYPE_CLASS_NUMBER);

    }

    public Context getActContext() {
        return ViewMultiDeliveryDetailsActivity.this;
    }

    private void init() {

        toolbar = findViewById(R.id.toolbar);
        toolbar.setBackgroundColor(getResources().getColor(R.color.mdtp_transparent_full));
        scrollView = findViewById(R.id.mainScroll);
        deliveryDetailSummuryRecyclerView = (RecyclerView) findViewById(R.id.deliveryDetailSummuryRecyclerView);

        verificationCodeArea = (LinearLayout) findViewById(R.id.verificationCodeArea);
        mainSignCodeArea = (LinearLayout) findViewById(R.id.mainSignCodeArea);
        submitCodeBtn = (MTextView) findViewById(R.id.submitCodeBtn);
        verificationCodeBox = (MaterialEditText) findViewById(R.id.editBox);

        signatureArea = (LinearLayout) findViewById(R.id.signatureArea);
        totalfareTitleTxtLayout = (LinearLayout) findViewById(R.id.totalfareTitleTxtLayout);
        buttonArea = (LinearLayout) findViewById(R.id.buttonArea);
        mainArea = (LinearLayout) findViewById(R.id.mainArea);
        submitBtn = (MTextView) findViewById(R.id.submitBtn);
        cancelBtn = (MTextView) findViewById(R.id.cancelBtn);
        clearBtn = (MTextView) findViewById(R.id.clearBtn);
        signatureTxt = (MTextView) findViewById(R.id.signatureTxt);
        callTxt = (MTextView) findViewById(R.id.callTxt);
        message_text = (MTextView) findViewById(R.id.message_text);
        subMainArea = (LinearLayout) findViewById(R.id.subMainArea);
        RelativeLayout senderDetailArea = (RelativeLayout) findViewById(R.id.senderDetailArea);
        LinearLayout chatArea = (LinearLayout) findViewById(R.id.chatArea);
        LinearLayout callArea = (LinearLayout) findViewById(R.id.callArea);
        LinearLayout callMsgArea = (LinearLayout) findViewById(R.id.callMsgArea);
        paymentDetailsTitleTxt = (MTextView) findViewById(R.id.paymentDetailsTitleTxt);
        paymentTypeTitleTxt = (MTextView) findViewById(R.id.paymentTypeTitleTxt);
        paymentTypeTxt = (MTextView) findViewById(R.id.paymentTypeTxt);
        payByTitleTxt = (MTextView) findViewById(R.id.payByTitleTxt);
        payByTxt = (MTextView) findViewById(R.id.payByTxt);
        totalfareTitleTxt = (MTextView) findViewById(R.id.totalfareTitleTxt);
        totalfareTxt = (MTextView) findViewById(R.id.totalfareTxt);
        senderDetailsTitleTxt = (MTextView) findViewById(R.id.senderDetailsTitleTxt);
        senderNameValTxt = (MTextView) findViewById(R.id.senderNameValTxt);
        phoneTitleTxt = (MTextView) findViewById(R.id.phoneTitleTxt);
        senderPhoneValTxt = (MTextView) findViewById(R.id.senderPhoneValTxt);
        userProfileImgView = (SelectableRoundedImageView) findViewById(R.id.userProfileImgView);
        loading = (ProgressBar) findViewById(R.id.loading);
        errorView = (ErrorView) findViewById(R.id.errorView);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);

        if (getIntent().hasExtra("Status") && getIntent().getStringExtra("Status").equalsIgnoreCase("cabRequestScreen")) {
            senderDetailArea.setVisibility(View.GONE);
            findViewById(R.id.DetailsContainer).setVisibility(View.GONE);
        } else if (getIntent().hasExtra("Status") && getIntent().getStringExtra("Status").equalsIgnoreCase("showHistoryScreen")) {
            senderDetailArea.setVisibility(View.VISIBLE);
            findViewById(R.id.DetailsContainer).setVisibility(View.GONE);
            callMsgArea.setVisibility(View.GONE);
        } else {
            senderDetailArea.setVisibility(View.VISIBLE);
        }
        addToClickHandler(chatArea);
        addToClickHandler(callArea);

        if (getIntent().hasExtra("CheckFor")) {
            backImgView.setVisibility(View.GONE);
        }


        addToClickHandler(backImgView);
        addToClickHandler(cancelBtn);
        addToClickHandler(clearBtn);
        addToClickHandler(submitBtn);
        addToClickHandler(verificationCodeBox);
        addToClickHandler(submitCodeBtn);

        // make rounded corner

        int backColor = getActContext().getResources().getColor(R.color.appThemeColor_1);
        int backColor2 = getActContext().getResources().getColor(R.color.mdtp_transparent_full);
        int strokeColor2 = getActContext().getResources().getColor(R.color.white);
        int cornorRadius = Utils.dipToPixels(getActContext(), 5);
        int strokeWidth = Utils.dipToPixels(getActContext(), 1);
        new CreateRoundedView(backColor, cornorRadius, strokeWidth, backColor, submitCodeBtn);
        new CreateRoundedView(backColor2, cornorRadius, strokeWidth, strokeColor2, chatArea);
        new CreateRoundedView(backColor2, cornorRadius, strokeWidth, strokeColor2, callArea);

    }


    public void getTripDeliveryLocations() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (deliveryDetailSummuryRecyclerView.getVisibility() == View.VISIBLE) {
            deliveryDetailSummuryRecyclerView.setVisibility(View.GONE);
        }
        if (loading.getVisibility() != View.VISIBLE) {
            loading.setVisibility(View.VISIBLE);
            subMainArea.setVisibility(View.GONE);
        }
        recipientDetailList.clear();
        deliveryDetailSummaryAdapter.notifyDataSetChanged();

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getTripDeliveryDetails");
        parameters.put("iCabBookingId", "");

        String iCabBookingId = getIntent().hasExtra("iCabBookingId") ? getIntent().getStringExtra("iCabBookingId") : "";
        if (Utils.checkText(iCabBookingId)) {
            parameters.put("iCabBookingId", iCabBookingId);
        }

        String iCabRequestId = getIntent().hasExtra("iCabRequestId") ? getIntent().getStringExtra("iCabRequestId") : "";
        if (Utils.checkText(iCabRequestId)) {
            parameters.put("iCabRequestId", iCabRequestId);
        }
        parameters.put("iTripId", getIntent().getStringExtra("TripId"));
        parameters.put("userType", Utils.userType);
        parameters.put("iDriverId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {
                    JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                    if (responseStringObj != null && !responseStringObj.toString().equals("")) {

                        closeLoader();
                        String msg_str = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                            if (Utils.checkText(msg_str)) {
                                JSONObject jobject = generalFunc.getJsonObject("MemberDetails", msg_str);
                                DELIVERY_VERIFICATION_METHOD = generalFunc.getJsonValueStr("DELIVERY_VERIFICATION_METHOD", responseStringObj);
                                last_trip_data = generalFunc.getJsonValueStr("TripDetails", responseStringObj);
                                last_trip_fare_data = responseStringObj;

                                if (jobject != null) {
                                    setData(jobject.toString(), responseStringObj);
                                }


                                JSONArray deliveries = generalFunc.getJsonArray("Deliveries", msg_str);
                                if (deliveries != null) {

                                    String LBL_RECIPIENT = "", LBL_Status = "", LBL_CANCELLED = "", LBL_CANCELED_TRIP_TXT = "", LBL_FINISHED_TXT = "", LBL_MULTI_AMOUNT_COLLECT_TXT = "", LBL_PICK_UP_INS = "", LBL_DELIVERY_INS = "", LBL_PACKAGE_DETAILS = "", LBL_CALL_TXT = "", LBL_VIEW_SIGN_TXT = "", LBL_MESSAGE_ACTIVE_TRIP = "", LBL_MULTI_RESPONSIBLE_FOR_PAYMENT_TXT = "";

                                    if (deliveries.length() > 0) {
                                        LBL_RECIPIENT = generalFunc.retrieveLangLBl("", "LBL_RECIPIENT");
                                        LBL_Status = generalFunc.retrieveLangLBl("", "LBL_Status");
                                        LBL_CANCELLED = generalFunc.retrieveLangLBl("", "LBL_CANCELLED");
                                        LBL_FINISHED_TXT = generalFunc.retrieveLangLBl("", "LBL_FINISHED_TXT");
                                        LBL_MULTI_AMOUNT_COLLECT_TXT = generalFunc.retrieveLangLBl("", "LBL_MULTI_AMOUNT_COLLECT_TXT");
                                        LBL_PICK_UP_INS = generalFunc.retrieveLangLBl("", "LBL_PICK_UP_INS");
                                        LBL_DELIVERY_INS = generalFunc.retrieveLangLBl("", "LBL_DELIVERY_INS");
                                        LBL_PACKAGE_DETAILS = generalFunc.retrieveLangLBl("", "LBL_PACKAGE_DETAILS");
                                        LBL_CALL_TXT = generalFunc.retrieveLangLBl("", "LBL_CALL_TXT");
                                        LBL_VIEW_SIGN_TXT = generalFunc.retrieveLangLBl("", "LBL_VIEW_SIGN_TXT");
                                        LBL_MESSAGE_ACTIVE_TRIP = generalFunc.retrieveLangLBl("", "LBL_MESSAGE_ACTIVE_TRIP");
                                        LBL_MULTI_RESPONSIBLE_FOR_PAYMENT_TXT = generalFunc.retrieveLangLBl("Responsible for payment", "LBL_MULTI_RESPONSIBLE_FOR_PAYMENT_TXT");
                                    }


                                    for (int i = 0; i < deliveries.length(); i++) {
                                        Trip_Status recipientDetailMap1 = new Trip_Status();
                                        recipientDetailMap1.setePaymentBy(generalFunc.getJsonValueStr("ePaymentBy", responseStringObj));
                                        recipientDetailMap1.setFare_Payable(generalFunc.getJsonValueStr("Fare_Payable", responseStringObj));

                                        JSONArray deliveriesArray = generalFunc.getJsonArray(deliveries, i);

                                        if (deliveriesArray != null && deliveriesArray.length() > 0) {


                                            ArrayList<Delivery_Data> subrecipientDetailList = new ArrayList<>();

                                            for (int j = 0; j < deliveriesArray.length(); j++) {

                                                JSONObject jobject1 = generalFunc.getJsonObject(deliveriesArray, j);
                                                Delivery_Data recipientDetailMap = new Delivery_Data();

                                                String vValue = generalFunc.getJsonValueStr("vValue", jobject1);
                                                String vFieldName = generalFunc.getJsonValueStr("vFieldName", jobject1);


                                                recipientDetailMap.setvValue(vValue);

                                                if (vFieldName.equalsIgnoreCase("Recepient Name") || (generalFunc.getJsonValueStr("iDeliveryFieldId", jobject1).equalsIgnoreCase("2"))) {
                                                    recipientDetailMap1.setRecepientName(vValue);
                                                } else if (vFieldName.equalsIgnoreCase("Mobile Number") || (generalFunc.getJsonValueStr("iDeliveryFieldId", jobject1).equalsIgnoreCase("3"))) {
                                                    recipientDetailMap1.setRecepientNum(vValue);
                                                    recipientDetailMap1.setRecepientMaskNum(generalFunc.getJsonValueStr("vMaskValue", jobject1));
                                                } else if (vFieldName.equalsIgnoreCase("Address")) {
                                                    recipientDetailMap1.setePaymentByReceiver(generalFunc.getJsonValueStr("ePaymentByReceiver", jobject1));
                                                    recipientDetailMap1.setRecepientAddress(GeneralFunctions.fromHtml(generalFunc.getJsonValue("tDaddress", jobject1.toString())).toString());
                                                    recipientDetailMap.setiTripDeliveryLocationId(GeneralFunctions.fromHtml(generalFunc.getJsonValue("iTripDeliveryLocationId", jobject1.toString())).toString());

                                                    recipientDetailMap.setvValue(generalFunc.getJsonValueStr("tDaddress", jobject1));
                                                    recipientDetailMap1.setReceipent_Signature(generalFunc.getJsonValueStr("Receipent_Signature", jobject1));

                                                    recipientDetailMap1.setiTripDeliveryLocationId(generalFunc.getJsonValueStr("iTripDeliveryLocationId", jobject1));

                                                    recipientDetailMap1.setiActive(generalFunc.getJsonValueStr("iActive", jobject1));

                                                    recipientDetailMap1.setisActiveDelivery(generalFunc.getJsonValueStr("isActiveDelivery", jobject1));
                                                    if (generalFunc.getJsonValueStr("isActiveDelivery", jobject1).equalsIgnoreCase("Yes")) {
                                                        activeScrollPos = i;
                                                    }
                                                    recipientDetailMap1.setBGColor(generalFunc.getJsonValueStr("BG_color", jobject1));
                                                    recipientDetailMap1.setTEXTColor(generalFunc.getJsonValueStr("TEXT_color", jobject1));

                                                }

                                                recipientDetailMap.setvFieldName(vFieldName);

                                                recipientDetailMap.setiDeliveryFieldId(generalFunc.getJsonValueStr("iDeliveryFieldId", jobject1));

                                                recipientDetailMap.settSaddress(generalFunc.getJsonValueStr("tSaddress", jobject1));

                                                recipientDetailMap.settStartLat(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("tStartLat", jobject1)));

                                                recipientDetailMap.settStartLong(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("tStartLong", jobject1)));


                                                recipientDetailMap.settDaddress(generalFunc.getJsonValueStr("tDaddress", jobject1));


                                                recipientDetailMap.settDestLat(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("tEndLat", jobject1)));

                                                recipientDetailMap.settDestLong(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("tEndLong", jobject1)));

                                                recipientDetailMap.setePaymentByReceiver(generalFunc.getJsonValueStr("ePaymentByReceiver", jobject1));
                                                recipientDetailMap.setShowDetails(false);
                                                if (!vFieldName.equalsIgnoreCase("Address") && (!vFieldName.equalsIgnoreCase("Mobile Number") && !(generalFunc.getJsonValueStr("iDeliveryFieldId", jobject1).equalsIgnoreCase("3"))) && (!vFieldName.equalsIgnoreCase("Recepient Name") && !(generalFunc.getJsonValueStr("iDeliveryFieldId", jobject1).equalsIgnoreCase("2"))) /*&& Utils.checkText(generalFunc.getJsonValue("vValue", jobject1))*/) {

                                                    recipientDetailMap.setShowDetails(true);
                                                    subrecipientDetailList.add(recipientDetailMap);

                                                }
                                            }

                                            String status = getIntent().hasExtra("Status") ? getIntent().getStringExtra("Status") : "";
                                            if (status.equalsIgnoreCase("activeTrip")) {
                                                recipientDetailMap1.setShowUpcomingLocArea("Yes");
                                            } else {
                                                recipientDetailMap1.setShowUpcomingLocArea("No");
                                            }
                                            if (status.equalsIgnoreCase("cabRequestScreen")) {
                                                recipientDetailMap1.setShowMobile("No");
                                            } else {
                                                recipientDetailMap1.setShowMobile("Yes");
                                            }

                                            recipientDetailMap1.setLBL_RECIPIENT(LBL_RECIPIENT);
                                            recipientDetailMap1.setLBL_MULTI_AMOUNT_COLLECT_TXT(LBL_MULTI_AMOUNT_COLLECT_TXT);
                                            recipientDetailMap1.setLBL_Status(LBL_Status);
                                            recipientDetailMap1.setLBL_CANCELED_TRIP_TXT(LBL_CANCELLED);
                                            recipientDetailMap1.setLBL_FINISHED_TRIP_TXT(LBL_FINISHED_TXT);

                                            recipientDetailMap1.setLBL_PACKAGE_DETAILS(LBL_PICK_UP_INS);
                                            recipientDetailMap1.setLBL_DELIVERY_INS(LBL_DELIVERY_INS);
                                            recipientDetailMap1.setLBL_PACKAGE_DETAILS(LBL_PACKAGE_DETAILS);
                                            recipientDetailMap1.setLBL_CALL_TXT(LBL_CALL_TXT);
                                            recipientDetailMap1.setLBL_MESSAGE_ACTIVE_TRIP(LBL_MESSAGE_ACTIVE_TRIP);

                                            recipientDetailMap1.setLBL_RESPONSIBLE_FOR_PAYMENT_TXT(LBL_MULTI_RESPONSIBLE_FOR_PAYMENT_TXT);
                                            recipientDetailMap1.setLBL_VIEW_SIGN_TXT(LBL_VIEW_SIGN_TXT);

                                            recipientDetailMap1.setListOfDeliveryItems(subrecipientDetailList);
                                            recipientDetailList.add(recipientDetailMap1);
                                        }

                                    }

                                }

                            }

                            if (isIndividualFare) {
                                findViewById(R.id.indiFareArea).setVisibility(View.VISIBLE);
                            }


                            if (getIntent().hasExtra("CheckFor") && getIntent().getStringExtra("CheckFor").equals("Sender")) {
                                enableSignatureView();
                            } else if ((getIntent().hasExtra("CheckFor") && DELIVERY_VERIFICATION_METHOD.equalsIgnoreCase("Signature"))) {
                                enableSignatureView();

                            } else if (getIntent().hasExtra("CheckFor") && !DELIVERY_VERIFICATION_METHOD.equalsIgnoreCase("Signature")) {
                                enableConfirmationView();

                            } else {
                                resetView();
                            }

                            deliveryDetailSummaryAdapter.notifyDataSetChanged();

                        } else {
                            generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("Error", "LBL_ERROR_TXT"),
                                    generalFunc.retrieveLangLBl("", msg_str));
                            deliveryDetailSummaryAdapter.notifyDataSetChanged();

                        }
                    } else {
                        generateErrorView();
                        deliveryDetailSummaryAdapter.notifyDataSetChanged();
                    }
                });

    }

    public void scrollToPosition() {
        if (!getIntent().hasExtra("CheckFor") && activeScrollPos != 0) {
            scrollView.postDelayed(() -> {
                float y = deliveryDetailSummuryRecyclerView.getChildAt(activeScrollPos).getY();
                if (activeScrollPos != recipientDetailList.size() - 1) {
                    scrollView.scrollTo(0, (int) y + Utils.dipToPixels(getActContext(), 65));
                } else {
                    scrollView.scrollTo(0, (int) y);
                }
            }, 300);
        }
    }

    private void resetView() {
        isSignatureView = false;
        signatureArea.setVisibility(View.GONE);
        buttonArea.setVisibility(View.GONE);
        verificationCodeArea.setVisibility(View.GONE);
        mainSignCodeArea.setVisibility(View.GONE);
        deliveryDetailSummuryRecyclerView.setVisibility(View.VISIBLE);
        findViewById(R.id.indiFareArea).setVisibility(View.GONE);
        setView();
    }

    @Override
    public void onSaveInstanceState(Bundle outState, PersistableBundle outPersistentState) {
        super.onSaveInstanceState(outState, outPersistentState);

        recyclerViewState = deliveryDetailSummuryRecyclerView.getLayoutManager().onSaveInstanceState();


    }

    @Override
    public void onRestoreInstanceState(Bundle savedInstanceState, PersistableBundle persistentState) {
        super.onRestoreInstanceState(savedInstanceState, persistentState);
        deliveryDetailSummuryRecyclerView.getLayoutManager().onRestoreInstanceState(recyclerViewState);

    }


    private void enableConfirmationView() {
        isSignatureView = false;
        signatureArea.setVisibility(View.GONE);
        buttonArea.setVisibility(View.GONE);
        verificationCodeArea.setVisibility(View.VISIBLE);
        mainSignCodeArea.setVisibility(View.VISIBLE);
        deliveryDetailSummuryRecyclerView.setVisibility(View.GONE);


        if (isIndividualFare) {
            findViewById(R.id.indiFareArea).setVisibility(View.VISIBLE);
        }

        titleTxt.setText(generalFunc.retrieveLangLBl("Booking Summary", "LBL_VERIFICATION_PAGE_HEADER"));

    }

    private void enableSignatureView() {
        isSignatureView = true;
        signatureArea.setVisibility(View.VISIBLE);
        buttonArea.setVisibility(View.VISIBLE);
        verificationCodeArea.setVisibility(View.GONE);
        mainSignCodeArea.setVisibility(View.VISIBLE);

        if (isIndividualFare) {
            findViewById(R.id.indiFareArea).setVisibility(View.VISIBLE);
        }

        deliveryDetailSummuryRecyclerView.setVisibility(View.GONE);
        assignSignatureView();
        titleTxt.setText(generalFunc.retrieveLangLBl("Booking Summary", "LBL_VERIFICATION_PAGE_HEADER"));


        mainArea.setOnTouchListener((view, event) -> {
            scrollView.setScrolling(true);
            return false;
        });


    }


    private void assignSignatureView() {
        mContent = (LinearLayout) findViewById(R.id.linearLayout);
        mSignature = new signature(getApplicationContext(), null);
        mSignature.setBackgroundColor(Color.WHITE);
        // Dynamically generating Layout through java code
        mContent.addView(mSignature, ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        mView = mContent;
    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getTripDeliveryLocations());
    }


    public void setView() {
        mainSignCodeArea.setVisibility(View.GONE);
        deliveryDetailSummuryRecyclerView.setVisibility(View.VISIBLE);
        deliveryDetailSummaryAdapter = new ViewMultiDeliveryDetailRecyclerAdapter(getActContext(), ViewMultiDeliveryDetailsActivity.this, recipientDetailList, generalFunc);
        deliveryDetailSummaryAdapter.isFromHistory(getIntent().hasExtra("Status") && getIntent().getStringExtra("Status").equalsIgnoreCase("showHistoryScreen"));
        deliveryDetailSummuryRecyclerView.setItemAnimator(new DefaultItemAnimator());
        deliveryDetailSummuryRecyclerView.setAdapter(deliveryDetailSummaryAdapter);
        deliveryDetailSummaryAdapter.notifyDataSetChanged();
        deliveryDetailSummaryAdapter.setOnItemClickList(this);
    }

    public void closeLoader() {
        if (loading.getVisibility() == View.VISIBLE) {
            loading.setVisibility(View.GONE);
            subMainArea.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public void onBackPressed() {

        if (backImgView.getVisibility() == View.VISIBLE) {
            super.onBackPressed();

        }
    }

    @Override
    public void onItemClick(String data, String type, int position) {
        MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                .setPhoneNumber(data)
                .setToMemberName(recipientDetailList.get(position).getRecepientName())
                .setMedia(CommunicationManager.MEDIA.DEFAULT)
                .build();
        CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, type.equalsIgnoreCase("msg") ? CommunicationManager.TYPE.CHAT : CommunicationManager.TYPE.OTHER);
    }

    @Override
    public void onItemClick(String type, int position) {
        showSignatureImage(generalFunc.retrieveLangLBl("", "LBL_RECIPIENT_NAME_HEADER_TXT") + " : " + recipientDetailList.get(position).getRecepientName(), recipientDetailList.get(position).getReceipent_Signature(), false);
    }

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


    public void onClick(View view) {
        int id = view.getId();
        if (id == R.id.backImgView) {
            ViewMultiDeliveryDetailsActivity.super.onBackPressed();
        } else if (id == R.id.callArea || id == R.id.chatArea) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(view.getId() == R.id.chatArea ? iUserId : data_trip.get("PassengerId"))
                    .setPhoneNumber(CommunicationManager.MEDIA_TYPE == CommunicationManager.MEDIA.DEFAULT ? Utils.getText(senderPhoneValTxt) : data_trip.get("vPhone_U"))
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(data_trip.get("PName"))
                    .setToMemberImage(riderImage)
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(getIntent().getStringExtra("TripId"))
                    .setBookingNo(data_trip.get("vRideNo"))
                    .build();

            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, view.getId() == R.id.chatArea ? CommunicationManager.TYPE.CHAT : CommunicationManager.TYPE.OTHER);
        } else if (id == R.id.clearBtn) {
            mSignature.clear();
            filePath = "";
            //assignSignatureView();
        } else if (id == R.id.submitBtn) {
            if (!noSign) {
                generalFunc.showMessage(mView, generalFunc.retrieveLangLBl("", "LBL_REQUIRED_SIGNATURE"));
                return;
            }
            String[] permission;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                    permission = new String[]{Manifest.permission.READ_MEDIA_IMAGES};
                } else {
                    permission = new String[]{Manifest.permission.READ_EXTERNAL_STORAGE};
                }
            } else {
                permission = new String[]{Manifest.permission.WRITE_EXTERNAL_STORAGE};
            }
            if (PermissionHandler.getInstance().checkAnyPermissions(generalFunc, true, permission, FileSelector.REQUEST_STORAGE)) {
                mView.setDrawingCacheEnabled(true);
                mSignature.save(mView);
            }
        } else if (id == R.id.cancelArea) {
            Log.v("log_tag", "Panel Canceled");
            // Calling the same class
            recreate();
        } else if (id == R.id.totalfareTitleTxtLayout) {
            if (alert_showFare_detail != null) {
                showFareDetails();
            } else {
                loadFareDetails();
            }
        } else if (id == R.id.submitCodeBtn) {
            if (!Utils.checkText(verificationCodeBox)) {
                verificationCodeBox.setError(generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD"));
                return;
            }

            if (data_trip != null && Utils.checkText(data_trip.get("vDeliveryConfirmCode")) && !Utils.getText(verificationCodeBox).equals(data_trip.get("vDeliveryConfirmCode"))) {

                verificationCodeBox.setError(generalFunc.retrieveLangLBl("Invalid code", "LBL_INVALID_DELIVERY_CONFIRM_CODE"));
                return;

            }
            String eAskCodeToUser = null;
            if (data_trip != null) {
                eAskCodeToUser = data_trip.get("eAskCodeToUser");
            }
            String CheckFor = getIntent().hasExtra("CheckFor") ? getIntent().getStringExtra("CheckFor") : "";

            if (CheckFor.equals("Sender") && Utils.checkText(eAskCodeToUser) && eAskCodeToUser.equalsIgnoreCase("Yes") && !isOtpVerified) { // NOSONAR
                if (isOtpVerificationDenied) {
                    isOtpVerificationDenied = false;
                    return;
                }
                openEnterOtpView();
            } else {
                confirmDeliveryStatus();
            }
        }
    }


    public void showFareDetails() {
        if (alert_showFare_detail != null) {
            alert_showFare_detail.show();
        }
    }

    public void loadFareDetails() {
        alert_showFare_detail = new Dialog(getActContext(), R.style.Theme_Dialog1);
        alert_showFare_detail.requestWindowFeature(Window.FEATURE_NO_TITLE);
        alert_showFare_detail.getWindow().setBackgroundDrawable(getResources().getDrawable(R.drawable.all_roundcurve_card));

        alert_showFare_detail.setContentView(R.layout.multi_design_fare_detail_cell);

        final MTextView cartypeTxt = (MTextView) alert_showFare_detail.findViewById(R.id.cartypeTxt);
        final MTextView titleTxt = (MTextView) alert_showFare_detail.findViewById(R.id.titleTxt);
        final LinearLayout fareDetailDisplayArea = (LinearLayout) alert_showFare_detail.findViewById(R.id.fareDetailDisplayArea);
        final MButton btn_type2 = ((MaterialRippleLayout) alert_showFare_detail.findViewById(R.id.btn_type2)).getChildView();

        addFareDetailLayout(cartypeTxt, fareDetailDisplayArea);

        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_OK"));
        titleTxt.setText(generalFunc.retrieveLangLBl("Fare Details", "LBL_FARE_DETAILS"));

        btn_type2.setOnClickListener(view -> alert_showFare_detail.dismiss());
        alert_showFare_detail.setCanceledOnTouchOutside(false);
        alert_showFare_detail.setCancelable(false);
        LayoutDirection.setLayoutDirection(alert_showFare_detail);
        if (alert_showFare_detail != null) {
            alert_showFare_detail.show();
        }
    }

    private void addFareDetailLayout(MTextView cartypeTxt, LinearLayout fareDetailDisplayArea) {

        if (fareDetailDisplayArea.getChildCount() > 0) {
            fareDetailDisplayArea.removeAllViewsInLayout();
        }


        cartypeTxt.setText(generalFunc.getJsonValueStr("carTypeName", last_trip_fare_data));

        boolean FareDetailsArrNew = generalFunc.isJSONkeyAvail("FareDetailsNewArr", last_trip_fare_data != null ? last_trip_fare_data.toString() : "");

        JSONArray FareDetailsArrNewObj = null;
        if (FareDetailsArrNew) {
            FareDetailsArrNewObj = generalFunc.getJsonArray("FareDetailsNewArr", last_trip_fare_data != null ? last_trip_fare_data.toString() : "");
        }

        if (FareDetailsArrNewObj != null) {
            for (int i = 0; i < FareDetailsArrNewObj.length(); i++) {
                JSONObject jobject = generalFunc.getJsonObject(FareDetailsArrNewObj, i);
                try {
                    String data = jobject.names().getString(0);
                    addFareDetailRow(fareDetailDisplayArea, data, jobject.get(data).toString(), (FareDetailsArrNewObj.length() - 1) == i ? true : false);

                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
        }

    }

    private void addFareDetailRow(LinearLayout fareDetailDisplayArea, String row_name, String row_value, boolean isLast) {
        View convertView = null;
        if (row_name.equalsIgnoreCase("eDisplaySeperator")) {
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

            convertView.setMinimumHeight(Utils.dipToPixels(getActContext(), 30));

            MTextView titleHTxt = (MTextView) convertView.findViewById(R.id.titleHTxt);
            MTextView titleVTxt = (MTextView) convertView.findViewById(R.id.titleVTxt);

            titleHTxt.setText(generalFunc.convertNumberWithRTL(row_name));
            titleVTxt.setText(generalFunc.convertNumberWithRTL(row_value));

            if (isLast) {
                titleHTxt.setTextColor(getResources().getColor(R.color.black));
                titleHTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);

                titleHTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);
                titleVTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));
            }


        }

        if (convertView != null)
            fareDetailDisplayArea.addView(convertView);
    }

    private void confirmDeliveryStatus() {

        if (isSignatureView) {

            HashMap<String, String> paramsList = new HashMap<String, String>() {{
                put("type", "ConfirmDelivery");
                put("iTripId", getIntent().getStringExtra("TripId"));
                put("UserType", Utils.userType);
                put("CheckFor", getIntent().getStringExtra("CheckFor"));
                put("vDeliveryConfirmCode", Utils.getText(verificationCodeBox));

                put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
                put("GeneralUserType", Utils.app_type);
                put("GeneralMemberId", generalFunc.getMemberId());
            }};

            new UploadProfileImage(ViewMultiDeliveryDetailsActivity.this, filePath, Utils.TempProfileImageName, paramsList, "Signature").execute();
        } else {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "ConfirmDelivery");
            parameters.put("iTripId", getIntent().getStringExtra("TripId"));
            parameters.put("UserType", Utils.userType);
            parameters.put("CheckFor", getIntent().getStringExtra("CheckFor"));
            parameters.put("vDeliveryConfirmCode", Utils.getText(verificationCodeBox));


            ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                    responseString -> tripResponse(responseString));


        }


    }

    public void handleImgUploadResponse(String responseString, String imageUploadedType) {

        if (responseString != null && !responseString.equals("")) {
            if (imageUploadedType.equalsIgnoreCase("Signature")) {
                tripResponse(responseString);
            }

        } else {
            generalFunc.showError();
        }
    }


    private void tripResponse(String responseString) {

        if (responseString != null && !responseString.equals("")) {

            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

            if (isDataAvail) {
                //MyApp.getInstance().restartWithGetDataApp(false);
                MyApp.getInstance().refreshView(this, responseString);
            } else {
                String msg_str = generalFunc.getJsonValue(Utils.message_str, responseString);
                if (msg_str.equals(Utils.GCM_FAILED_KEY) || msg_str.equals(Utils.APNS_FAILED_KEY) || msg_str.equals("LBL_SERVER_COMM_ERROR")) {
                    generalFunc.restartApp();
                } else {
                    String CheckFor = getIntent().hasExtra("CheckFor") ? getIntent().getStringExtra("CheckFor") : "";

                    if (CheckFor.equals("Sender")) {
                        buildPaymentCollectFailedMessage(generalFunc.retrieveLangLBl("",
                                generalFunc.getJsonValue(Utils.message_str, responseString)), "");

                    } else {
                        generalFunc.showGeneralMessage("",
                                generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }

            }
        } else {
            generalFunc.showError();
        }
    }


    public void buildPaymentCollectFailedMessage(String msg, final String from) {

        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext(), R.style.StackedAlertDialogStyle);
        builder.setTitle("");
        builder.setCancelable(false);

        builder.setMessage(msg);

        builder.setPositiveButton(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"), (dialog, which) -> {
            collectPaymentFailedDialog.dismiss();
            if (from.equalsIgnoreCase("collectCash")) {
                collectPayment("true");
            } else {
                String eAskCodeToUser = data_trip.get("eAskCodeToUser");
                String CheckFor = getIntent().hasExtra("CheckFor") ? getIntent().getStringExtra("CheckFor") : "";

                if (CheckFor.equals("Sender") && Utils.checkText(eAskCodeToUser) && eAskCodeToUser.equalsIgnoreCase("Yes") && !isOtpVerified) {
                    if (isOtpVerificationDenied) {
                        isOtpVerificationDenied = false;
                        return;
                    }
                    openEnterOtpView();
                } else {
                    confirmDeliveryStatus();
                }
            }
        });
        builder.setNegativeButton(generalFunc.retrieveLangLBl("Collect Cash", "LBL_COLLECT_CASH"), (dialog, which) -> {
            collectPaymentFailedDialog.dismiss();
            collectPayment("true");
        });

        collectPaymentFailedDialog = builder.create();

        collectPaymentFailedDialog.setOnShowListener(dialog -> {

            ((Button) ((AlertDialog) dialog).getButton(Dialog.BUTTON_POSITIVE)).setTypeface(SystemFont.FontStyle.BOLD.font);

            //Personalizamos
            Resources res = getActContext().getResources();
            int posBtnTxtColor = res.getColor(android.R.color.white);
            int posBtnBackColor = res.getColor(R.color.appThemeColor_1);

            //Buttons
            ((Button) ((AlertDialog) dialog).getButton(Dialog.BUTTON_POSITIVE)).setTextColor(posBtnTxtColor);
            ((Button) ((AlertDialog) dialog).getButton(Dialog.BUTTON_POSITIVE)).setBackgroundColor(posBtnBackColor);

            //Buttons
            Button negButton = ((AlertDialog) dialog).getButton(DialogInterface.BUTTON_NEGATIVE);

            ((Button) ((AlertDialog) dialog).getButton(Dialog.BUTTON_NEGATIVE)).setTextColor(posBtnTxtColor);
            ((Button) ((AlertDialog) dialog).getButton(Dialog.BUTTON_NEGATIVE)).setBackgroundColor(posBtnBackColor);

            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.WRAP_CONTENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT
            );
            params.setMargins(10, 0, 10, 0);

            negButton.setLayoutParams(params);

        });
        collectPaymentFailedDialog.setCancelable(false);
        collectPaymentFailedDialog.setCanceledOnTouchOutside(false);
        collectPaymentFailedDialog.show();
    }

    public void collectPayment(String isCollectCash) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CollectPayment");
        parameters.put("iTripId", getIntent().getStringExtra("TripId"));
        if (!isCollectCash.equals("")) {
            parameters.put("isCollectCash", isCollectCash);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                    if (responseStringObj != null) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj);

                        if (isDataAvail) {
                            // MyApp.getInstance().restartWithGetDataApp(false);
                            MyApp.getInstance().refreshView(this, responseString);
                        } else {
                            buildPaymentCollectFailedMessage(generalFunc.retrieveLangLBl("",
                                    generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)), "collectCash");

                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }

    private void openEnterOtpView() {
        if (dialog_verify_via_otp != null) {
            dialog_verify_via_otp.dismiss();
            dialog_verify_via_otp = null;
        }
        dialog_verify_via_otp = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        dialog_verify_via_otp.setContentView(R.layout.verify_with_otp_layout);
        MTextView titleTxt = (MTextView) dialog_verify_via_otp.findViewById(R.id.titleTxt);
        MTextView cancelTxt = (MTextView) dialog_verify_via_otp.findViewById(R.id.cancelTxt);
        MTextView verifyOtpNote = (MTextView) dialog_verify_via_otp.findViewById(R.id.verifyOtpNote);
        MTextView verifyOtpValidationNote = (MTextView) dialog_verify_via_otp.findViewById(R.id.verifyOtpValidationNote);
        LinearLayout OtpAddArea = (LinearLayout) dialog_verify_via_otp.findViewById(R.id.OtpAddArea);
        MaterialEditText otpBox = (MaterialEditText) dialog_verify_via_otp.findViewById(R.id.otpBox);
        OtpView otp_view = (OtpView) dialog_verify_via_otp.findViewById(R.id.otp_verify_view);
        MButton btn_type2 = ((MaterialRippleLayout) dialog_verify_via_otp.findViewById(R.id.btn_type2)).getChildView();

        if (generalFunc.isRTLmode()) {
            otp_view.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        }
        int vRandomCode = generalFunc.parseIntegerValue(4, data_trip.get("vRandomCode"));
        String LBL_OTP_INVALID_TXT = generalFunc.retrieveLangLBl("", "LBL_OTP_INVALID_TXT");
        if (vRandomCode <= 6) {
            OtpAddArea.setVisibility(View.VISIBLE);
            otpBox.setVisibility(View.GONE);
            verifyOtpValidationNote.setText(LBL_OTP_INVALID_TXT);
            otp_view.setItemCount(generalFunc.parseIntegerValue(4, String.valueOf(vRandomCode)));
        } else {
            otpBox.setBothText("", generalFunc.retrieveLangLBl("OTP", "LBL_ENTER_OTP_TITLE_TXT"));
            OtpAddArea.setVisibility(View.GONE);
            otpBox.setVisibility(View.VISIBLE);
            otpBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        }

        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_DONE"));

        titleTxt.setText(generalFunc.retrieveLangLBl("Verify OTP", "LBL_OTP_VERIFICATION_TITLE_TXT"));
        verifyOtpNote.setText(generalFunc.retrieveLangLBl("Ask user to provide you an OTP.", "LBL_OTP_VERIFICATION_DESCRIPTION_TXT"));
        btn_type2.setEnabled(false);
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            isOtpVerified = false;
            isOtpVerificationDenied = true;
            if (dialog_verify_via_otp != null) {
                dialog_verify_via_otp.dismiss();
                dialog_verify_via_otp = null;
            }
        });

        String vText = data_trip.get("vText");
        Logger.d("MD5_HASH", "Original  Values is ::" + vText);

        btn_type2.setOnClickListener(v -> {

            Utils.hideKeyboard(getActContext());

            if (OtpAddArea.getVisibility() == View.VISIBLE) {
                String finalCode = Utils.getText(otp_view);

                boolean isCorrectCOde = Utils.checkText(finalCode) &&
                        generalFunc.convertOtpToMD5(finalCode).equalsIgnoreCase(vText);
                boolean isCodeEntered = isCorrectCOde ? true : Utils.setErrorFields(otpBox, LBL_OTP_INVALID_TXT);

                otp_view.setLineColor(isCorrectCOde ? getActContext().getResources().getColor(R.color.appThemeColor_1) : getActContext().getResources().getColor(R.color.red));

                if (isCodeEntered) {
                    verifyOtpValidationNote.setVisibility(View.GONE);
                    if (dialog_verify_via_otp != null) {
                        dialog_verify_via_otp.dismiss();
                        dialog_verify_via_otp = null;
                    }

                    isOtpVerified = true;
                    confirmDeliveryStatus();
                } else {
                    verifyOtpValidationNote.setVisibility(View.VISIBLE);
                }
            } else {
                String finalCode = Utils.getText(otpBox);
                boolean isCorrectCOde = Utils.checkText(finalCode) &&
                        generalFunc.convertOtpToMD5(finalCode).equalsIgnoreCase(vText);
                boolean isCodeEntered = isCorrectCOde ? true : Utils.setErrorFields(otpBox, LBL_OTP_INVALID_TXT);

                otp_view.setLineColor(isCorrectCOde ? getActContext().getResources().getColor(R.color.appThemeColor_1) : getActContext().getResources().getColor(R.color.red));

                if (isCodeEntered) {

                    if (dialog_verify_via_otp != null) {
                        dialog_verify_via_otp.dismiss();
                        dialog_verify_via_otp = null;
                    }
                    isOtpVerified = true;
                    confirmDeliveryStatus();
                }
            }
        });
        otp_view.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {

            }

            @Override
            public void afterTextChanged(Editable s) {
                if (s.length() < otp_view.getItemCount()) {
                    btn_type2.setEnabled(false);
                    otp_view.setLineColor(getResources().getColor(R.color.gray));
                    verifyOtpValidationNote.setVisibility(View.GONE);
                }
            }
        });

        otpBox.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {

            }

            @Override
            public void afterTextChanged(Editable s) {
                if (s.length() < vRandomCode) {
                    btn_type2.setEnabled(false);
                } else {
                    btn_type2.setEnabled(true);
                }
            }
        });

        otp_view.setOtpCompletionListener(otp -> {
            verifyOtpValidationNote.setVisibility(View.GONE);
            otp_view.setLineColor(getResources().getColor(R.color.appThemeColor_1));
            btn_type2.setEnabled(true);
        });
        otp_view.setCursorVisible(true);
        dialog_verify_via_otp.setCanceledOnTouchOutside(false);
        Window window = dialog_verify_via_otp.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        dialog_verify_via_otp.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        LayoutDirection.setLayoutDirection(dialog_verify_via_otp);
        dialog_verify_via_otp.show();
    }

    // signature View
    public class signature extends View {

        private static final float STROKE_WIDTH = 5f;
        private static final float HALF_STROKE_WIDTH = STROKE_WIDTH / 2;
        private Paint paint = new Paint();
        private Path path = new Path();

        private float lastTouchX;
        private float lastTouchY;
        private final RectF dirtyRect = new RectF();
        private int width, height;

        public signature(Context context, AttributeSet attrs) {
            super(context, attrs);
            filePath = "";
            paint.setAntiAlias(true);
            paint.setColor(Color.BLACK);
            paint.setStyle(Paint.Style.STROKE);
            paint.setStrokeJoin(Paint.Join.ROUND);
            paint.setStrokeWidth(STROKE_WIDTH);
        }


        public void save(View v) {
            if (bitmap == null) {
                bitmap = Bitmap.createBitmap(mContent.getWidth(), mContent.getHeight(), Bitmap.Config.RGB_565);
            }
            Canvas canvas = new Canvas(bitmap);
            FileOutputStream mFileOutStream = null;
            try {
                // Output the file
                filePath = getOutputMediaFilePath();
                mFileOutStream = new FileOutputStream(filePath); // NOSONAR
                v.draw(canvas);
                // Convert the output file to Image such as .png
                bitmap.compress(Bitmap.CompressFormat.PNG, 90, mFileOutStream);
                mFileOutStream.flush();
                mFileOutStream.close();

                String eAskCodeToUser = data_trip.get("eAskCodeToUser");
                String CheckFor = getIntent().hasExtra("CheckFor") ? getIntent().getStringExtra("CheckFor") : "";

                if (CheckFor.equals("Sender") && Utils.checkText(eAskCodeToUser) && eAskCodeToUser.equalsIgnoreCase("Yes") && !isOtpVerified) {
                    if (isOtpVerificationDenied) {
                        isOtpVerificationDenied = false;
                        return;
                    }
                    openEnterOtpView();
                } else {
                    confirmDeliveryStatus();
                }

            } catch (Exception e) {
                Log.v("log_tag", e.toString());
            } finally {
                try {
                    if (mFileOutStream != null) {
                        mFileOutStream.close();
                    }
                } catch (IOException e) {
                }
            }
        }

        public void clear() {
            noSign = false;
            path.reset();

            onSizeChanged(width, height, width, height);
            filePath = "";
            invalidate();

            setDrawingCacheEnabled(false);
        }

        @Override
        protected void onSizeChanged(int w, int h, int oldw, int oldh) {
            super.onSizeChanged(w, h, oldw, oldh);
        }

        @Override
        protected void onDraw(Canvas canvas) {
            canvas.drawPath(path, paint);
        }

        @Override
        public boolean onTouchEvent(MotionEvent event) {
            float eventX = event.getX();
            float eventY = event.getY();

            scrollView.setScrolling(false);

            switch (event.getAction()) {
                case MotionEvent.ACTION_DOWN:
                    noSign = true;

                    path.moveTo(eventX, eventY);
                    lastTouchX = eventX;
                    lastTouchY = eventY;
                    return true;

                case MotionEvent.ACTION_MOVE:


                case MotionEvent.ACTION_UP:
                    noSign = true;
                    resetDirtyRect(eventX, eventY);
                    int historySize = event.getHistorySize();
                    for (int i = 0; i < historySize; i++) {
                        float historicalX = event.getHistoricalX(i);
                        float historicalY = event.getHistoricalY(i);
                        expandDirtyRect(historicalX, historicalY);
                        path.lineTo(historicalX, historicalY);
                    }
                    path.lineTo(eventX, eventY);
                    break;

                default:
                    scrollView.setScrolling(true);
                    debug("Ignored touch event: " + event.toString());
                    return false;
            }

            invalidate((int) (dirtyRect.left - HALF_STROKE_WIDTH),
                    (int) (dirtyRect.top - HALF_STROKE_WIDTH),
                    (int) (dirtyRect.right + HALF_STROKE_WIDTH),
                    (int) (dirtyRect.bottom + HALF_STROKE_WIDTH));

            lastTouchX = eventX;
            lastTouchY = eventY;

            return true;
        }

        private void debug(String string) {
            Log.v("log_tag", string);
        }

        private void expandDirtyRect(float historicalX, float historicalY) {
            if (historicalX < dirtyRect.left) {
                dirtyRect.left = historicalX;
            } else if (historicalX > dirtyRect.right) {
                dirtyRect.right = historicalX;
            }

            if (historicalY < dirtyRect.top) {
                dirtyRect.top = historicalY;
            } else if (historicalY > dirtyRect.bottom) {
                dirtyRect.bottom = historicalY;
            }
        }

        private void resetDirtyRect(float eventX, float eventY) {
            dirtyRect.left = Math.min(lastTouchX, eventX);
            dirtyRect.right = Math.max(lastTouchX, eventX);
            dirtyRect.top = Math.min(lastTouchY, eventY);
            dirtyRect.bottom = Math.max(lastTouchY, eventY);
        }
    }

    @Override
    public void onReqPermissionsResult() {
        submitBtn.performClick();
    }
}
