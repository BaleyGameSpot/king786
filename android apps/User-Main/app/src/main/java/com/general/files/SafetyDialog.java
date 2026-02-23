package com.general.files;

import android.annotation.SuppressLint;
import android.app.Dialog;
import android.content.Context;
import android.graphics.Bitmap;
import android.os.Bundle;
import android.view.View;
import android.view.ViewGroup;
import android.view.WindowManager;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.DailogSafetyMeasureBinding;
import com.utils.Logger;
import com.utils.Utils;
import com.view.WKWebView;
import com.view.anim.loader.AVLoadingIndicatorView;


public class SafetyDialog extends ParentActivity {
    DailogSafetyMeasureBinding binding;
    String mFailingUrl = "";

    private InternetConnection internetConnection;

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.dailog_safety_measure);

        ImageView backArrowImgView = (ImageView) findViewById(R.id.backArrowImgView);
        backArrowImgView.setOnClickListener(v -> finish());
        internetConnection = new InternetConnection(getActContext());

        binding.webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                if (newProgress > 90) {
                    binding.loaderView.setVisibility(View.GONE);
                    backArrowImgView.setVisibility(View.GONE);
                }
            }
        });

        String url = getIntent().getStringExtra("URL");
        binding.webView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                if (url.contains("success=0") || url.contains("page_action=close")) {
                    finish();
                }
            }
            @Override
            public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                //super.onReceivedError(view, request, error);
                view.stopLoading();
                view.loadData("", "", "");
                binding.errorView.setVisibility(View.VISIBLE);
            }
            @Override
            public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                Logger.d("TESTOREDERID", "::" + description + "::" + errorCode + "::" + failingUrl);

                binding.errorView.setVisibility(View.VISIBLE);

                view.loadData("", "", "");
                mFailingUrl = failingUrl;
            }

        });
        binding.webView.loadUrl(url + "&fromapp=yes");
        mFailingUrl = binding.webView.getUrl();
        binding.loaderView.setVisibility(View.VISIBLE);
        binding.errorView.setVisibility(View.GONE);
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        binding.errorView.setOnRetryListener(() -> {
            if (internetConnection.isNetworkConnected() && internetConnection.check_int()) {
                binding.webView.loadUrl(mFailingUrl);
                binding. errorView.setVisibility(View.GONE);
                binding.backArrowImgView.setVisibility(View.VISIBLE);
                binding.loaderView.setVisibility(View.VISIBLE);
            }
        });
    }

    public Context getActContext() {
        return SafetyDialog.this;
    }

    @SuppressLint({"ClickableViewAccessibility", "SetJavaScriptEnabled"})
    public void open(String url, String imageURL) {
        final Dialog addAddressDialog = new Dialog(getActContext());
        View contentView = View.inflate(getActContext(), R.layout.dailog_safety_measure, null);

        addAddressDialog.setContentView(contentView, new ViewGroup.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT,
                Utils.dpToPx(ViewGroup.LayoutParams.MATCH_PARENT, getActContext())));
        addAddressDialog.getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN, WindowManager.LayoutParams.FLAG_FULLSCREEN);

        addAddressDialog.setCancelable(true);
        int screenHeight = ((int) Utils.getScreenPixelHeight(getActContext()));
        int peekHeight = 0;
        int bottomMarginForLoader = Utils.dpToPx(50, getActContext());


        ImageView backArrowImgView = (ImageView) addAddressDialog.findViewById(R.id.backArrowImgView);

        backArrowImgView.setOnClickListener(v -> addAddressDialog.dismiss());
        RelativeLayout container = (RelativeLayout) addAddressDialog.findViewById(R.id.container);
        WKWebView mWebView = (WKWebView) addAddressDialog.findViewById(R.id.webView);
        AVLoadingIndicatorView loaderView = (AVLoadingIndicatorView) addAddressDialog.findViewById(R.id.loaderView);

        RelativeLayout.LayoutParams loaderView_ly_params = (RelativeLayout.LayoutParams) loaderView.getLayoutParams();
        loaderView_ly_params.bottomMargin = screenHeight - peekHeight + bottomMarginForLoader;
        loaderView.setLayoutParams(loaderView_ly_params);

        mWebView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                if (newProgress > 90) {
                    loaderView.setVisibility(View.GONE);
                    backArrowImgView.setVisibility(View.GONE);
                }
            }
        });

        mWebView.getSettings().setJavaScriptEnabled(MyApp.isJSEnabled);
        mWebView.loadUrl(url + "&fromapp=yes");
        loaderView.setVisibility(View.VISIBLE);

        addAddressDialog.show();
    }
}
