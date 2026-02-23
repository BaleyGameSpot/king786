package com.act;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.text.InputType;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.SlideAnimationUtil;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityBiddingHistoryDetailBinding;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;

public class BiddingHistoryDetailActivity extends ParentActivity {


    String isRatingDone = "";
    MButton btn_type2;
    private int rateBtnId;
    ActivityBiddingHistoryDetailBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_bidding_history_detail);
        initViews(binding);
    }

    private void initViews(ActivityBiddingHistoryDetailBinding binding) {
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        binding.driverImgView.setScaleType(ImageView.ScaleType.CENTER_CROP);
        binding.driverImgView.setShapeAppearanceModel(binding.driverImgView.getShapeAppearanceModel()
                .toBuilder()
                .setAllCorners(CornerFamily.ROUNDED, getResources().getDimension(R.dimen._7sdp))
                .build());
        binding.locationHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_SERVICE_ADDRESS_TXT"));

        btn_type2 = ((MaterialRippleLayout) binding.btnType2).getChildView();
        rateBtnId = Utils.generateViewId();
        btn_type2.setId(rateBtnId);
        getDisplayFareBiddingService();

        binding.commentBox.setInputType(InputType.TYPE_TEXT_FLAG_IME_MULTI_LINE);
        binding.commentBox.setLines(5);
        binding.commentBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_WRITE_COMMENT_HINT_TXT"));
        binding.commentBox.setTextColor(getResources().getColor(R.color.mdtp_transparent_black));

        addToClickHandler(btn_type2);
        addToClickHandler(binding.viewReqServicesArea);
        addToClickHandler(binding.toolbarInclude.backImgView);
        addToClickHandler(binding.toolbarInclude.subTitleTxt);
        addToClickHandler(binding.helpTxt);
        addToClickHandler(binding.toolbarInclude.receiptImgView);
        binding.commentBox.setOnTouchListener((v, event) -> {
            binding.scrollContainer.requestDisallowInterceptTouchEvent(true);
            return false;
        });
    }


    private void getDisplayFareBiddingService() {
        if (binding.errorView.getVisibility() == View.VISIBLE) {
            binding.errorView.setVisibility(View.GONE);
        }
        if (binding.container.getVisibility() == View.VISIBLE) {
            binding.container.setVisibility(View.GONE);
            binding.paymentMainArea.setVisibility(View.GONE);
        }
        if (binding.loading.getVisibility() != View.VISIBLE) {
            binding.loading.setVisibility(View.VISIBLE);
        }


        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "displayFareBiddingService");
        parameters.put("memberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("memberType", Utils.app_type);
        if (getIntent().hasExtra("iBiddingPostId")) {
            parameters.put("iBiddingPostId", getIntent().getExtras().getString("iBiddingPostId"));
        }


        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {
                closeLoader();

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);


                    String vImage = generalFunc.getJsonValue("driverImage", message);
                    if (vImage == null || vImage.equals("") || vImage.equals("NONE")) {
                        (binding.driverImgView).setImageResource(R.mipmap.ic_no_pic_user);
                    } else {
                        new LoadImage.builder(LoadImage.bind(vImage), binding.driverImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
                    }

                    binding.nameDriverVTxt.setText(generalFunc.getJsonValue("driverName", message));
                    //binding.dateTxt.setText(/*": "+*/generalFunc.getDateFormatedType(generalFunc.getJsonValue("dBiddingDate", message), Utils.OriginalDateFormate, DateTimeUtils.DateFormat));
                    //binding.timeTxt.setText(/*": "+*/generalFunc.getDateFormatedType(generalFunc.getJsonValue("dBiddingDate", message), Utils.OriginalDateFormate, DateTimeUtils.TimeFormat));
                    binding.dateTxt.setText(generalFunc.getJsonValue("tDisplayDate", message));
                    binding.timeTxt.setText(generalFunc.getJsonValue("tDisplayTime", message));

                    binding.pickUpVTxt.setText(generalFunc.getJsonValue("tSaddress", message));


                    binding.cartypeTxt.setText(generalFunc.getJsonValue("vServiceDetailTitle", message));

                    JSONArray FareDetailsArrNewObj = null;
                    boolean FareDetailsArrNew = generalFunc.isJSONkeyAvail("FareDetailsNewArr", message);
                    if (FareDetailsArrNew) {
                        FareDetailsArrNewObj = generalFunc.getJsonArray("FareDetailsNewArr", message);
                    }
                    if (FareDetailsArrNewObj != null) {
                        addFareDetailLayout(FareDetailsArrNewObj);
                    }

                    isRatingDone = generalFunc.getJsonValue("is_rating", message);

                    if (isRatingDone.equalsIgnoreCase("No") && generalFunc.getJsonValue("vTaskStatus", message).equalsIgnoreCase("Finished")) {
                        binding.rateDriverArea.setVisibility(View.VISIBLE);
                        binding.rateCardDriverArea.setVisibility(View.VISIBLE);
                    } else {
                        binding.rateDriverArea.setVisibility(View.GONE);
                        binding.rateCardDriverArea.setVisibility(View.GONE);

                    }
                    binding.ratingTxt.setText(generalFunc.getJsonValue("driverAvgRating", message));

                    binding.rideNoVTxt.setText("#" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vBiddingPostNo", message)));
                    LinearLayout.LayoutParams txtParam = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
                    txtParam.setMargins(2, 10, 2, 0);
                    binding.rideNoVTxt.setLayoutParams(txtParam);
                    binding.rideNoHTxt.setLayoutParams(txtParam);

                    if (generalFunc.getJsonValue("vBiddingPaymentMode", message).equals("Cash")) {
                        binding.paymentTypeImgeView.setImageResource(R.drawable.ic_cash_payment);
                        binding.paymentTypeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CASH_PAYMENT_TXT"));
                    } else if (generalFunc.getJsonValue("vBiddingPaymentMode", message).equals("Wallet")) {
                        binding.paymentTypeImgeView.setImageResource(R.drawable.ic_menu_wallet);
                        binding.paymentTypeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PAID_VIA_WALLET"));
                    } else {
                        binding.paymentTypeTxt.setText(generalFunc.retrieveLangLBl("Card Payment", "LBL_CARD_PAYMENT"));
                        binding.paymentTypeImgeView.setImageResource(R.mipmap.ic_card_new);

                    }

                    if (generalFunc.getJsonValue("ePayWallet", message).equals("Yes")) {
                        binding.paymentTypeTxt.setText(generalFunc.retrieveLangLBl("Paid By Wallet", "LBL_PAID_VIA_WALLET"));
                        binding.paymentTypeImgeView.setImageResource(R.drawable.ic_menu_wallet);
                    }

                    if (generalFunc.getJsonValue("vTaskStatus", message).equalsIgnoreCase("Finished")) {
                        binding.toolbarInclude.receiptImgView.setVisibility(View.VISIBLE);
                    }
                    setLabels();

                    if (!generalFunc.getJsonValue("vTaskStatus", message).equalsIgnoreCase("Finished")) {

                        String cancelLable = "";
                        String cancelableReason = generalFunc.getJsonValue("vCancelReason", message);

                        if (generalFunc.getJsonValue("eCancelledBy", message).equalsIgnoreCase("DRIVER")) {
                            cancelLable = generalFunc.retrieveLangLBl("Task has been cancelled by the provider.", "LBL_PREFIX_JOB_CANCEL_PROVIDER_TXT");

                        } else {
                            cancelLable = generalFunc.retrieveLangLBl("You have cancelled this Task.", "LBL_CANCELED_TASK");
                        }


                        binding.cancelReasonArea.setVisibility(View.VISIBLE);
                        binding.tripStatusArea.setVisibility(View.GONE);
                        binding.vReasonHTxt.setText(cancelLable);
                        binding.vReasonVTxt.setText(cancelableReason);

                    }


                } else {
                    generateErrorView();
                }
            } else {
                generateErrorView();
            }
        });
    }

    public void closeLoader() {
        if (binding.loading.getVisibility() == View.VISIBLE) {
            binding.loading.setVisibility(View.GONE);
        }

        if (binding.container.getVisibility() == View.GONE) {
            binding.container.setVisibility(View.VISIBLE);
            binding.paymentMainArea.setVisibility(View.VISIBLE);
        }
    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (binding.errorView.getVisibility() != View.VISIBLE) {
            binding.errorView.setVisibility(View.VISIBLE);
        }

        if (binding.container.getVisibility() == View.VISIBLE) {
            binding.container.setVisibility(View.GONE);
            binding.paymentMainArea.setVisibility(View.GONE);
        }

        binding.errorView.setOnRetryListener(() -> getDisplayFareBiddingService());
    }


    String headerLable = "", noVal = "", driverhVal = "";

    public void setLabels() {

        binding.viewReqServicesTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_REQUESTED_SERVICES"));


        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("RECEIPT", "LBL_RECEIPT_HEADER_TXT"));
        binding.toolbarInclude.subTitleTxt.setText(generalFunc.retrieveLangLBl("GET RECEIPT", "LBL_GET_RECEIPT_TXT"));
        headerLable = generalFunc.retrieveLangLBl("", "LBL_THANKS_TXT");
        noVal = generalFunc.retrieveLangLBl("", "LBL_TASK_TXT");
        driverhVal = generalFunc.retrieveLangLBl("", "LBL_SERVICE_PROVIDER_TXT");


        binding.headerTxt.setText(generalFunc.retrieveLangLBl("", headerLable));


        binding.rideNoHTxt.setText(noVal);
        binding.chargesHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHARGES_TXT"));
        btn_type2.setText(generalFunc.retrieveLangLBl("Rate", "LBL_RATE_DRIVER_TXT"));
        binding.ufxratingDriverHTxt.setText(generalFunc.retrieveLangLBl("How's your Task? Rate provider", "LBL_BIDDING_RATE_HEADING_DRIVER_TXT"));
        binding.tripStatusTxt.setText(generalFunc.retrieveLangLBl("This Task was successfully finished", "LBL_FINISHED_TASK_TXT"));
    }

    private void addFareDetailLayout(JSONArray jobjArray) {

        if (binding.fareDetailDisplayArea.getChildCount() > 0) {
            binding.fareDetailDisplayArea.removeAllViewsInLayout();
        }

        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                addFareDetailRow(jobject.names().getString(0), jobject.get(jobject.names().getString(0)).toString(), (jobjArray.length() - 1) == i);
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
            params.setMargins(0, (int) getResources().getDimension(R.dimen._7sdp), 0, 0);
            convertView.setBackgroundColor(Color.parseColor("#dedede"));
            convertView.setLayoutParams(params);
        } else {
            LayoutInflater infalInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = infalInflater.inflate(R.layout.design_fare_deatil_row, null);

            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
            //params.setMargins( (int) getResources().getDimension(R.dimen._10sdp), 0, isLast ? (int) getResources().getDimension(R.dimen._10sdp) : 0,0);
            convertView.setLayoutParams(params);

            MTextView titleHTxt = (MTextView) convertView.findViewById(R.id.titleHTxt);
            MTextView titleVTxt = (MTextView) convertView.findViewById(R.id.titleVTxt);

            titleHTxt.setText(generalFunc.convertNumberWithRTL(row_name));
            titleVTxt.setText(generalFunc.convertNumberWithRTL(row_value));

            if (isLast) {
                // convertView.setMinimumHeight(Utils.dipToPixels(getActContext(), 40));

                // CALCULATE individual fare & show
                titleHTxt.setTextColor(getResources().getColor(R.color.black));
                titleHTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);

                titleHTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);
                titleVTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));

            }
        }
        binding.fareDetailDisplayArea.addView(convertView);
    }


    public void sendReceipt() {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getReceipt");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId"));

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                generalFunc.showMessage(generalFunc.getCurrentView(BiddingHistoryDetailActivity.this), generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            } else {
                generalFunc.showError();
            }
        });

    }

    public Context getActContext() {
        return BiddingHistoryDetailActivity.this;
    }

    public void submitRating() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "submitRatingBiddingService");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iGeneralUserId", generalFunc.getMemberId());
        parameters.put("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId"));
        parameters.put("rating", "" + binding.ufxratingBar.getRating());
        parameters.put("message", Utils.getText(binding.commentBox));
        parameters.put("UserType", Utils.app_type);
        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(true);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();

                        Intent returnIntent = new Intent();
                        setResult(Activity.RESULT_OK, returnIntent);
                        finish();
                    });

                    generateAlert.setContentMessage(generalFunc.retrieveLangLBl("", "LBL_TASK_COMPLETED_TXT"), generalFunc.retrieveLangLBl("", "LBL_TASK_FINISHED_TXT"));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                    generateAlert.setCancelable(false);


                } else {
                    resetRatingData();
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void resetRatingData() {
        binding.commentBox.setText("");
        binding.ufxratingBar.setRating(0);
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

        if (id == binding.toolbarInclude.backImgView.getId()) {
            onBackPressed();
        } else if (id == binding.toolbarInclude.receiptImgView.getId()) {
            sendReceipt();
        } else if (id == binding.viewReqServicesArea.getId()) {
            new ActUtils(getActContext()).startActWithData(MoreServiceInfoActivity.class, bn);
        } else if (id == binding.helpTxt.getId()) {
            bn.putString("iBiddingPostId", getIntent().getExtras().getString("iBiddingPostId"));
            new ActUtils(getActContext()).startActWithData(HelpMainCategory23Pro.class, bn);
        } else if (id == btn_type2.getId()) {
            if (binding.ufxratingBar.getRating() <= 0.0) {
                generalFunc.showMessage(generalFunc.getCurrentView(BiddingHistoryDetailActivity.this), generalFunc.retrieveLangLBl("", "LBL_ERROR_RATING_DIALOG_TXT"));
                return;
            }
            submitRating();
        }
    }

}
