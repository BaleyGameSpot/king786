package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.InputType;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.ImageView;

import androidx.annotation.Nullable;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

public class NameStageActivity extends ParentActivity {

    MaterialEditText fNameBox;
    MaterialEditText lNameBox;
    MTextView titleTxt;
    ImageView backBtn, nextBtn;
    String required_str = "";
    View contentArea;
    static final int NEXT_STAGE = 002;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_name_stage);
        init();
    }

    private void init() {

        backBtn = findViewById(R.id.backBtn);
        nextBtn = findViewById(R.id.nextBtn);
        titleTxt = findViewById(R.id.titleTxt);
        fNameBox = (MaterialEditText) findViewById(R.id.fNameBox);
        lNameBox = (MaterialEditText) findViewById(R.id.lNameBox);
        contentArea = findViewById(R.id.contentArea);
        fNameBox.setInputType(InputType.TYPE_CLASS_TEXT);
        lNameBox.setInputType(InputType.TYPE_CLASS_TEXT);
        fNameBox.setImeOptions(EditorInfo.IME_ACTION_NEXT);
        fNameBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_FIRST_NAME_HEADER_TXT"));
        lNameBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_LAST_NAME_HEADER_TXT"));
        titleTxt.setText(generalFunc.retrieveLangLBl("what's your name ?", "LBL_WHAT_YOUR_NAME"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        addToClickHandler(backBtn);
        addToClickHandler(nextBtn);
        if (generalFunc.isRTLmode()) {
            nextBtn.setRotation(180);
            backBtn.setRotation(180);
        }
        manageAnimation(contentArea);

    }

    @Override
    public void onBackPressed() {
        new ActUtils(getActContext()).setOkResult();
        super.onBackPressed();
    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == backBtn.getId()) {
            onBackPressed();
        } else if (i == nextBtn.getId()) {
            boolean fNameEntered = Utils.checkText(fNameBox) ? true : Utils.setErrorFields(fNameBox, required_str);
            boolean lNameEntered = Utils.checkText(lNameBox) ? true : Utils.setErrorFields(lNameBox, required_str);


            if (fNameEntered == false || lNameEntered == false) {
                return;
            }

            Bundle bn = new Bundle();
            bn.putString("mob", "+" + getIntent().getStringExtra("mob"));
            bn.putString("vPhoneCode", getIntent().getStringExtra("vPhoneCode"));
            bn.putString("vCountryCode", getIntent().getStringExtra("vCountryCode"));
            bn.putString("vmobile", getIntent().getStringExtra("vmobile"));
            bn.putString("vFirstName", Utils.getText(fNameBox));
            bn.putString("vLastName", Utils.getText(lNameBox));
            bn.putString("vPassword", getIntent().getStringExtra("vPassword"));
            new ActUtils(getActContext()).startActForResult(EmailStageActivity.class, bn, NEXT_STAGE);
        }

    }


    private Context getActContext() {
        return NameStageActivity.this;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable @org.jetbrains.annotations.Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        manageAnimation(contentArea);
    }
}