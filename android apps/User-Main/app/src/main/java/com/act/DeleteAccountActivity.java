package com.act;

import android.annotation.SuppressLint;
import android.graphics.Bitmap;
import android.os.Bundle;
import android.view.MotionEvent;
import android.view.View;
import android.webkit.WebView;
import android.webkit.WebViewClient;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityDeleteAccountBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.HashMap;

public class DeleteAccountActivity extends ParentActivity {

    private ActivityDeleteAccountBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_delete_account);

        initializeUi();
        accountDelete();
    }

    private void initializeUi() {
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binding.toolbarInclude.backImgView);
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELETE_ACCOUNT_TXT"));
    }

    private void accountDelete() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "AccountDelete");
        parameters.put("iMemberId", generalFunc.getMemberId());

        ApiHandler.execute(this, parameters, responseString -> {
            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    binding.deleteAccountWebView.setWebViewClient(new myWebClient());
                    binding.deleteAccountWebView.getSettings().setJavaScriptEnabled(MyApp.isJSEnabled);
                    binding.deleteAccountWebView.loadUrl(generalFunc.getJsonValueStr("Url", responseStringObj));
                    binding.deleteAccountWebView.setFocusable(true);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)), true);
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private class myWebClient extends WebViewClient {
        @Override
        public boolean shouldOverrideUrlLoading(WebView view, String url) {
            view.loadUrl(url);
            return true;
        }

        @SuppressLint("ClickableViewAccessibility")
        @Override
        public void onPageStarted(WebView view, String url, Bitmap favicon) {
            binding.deleteAccountLoaderView.setVisibility(View.VISIBLE);
            view.setOnTouchListener(null);

            if (url.contains("success=1&account_deleted=Yes")) {
                binding.deleteAccountWebView.setVisibility(View.GONE);
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.retrieveLangLBl("", "LBL_ACCOUNT_DELETED_SUCCESS_MSG")), "", generalFunc.retrieveLangLBl("", "LBL_OK"), i -> {
                    if (intCheck.isNetworkConnected()) {
                        MyApp.getInstance().logOutFromDevice(true);
                    } else {
                        generalFunc.showMessage(binding.deleteAccountLoaderView, generalFunc.retrieveLangLBl("", "LBL_NO_INTERNET_TXT"));
                    }
                });
            }

            if (url.contains("success=0")) {
                binding.deleteAccountWebView.setVisibility(View.GONE);

                String message;
                if (Utils.checkText(url) && url.contains("&message=")) {
                    String msg = GeneralFunctions.substringAfterLast(url, "&message=");
                    message = Utils.checkText(msg) ? msg.replaceAll("%20", " ") : "";
                } else {
                    message = generalFunc.retrieveLangLBl("", "LBL_REQUEST_FAILED_PROCESS");
                }
                generalFunc.showGeneralMessage("", message, "", generalFunc.retrieveLangLBl("", "LBL_OK"), i -> finish());
            }
        }

        @Override
        public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
            generalFunc.showError();
            binding.deleteAccountLoaderView.setVisibility(View.GONE);
        }

        @SuppressLint("ClickableViewAccessibility")
        @Override
        public void onPageFinished(WebView view, String url) {
            binding.deleteAccountLoaderView.setVisibility(View.GONE);

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
    }

    @SuppressLint("NonConstantResourceId")
    public void onClick(View view) {
        if (view.getId() == binding.toolbarInclude.backImgView.getId()) {
            getOnBackPressedDispatcher().onBackPressed();
        }
    }
}