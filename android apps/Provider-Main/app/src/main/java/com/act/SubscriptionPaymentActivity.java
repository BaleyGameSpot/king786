package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivitySubscriptionPaymentBinding;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONObject;

import java.util.HashMap;

public class SubscriptionPaymentActivity extends ParentActivity {

    private ActivitySubscriptionPaymentBinding binding;
    private final int WALLET_MONEY_ADDED = 12789;

    private MButton subScribeBtn;
    private HashMap<String, String> planDetails;
    private ActivityResultLauncher<Intent> webViewPaymentActivity;
    String isRenew = "";

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_subscription_payment);

        getUserProfileJson(generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));

        planDetails = (HashMap<String, String>) getIntent().getSerializableExtra("PlanDetails");
        isRenew = getIntent().hasExtra("isRenew") ? getIntent().getStringExtra("isRenew") : "";

        initialization();
        setValues();
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        webViewPaymentActivity = registerForActivityResult(
                new ActivityResultContracts.StartActivityForResult(), result -> {
                    if (result.getResultCode() == Activity.RESULT_OK) {
                        redirectToThankYouScreen();
                    }
                });
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_PAYMENT_METHOD_TXT"));

        subScribeBtn = ((MaterialRippleLayout) binding.subScribeBtn).getChildView();
        subScribeBtn.setId(Utils.generateViewId());
        addToClickHandler(subScribeBtn);
        subScribeBtn.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_TXT"));

        addToClickHandler(binding.walletArea);
        addToClickHandler(binding.cardArea);

        if (generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).contains("Card")) {
            binding.cardArea.setVisibility(View.VISIBLE);
        } else {
            binding.cardArea.setVisibility(View.GONE);
        }
    }

    private void getUserProfileJson(JSONObject object) {
        obj_userProfile = object;

        binding.walletBalanceValTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("user_available_balance", obj_userProfile))));
    }

    @SuppressLint("SetTextI18n")
    private void setValues() {
        binding.subscriptionDesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_PAYMENT_METHOD_DESC_TXT"));
        binding.walletBalanceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USE_WALLET_BALANCE"));
        binding.cardPaymentTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CARD"));

        ///
        binding.noteText.setText(generalFunc.retrieveLangLBl("", "LBL_SUB_NOTE_TXT") + ": ");
        binding.noteDetailsText.setText(generalFunc.retrieveLangLBl("LBL_UPGRADE_NOTE_TXT", "LBL_UPGRADE_NOTE_TXT"));

        binding.planNameHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_PLAN_NAME") + ": ");
        binding.planNameTxt.setText(planDetails.get("vPlanName"));

        binding.planPriceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUB_PLAN_PRICE_TXT") + ": ");
        binding.planPriceTxt.setText(planDetails.get("fPlanPrice"));
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();

        } else if (i == subScribeBtn.getId()) {
            if (!binding.cbWallet.isChecked() && !binding.cardPaymentRadioBtn.isChecked()) {
                generalFunc.showMessage(binding.subscriptionDesTxt, generalFunc.retrieveLangLBl("", "LBL_SELECT_PAYMENT_METHOD_DESC_TXT"));
                return;
            }
            confirmSubscription();

        } else if (i == binding.walletArea.getId()) {
            binding.cbWallet.setChecked(!binding.cbWallet.isChecked());

        } else if (i == binding.cardArea.getId()) {
            binding.cardPaymentRadioBtn.setChecked(!binding.cardPaymentRadioBtn.isChecked());
        }
    }

    private void confirmSubscription() {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();

            } else {
                generateAlert.closeAlertBox();
                subscribePlan("");
            }
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_ENABLE_SUBSCRIPTION_NOTE"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
        generateAlert.showAlertBox();
    }

    private void subscribePlan(String isUpgrade) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "SubscribePlan");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("isCard", binding.cardPaymentRadioBtn.isChecked() ? "Yes" : "No");
        parameters.put("isWallet", binding.cbWallet.isChecked() ? "Yes" : "No");
        parameters.put("iDriverSubscriptionPlanId", planDetails.get("iDriverSubscriptionPlanId"));

        if (isUpgrade.equalsIgnoreCase("Yes")) {
            parameters.put("isUpgrade", isUpgrade);
        }


        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {

                String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObject);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {

                    String isUpgradeStr = generalFunc.getJsonValueStr("isUpgrade", responseStringObject);
                    String loadWebView = generalFunc.getJsonValueStr("loadWebView", responseStringObject);

                    if (isUpgradeStr.equalsIgnoreCase("Yes")) {
                        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                        generateAlert.setCancelable(false);
                        generateAlert.setBtnClickList(btn_id -> {
                            if (btn_id == 0) {
                                generateAlert.closeAlertBox();

                            } else {
                                generateAlert.closeAlertBox();
                                subscribePlan(isUpgradeStr);

                            }

                        });
                        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", message));
                        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
                        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
                        generateAlert.showAlertBox();

                    } else {
                        //LBL_LOW_WALLET_BAL_NOTE
                        if (loadWebView.equalsIgnoreCase("Yes")) {
                            Intent intent = new Intent(this, PaymentWebviewActivity.class);
                            Bundle bn = new Bundle();
                            bn.putString("url", message);
                            bn.putBoolean("handleResponse", true);
                            intent.putExtras(bn);
                            webViewPaymentActivity.launch(intent);

                        } else {
                            redirectToThankYouScreen();
                        }
                    }

                } else {

                    if (message.equalsIgnoreCase("LBL_LOW_WALLET_BAL_NOTE")) {
                        MyUtils.buildLowBalanceMessage(getActContext(), generalFunc, obj_userProfile, generalFunc.retrieveLangLBl("", message), () -> {
                            if (generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).equalsIgnoreCase("Cash")) {
                                new ActUtils(getActContext()).startAct(ContactUsActivity.class);

                            } else {
                                new ActUtils(getActContext()).startActForResult(MyWalletActivity.class, WALLET_MONEY_ADDED);
                            }
                        });

                    } else if (!generalFunc.getJsonValueStr("ADD_CARD_URL", responseStringObject).equalsIgnoreCase("")) {
                        Intent intent = new Intent(this, PaymentWebviewActivity.class);
                        Bundle bn = new Bundle();
                        bn.putString("url", generalFunc.getJsonValueStr("ADD_CARD_URL", responseStringObject));
                        bn.putBoolean("handleResponse", true);
                        intent.putExtras(bn);
                        webViewPaymentActivity.launch(intent);
                    } else {
                        generalFunc.showMessage(binding.subscriptionDesTxt, generalFunc.retrieveLangLBl("", message));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
        exeWebServer.setCancelAble(false);
    }

    private void redirectToThankYouScreen() {
        CustomDialog customDialog = new CustomDialog(this);
        customDialog.setDetails(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIBED_THANK_YOU_TXT"),
                generalFunc.retrieveLangLBl("", "LBL_SUBSCRIBED_DESCRIPTION_TXT"),
                generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), "",
                false, R.drawable.ic_correct, false, 2, true);
        customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
        customDialog.setRoundedViewBorderColor(R.color.white);
        customDialog.setImgStrokWidth(15);
        customDialog.setBtnRadius(10);
        customDialog.setIconTintColor(R.color.white);
        customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
        customDialog.setPositiveBtnTextColor(R.color.white);
        customDialog.createDialog();
        customDialog.setPositiveButtonClick(() -> {
            Intent returnIntent = new Intent();
            setResult(Activity.RESULT_OK, returnIntent);
            finish();
        });
        customDialog.setNegativeButtonClick(() -> {

        });
        customDialog.show();
    }

    private Context getActContext() {
        return SubscriptionPaymentActivity.this; // Must be context of activity not application
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if ((requestCode == WALLET_MONEY_ADDED || requestCode == Utils.CARD_PAYMENT_REQ_CODE)) {
            getUserProfileJson(generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        getWalletBalDetails();
    }

    private void getWalletBalDetails() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetMemberWalletBalance");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    try {
                        String MemberBalance = generalFunc.getJsonValueStr("MemberBalance", responseStringObject);

                        JSONObject object = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                        object.put("user_available_balance", MemberBalance);
                        generalFunc.storeData(Utils.USER_PROFILE_JSON, object.toString());

                        getUserProfileJson(object);
                    } catch (Exception e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                }
            }
        });
    }
}