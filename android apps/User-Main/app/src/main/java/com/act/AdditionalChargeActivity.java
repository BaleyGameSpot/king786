package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityAdditionalChargeBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.HashMap;

public class AdditionalChargeActivity extends ParentActivity {

    private MButton submitBtn, skipBtn;
    public HashMap<String, String> tripDetail = new HashMap<>();
    private boolean isTollOrOtherCharges = false;
    private boolean isUFX = false, isTollEnable = false, isOtherChargesEnable = false;
    private ActivityAdditionalChargeBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_additional_charge);
        tripDetail = (HashMap<String, String>) getIntent().getSerializableExtra("TripDetail");

        initViews();
    }

    public Context getActContext() {
        return AdditionalChargeActivity.this;
    }


    @SuppressLint("SetTextI18n")
    public void initViews() {


        if (getIntent().getExtras().getString("eType") != null && !getIntent().getExtras().getString("eType").equals("")) {
            isUFX = getIntent().getExtras().getString("eType").equalsIgnoreCase("UberX");
        }
        isTollEnable = generalFunc.getJsonValueStr("ENABLE_MANUAL_TOLL_FEATURE", obj_userProfile).equalsIgnoreCase("Yes");
        isOtherChargesEnable = generalFunc.getJsonValueStr("ENABLE_OTHER_CHARGES_FEATURE", obj_userProfile).equalsIgnoreCase("Yes");
        submitBtn = ((MaterialRippleLayout) findViewById(R.id.submitBtn)).getChildView();
        skipBtn = ((MaterialRippleLayout) findViewById(R.id.skipBtn)).getChildView();


        submitBtn.setId(Utils.generateViewId());
        skipBtn.setId(Utils.generateViewId());

        addToClickHandler(submitBtn);
        addToClickHandler(skipBtn);
        addToClickHandler(binding.toolbarInclude.backImgView);
        setLabel();

        String fMaterialFee = getIntent().getStringExtra("fMaterialFee");
        String isFromToll = getIntent().getStringExtra("isFromToll");
        if (Utils.checkText(isFromToll)) {
            binding.toolbarInclude.backImgView.setVisibility(View.GONE);
        }
        String fMiscFee = getIntent().getStringExtra("fMiscFee");
        String fDriverDiscount = getIntent().getStringExtra("fDriverDiscount");
        String serviceCost = getIntent().getStringExtra("serviceCost");
        String fOtherAmount = getIntent().getStringExtra("fOtherCharges");
        String fTollAmount = getIntent().getStringExtra("fTollPrice");
        String totalAmount = getIntent().getStringExtra("totalAmount");
        String vConfirmationCode = getIntent().getStringExtra("vConfirmationCode");
        String CurrencySymbol = Utils.checkText(getIntent().getStringExtra("CurrencySymbol")) ? getIntent().getStringExtra("CurrencySymbol") : generalFunc.getJsonValueStr("CurrencySymbol", obj_userProfile);
        binding.verificationCodeVTxt.setText("" + vConfirmationCode);

        binding.timatrialfeeVTxt.setText(Utils.checkText(fMaterialFee) ? fDriverDiscount : "");
        binding.miscfeeVTxt.setText(Utils.checkText(fMiscFee) ? fDriverDiscount : "");
        binding.discountVTxt.setText(Utils.checkText(fDriverDiscount) ? fDriverDiscount : "");
        binding.currentchargeVTxt.setText(Utils.checkText(serviceCost) ? serviceCost : "");
        binding.finalvalTxt.setText(Utils.checkText(totalAmount) ? totalAmount : "");

        String eApproveRequestSentByDriver = getIntent().getStringExtra("eApproveRequestSentByDriver");
        if (Utils.checkText(eApproveRequestSentByDriver) && eApproveRequestSentByDriver.equalsIgnoreCase("Yes") && !Utils.checkText(vConfirmationCode)) {
            binding.btnArea.setVisibility(View.VISIBLE);
            binding.confirmationCodeArea.setVisibility(View.GONE);
            binding.noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPROVE_DECLINE_CHARGES_BY_USER_TXT"));

        } else {
            binding.btnArea.setVisibility(View.GONE);
            binding.confirmationCodeArea.setVisibility(View.VISIBLE);
            binding.noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GIVE_CHARGES_CONFIRMATION_CODE_MSG"));

        }

        if (isUFX) {
            binding.finalHTxt.setText(generalFunc.retrieveLangLBl("FINAL TOTAL", "LBL_FINAL_TOTAL_HINT"));
        } else {
            if (isTollEnable) {
                isTollOrOtherCharges = true;
                binding.tollFeelayout.setVisibility(View.VISIBLE);
            } else {
                binding.tollFeelayout.setVisibility(View.GONE);
            }
            if (isOtherChargesEnable) {
                isTollOrOtherCharges = true;
                binding.otherFeelayout.setVisibility(View.VISIBLE);
            } else {
                binding.otherFeelayout.setVisibility(View.GONE);
            }
            binding.serviceCostShowArea.setVisibility(View.GONE);
            binding.materialFeeLayout.setVisibility(View.GONE);
            binding.miscFeelayout.setVisibility(View.GONE);
            binding.discountArea.setVisibility(View.GONE);

            binding.finalHTxt.setText(generalFunc.retrieveLangLBl("Extra Charges", "LBL_TOTAL_EXTRA_CHARGES_TXT"));
        }

        binding.otherAmountVTxt.setText(Utils.checkText(fOtherAmount) ? fOtherAmount : "");
        binding.tollvalTxt.setText(Utils.checkText(fTollAmount) ? fTollAmount : "");

    }

    @SuppressLint("SetTextI18n")
    public void setLabel() {
        binding.matrialfeeHTxt.setText(generalFunc.retrieveLangLBl("Material fee", "LBL_MATERIAL_FEE"));
        binding.miscfeeHTxt.setText(generalFunc.retrieveLangLBl("Misc fee", "LBL_MISC_FEE"));
        binding.discountHTxt.setText(generalFunc.retrieveLangLBl("Provider Discount", "LBL_PROVIDER_DISCOUNT"));
        binding.finalHTxt.setText(generalFunc.retrieveLangLBl("FINAL TOTAL", "LBL_FINAL_TOTAL_HINT"));
        binding.verificationCodeTxt.setText(generalFunc.retrieveLangLBl("Confirmation Code", "LBL_CONFIRMATION_CODE"));
        binding.tollHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOLL_CHARGES"));
        binding.otherAmountHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_CHARGES"));
        binding.currentchargeHTxt.setText(generalFunc.retrieveLangLBl("Service Cost", "LBL_SERVICE_COST"));
        binding.noteLbl.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE") + ":-");
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_CHARGES"));
        submitBtn.setText(generalFunc.retrieveLangLBl("", "LBL_APPROVE_TXT"));
        skipBtn.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT"));

    }

    public void pubNubMsgArrived(final String message, final Boolean ishow) {

        runOnUiThread(() -> {

            String msgType = generalFunc.getJsonValue("MsgType", message);
            String iDriverId = generalFunc.getJsonValue("iDriverId", message);
            if (tripDetail.get("iDriverId").equals(iDriverId)) {
                if (msgType.equals("VerifyCharges")) {
                    Intent returnIntent = new Intent();
                    setResult(Activity.RESULT_OK, returnIntent);
                    finish();
                } else {
                    onGcmMessageArrived(message, ishow);
                }


            }


        });
    }

    public void onGcmMessageArrived(final String message, boolean ishow) {

        String driverMsg = generalFunc.getJsonValue("Message", message);

        if (driverMsg.equals("VerifyCharges")) {
            Intent returnIntent = new Intent();
            setResult(Activity.RESULT_OK, returnIntent);
            finish();
        } else if (driverMsg.equals("TripEnd")) {


            GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
            alertBox.setContentMessage("", generalFunc.getJsonValue("vTitle", message));
            alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));


            alertBox.setCancelable(false);
            alertBox.setBtnClickList(btn_id -> {
                Intent returnIntent = new Intent();
                setResult(Activity.RESULT_OK, returnIntent);
                finish();
            });
            alertBox.showAlertBox();


        }
    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == skipBtn.getId()) {
            sendChargeVerificationCode("No");
        } else if (i == submitBtn.getId()) {
            sendChargeVerificationCode("Yes");
        } else if (i == binding.toolbarInclude.backImgView.getId()) {
            getOnBackPressedDispatcher().onBackPressed();
        }
    }


    private void sendChargeVerificationCode(String eApproveByUser) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "sendChargeVerificationCode");
        parameters.put("TripId", tripDetail.get("iTripId"));
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("eApproveByUser", eApproveByUser);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> verificationResponse(responseString, eApproveByUser));

    }


    private void verificationResponse(String responseString, String eApproveByUser) {

        if (responseString != null && !responseString.equals("")) {

            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), i -> {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (isTollOrOtherCharges) {
                        if (eApproveByUser.equalsIgnoreCase("No") || generalFunc.getJsonValue("MSG_TYPE", responseString).equalsIgnoreCase("DO_RESTART")) {
                            MyApp.getInstance().restartWithGetDataApp();
                        }
                    } else {
                        binding.toolbarInclude.backImgView.performClick();
                    }
                }

            });

        }
    }


}
