package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityStaticPageBinding;
import com.service.handler.ApiHandler;
import com.service.utils.DefaultParams;
import com.utils.CommonUtilities;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.WKWebView;

import org.json.JSONObject;

import java.util.HashMap;

public class StaticPageActivity extends ParentActivity {
    private ActivityStaticPageBinding binding;
    private String static_page_id = "1";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_static_page);

        static_page_id = getIntent().getStringExtra("staticpage");

        initViews();
    }

    private void initViews() {
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binding.toolbarInclude.backImgView);

        HashMap<String, String> paramsList = new HashMap<>(DefaultParams.getInstance().getDefaultParams());
        paramsList.put("isFromApp", "Yes");

        if (static_page_id.equalsIgnoreCase("1")) {
            binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT_US_HEADER_TXT"));

            loadAboutUsDetail(CommonUtilities.SERVER + "about?" + MyUtils.mapToString(paramsList));

        } else if (static_page_id.equalsIgnoreCase("33")) {
            binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PRIVACY_POLICY_TEXT"));

            loadAboutUsDetail(CommonUtilities.SERVER + "privacy-policy?" + MyUtils.mapToString(paramsList));

        } else if (static_page_id.equals("4")) {
            binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TERMS_AND_CONDITION"));
            loadAboutUsDetail(CommonUtilities.SERVER + "terms-condition?" + MyUtils.mapToString(paramsList));

        } else {
            binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DETAILS"));
            loadAboutUsData();
        }
    }

    private void loadAboutUsData() {
        if (binding.errorView.getVisibility() == View.VISIBLE) {
            binding.errorView.setVisibility(View.GONE);
        }
        if (binding.loading.getVisibility() != View.VISIBLE) {
            binding.loading.setVisibility(View.VISIBLE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "staticPage");
        parameters.put("iPageId", static_page_id);
        parameters.put("appType", Utils.app_type);
        parameters.put("iMemberId", generalFunc.getMemberId());

        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
            parameters.put("vLangCode", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            binding.loading.setVisibility(View.GONE);
            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            if (responseObj != null && !responseObj.toString().equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    String message = generalFunc.getJsonValueStr(Utils.message_str, responseObj);
                    loadAboutUsDetail(generalFunc.getJsonValue("tPageDesc", message));
                } else {
                    loadAboutUsDetail(generalFunc.getJsonValueStr("page_desc", responseObj));
                }
            } else {
                generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
                if (binding.errorView.getVisibility() != View.VISIBLE) {
                    binding.errorView.setVisibility(View.VISIBLE);
                }
                binding.errorView.setOnRetryListener(this::loadAboutUsData);
            }
        });
    }

    @SuppressLint("ClickableViewAccessibility")
    private void loadAboutUsDetail(String data) {
        WKWebView view = new WKWebView(this);
        binding.container.addView(view);
        if (data.startsWith("http")) {
            view.loadUrl(data);
        } else {
            view.loadData(data);
        }
        view.setOnTouchListener((v, event) -> event.getPointerCount() > 1);
        view.setWebClient(new WKWebView.WebClient() {
            @Override
            public void onPageStarted(WKWebView view, String url, Bitmap favicon) {
                binding.loading.setVisibility(View.VISIBLE);
                WKWebView.WebClient.super.onPageStarted(view, url, favicon);
            }

            @Override
            public void onPageFinished(WKWebView view, String url) {
                binding.loading.setVisibility(View.GONE);
                WKWebView.WebClient.super.onPageFinished(view, url);
            }

            @Override
            public boolean shouldOverrideUrlLoading(WKWebView view, WebResourceRequest request) {
                Bundle bn = new Bundle();
                if (request.getUrl().toString().contains("/terms-condition")) {
                    if (static_page_id.equalsIgnoreCase("4")) {
                        return false;
                    }
                    bn.putString("staticpage", "4");
                    new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
                } else if (request.getUrl().toString().contains("/contact-us")) {
                    new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                } else if (request.getUrl().toString().contains("/about")) {
                    if (static_page_id.equalsIgnoreCase("1")) {
                        return false;
                    }
                    bn.putString("staticpage", "1");
                    new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
                } else if (request.getUrl().toString().contains("/privacy-policy")) {
                    if (static_page_id.equalsIgnoreCase("33")) {
                        return false;
                    }
                    bn.putString("staticpage", "33");
                    new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
                } else if (request.getUrl().toString().contains("tel:")) {
                    try {
                        Intent callIntent = new Intent(Intent.ACTION_DIAL);
                        callIntent.setData(Uri.parse(request.getUrl().toString()));
                        startActivity(callIntent);
                    } catch (Exception ignored) {
                    }

                } else if (request.getUrl().toString().contains("mailto:")) {
                    String email = request.getUrl().toString().replace("mailto:", "");
                    Intent emailIntent = new Intent(Intent.ACTION_SEND);
                    emailIntent.setType("message/rfc822");
                    String[] recipients = {email};
                    emailIntent.putExtra(Intent.EXTRA_EMAIL, recipients);
                    startActivity(Intent.createChooser(emailIntent, "Choose an Email client :"));
                } else {
                    if (request.getUrl().toString().equalsIgnoreCase("about:blank#blocked")) {
                        return false;
                    }
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(request.getUrl().toString()));
                    startActivity(Intent.createChooser(intent, "Choose browser"));
                }
                return true;
            }

            @Override
            public void onReceivedError(WKWebView view, WebResourceRequest request, WebResourceError error) {
                binding.errorView.setVisibility(View.VISIBLE);
                generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
                binding.errorView.setOnRetryListener(() -> view.loadUrl(request.getUrl().toString()));
                WKWebView.WebClient.super.onReceivedError(view, request, error);
            }
        });
    }

    private Context getActContext() {
        return StaticPageActivity.this;
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        if (view.getId() == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();
        }
    }
}