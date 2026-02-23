package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.providers.R;
import com.utils.Utils;
import com.view.MTextView;
import com.view.anim.loader.AVLoadingIndicatorView;

import org.json.JSONObject;


public class CardPaymentActivity extends ParentActivity {


    MTextView titleTxt;
    ImageView backImgView;
    String APP_PAYMENT_METHOD;
    AVLoadingIndicatorView loaderView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_card_payment);

        getUserProfileJson(generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        loaderView = (AVLoadingIndicatorView) findViewById(R.id.loaderView);
        setLabels();
        addToClickHandler(backImgView);
        String url = generalFunc.getJsonValueStr("PAYMENT_BASE_URL", obj_userProfile) + "&PAGE_TYPE=PAYMENT_LIST" +
                "&currency=" + generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile);
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
        obj_userProfile = object;


        APP_PAYMENT_METHOD = generalFunc.getJsonValueStr("APP_PAYMENT_METHOD", obj_userProfile);
    }


    public void setLabels() {
        changePageTitle(generalFunc.retrieveLangLBl("", "LBL_CARD_PAYMENT_DETAILS"));
    }

    public void changePageTitle(String title) {
        titleTxt.setText(title);
    }


    public Context getActContext() {
        return CardPaymentActivity.this;
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();

    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(CardPaymentActivity.this);
        if (i == R.id.backImgView) {
            onBackPressed();
        }
    }


}
