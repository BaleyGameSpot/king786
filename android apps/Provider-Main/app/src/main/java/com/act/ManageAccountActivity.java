package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.providers.R;
import com.utils.Utils;
import com.view.MTextView;

public class ManageAccountActivity extends ParentActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_manage_account);

        ImageView backImgView = (ImageView) findViewById(R.id.backImgView);
        MTextView titleTxt = (MTextView) findViewById(R.id.titleTxt);

        LinearLayout personalDetailsArea = (LinearLayout) findViewById(R.id.personalDetailsArea);
        LinearLayout deleteAccountArea = (LinearLayout) findViewById(R.id.deleteAccountArea);

        MTextView txtPersonalTitle = (MTextView) findViewById(R.id.txtPersonalTitle);
        MTextView txtDeleteAccountTitle = (MTextView) findViewById(R.id.txtDeleteAccountTitle);

        addToClickHandler(personalDetailsArea);
        addToClickHandler(deleteAccountArea);
        addToClickHandler(backImgView);

        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_ACCOUNT_TXT"));
        txtPersonalTitle.setText(generalFunc.retrieveLangLBl("", "LBL_PERSONAL_DETAILS"));
        txtDeleteAccountTitle.setText(generalFunc.retrieveLangLBl("", "LBL_DELETE_ACCOUNT_TXT"));

        if (generalFunc.isRTLmode()) {
            ((ImageView) findViewById(R.id.arrowPersonalTitle)).setRotationY(180);
            ((ImageView) findViewById(R.id.arrowDeleteAccountTitle)).setRotationY(180);
            backImgView.setRotation(180);
        }
    }

    private Context getActContext() {
        return ManageAccountActivity.this;
    }

    @SuppressLint("NonConstantResourceId")
    public void onClick(View view) {

        int id = view.getId();
        if (id == R.id.backImgView) {
            ManageAccountActivity.super.onBackPressed();
        } else if (id == R.id.personalDetailsArea) {
            new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, new Bundle(), Utils.MY_PROFILE_REQ_CODE);
        } else if (id == R.id.deleteAccountArea) {
            new ActUtils(getActContext()).startAct(DeleteAccountActivity.class);
        }
    }
}