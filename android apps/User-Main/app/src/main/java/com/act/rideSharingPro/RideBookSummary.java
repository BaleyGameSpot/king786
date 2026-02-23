package com.act.rideSharingPro;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.act.ContactUsActivity;
import com.act.MyWalletActivity;
import com.act.PaymentWebviewActivity;
import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideBookSummaryBinding;
import com.model.ServiceModule;
import com.model.getProfilePaymentModel;
import com.model.profileDelegate;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Objects;

public class RideBookSummary extends ParentActivity implements profileDelegate {

    private ActivityRideBookSummaryBinding binding;
    private HashMap<String, String> myRideDataHashMap;
    private ServerTask currentExeTask;
    private MButton continueBtn;
    private boolean isProcessAPI = false;
    AlertDialog outstanding_dialog;
    String ShowAdjustTripBtn;
    String ShowPayNow;
    String ShowContactUsBtn;
    private static final int WEBVIEWPAYMENT = 001;

    MTextView payTypeTxt, organizationTxt;
    ImageView payImgView, errorImage;
    View showDropDownArea;
    LinearLayout payArea;
    String iAuthorizePaymentId = "";
    String isFareAuthorized = "";
    boolean isContectLessPrefSelected = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_book_summary);

        myRideDataHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("myRideDataHashMap");
        if (myRideDataHashMap == null) {
            return;
        }

        initialization();
        getBookSummaryList();
        managePaymentOptions();
        manageProfilePayment();
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_BOOKING_SUMMARY"));

        binding.requestReplayHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_REQUEST_REPLY_BY_TXT"));
        binding.totalHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_TOTAL_PRICE"));

        continueBtn = ((MaterialRippleLayout) binding.continueBtn).getChildView();
        continueBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CONTINUE"));
        continueBtn.setId(Utils.generateViewId());
        addToClickHandler(continueBtn);

        payTypeTxt = findViewById(R.id.payTypeTxt);
        organizationTxt = findViewById(R.id.organizationTxt);
        payImgView = findViewById(R.id.payImgView);
        errorImage = findViewById(R.id.errorImage);
        showDropDownArea = findViewById(R.id.showDropDownArea);
        payArea = (LinearLayout) findViewById(R.id.payArea);
        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
            payArea.setVisibility(View.GONE);
        }
        addToClickHandler(payArea);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getBookSummaryList() {
        binding.mainDetailArea.setVisibility(View.GONE);
        binding.loading.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "BookingSummary");

        parameters.put("iPublishedRideId", myRideDataHashMap.get("iPublishedRideId"));
        parameters.put("iBookNoOfSeats", myRideDataHashMap.get("selectedNoOfSeats"));

        parameters.put("iPublishedRideWayPointId", myRideDataHashMap.get("iPublishedRideWayPointId"));

        ApiHandler.execute(this, parameters, responseString -> {

            binding.loading.setVisibility(View.GONE);
            binding.mainDetailArea.setVisibility(View.VISIBLE);

            if (responseString != null && !responseString.isEmpty()) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                    binding.requestReplayTxt.setText(generalFunc.getJsonValue("tDisplayDateTime", message));
                    //binding.requestReplayTxt.setText(generalFunc.getJsonValue("dStartDate", message));
                    binding.fPriceTxt.setText(generalFunc.getJsonValue("TotalPrice", message));
                    binding.noOfPassengerText.setText(myRideDataHashMap.get("vNoOfPassengerText"));

                    JSONArray summaryArray = generalFunc.getJsonArray("Summary", message);
                    if (summaryArray != null) {

                        if (binding.summaryData.getChildCount() > 0) {
                            binding.summaryData.removeAllViewsInLayout();
                        }

                        for (int i = 0; i < summaryArray.length(); i++) {
                            JSONObject jobject = generalFunc.getJsonObject(summaryArray, i);
                            try {
                                String data = Objects.requireNonNull(jobject.names()).getString(0);

                                RideSharingUtils.addSummaryRow(this, generalFunc, binding.summaryData, data, jobject.get(data).toString(), false);
                            } catch (JSONException e) {
                                Logger.e("Exception", "::" + e.getMessage());
                            }
                        }
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)),
                            "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), buttonId -> onBackPressed());
                }
            } else {
                generalFunc.showError();

            }
        });
    }

    ActivityResultLauncher<Intent> webViewPaymentActivity = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {

                    if (data.hasExtra("iAuthorizePaymentId")) {
                        iAuthorizePaymentId = data.getStringExtra("iAuthorizePaymentId");
                        isFareAuthorized = "Yes";
                    } else {
                        isFareAuthorized = "No";
                    }
                    managePaymentOptions();
                }
            });

    private void createBookRide(String isFareAuthorized, String iAuthorizePaymentId) {

        if (binding.loading.getVisibility() == View.VISIBLE) {
            return;
        }
        binding.loading.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "bookRide");

        parameters.put("iPublishedRideId", myRideDataHashMap.get("iPublishedRideId"));
        parameters.put("iBookNoOfSeats", myRideDataHashMap.get("selectedNoOfSeats"));

        parameters.put("iPublishedRideWayPointId", myRideDataHashMap.get("iPublishedRideWayPointId"));

        parameters.put("isFareAuthorized", isFareAuthorized);
        parameters.put("iAuthorizePaymentId", iAuthorizePaymentId);

        if (currentExeTask != null) {
            currentExeTask.cancel(true);
            currentExeTask = null;
        }
        currentExeTask = ApiHandler.execute(this, parameters, responseString -> {

            isProcessAPI = false;
            currentExeTask = null;
            binding.loading.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    String WebviewPayment = generalFunc.getJsonValue("WebviewPayment", responseString);

                    if (WebviewPayment.equalsIgnoreCase("Yes")) {

                        String message1 = generalFunc.getJsonValue(Utils.message_str_one, responseString);

                        generalFunc.showGeneralMessage("", message1, "", generalFunc.retrieveLangLBl("", "LBL_OK"), buttonId -> {
                            if (buttonId == 1) {

                                Intent intent = new Intent(this, PaymentWebviewActivity.class);
                                Bundle bn = new Bundle();
                                bn.putString("url", message);
                                bn.putBoolean("handleResponse", true);
                                bn.putBoolean("isBack", false);
                                bn.putString("eType", "RideShare");
                                intent.putExtras(bn);
                                webViewPaymentActivity.launch(intent);
                            }
                        });


                    } else {
                        CustomDialog customDialog = new CustomDialog(this, generalFunc);
                        customDialog.setDetails(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)),
                                generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)),
                                generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_VIEW_PUBLISHED_RIDES_TXT"),
                                generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"),
                                false, R.drawable.ic_correct_2, false, 1, true);
                        customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
                        customDialog.setRoundedViewBorderColor(R.color.white);
                        customDialog.setImgStrokWidth(15);
                        customDialog.setBtnRadius(10);
                        customDialog.setIconTintColor(R.color.white);
                        customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
                        customDialog.setPositiveBtnTextColor(R.color.white);
                        customDialog.createDialog();
                        customDialog.setPositiveButtonClick(() -> {
                            if (ServiceModule.OnlyRideSharingPro) {
                                Bundle returnBundle = new Bundle();
                                returnBundle.putBoolean("isShowRideBooking", true);
                                new ActUtils(this).setOkResult(returnBundle);
                                finish();
                            } else {
                                Bundle bn = new Bundle();
                                bn.putBoolean("isRestartApp", true);
                                new ActUtils(this).startActWithData(RideMyList.class, bn);
                            }

                        });
                        customDialog.setNegativeButtonClick(() -> MyApp.getInstance().restartWithGetDataApp());
                        customDialog.show();
                    }

                } else {

                    String fOutStandingAmount = generalFunc.getJsonValue("fOutStandingAmount", responseString);
                    if (GeneralFunctions.parseDoubleValue(0.0, fOutStandingAmount) > 0) {
                        outstandingDialog(responseString);
                        return;
                    }

                    if (message.equalsIgnoreCase("LOW_WALLET_AMOUNT")) {

                        String walletMsg;
                        String low_balance_content_msg = generalFunc.getJsonValue("low_balance_content_msg", responseString);

                        if (low_balance_content_msg != null && !low_balance_content_msg.equalsIgnoreCase("")) {
                            walletMsg = low_balance_content_msg;
                        } else {
                            walletMsg = generalFunc.retrieveLangLBl("", "LBL_WALLET_LOW_AMOUNT_MSG_TXT");
                        }

                        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BALANCE"),
                                walletMsg, generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), generalFunc.retrieveLangLBl("", "LBL_ADD_NOW"), button_Id -> {
                                    if (button_Id == 1) {
                                        new ActUtils(this).startAct(MyWalletActivity.class);
                                    }
                                });
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                    }
                }
            } else {
                generalFunc.showError();

            }
        });
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();

        } else if (i == continueBtn.getId()) {

            if (isProcessAPI) {
                return;
            }
            isProcessAPI = true;
            createBookRide(isFareAuthorized, iAuthorizePaymentId);

        } else if (i == payArea.getId()) {
            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", obj_userProfile) + "&eType=RideShare";
            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();

            Intent intent = new Intent(this, PaymentWebviewActivity.class);
            Bundle bn = new Bundle();
            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            bn.putString("eType", "RideShare");
            intent.putExtras(bn);
            webViewPaymentActivity.launch(intent);
        }
    }

    private void managePaymentOptions() {
        getProfilePaymentModel.getProfilePayment(Utils.eSystem_Type, this, this, isContectLessPrefSelected, false);
    }

    public void manageProfilePayment() {
        payTypeTxt.setText(generalFunc.getJsonValue("PAYMENT_DISPLAY_LBL", getProfilePaymentModel.getProfileInfo()).toString());
        continueBtn.setEnabled(true);
        organizationTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_TXT"));
        payImgView.setVisibility(View.VISIBLE);
        errorImage.setVisibility(View.GONE);
        showDropDownArea.setVisibility(View.VISIBLE);

        if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("")) {
            payTypeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PAYMENT"));
            errorImage.setVisibility(View.VISIBLE);
            payImgView.setVisibility(View.GONE);
            showDropDownArea.setVisibility(View.GONE);
            continueBtn.setEnabled(false);
            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                continueBtn.setEnabled(true);
            }

        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("CASH")) {
            payImgView.setImageResource(R.drawable.ic_money_cash);
        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("CARD")) {
            payImgView.setImageResource(R.mipmap.ic_card_new);
        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("BUSINESS")) {
            payImgView.setImageResource(R.drawable.ic_business_pay);
        } else {
            payImgView.setImageResource(R.drawable.ic_menu_wallet);
        }
    }

    @Override
    public void notifyProfileInfoInfo() {
        manageProfilePayment();
    }

    private Context getActContext() {
        return RideBookSummary.this;
    }

    public void outstandingDialog(String data) {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
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
        adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in Your trip", "LBL_ADJUST_OUT_AMT_DELIVERY_TXT"));
        adjustSubTitleTxt.setText(generalFunc.retrieveLangLBl("Outstanding amount will be added in invoice total amount.", "LBL_OUTSTANDING_AMOUNT_ADDED_INVOICE_NOTE"));
        String outstanding_amt_pay_label = generalFunc.getJsonValue("outstanding_amt_pay_label", data);
        String outstanding_restriction_label_card = generalFunc.getJsonValue("outstanding_restriction_label_card", data);
        String outstanding_restriction_label_cash = generalFunc.getJsonValue("outstanding_restriction_label_cash", data);

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
            adjustarea.setVisibility(View.VISIBLE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
        } else {
            adjustarea.setVisibility(View.GONE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        }

        if (outstanding_amt_pay_label != null && !outstanding_amt_pay_label.isEmpty()) {
            adjustTripMessageTxt.setVisibility(View.VISIBLE);
            adjustTripMessageTxt.setText(outstanding_amt_pay_label);
        }

        final LinearLayout contactUsArea = dialogView.findViewById(R.id.contactUsArea);
        contactUsArea.setVisibility(View.GONE);
        ShowContactUsBtn = generalFunc.getJsonValueStr("ShowContactUsBtn", obj_userProfile);
        if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
            MTextView contactUsTxt = dialogView.findViewById(R.id.contactUsTxt);
            contactUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
            contactUsArea.setVisibility(View.VISIBLE);
            contactUsArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(ContactUsActivity.class));
        }

        cardArea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", obj_userProfile) + "&eType=" + "RideShare" + "&ePaymentType=ChargeOutstandingAmount";
            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();

            Intent intent = new Intent(this, PaymentWebviewActivity.class);
            Bundle bn = new Bundle();
            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            bn.putString("eType", "RideShare");
            intent.putExtras(bn);
            webViewPaymentActivity.launch(intent);
        });

        adjustarea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            //createBookRide(,"");
        });

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1)));
        btn_type2.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        btn_type2.setOnClickListener(v -> {
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
        Objects.requireNonNull(outstanding_dialog.getWindow()).setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        outstanding_dialog.show();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK && requestCode == WEBVIEWPAYMENT) {

        }
    }
}