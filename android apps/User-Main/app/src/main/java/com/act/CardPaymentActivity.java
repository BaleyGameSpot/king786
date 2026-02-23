package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONObject;

public class CardPaymentActivity extends ParentActivity {


    public boolean isufxbook = false;
    MTextView titleTxt;
    ImageView backImgView;
    boolean fromcabselection = false;
    String APP_PAYMENT_METHOD;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_card_payment);


        fromcabselection = getIntent().getBooleanExtra("fromcabselection", false);
        isufxbook = getIntent().getBooleanExtra("isufxbook", false);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);


        setLabels();
        addToClickHandler(backImgView);

        String url = generalFunc.getJsonValueStr("PAYMENT_BASE_URL", obj_userProfile) + "&PAGE_TYPE=PAYMENT_LIST" +
                "&currency=" + generalFunc.getJsonValueStr("vCurrencyPassenger", obj_userProfile);
        url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
        url = url + "&GeneralUserType=" + Utils.app_type;
        url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
        url = url + "&ePaymentOption=" + "Card";
        url = url + "&vPayMethod=" + "Instant";
        url = url + "&SYSTEM_TYPE=" + "APP";
        url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();

        Bundle bn = new Bundle();
        bn.putString("url", url);
        new ActUtils(getActContext()).startActWithData(PaymentWebviewActivity.class, bn);
        finish();

    }


    private void getUserProfileJson(JSONObject object) {


        APP_PAYMENT_METHOD = generalFunc.getJsonValueStr("APP_PAYMENT_METHOD", obj_userProfile);
    }


    public void setLabels() {
        changePageTitle(generalFunc.retrieveLangLBl("", "LBL_CARD_PAYMENT_DETAILS"));
    }

    public void changePageTitle(String title) {
        titleTxt.setText(title);
    }

    public void changeUserProfileJson(String userProfileJson) {
        getUserProfileJson(generalFunc.getJsonObject(userProfileJson));

        if (isufxbook) {
            finish();
        }

        Bundle bn = new Bundle();
        bn.putString("UserProfileJson", userProfileJson);
        new ActUtils(getActContext()).setOkResult(bn);

        if (fromcabselection) {
            finish();

        }

        generalFunc.showMessage(getCurrView(), generalFunc.retrieveLangLBl("", "LBL_INFO_UPDATED_TXT"));
    }

    public View getCurrView() {
        return generalFunc.getCurrentView(CardPaymentActivity.this);
    }


    public Context getActContext() {
        return CardPaymentActivity.this;
    }

    @Override
    public void onBackPressed() {
        backImgView.performClick();
        return;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());

        if (i == R.id.backImgView) {
            onBackPressed();
        }
    }


}
