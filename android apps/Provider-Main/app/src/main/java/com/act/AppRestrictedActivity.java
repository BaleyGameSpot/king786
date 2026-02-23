package com.act;

import android.os.Bundle;
import android.view.View;
import android.widget.LinearLayout;

import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.MyApp;
import com.buddyverse.providers.BuildConfig;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityAppRestrictedBinding;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONObject;

public class AppRestrictedActivity extends ParentActivity {

    private ActivityAppRestrictedBinding binding;
    private MButton tryAgainBtn, okBtn;
    private JSONObject responseObj;
    private boolean isSessionTimeOut = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_app_restricted);

        responseObj = generalFunc.getJsonObject(getIntent().getStringExtra("RESTRICT_APP"));

        initialization();
        setLabel();
    }

    private void setLabel() {
        binding.appRestrictedTitle.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("message_title", responseObj)));
        binding.appRestrictedSubTitle.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("message", responseObj)));

        tryAgainBtn.setText(generalFunc.retrieveLangLBl("Try Again", "LBL_TRY_AGAIN_BTN_TXT"));
        okBtn.setText(generalFunc.retrieveLangLBl("Ok", "LBL_OK"));

        // TODO: 08-02-2023 : Session Expired
        if (generalFunc.getJsonValueStr("isSessionExpired", responseObj).equalsIgnoreCase("Yes")) {
            isSessionTimeOut = true;
            generalFunc.logOutUser(MyApp.getInstance());

            binding.contactUsImg.setVisibility(View.GONE);
            binding.tryAgainBtn.setVisibility(View.GONE);
            binding.appRestrictedImg.setImageDrawable(ContextCompat.getDrawable(this, R.drawable.ic_app_restricted_session_expired));
        }

        // TODO: 08-02-2023 : Account Inactive OR Deleted
        if (generalFunc.getJsonValueStr("isAccountInactive", responseObj).equalsIgnoreCase("Yes")
                || generalFunc.getJsonValueStr("isAccountDeleted", responseObj).equalsIgnoreCase("Yes")) {
            isSessionTimeOut = true;
            binding.appRestrictedImg.setImageDrawable(ContextCompat.getDrawable(this, R.drawable.ic_app_restricted_account_inactive_or_delete));
        }

        // TODO: 08-02-2023 : App Update
        if (generalFunc.getJsonValueStr("isAppUpdate", responseObj).equalsIgnoreCase("Yes")) {
            binding.contactUsImg.setVisibility(View.GONE);
            okBtn.setText(generalFunc.retrieveLangLBl("Update", "LBL_UPDATE_GENERAL"));
            binding.appRestrictedImg.setImageDrawable(ContextCompat.getDrawable(this, R.drawable.ic_app_restricted_update_available));
        }

        // TODO: 08-02-2023 : Country Restrict
        if (generalFunc.getJsonValueStr("isAppRestrict", responseObj).equalsIgnoreCase("Yes")) {
            binding.contactUsImg.setVisibility(View.GONE);
            binding.appRestrictedImg.setImageDrawable(ContextCompat.getDrawable(this, R.drawable.ic_app_restricted_country));
        }

        // TODO: 08-02-2023 : Maintenance
        if (generalFunc.getJsonValueStr("isAppMaintenance", responseObj).equalsIgnoreCase("Yes") || responseObj == null) {
            binding.appRestrictedImg.setImageDrawable(ContextCompat.getDrawable(this, R.drawable.ic_app_restricted_under_maintenance));

            if (!Utils.checkText(Utils.getText(binding.appRestrictedTitle))) {
                binding.appRestrictedTitle.setText(generalFunc.retrieveLangLBl("Under Maintenance", "LBL_MAINTENANCE_HEADER_MSG"));
            }
            if (!Utils.checkText(Utils.getText(binding.appRestrictedSubTitle))) {
                binding.appRestrictedSubTitle.setText(generalFunc.retrieveLangLBl("Site is under maintenance. Please stay with us and check back later.", "LBL_MAINTENANCE_CONTENT_MSG"));
            }
        }
    }

    private void initialization() {
        int sWidth = (int) Utils.getScreenPixelWidth(this);
        sWidth = sWidth - getResources().getDimensionPixelSize(R.dimen._30sdp);

        LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) binding.appRestrictedImg.getLayoutParams();
        mParams.width = sWidth;
        mParams.height = (int) (sWidth / 1.33333333333);
        binding.appRestrictedImg.setLayoutParams(mParams);

        addToClickHandler(binding.contactUsImg);

        tryAgainBtn = ((MaterialRippleLayout) binding.tryAgainBtn).getChildView();
        tryAgainBtn.setId(Utils.generateViewId());
        addToClickHandler(tryAgainBtn);

        okBtn = ((MaterialRippleLayout) binding.okBtn).getChildView();
        okBtn.setId(Utils.generateViewId());
        addToClickHandler(okBtn);
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.contactUsImg.getId()) {
            Bundle bundle = new Bundle();
            bundle.putString("iMemberId", generalFunc.getJsonValueStr("iMemberId", generalFunc.getJsonObject("EXTRA_DATA", responseObj)));
            new ActUtils(this).startActWithData(ContactUsActivity.class, bundle);

        } else if (i == tryAgainBtn.getId()) {
            generalFunc.restartApp();

        } else if (i == okBtn.getId()) {
            if (generalFunc.getJsonValueStr("isAppUpdate", responseObj).equalsIgnoreCase("Yes")) {
                boolean isSuccessfulOpen = new ActUtils(this).openURL("market://details?id=" + BuildConfig.APPLICATION_ID);
                if (!isSuccessfulOpen) {
                    new ActUtils(this).openURL("http://play.google.com/store/apps/details?id=" + BuildConfig.APPLICATION_ID);
                }
            } else if (isSessionTimeOut) {
                MyApp.getInstance().forceLogoutRemoveData();
            } else {
                MyApp.getInstance().getCurrentAct().finishAffinity();
            }
        }
    }
}