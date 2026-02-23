package com.act;

import android.content.Context;
import android.os.Bundle;
import android.text.InputType;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityBusinessSetupBinding;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

public class BusinessSetupActivity extends ParentActivity {


    private MButton skipbtn, nextbtn;

    private boolean emailEntered;
    private String required_str = "", error_email_str = "";
    private ActivityBusinessSetupBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_business_setup);

        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR_TXT");
        skipbtn = ((MaterialRippleLayout) findViewById(R.id.skipbtn)).getChildView();
        nextbtn = ((MaterialRippleLayout) findViewById(R.id.nextbtn)).getChildView();

        skipbtn.setId(Utils.generateViewId());
        nextbtn.setId(Utils.generateViewId());
        binding.emailBox.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS);
        addToClickHandler(binding.header.backImgView);
        if (generalFunc.isRTLmode()) {
            binding.header.backImgView.setRotation(180);
        }
        binding.header.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_SETUP_PROFILE_TXT"));
        binding.emailLabelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL"));
        binding.emailNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BUSINESS_EMAIL_FOR_BILL"));
        binding.emailBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_EMAIL_HINT"));
        skipbtn.setText(generalFunc.retrieveLangLBl("", "LBL_SKIP"));
        nextbtn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
        addToClickHandler(skipbtn);
        addToClickHandler(nextbtn);
        binding.emailBox.setText(generalFunc.getJsonValueStr("vEmail", obj_userProfile));

    }

    private Context getActContext() {
        return BusinessSetupActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.header.backImgView.getId()) {
            getOnBackPressedDispatcher().onBackPressed();
        } else if (i == skipbtn.getId()) {
            Bundle bn = new Bundle();
            bn.putString("iUserProfileMasterId", getIntent().getStringExtra("iUserProfileMasterId"));
            bn.putString("email", generalFunc.getJsonValueStr("vEmail", obj_userProfile));
            new ActUtils(getActContext()).startActWithData(SelectOrganizationActivity.class, bn);
        } else if (i == nextbtn.getId()) {
            emailEntered = Utils.checkText(binding.emailBox) ?
                    (generalFunc.isEmailValid(Utils.getText(binding.emailBox)) || Utils.setErrorFields(binding.emailBox, error_email_str))
                    : Utils.setErrorFields(binding.emailBox, required_str);

            if (!emailEntered) {
                return;
            }
            Bundle bn = new Bundle();
            bn.putString("iUserProfileMasterId", getIntent().getStringExtra("iUserProfileMasterId"));
            bn.putString("email", binding.emailBox.getText().toString().trim());
            new ActUtils(getActContext()).startActWithData(SelectOrganizationActivity.class, bn);
        }
    }

}