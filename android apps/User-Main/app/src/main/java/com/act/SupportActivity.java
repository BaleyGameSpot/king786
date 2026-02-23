package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivitySupportBinding;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

public class SupportActivity extends ParentActivity {

    private ActivitySupportBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_support);

        initView();

        if (getIntent().getBooleanExtra("islogin", false)) {
            binding.aboutUsArea.setVisibility(View.GONE);
            binding.aboutUsLine.setVisibility(View.GONE);
            binding.chatLiveArea.setVisibility(View.GONE);
            binding.chatLiveLine.setVisibility(View.GONE);
            binding.contactArea.setVisibility(View.GONE);
            binding.contactLine.setVisibility(View.GONE);
            binding.helpArea.setVisibility(View.GONE);
            binding.helpLine.setVisibility(View.GONE);
        }
    }

    private void initView() {
        ImageView backImgView = findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        MTextView titleTxt = (MTextView) findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUPPORT_HEADER_TXT"));

        binding.aboutUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT_US_TXT"));
        binding.privacyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PRIVACY_POLICY_TEXT"));
        binding.termsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TERMS_AND_CONDITION"));
        binding.chatLiveTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LIVE_CHAT"));
        binding.contactTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        binding.helpTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAQ_TXT"));

        addToClickHandler(binding.aboutUsArea);
        addToClickHandler(binding.privacyArea);
        addToClickHandler(binding.termsCondArea);
        addToClickHandler(binding.chatLiveArea);
        addToClickHandler(binding.contactArea);
        addToClickHandler(binding.helpArea);

        if (generalFunc.getJsonValueStr("ENABLE_LIVE_CHAT", obj_userProfile).equalsIgnoreCase("Yes")) {
            binding.chatLiveArea.setVisibility(View.VISIBLE);
            binding.chatLiveLine.setVisibility(View.VISIBLE);
        } else {
            binding.chatLiveArea.setVisibility(View.GONE);
            binding.chatLiveLine.setVisibility(View.GONE);
        }
    }

    private Context getActContext() {
        return SupportActivity.this;
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());

        Bundle bn = new Bundle();

        int i = view.getId();
        if (i == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();

        } else if (i == binding.aboutUsArea.getId()) {
            bn.putString("staticpage", "1");
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);

        } else if (i == binding.privacyArea.getId()) {
            bn.putString("staticpage", "33");
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);

        } else if (i == binding.termsCondArea.getId()) {
            bn.putString("staticpage", "4");
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);

        } else if (i == binding.chatLiveArea.getId()) {
            MyUtils.openLiveChatActivity(getActContext(), generalFunc, obj_userProfile);

        } else if (i == binding.contactArea.getId()) {
            new ActUtils(getActContext()).startAct(ContactUsActivity.class);

        } else if (i == binding.helpArea.getId()) {
            new ActUtils(getActContext()).startAct(HelpActivity23Pro.class);

        }
    }
}