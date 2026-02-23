package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.os.Bundle;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Objects;

public class BiddingHistoryDetailActivity extends ParentActivity {

    private FrameLayout paymentMainArea;
    private ProgressBar loading, progresdefault;
    private ErrorView errorView;
    private RelativeLayout container;
    private MTextView namePassengerVTxt, headerTxt, rideNoHTxt, rideNoVTxt, tripdateVTxt, pickUpAddressVTxt, cartypeTxt, paymentTypeTxt;
    private LinearLayout fareDetailDisplayArea;
    private ImageView driverImageview;
    private SimpleRatingBar ratingBar;
    private ImageView paymentTypeImgeView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_bidding_history_detail);

        paymentMainArea = findViewById(R.id.paymentMainArea);
        MTextView titleTxt = (MTextView) findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECEIPT_HEADER_TXT"));
        ImageView backImgView = (ImageView) findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        backImgView.setOnClickListener(v -> finish());

        progresdefault = (ProgressBar) findViewById(R.id.progresdefault);
        progresdefault.setVisibility(View.VISIBLE);
        loading = (ProgressBar) findViewById(R.id.loading);
        errorView = (ErrorView) findViewById(R.id.errorView);
        container = (RelativeLayout) findViewById(R.id.container);

        driverImageview = (SelectableRoundedImageView) findViewById(R.id.driverImgView);
        cartypeTxt = (MTextView) findViewById(R.id.cartypeTxt);
        fareDetailDisplayArea = (LinearLayout) findViewById(R.id.fareDetailDisplayArea);
        namePassengerVTxt = (MTextView) findViewById(R.id.namePassengerVTxt);
        ratingBar = (SimpleRatingBar) findViewById(R.id.ratingBar);
        headerTxt = (MTextView) findViewById(R.id.headerTxt);
        rideNoHTxt = (MTextView) findViewById(R.id.rideNoHTxt);
        rideNoVTxt = (MTextView) findViewById(R.id.rideNoVTxt);
        tripdateVTxt = (MTextView) findViewById(R.id.tripdateVTxt);
        MTextView pickUpAddressHTxt = (MTextView) findViewById(R.id.pickUpAddressHTxt);
        pickUpAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_SERVICE_ADDRESS_TXT"));
        pickUpAddressVTxt = (MTextView) findViewById(R.id.pickUpAddressVTxt);
        MTextView chargesHTxt = (MTextView) findViewById(R.id.chargesHTxt);
        chargesHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHARGES_TXT"));

        paymentTypeImgeView = (ImageView) findViewById(R.id.paymentTypeImgeView);
        paymentTypeTxt = (MTextView) findViewById(R.id.paymentTypeTxt);

        getMemberBookings();
    }

    @SuppressLint("SetTextI18n")
    private void getMemberBookings() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (container.getVisibility() == View.VISIBLE) {
            container.setVisibility(View.GONE);
        }
        if (paymentMainArea.getVisibility() == View.VISIBLE) {
            paymentMainArea.setVisibility(View.GONE);
        }
        if (loading.getVisibility() != View.VISIBLE) {
            loading.setVisibility(View.VISIBLE);
        }


        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "displayFareBiddingService");
        parameters.put("memberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("memberType", Utils.app_type);
        if (getIntent().hasExtra("iBiddingPostId")) {
            parameters.put("iBiddingPostId", getIntent().getExtras().getString("iBiddingPostId"));
        }

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {
                    JSONObject responseObj = generalFunc.getJsonObject(responseString);

                    if (responseObj != null) {
                        closeLoader();

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                            String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                            String vImage = generalFunc.getJsonValue("userImage", message);
                            if (vImage == null || vImage.equals("") || vImage.equals("NONE")) {
                                driverImageview.setImageResource(R.mipmap.ic_no_pic_user);
                            } else {
                                new LoadImage.builder(LoadImage.bind(vImage), driverImageview).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
                            }

                            namePassengerVTxt.setText(generalFunc.getJsonValue("userName", message) + " (" + generalFunc.retrieveLangLBl("", "LBL_USER") + " )");
                            ratingBar.setRating(GeneralFunctions.parseFloatValue(0, generalFunc.getJsonValue("userAvgRating", message)));

                            headerTxt.setText(generalFunc.retrieveLangLBl("", "LBL_THANKS_TXT"));
                            rideNoHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TASK_TXT"));
                            rideNoVTxt.setText("#" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vBiddingPostNo", message)));

                            LinearLayout.LayoutParams txtParam = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
                            txtParam.setMargins(2, 10, 2, 0);
                            rideNoVTxt.setLayoutParams(txtParam);
                            rideNoHTxt.setLayoutParams(txtParam);

                            tripdateVTxt.setText(generalFunc.getJsonValue("tDisplayDateTime", responseString));
                            //tripdateVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("dBiddingDate", message), Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));

                            pickUpAddressVTxt.setText(generalFunc.getJsonValue("tSaddress", message));
                            ((MTextView) findViewById(R.id.pickUpAddressVTxt)).setText(generalFunc.getJsonValue("tSaddress", message));

                            cartypeTxt.setText(generalFunc.getJsonValue("vServiceDetailTitle", message));

                            if (generalFunc.isJSONkeyAvail("FareDetailsNewArr", message)) {
                                addFareDetailLayout(generalFunc.getJsonArray("FareDetailsNewArr", message));
                            }

                            if (generalFunc.getJsonValue("vBiddingPaymentMode", message).equals("Cash")) {
                                paymentTypeImgeView.setImageResource(R.drawable.ic_cash_payment);
                                paymentTypeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CASH_PAYMENT_TXT"));
                            } else if (generalFunc.getJsonValue("vBiddingPaymentMode", message).equals("Wallet")) {
                                ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.drawable.ic_menu_wallet);
                                ((MTextView) findViewById(R.id.paymentTypeTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_PAID_VIA_WALLET"));
                            } else {
                                paymentTypeImgeView.setImageResource(R.mipmap.ic_card_new);
                                paymentTypeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CARD_PAYMENT"));
                            }

                            if (generalFunc.getJsonValue("ePayWallet", message).equals("Yes")) {
                                paymentTypeImgeView.setImageResource(R.drawable.ic_menu_wallet);
                                paymentTypeTxt.setText(generalFunc.retrieveLangLBl("Paid By Wallet", "LBL_PAID_VIA_WALLET"));
                            }
                            ((MTextView) findViewById(R.id.tripStatusTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_FINISHED_TASK_TXT"));
                        } else {
                            generateErrorView();
                        }
                    } else {
                        generateErrorView();
                    }
                });

    }

    public void closeLoader() {
        progresdefault.setVisibility(View.GONE);
        if (loading.getVisibility() == View.VISIBLE) {
            loading.setVisibility(View.GONE);
        }
        if (container.getVisibility() == View.GONE) {
            container.setVisibility(View.VISIBLE);
        }
        if (paymentMainArea.getVisibility() == View.GONE) {
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
        }
        if (paymentMainArea.getVisibility() == View.VISIBLE) {
            paymentMainArea.setVisibility(View.GONE);
        }
        errorView.setOnRetryListener(this::getMemberBookings);
    }

    private void addFareDetailLayout(JSONArray jobjArray) {
        if (fareDetailDisplayArea.getChildCount() > 0) {
            fareDetailDisplayArea.removeAllViewsInLayout();
        }
        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                String data = Objects.requireNonNull(jobject.names()).getString(0);

                addFareDetailRow(data, jobject.get(data).toString(), jobjArray.length() - 1 == i);
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }
    }

    @SuppressLint("InflateParams")
    private void addFareDetailRow(String row_name, String row_value, boolean isLast) {
        View convertView;
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

    public Context getActContext() {
        return BiddingHistoryDetailActivity.this;
    }
}
