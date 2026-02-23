package com.act.giftcard;

import android.content.Context;
import android.os.Bundle;
import android.text.Editable;
import android.text.InputFilter;
import android.text.TextWatcher;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.act.MyWalletActivity;
import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityGiftcardRedeemBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.HashMap;

public class GiftCardRedeemActivity extends ParentActivity {

    private ActivityGiftcardRedeemBinding binding;
    private MTextView titleTxt;
    private String required_str = "";
    private MButton redeemBtn;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_giftcard_redeem);

        initViews();
        setLabel();
    }

    private void openDialog(String title, String message) {
        CustomDialog customDialog = new CustomDialog(this);
        customDialog.setDetails(title, message, generalFunc.retrieveLangLBl("Go to Wallet", "LBL_GIFT_CARD_CHECK_WALLET_TXT"), generalFunc.retrieveLangLBl("Ok", "LBL_OK"), false, R.drawable.ic_correct_2, false, 1, true);
        customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
        customDialog.setRoundedViewBorderColor(R.color.white);
        customDialog.setImgStrokWidth(15);
        customDialog.setBtnRadius(10);
        customDialog.setIconTintColor(R.color.white);
        customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
        customDialog.setPositiveBtnTextColor(R.color.white);
        customDialog.createDialog();
        customDialog.setNegativeButtonClick(this::finish);
        customDialog.setPositiveButtonClick(this::moveToWalletActivity);
        customDialog.show();
    }

    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        titleTxt = findViewById(R.id.titleTxt);

        redeemBtn = ((MaterialRippleLayout) findViewById(R.id.redeemBtn)).getChildView();
        redeemBtn.setId(Utils.generateViewId());
        addToClickHandler(redeemBtn);
    }

    private Context getActContext() {
        return GiftCardRedeemActivity.this;
    }

    private void setLabel() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GIFT_CARD_REDEEM_GIFT_CARD_TXT"));
        redeemBtn.setText(generalFunc.retrieveLangLBl("", "LBL_GIFT_CARD_REDEEM_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");

        binding.voucherHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GIFT_CARD_VOUCHER_CODE_TXT"));
        binding.voucherCodeEditBox.setHint(generalFunc.retrieveLangLBl("", "LBL_GIFT_CARD_ENTER_CODE_TXT"));
        binding.voucherCodeEditBox.setFilters(new InputFilter[]{new InputFilter.AllCaps()});
        binding.enterOtpError.setVisibility(View.GONE);
        binding.voucherCodeHelperTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GIFT_CARD_ENTER_CODE_TO_CLAIM_TXT"));
        binding.voucherCodeEditBox.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence charSequence, int i, int i1, int i2) {

            }

            @Override
            public void onTextChanged(CharSequence charSequence, int i, int i1, int i2) {
                binding.enterOtpError.setVisibility(View.GONE);
            }

            @Override
            public void afterTextChanged(Editable editable) {

            }
        });
    }

    private void checkValues() {
        boolean VoucherEntered = Utils.checkText(binding.voucherCodeEditBox);
        if (!VoucherEntered) {
            binding.enterOtpError.setVisibility(View.VISIBLE);
            binding.enterOtpError.setText(required_str);
            return;
        }
        RedeemGiftCard();
    }

    private void RedeemGiftCard() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "RedeemGiftCard");
        parameters.put("vGiftCardCode", binding.voucherCodeEditBox.getText().toString().trim());

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    openDialog(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)), generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void moveToWalletActivity() {
        new ActUtils(this).startAct(MyWalletActivity.class);
        finish();
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == redeemBtn.getId()) {
            checkValues();
        }
    }
}