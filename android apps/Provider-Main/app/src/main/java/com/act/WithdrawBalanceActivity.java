package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.os.Bundle;
import android.os.Handler;
import android.text.InputFilter;
import android.text.InputType;
import android.text.Spannable;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.method.LinkMovementMethod;
import android.text.style.ClickableSpan;
import android.text.style.ImageSpan;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.general.files.TrendyDialog;
import com.google.android.material.snackbar.Snackbar;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.AutoFitEditText;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.HashMap;
import java.util.Locale;

public class WithdrawBalanceActivity extends ParentActivity {

    MTextView titleTxt, withdrawTitle, accountdetailText;
    RelativeLayout readFAQ, support, buttonlayout;
    ErrorView errorView;

    ImageView backImgView, readFAQimage, supportimage, helipnonwithdrawamount, helpwithdrawamount, addaccountImage;
    private MButton withdrawnow;

    String WITHDRAWABLE_AMOUNT, NON_WITHDRAWABLE_AMOUNT, ACCOUNT_NO, MemberBalance;
    String ORIG_WITHDRAWABLE_AMOUNT = "", ORIG_NON_WITHDRAWABLE_AMOUNT = "", vAccountNumber = "";

    MTextView walletamountTxt, withdrawAmount, nonwithdrawAmount, accountHTxt;
    MTextView needhelptext, withdrawAmountTitle, nonwithdrawAmountTitle;
    AutoFitEditText autofitEditText;
    boolean accountDetailsAdded;
    LinearLayout withdrawBalArea;

    GenerateAlertBox currentAlertBox;
    String LBL_BTN_OK_TXT, LBL_CANCEL_TXT, LBL_RETRY_TXT, LBL_TRY_AGAIN_TXT;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_withdraw_balance);

        initView();
        setLables();
    }

    private void initView() {
        errorView = findViewById(R.id.errorView);
        titleTxt = findViewById(R.id.titleTxt);
        withdrawTitle = findViewById(R.id.withdrawTitle);
        support = findViewById(R.id.support);
        readFAQ = findViewById(R.id.readFAQ);
        withdrawBalArea = findViewById(R.id.withdrawBalArea);
        if (!generalFunc.getJsonValueStr("ENABLE_WALLET_WITHDRAWAL_REQUEST_RESTRICTION", obj_userProfile).equalsIgnoreCase("Yes")) {
            withdrawBalArea.setVisibility(View.GONE);
        }

        supportimage = findViewById(R.id.supportimage);
        readFAQimage = findViewById(R.id.readFAQimage);
        readFAQimage = findViewById(R.id.readFAQimage);
        helipnonwithdrawamount = findViewById(R.id.helipnonwithdrawamount);
        helpwithdrawamount = findViewById(R.id.helpwithdrawamount);
        addaccountImage = findViewById(R.id.addaccountImage);

        backImgView = findViewById(R.id.backImgView);
        nonwithdrawAmountTitle = findViewById(R.id.nonwithdrawAmountTitle);
        withdrawAmountTitle = findViewById(R.id.withdrawAmountTitle);
        accountdetailText = findViewById(R.id.accountdetailText);
        buttonlayout = findViewById(R.id.buttonlayout);

        walletamountTxt = findViewById(R.id.walletamountTxt);
        withdrawAmount = findViewById(R.id.withdrawAmount);
        nonwithdrawAmount = findViewById(R.id.nonwithdrawAmount);
        accountHTxt = findViewById(R.id.accountHTxt);
        needhelptext = findViewById(R.id.needhelptext);


        addToClickHandler(backImgView);
        addToClickHandler(readFAQ);
        addToClickHandler(support);
        buttonlayout.setId(Utils.generateViewId());
        addToClickHandler(buttonlayout);

        withdrawnow = ((MaterialRippleLayout) findViewById(R.id.withdrawnow)).getChildView();
        withdrawnow.setId(Utils.generateViewId());
        addToClickHandler(withdrawnow);
        autofitEditText = findViewById(R.id.autofitEditText);

        WITHDRAWABLE_AMOUNT = getIntent().getStringExtra("WITHDRAWABLE_AMOUNT");
        NON_WITHDRAWABLE_AMOUNT = getIntent().getStringExtra("NON_WITHDRAWABLE_AMOUNT");
        ACCOUNT_NO = getIntent().getStringExtra("ACCOUNT_NO");
        MemberBalance = getIntent().getStringExtra("MemberBalance");

        ORIG_WITHDRAWABLE_AMOUNT = getIntent().getStringExtra("ORIG_WITHDRAWABLE_AMOUNT");
        ORIG_NON_WITHDRAWABLE_AMOUNT = getIntent().getStringExtra("ORIG_NON_WITHDRAWABLE_AMOUNT");
        vAccountNumber = getIntent().getStringExtra("vAccountNumber");

        walletamountTxt.setText(generalFunc.convertNumberWithRTL(MemberBalance));
        withdrawAmount.setText(generalFunc.convertNumberWithRTL(WITHDRAWABLE_AMOUNT));
        nonwithdrawAmount.setText(generalFunc.convertNumberWithRTL(NON_WITHDRAWABLE_AMOUNT));
        accountHTxt.setText(ACCOUNT_NO);

        LBL_RETRY_TXT = generalFunc.retrieveLangLBl("Retry", "LBL_RETRY_TXT");
        LBL_CANCEL_TXT = generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT");
        LBL_BTN_OK_TXT = generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT");
        LBL_TRY_AGAIN_TXT = generalFunc.retrieveLangLBl("Please try again.", "LBL_TRY_AGAIN_TXT");
    }

    @SuppressLint("SetTextI18n")
    private void setLables() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WITHDRAW_REQUEST"));
        withdrawTitle.setText(generalFunc.retrieveLangLBl("", "LBL_WITHDRAW_AMT"));
        withdrawnow.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_REQUEST"));
        needhelptext.setText(generalFunc.retrieveLangLBl("", "LBL_NEED_HELP"));


        supportimage.setBackground(getRoundBG(String.format(Locale.ENGLISH, "#%06X", 0xFFFFFF & getResources().getColor(R.color.appThemeColor_1)), 100, "#CCCACA"));
        readFAQimage.setBackground(getRoundBG("#ffa60a", 100, "#CCCACA"));
        autofitEditText.setBackground(getRoundBG("#ffffff", 8, "#CCCACA"));
        autofitEditText.setHint("0.00");

        autofitEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        autofitEditText.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        autofitEditText.addTextChangedListener(null, false);

        accountdetailText.setText(generalFunc.retrieveLangLBl("", "LBL_BANK_DETAILS_TXT"));
        withdrawAmountTitle.setText(generalFunc.retrieveLangLBl("", "LBL_WITHDRAWABLE_BAL") + " ");
        ((MTextView) findViewById(R.id.yourBalTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_BALANCE"));

        nonwithdrawAmountTitle.setText(generalFunc.retrieveLangLBl("", "LBL_NON_WITHDRAWABLE_BAL") + " ");

        ImageSpan imageSpan = new ImageSpan(this, (R.drawable.ic_question_circle), ImageSpan.ALIGN_BASELINE); //Find your drawable.
        SpannableString spannableString = new SpannableString(nonwithdrawAmountTitle.getText()); //Set text of SpannableString from TextView
        spannableString.setSpan(imageSpan, nonwithdrawAmountTitle.getText().length() - 1, nonwithdrawAmountTitle.getText().length(), Spanned.SPAN_EXCLUSIVE_EXCLUSIVE); //Add image at end of string

        spannableString.setSpan(new ClickableSpan() {
            @Override
            public void onClick(@NonNull View widget) {

                TrendyDialog customDialog = new TrendyDialog(WithdrawBalanceActivity.this);
                customDialog.setDetails("",
                        generalFunc.retrieveLangLBl("", "LBL_NOTE_NON_WITHDRAWABLE_BAL"), LBL_BTN_OK_TXT, true,
                        ContextCompat.getDrawable(getActContext(), R.drawable.ic_walletnonwithdraw));
                customDialog.showDialog();
                customDialog.setPositiveBtnClick(() -> {
                });
            }
        }, nonwithdrawAmountTitle.getText().length() - 1, nonwithdrawAmountTitle.getText().length(), Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);
        nonwithdrawAmountTitle.setText(spannableString); //Assign string to TextView (Use TextFormatted for Spannables)
        nonwithdrawAmountTitle.setMovementMethod(LinkMovementMethod.getInstance());

        ImageSpan imageSpan1 = new ImageSpan(this, (R.drawable.ic_question_circle), ImageSpan.ALIGN_BASELINE); //Find your drawable.
        SpannableString spannableString1 = new SpannableString(withdrawAmountTitle.getText()); //Set text of SpannableString from TextView
        spannableString1.setSpan(imageSpan1, withdrawAmountTitle.getText().length() - 1, withdrawAmountTitle.getText().length(), Spanned.SPAN_EXCLUSIVE_EXCLUSIVE); //Add image at end of string

        spannableString1.setSpan(new ClickableSpan() {
            @Override
            public void onClick(@NonNull View widget) {
                TrendyDialog customDialog = new TrendyDialog(WithdrawBalanceActivity.this);
                customDialog.setDetails("",
                        generalFunc.retrieveLangLBl("", "LBL_NOTE_WITHDRAWABLE_BAL"), LBL_BTN_OK_TXT, true,
                        ContextCompat.getDrawable(getActContext(), R.drawable.ic_walletwithdraw));
                customDialog.showDialog();
                customDialog.setPositiveBtnClick(() -> {
                });
            }
        }, withdrawAmountTitle.getText().length() - 1, withdrawAmountTitle.getText().length(), Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);
        withdrawAmountTitle.setText(spannableString1); //Assign string to TextView (Use TextFormatted for Spannables)
        withdrawAmountTitle.setMovementMethod(LinkMovementMethod.getInstance());

        if (generalFunc.isRTLmode()) {
            withdrawAmountTitle.setTextDirection(View.TEXT_DIRECTION_RTL);
            nonwithdrawAmountTitle.setTextDirection(View.TEXT_DIRECTION_RTL);
            backImgView.setRotation(180);
        }
        if (vAccountNumber.equalsIgnoreCase("no")) {
            accountDetailsAdded = false;
            addaccountImage.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_pic_add));
        } else {
            accountDetailsAdded = true;
            addaccountImage.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_edit));
        }
    }

    private GradientDrawable getRoundBG(String color, int radius, String stroke) {
        int strokeWidth = 2;
        int strokeColor = Color.parseColor(stroke);
        GradientDrawable gD = new GradientDrawable();
        gD.setColor(Color.parseColor(color));
        gD.setShape(GradientDrawable.RECTANGLE);
        gD.setCornerRadius(radius);
        gD.setStroke(strokeWidth, strokeColor);
        return gD;
    }

    private Context getActContext() {
        return WithdrawBalanceActivity.this;
    }

    public void onClick(View view) {
        Utils.hideKeyboard(WithdrawBalanceActivity.this);
        int i = view.getId();
        if (i == buttonlayout.getId()) {
            Bundle bn = new Bundle();
            bn.putString("from", "walletWithDraw");
            new ActUtils(getActContext()).startActForResult(BankDetailActivity.class, bn, 123);
        }
        if (i == withdrawnow.getId()) {
            if (Utils.checkText(autofitEditText) && GeneralFunctions.parseDoubleValue(0, autofitEditText.getTxt()) > 0) {
                double amount = GeneralFunctions.parseDoubleValue(0, autofitEditText.getTxt());
                if (amount > 0) {
                    if (amount > Double.parseDouble(ORIG_WITHDRAWABLE_AMOUNT) && generalFunc.getJsonValueStr("ENABLE_WALLET_WITHDRAWAL_REQUEST_RESTRICTION", obj_userProfile).equalsIgnoreCase("Yes")) {
                        Snackbar.make(autofitEditText, generalFunc.retrieveLangLBl("", "LBL_RESTRICT_WITHDRAW_AMT_NOTE"), Snackbar.LENGTH_SHORT).show();
                        autofitEditText.setBackground(getRoundBG("#ffffff", 8, "#F44336"));
                        Handler handler = new Handler();
                        handler.postDelayed(() -> autofitEditText.setBackground(getRoundBG("#ffffff", 8, "#CCCACA")), 2000);
                    } else if (!accountDetailsAdded) {
                        generalFunc.showMessage(withdrawnow, generalFunc.retrieveLangLBl("", "LBL_ADD_BANK_DETAIL_MSG"));
                    } else {
                        withDrawlRequest(autofitEditText.getTxt());
                    }
                } else {
                    Snackbar.make(autofitEditText, generalFunc.retrieveLangLBl("", "LBL_WITHDRAW_AMT_ERROR"), Snackbar.LENGTH_SHORT).show();
                    autofitEditText.setBackground(getRoundBG("#ffffff", 8, "#F44336"));
                    Handler handler = new Handler();
                    handler.postDelayed(() -> autofitEditText.setBackground(getRoundBG("#ffffff", 8, "#CCCACA")), 2000);
                }
            } else {
                Snackbar.make(autofitEditText, generalFunc.retrieveLangLBl("", "LBL_WITHDRAW_AMT_MSG"), Snackbar.LENGTH_SHORT).show();
                autofitEditText.setBackground(getRoundBG("#ffffff", 8, "#F44336"));
                Handler handler = new Handler();
                handler.postDelayed(() -> autofitEditText.setBackground(getRoundBG("#ffffff", 8, "#CCCACA")), 2000);
            }
        } else if (i == R.id.readFAQ) {
            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
        } else if (i == R.id.support) {
            new ActUtils(getActContext()).startAct(HelpActivity23Pro.class);
        } else if (i == R.id.backImgView) {
            WithdrawBalanceActivity.super.onBackPressed();
        } else if (i == R.id.nonwithdrawAmountTitle) {
            Toast.makeText(getActContext(), "Clicked", Toast.LENGTH_LONG).show();
        }
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
        Intent returnIntent = new Intent();
        returnIntent.putExtra("vAccountNumber", Utils.getText(accountHTxt));
        setResult(Activity.RESULT_OK, returnIntent);
        finish();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == 123 && data != null) {
            vAccountNumber = "Yes";
            accountHTxt.setText(data.getStringExtra("vAccountNumber"));
            accountDetailsAdded = true;
            addaccountImage.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_edit));
        }
    }

    private void withDrawlRequest(String amount) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "createWithdrawlRequest");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("amount", amount);
        parameters.put("iServiceId", "0");

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                currentAlertBox = generalFunc.showGeneralMessage("",
                        generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message", responseString)),
                        "", LBL_BTN_OK_TXT, buttonId -> finish());
            } else {
                generalFunc.showError();
            }
        });

    }
}