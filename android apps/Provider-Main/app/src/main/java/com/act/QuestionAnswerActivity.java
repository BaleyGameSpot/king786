package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Build;
import android.os.Bundle;
import android.text.Html;
import android.util.Log;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.CookieSyncManager;
import android.widget.ImageView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.providers.R;
import com.utils.Utils;
import com.view.MTextView;
import com.view.WKWebView;

public class QuestionAnswerActivity extends ParentActivity {

    MTextView titleTxt;
    MTextView vQuestion;
    ImageView backImgView;
    MTextView contact_us_btn;
    MTextView textstillneedhelp;
    WKWebView webView;

    @SuppressLint("ClickableViewAccessibility")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_question_answer);


        titleTxt = findViewById(R.id.titleTxt);
        backImgView = findViewById(R.id.backImgView);
        contact_us_btn = findViewById(R.id.contact_us_btn);
        textstillneedhelp = findViewById(R.id.textstillneedhelp);

        vQuestion = findViewById(R.id.vQuestion);
        clearCookies(getApplication());
        webView = findViewById(R.id.webView);


        if (getIntent().getStringExtra("QUESTION") != null) {
            vQuestion.setText(Html.fromHtml(getIntent().getStringExtra("QUESTION") + ""));
            webView.loadData(getIntent().getStringExtra("ANSWER"));
            webView.setOnTouchListener((v, event) -> event.getPointerCount() > 1);
            webView.setBackgroundColor(getResources().getColor(R.color.transparent_full));
        }
        titleTxt.setText(generalFunc.retrieveLangLBl("FAQ", "LBL_FAQ_TXT"));
        addToClickHandler(contact_us_btn);
        addToClickHandler(backImgView);

        setData();
    }

    @SuppressWarnings("deprecation")
    public static void clearCookies(Context context) {

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP_MR1) {
            Log.d("Api", "Using clearCookies code for API >=" + String.valueOf(Build.VERSION_CODES.LOLLIPOP_MR1));
            CookieManager.getInstance().removeAllCookies(null);
            CookieManager.getInstance().flush();
        } else {
            Log.d("Api", "Using clearCookies code for API <" + String.valueOf(Build.VERSION_CODES.LOLLIPOP_MR1));
            CookieSyncManager cookieSyncMngr = CookieSyncManager.createInstance(context);
            cookieSyncMngr.startSync();
            CookieManager cookieManager = CookieManager.getInstance();
            cookieManager.removeAllCookie();
            cookieManager.removeSessionCookie();
            cookieSyncMngr.stopSync();
            cookieSyncMngr.sync();
        }
    }

    public void setData() {
        contact_us_btn.setText("" + generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_HEADER_TXT"));
        textstillneedhelp.setText("" + generalFunc.retrieveLangLBl("", "LBL_STILL_NEED_HELP"));


    }

    public Context getActContext() {
        return QuestionAnswerActivity.this;
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.backImgView) {
            QuestionAnswerActivity.super.onBackPressed();
        } else if (id == R.id.contact_us_btn) {
            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
        }
    }

}
