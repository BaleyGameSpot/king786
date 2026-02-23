package com.act;

import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityContactUsBinding;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.HashMap;

public class ContactUsActivity extends ParentActivity {

    private ActivityContactUsBinding binding;
    private MButton sendBtn;
    private String required_str = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_contact_us);

        initializeUi();
    }

    private void initializeUi() {
        addToClickHandler(binding.toolbarInclude.toolBackIV);
        binding.toolbarInclude.toolTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_HEADER_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");

        binding.subHeaderTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_SUBHEADER_TXT"));
        binding.detailTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_DETAIL_TXT"));

        binding.subjectBoxTitle.setText(generalFunc.retrieveLangLBl("", "LBL_RES_TO_CONTACT"));
        binding.subjectBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ADD_SUBJECT_HINT_CONTACT_TXT"));

        binding.contentBoxTitle.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_QUERY"));
        binding.contentBox.setHint(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_WRITE_EMAIL_TXT"));

        MyUtils.editBoxMultiLine(binding.contentBox);

        binding.subjectBox.setOnFocusChangeListener((view, hasFocus) -> {
            if (hasFocus) {
                binding.subjectBoxError.setVisibility(View.INVISIBLE);
                binding.contentBoxError.setVisibility(View.INVISIBLE);
            }
        });

        binding.contentBox.setOnFocusChangeListener((view, hasFocus) -> {
            if (hasFocus) {
                binding.subjectBoxError.setVisibility(View.INVISIBLE);
                binding.contentBoxError.setVisibility(View.INVISIBLE);
            }
        });

        sendBtn = ((MaterialRippleLayout) binding.sendBtn).getChildView();
        sendBtn.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_QUERY_BTN_TXT"));
        sendBtn.setId(Utils.generateViewId());
        addToClickHandler(sendBtn);
    }

    private void submitQuery() {
        boolean subjectEntered = Utils.checkText(binding.subjectBox);
        boolean contentEntered = Utils.checkText(binding.contentBox);

        if (!subjectEntered || !contentEntered) {
            binding.subjectBoxError.setText(required_str);
            binding.subjectBoxError.setVisibility(!subjectEntered ? View.VISIBLE : View.INVISIBLE);

            binding.contentBoxError.setText(required_str);
            binding.contentBoxError.setVisibility(!contentEntered ? View.VISIBLE : View.INVISIBLE);
            return;
        } else {
            binding.subjectBoxError.setVisibility(View.INVISIBLE);
            binding.contentBoxError.setVisibility(View.INVISIBLE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "sendContactQuery");
        parameters.put("UserType", Utils.app_type);
        if (getIntent().hasExtra("iMemberId") && Utils.checkText(getIntent().getStringExtra("iMemberId"))) {
            parameters.put("UserId", getIntent().getStringExtra("iMemberId"));
        } else {
            parameters.put("UserId", generalFunc.getMemberId());
        }

        parameters.put("subject", Utils.getText(binding.subjectBox));
        parameters.put("message", Utils.getText(binding.contentBox));

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    binding.subjectBox.setText("");
                    binding.contentBox.setText("");
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    public void onClick(View view) {
        Utils.hideKeyboard(this);
        int i = view.getId();

        if (i == binding.toolbarInclude.toolBackIV.getId()) {
            getOnBackPressedDispatcher().onBackPressed();

        } else if (i == sendBtn.getId()) {
            submitQuery();
        }
    }
}