package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.view.MotionEvent;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.JsPromptResult;
import android.webkit.JsResult;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebStorage;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.annotation.Nullable;

import com.activity.ParentActivity;
import com.airbnb.lottie.LottieAnimationView;
import com.general.files.ActUtils;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.model.getProfilePaymentModel;
import com.utils.Logger;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.WKWebView;

import org.json.JSONArray;

import java.io.BufferedInputStream;
import java.io.UnsupportedEncodingException;
import java.net.URLDecoder;

@SuppressLint("SetJavaScriptEnabled")
public class PaymentWebviewActivity extends ParentActivity implements ErrorView.RetryListener {

    private LottieAnimationView loaderView;
    private WKWebView paymentWebview;
    private ImageView cancelImg;

    private boolean handleResponse = false, isApiCall = false;
    private JsPromptResult result = null;
    private String CouponCode = "", mFailingUrl = "";
    private ErrorView errorView;
    private InternetConnection internetConnection;
    private String CustomPaymentGatewayUrl = "";
    private String IsCustomPaymentGateway = "";
    private JSONArray hideElementsArray;

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getWindow().getDecorView();
        setContentView(R.layout.activity_payment_webview);

        if (getIntent() != null && getIntent().getExtras() != null && getIntent().getExtras().containsKey("CouponCode")) {
            CouponCode = getIntent().getStringExtra("CouponCode");
        }

        internetConnection = new InternetConnection(getActContext());

        isApiCall = getIntent().getBooleanExtra("isApiCall", false);

        paymentWebview = findViewById(R.id.paymentWebview);
        loaderView = findViewById(R.id.loaderView);
        cancelImg = findViewById(R.id.cancelImg);
        cancelImg.setOnClickListener(v -> finish());

        String url = getIntent().getStringExtra("url");

        handleResponse = getIntent().getBooleanExtra("handleResponse", false);

        Logger.d("WebViewURL", "::" + url);

        paymentWebview.setWebViewClient(new myWebClient());
        paymentWebview.getSettings().setDomStorageEnabled(true);
        paymentWebview.setWebChromeClient(new MyWebChromeClient());

        // Clear all the Application Cache, Web SQL Database and the HTML5 Web Storage
        WebStorage.getInstance().deleteAllData();

        // Clear all the cookies
        CookieManager.getInstance().removeAllCookies(null);
        CookieManager.getInstance().flush();

        paymentWebview.clearCache(true);
        paymentWebview.clearFormData();
        paymentWebview.clearHistory();
        paymentWebview.clearSslPreferences();

        mFailingUrl = url;
        paymentWebview.loadUrl(url);

        cancelImg.setVisibility(View.VISIBLE);

        paymentWebview.setFocusable(true);
        paymentWebview.setVisibility(View.VISIBLE);

        errorView = findViewById(R.id.errorView);
        errorView.setVisibility(View.GONE);
        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        errorView.setOnRetryListener(this);


        CustomPaymentGatewayUrl = generalFunc.getJsonValueStr("CUSTOM_PG_PREFIX_URL", obj_userProfile);
        IsCustomPaymentGateway = generalFunc.getJsonValueStr("CUSTOM_PAYMENT_GATEWAY", obj_userProfile);
        hideElementsArray = generalFunc.getJsonArray("HIDE_ELEMENTS_PG", obj_userProfile);

    }

    private Context getActContext() {
        return PaymentWebviewActivity.this;
    }

    @Override
    public void onBackPressed() {
        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_CANCEL_PAYMENT_PROCESS"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
            if (buttonId == 1) {
                super.onBackPressed();
            }
        });
    }

    @Override
    public void onRetry() {
        if (internetConnection.isNetworkConnected() && internetConnection.check_int()) {
            paymentWebview.loadUrl(mFailingUrl);
            errorView.setVisibility(View.GONE);
            cancelImg.setVisibility(View.GONE);
            loaderView.setVisibility(View.VISIBLE);
        }
    }

    public void HideHTMLTag(WebView view) {
        String name = generalFunc.getJsonValueStr("vName", obj_userProfile) + " " + generalFunc.getJsonValueStr("vLastName", obj_userProfile);
        String phone = generalFunc.getJsonValueStr("vPhone", obj_userProfile);
        String email = generalFunc.getJsonValueStr("vEmail", obj_userProfile);
        if (hideElementsArray != null) {
            for (int i = 0; i < hideElementsArray.length(); i++) {
                view.loadUrl(generalFunc.getValueFromJsonArr(hideElementsArray, i).toString().replace("#USER_NAME#", name).replace("#USER_PHONE#", phone).replace("#USER_EMAIL#", email));
            }
        }
    }

    private class myWebClient extends WebViewClient {

        @Override
        public boolean shouldOverrideUrlLoading(WebView view, String url) {
            view.loadUrl(url);
            mFailingUrl = url;
            return true;
        }

        @Override
        public void onLoadResource(WebView view, String url) {
            super.onLoadResource(view, url);
            Logger.d("WebData_onLoadResource", "onLoadResource ::" + url);
            if (IsCustomPaymentGateway.equalsIgnoreCase("Yes")) {
                HideHTMLTag(view);
            }
        }

        @SuppressLint("ClickableViewAccessibility")
        @Override
        public void onPageStarted(WebView view, String url, Bitmap favicon) {

            String data = url;
            Logger.d("WebData", "::" + data);
            if (IsCustomPaymentGateway.equalsIgnoreCase("Yes")) {
                HideHTMLTag(view);
            }
            data = data.substring(data.indexOf("data") + 5);
            try {

                String datajson = URLDecoder.decode(data, "UTF-8");
                Logger.d("WebData", "::2222222::" + datajson);
                loaderView.setVisibility(View.VISIBLE);
                cancelImg.setVisibility(View.VISIBLE);

                view.setOnTouchListener(null);

                if (url.contains("gift_action=success")) {
                    setResult(Activity.RESULT_OK, new Intent());
                    finish();
                }
                if (url.contains("OPEN_IN_APP_WALLET=Yes")) {
                    new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                    finish();
                }
                if (url.contains("BUSINESS_PROFILE_SETUP=Yes")) {
                    new ActUtils(getActContext()).startAct(BusinessProfileActivity.class);
                    finish();
                }


                if (url.contains("success=1") || url.contains("result.php?success=1")) {

                    if (isApiCall) {


                        MyApp.getInstance().refreshData();
                        finish();
                        return;
                    }
                    loaderView.setVisibility(View.GONE);
                    cancelImg.setVisibility(View.GONE);
                    if (handleResponse) {

                        Intent returnIntent = new Intent();

                        if (url.contains("LBL_MANUAL_STORE_THANK_YOU_ORDER_PLACE_ORDER")) {
                            returnIntent.putExtra("isGenieOrderPlace", "Yes");
                        }

                        if (url.contains("orderid")) {
                            Uri uri = Uri.parse(url);
                            String orderid = uri.getQueryParameter("orderid");
                            returnIntent.putExtra("iOrderId", orderid);
                            Logger.d("TESTOREDERID", "::" + orderid);
                        }

                        if (url.contains("paymentModeSelected=Yes")) {
                            returnIntent.putExtra("paymentModeSelected", "Yes");
                        }

                        if (getIntent().hasExtra("eType") && getIntent().getStringExtra("eType").equalsIgnoreCase("RideShare")) {
                            if (url.contains("success.php")) {
                                if (url.contains("iAuthorizePaymentId")) {
                                    returnIntent.putExtra("iAuthorizePaymentId", Uri.parse(url).getQueryParameter("iAuthorizePaymentId"));
                                }
                            }
                        }

                        setResult(Activity.RESULT_OK, returnIntent);
                        finish();
                    }
                }

                if (url.contains("success=0") || url.contains("page_action=close") || url.contains("page_action=contactus")) {

                    if (url.contains("page_action=close")) {
                        loaderView.setVisibility(View.GONE);
                        cancelImg.setVisibility(View.GONE);
                        finish();


                    }
                    if (url.contains("page_action=contactus")) {
                        new ActUtils(getActContext()).startAct(ContactUsActivity.class);

                    }

                }
                RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) paymentWebview.getLayoutParams();
                if (IsCustomPaymentGateway.equalsIgnoreCase("Yes") && Utils.checkText(CustomPaymentGatewayUrl) && url.contains(CustomPaymentGatewayUrl)) {
                    params.setMargins(0, (int) getResources().getDimension(R.dimen._30sdp), 0, 0);
                } else {
                    params.setMargins(0, 0, 0, 0);
                }

            } catch (UnsupportedEncodingException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

        @Override
        public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
            //super.onReceivedError(view, request, error);
            if (IsCustomPaymentGateway.equalsIgnoreCase("Yes") && Utils.checkText(CustomPaymentGatewayUrl) && request.getUrl().toString().contains(CustomPaymentGatewayUrl)) {
                return;
            }
            view.stopLoading();
            view.loadData("", "", "");
            errorView.setVisibility(View.VISIBLE);
            cancelImg.setVisibility(View.VISIBLE);
        }

        @Override
        public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
            Logger.d("TESTOREDERID", "::" + description + "::" + errorCode + "::" + failingUrl);
            if (IsCustomPaymentGateway.equalsIgnoreCase("Yes") && Utils.checkText(CustomPaymentGatewayUrl) && failingUrl.contains(CustomPaymentGatewayUrl)) {
                return;
            }
            errorView.setVisibility(View.VISIBLE);
            loaderView.setVisibility(View.GONE);
            cancelImg.setVisibility(View.VISIBLE);

            view.loadData("", "", "");
            mFailingUrl = failingUrl;
        }

        @SuppressLint("ClickableViewAccessibility")
        @Override
        public void onPageFinished(WebView view, String url) {
            loaderView.setVisibility(View.GONE);
            cancelImg.setVisibility(View.GONE);
            if (IsCustomPaymentGateway.equalsIgnoreCase("Yes")) {
                HideHTMLTag(view);
            }
            if (errorView.getVisibility() == View.VISIBLE || (IsCustomPaymentGateway.equalsIgnoreCase("Yes") && Utils.checkText(CustomPaymentGatewayUrl) && url.contains(CustomPaymentGatewayUrl))) {
                cancelImg.setVisibility(View.VISIBLE);
            }

            view.setOnTouchListener((v, event) -> {
                switch (event.getAction()) {
                    case MotionEvent.ACTION_DOWN:
                    case MotionEvent.ACTION_UP:
                        if (!v.hasFocus()) {
                            v.requestFocus();
                        }
                        break;
                }
                return false;
            });

        }

        @Override
        @SuppressLint("NewApi")
        public WebResourceResponse shouldInterceptRequest(WebView view, WebResourceRequest request) {

            Logger.d("WebResourceResponse", "Success ::" + request.getUrl());
            if (!request.isForMainFrame() && request.getUrl().getPath().endsWith("/favicon.ico")) {
                try {
                    return new WebResourceResponse("image/png", null, new BufferedInputStream(view.getContext().getAssets().open("empty_favicon.ico")));
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }

            return null;
        }
    }

    private class MyWebChromeClient extends WebChromeClient {

        @Override
        public boolean onJsAlert(WebView view, String url, String message, final android.webkit.JsResult result) {
            Logger.d("alert > onJsAlert", message);
            generalFunc.showGeneralMessage("", message, generalFunc.retrieveLangLBl("", "LBL_BTN_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"), button_Id -> {
                if (button_Id == 1) {
                    result.confirm();
                } else {
                    result.cancel();
                }
            });
            return true;
        }

        @Override
        public boolean onJsConfirm(WebView view, String url, String message, JsResult result) {

            Logger.d("alert > onJsConfirm", message);
            generalFunc.showGeneralMessage("", message, generalFunc.retrieveLangLBl("", "LBL_BTN_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"), button_Id -> {
                if (button_Id == 1) {
                    result.confirm();
                } else {
                    result.cancel();
                }
            });

            return true;
        }

        @Override
        public boolean onJsPrompt(WebView view, String url, String message, String defaultValue, JsPromptResult result) {
            Logger.d("alert > onJsConfirm", message + "::" + url + "::" + defaultValue);

            if (defaultValue.contains("OPEN_PROMOCODE")) {
                Bundle bn = new Bundle();
                String[] filterString = defaultValue.split("_");

                CouponCode = "";
                bn.putString("CouponCode", CouponCode);
                if (filterString.length >= 3) {
                    bn.putString("CouponCode", filterString[2]);
                }

                bn.putString("eType", getIntent().getStringExtra("eType"));
                bn.putBoolean("eFly", getIntent().getBooleanExtra("eFly", false));
                if (getIntent().getExtras().containsKey("vSourceLatitude")) {
                    bn.putString("vSourceLatitude", getIntent().getStringExtra("vSourceLatitude"));
                    bn.putString("vSourceLongitude", getIntent().getStringExtra("vSourceLongitude"));
                }
                if (getIntent().getExtras().containsKey("vDestLatitude")) {
                    bn.putString("vDestLatitude", getIntent().getStringExtra("vDestLatitude"));
                    bn.putString("vDestLongitude", getIntent().getStringExtra("vDestLongitude"));
                }
                bn.putString("eTakeAway", getIntent().getStringExtra("eTakeAway"));
                if (getIntent().getExtras().containsKey("isTaxiBid")) {
                    bn.putString("isTaxiBid", getIntent().getStringExtra("isTaxiBid"));
                }

                new ActUtils(getActContext()).startActForResult(CouponActivity.class, bn, Utils.SELECT_COUPON_REQ_CODE);
                setJsPromptResult(result);
                return true;

            } else if (defaultValue.contains("PROMO_REMOVED")) {
                result.confirm();
                return true;
            } else {
                return super.onJsPrompt(view, url, message, defaultValue, result);
            }
        }
    }

    private void setJsPromptResult(JsPromptResult result) {
        this.result = result;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable @org.jetbrains.annotations.Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        getProfilePaymentModel.getProfilePayment(getIntent().getStringExtra("eType"), this, null, false, false);

        if (requestCode == Utils.SELECT_COUPON_REQ_CODE && resultCode == RESULT_OK) {
            String couponCode = data != null ? data.getStringExtra("CouponCode") : null;
            CouponCode = couponCode;
            if (couponCode == null) {
                CouponCode = "";
                couponCode = "";
            }
            if (this.result != null) {
                result.confirm(couponCode);
            }
        } else {
            if (this.result != null) {
                result.confirm(CouponCode);
            }
        }

    }
}