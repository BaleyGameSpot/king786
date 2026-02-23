package com.act;

import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;
import android.text.InputFilter;
import android.text.InputType;
import android.text.TextUtils;
import android.text.TextWatcher;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.widget.CompoundButton;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.RelativeLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.AppCompatCheckBox;
import androidx.cardview.widget.CardView;
import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.RecentTransactionsTypeRecycleAdapter;
import com.adapter.files.WalletHistoryRecycleAdapter;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.model.TransactionTypesModel;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.AutoFitEditText;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;
import com.view.WKWebView;
import com.view.anim.loader.AVLoadingIndicatorView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Locale;

/**
 * Created by Admin on 04-11-2016.
 */
public class MyWalletActivity extends ParentActivity implements CompoundButton.OnCheckedChangeListener, RecentTransactionsTypeRecycleAdapter.OnTypeClickList {

    MTextView titleTxt, seeAllTxt, labletxt, withDrawTxt;
    ImageView backImgView, imgseeAll, helpmenu;
    ProgressBar loading_wallet_history;
    MTextView viewTransactionsTxt;
    ErrorView errorView;
    String required_str = "";
    String error_money_str = "";
    boolean mIsLoading = false;

    String next_page_str = "0";
    private MaterialEditText rechargeBox;
    private AutoFitEditText autofitEditText;
    private final String defaultAmountVal = AutoFitEditText.convertCommaToDecimal("0.00", false);
    private MTextView yourBalTxt;
    private MButton btn_type1;


    //private MTextView addMoneyTagTxt;
    private MTextView addMoneyTxt;
    private static final int SEL_CARD = 004;
    public static final int TRANSFER_MONEY = 87;
    private static final int WEBVIEWPAYMENT = 001;

    AppCompatCheckBox useBalChkBox;
    MTextView useBalanceTxt, walletamountTxt;

    AVLoadingIndicatorView loaderView;
    WKWebView paymentWebview;

    // Go Pay view declaration start
    LinearLayout addTransferArea, ProfileImageArea;
    String transferState = "SEARCH";
    MTextView sendMoneyTxt, transferMoneyTagTxt;
    LinearLayout seeAllArea;
    RadioButton driverRadioBtn, userRadioBtn;
    RadioGroup rg_whomType;
    MaterialEditText detailBox, otpverificationCodeBox;
    FrameLayout verificationArea;
    LinearLayout infoArea;
    ImageView ic_back_arrow, transerCardArea, addMoneyCardArea;
    MTextView whomTxt, userNameTxt, moneyTitleTxt;
    MButton btn_type3, btn_type4, btn_otp;
    SelectableRoundedImageView toUserImgView;
    CardView moneyDetailArea;
    LinearLayout transferMoneyAddDetailArea;
    String error_email_str = "";
    String error_verification_code = "";
    LinearLayout toWhomTransferArea;
    String isRegenerate = "No";
    boolean isClicked = false;
    // Go Pay view declaration end


    LinearLayout addMoneyArea, transerArea, TransactionArea, helpArea;
    MTextView transferTxt, transactionTxt, recentTransHTxt, noTransactionTxt, helpTxt;
    ArrayList<HashMap<String, String>> list = new ArrayList<>();
    ArrayList<TransactionTypesModel> typeList = new ArrayList<>();
    boolean isNextPageAvailable = false;
    RecyclerView recentTransactionRecyclerView, trasactionCategoryRecyclerView;
    private WalletHistoryRecycleAdapter wallethistoryRecyclerAdapter;
    private RecentTransactionsTypeRecycleAdapter recentTransactionsTypeRecycleAdapter;

    LinearLayout transferMoneyToWallet;

    String detailBoxVal = "";
    LinearLayout resendOtpArea, otpArea, moneyArea;
    String iUserId = "";
    String eUserType = "";
    String verificationCode = "";
    String username = "";
    String userImage = "";
    String userEmail = "";
    String userPhone = "";
    String fAmountWithSymbol = "";
    String amount = "";
    String transactionDate = "";
    String crtSelectedType = "All";
    LinearLayout WalletContentArea;
    String APP_PAYMENT_METHOD;


    MTextView requestTxt;
    RelativeLayout withdrawArea;
    LinearLayout requestView, withDrawSquareArea;

    String WITHDRAWABLE_AMOUNT = "";
    String NON_WITHDRAWABLE_AMOUNT = "";
    String ACCOUNT_NO = "";
    String MemberBalance = "";

    String ORIG_WITHDRAWABLE_AMOUNT = "";
    String ORIG_NON_WITHDRAWABLE_AMOUNT = "";
    String vAccountNumber = "";

    private ServerTask currentCallExeWebServer;
    private Boolean isTypeClick = false;

    private NestedScrollView nestedContentArea;
    String prvSelectedType = "";
    private ActivityResultLauncher<Intent> accountNumber;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_mywallet);


        APP_PAYMENT_METHOD = generalFunc.getJsonValueStr("APP_PAYMENT_METHOD", obj_userProfile);
        WalletContentArea = findViewById(R.id.WalletContentArea);
        transerCardArea = findViewById(R.id.transerCardArea);
        addMoneyCardArea = findViewById(R.id.addMoneyCardArea);
        titleTxt = findViewById(R.id.titleTxt);
        backImgView = findViewById(R.id.backImgView);
        helpmenu = findViewById(R.id.helpmenu);
        helpmenu.setVisibility(View.VISIBLE);
        walletamountTxt = findViewById(R.id.walletamountTxt);

        loading_wallet_history = findViewById(R.id.loading_wallet_history);
        viewTransactionsTxt = findViewById(R.id.viewTransactionsTxt);
        errorView = findViewById(R.id.errorView);

        addMoneyTxt = findViewById(R.id.addMoneyTxt);
        addMoneyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY_TXT"));
        errorView = findViewById(R.id.errorView);
        helpTxt = findViewById(R.id.helpTxt);
        seeAllTxt = findViewById(R.id.seeAllTxt);
        seeAllArea = findViewById(R.id.seeAllArea);
        seeAllTxt.setText(generalFunc.retrieveLangLBl("See all", "LBL_SEE_ALL"));
        labletxt = findViewById(R.id.labletxt);
        labletxt.setText(generalFunc.retrieveLangLBl("My Wallet", "LBL_MY_WALLET"));
        imgseeAll = findViewById(R.id.imgseeAll);
        yourBalTxt = findViewById(R.id.yourBalTxt);


        btn_type1 = ((MaterialRippleLayout) findViewById(R.id.btn_type1)).getChildView();

        addMoneyArea = findViewById(R.id.addMoneyArea);
        transerArea = findViewById(R.id.transerArea);
        TransactionArea = findViewById(R.id.TransactionArea);
        transferTxt = findViewById(R.id.transferTxt);
        transactionTxt = findViewById(R.id.transactionTxt);
        recentTransHTxt = findViewById(R.id.recentTransHTxt);
        noTransactionTxt = findViewById(R.id.noTransactionTxt);
        helpArea = findViewById(R.id.helpArea);
        withDrawSquareArea = findViewById(R.id.withDrawSquareArea);
        withDrawTxt = findViewById(R.id.withDrawTxt);
        recentTransactionRecyclerView = findViewById(R.id.recentTransactionRecyclerView);
        trasactionCategoryRecyclerView = findViewById(R.id.trasactionCategoryRecyclerView);
        seeAllTxt = findViewById(R.id.seeAllTxt);

        addToClickHandler(addMoneyArea);
        addToClickHandler(transerArea);
        addToClickHandler(TransactionArea);
        addToClickHandler(helpArea);
        addToClickHandler(withDrawSquareArea);
        addToClickHandler(seeAllArea);

        useBalanceTxt = findViewById(R.id.useBalanceTxt);
        useBalChkBox = findViewById(R.id.useBalChkBox);


        paymentWebview = findViewById(R.id.paymentWebview);
        loaderView = findViewById(R.id.loaderView);
        nestedContentArea = findViewById(R.id.nestedContentArea);


        addToClickHandler(backImgView);
        addToClickHandler(helpmenu);
        addToClickHandler(viewTransactionsTxt);
        btn_type1.setId(Utils.generateViewId());
        addToClickHandler(btn_type1);

        requestTxt = findViewById(R.id.requestTxt);
        withdrawArea = findViewById(R.id.withdrawArea);
        requestView = findViewById(R.id.requestView);
        addToClickHandler(requestView);
        if (generalFunc.isRTLmode()) {
            imgseeAll.setRotation(180);
            backImgView.setRotation(180);
        }
        setLabels();


        useBalChkBox.setOnCheckedChangeListener(this);
        wallethistoryRecyclerAdapter = new WalletHistoryRecycleAdapter(getActContext(), list, generalFunc, false);
        typeList.clear();
        typeList.add(new
                TransactionTypesModel(0, generalFunc.retrieveLangLBl("All", "LBL_ALL"), true, 1));
        typeList.add(new
                TransactionTypesModel(R.drawable.ic_credit_new,
                generalFunc.retrieveLangLBl("Credit", "LBL_CREDIT"), false, 2));
        typeList.add(new
                TransactionTypesModel(R.drawable.ic_debit_new, generalFunc.retrieveLangLBl("Debit", "LBL_DEBIT"), false, 3));

        recentTransactionsTypeRecycleAdapter = new RecentTransactionsTypeRecycleAdapter(getActContext(), generalFunc, typeList);
        recentTransactionRecyclerView.setAdapter(wallethistoryRecyclerAdapter);
        trasactionCategoryRecyclerView.setAdapter(recentTransactionsTypeRecycleAdapter);
        trasactionCategoryRecyclerView.setVisibility(View.GONE);
        nestedContentArea.getViewTreeObserver().addOnScrollChangedListener(() -> {
            View view = nestedContentArea.getChildAt(nestedContentArea.getChildCount() - 1);

            int diff = (view.getBottom() - (nestedContentArea.getHeight() + nestedContentArea
                    .getScrollY()));

            if (diff == 0 && !(mIsLoading) && isNextPageAvailable) {
                mIsLoading = true;
                wallethistoryRecyclerAdapter.addFooterView();

                getRecentTransction(true);

            } else if (!isNextPageAvailable) {
                wallethistoryRecyclerAdapter.removeFooterView();
            }
        });

        recentTransactionsTypeRecycleAdapter.setOnItemClickList(this::onTypeClick);

        getRecentTransction(false);

        if (getIntent().getBooleanExtra("isAddMoney", false)) {
            openAddMoneyDialog();
        } else if (getIntent().getBooleanExtra("isSendMoney", false)) {
            openTransferDialog();
        }

        showHideButton("");

        accountNumber = registerForActivityResult(
                new ActivityResultContracts.StartActivityForResult(), result -> {
                    if (result != null && result.getResultCode() == RESULT_OK) {
                        Intent intent = result.getData();
                        if (intent != null) {
                            ACCOUNT_NO = intent.getStringExtra("vAccountNumber");
                        }
                    }
                });

    }

    private void showHideButton(String setView) {

        boolean isOnlyCashEnabled = generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).equalsIgnoreCase("Cash");
        /*Go Pay Enabled Or Not - Delete Start if you don't want gopay */
        boolean isTransferMoneyEnabled = generalFunc.retrieveValue(Utils.ENABLE_GOPAY_KEY).equalsIgnoreCase("Yes");

        /*Go Pay Enabled Or Not - Delete End if you don't want gopay */
        if (TextUtils.isEmpty(setView)) {
            transerArea.setVisibility(isTransferMoneyEnabled ? View.VISIBLE : View.GONE);
            if (isOnlyCashEnabled) {
                addMoneyArea.setVisibility(View.GONE);
            } else {
                addMoneyArea.setVisibility(View.VISIBLE);
            }
        }
        /*Go Pay Enabled Or Not - Delete Start if you don't want gopay */
        else if (setView.equalsIgnoreCase("add")) {
            removeValues(true);
            //rechargeBox.setText("");

            btn_type1.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
            transferState = "SEARCH";
            configureView();
            transferMoneyToWallet.setVisibility(View.GONE);
            addTransferArea.setVisibility(View.VISIBLE);
            ProfileImageArea.setVisibility(View.VISIBLE);
            btn_type4.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_TO") + " " + username);
        } else if (setView.equalsIgnoreCase("transfer")) {
            removeValues(true);

            btn_type1.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
            addTransferArea.setVisibility(View.GONE);
            ProfileImageArea.setVisibility(View.GONE);
            transferMoneyToWallet.setVisibility(View.VISIBLE);
            transferState = "SEARCH";
            configureView();
            btn_type4.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY_TXT"));
        }
        /*Go Pay Enabled Or Not - Delete End if you don't want gopay */

    }


    private void configureView() {
        if (transferState.equalsIgnoreCase("SEARCH")) {
            btn_type3.setText(generalFunc.retrieveLangLBl("", "LBL_Search"));
        } else if (transferState.equalsIgnoreCase("ENTER_AMOUNT")) {

            userNameTxt.setText(username);

            new LoadImage.builder(LoadImage.bind(userImage), toUserImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
            transferMoneyToWallet.setVisibility(View.GONE);
            addTransferArea.setVisibility(View.VISIBLE);
            ProfileImageArea.setVisibility(View.VISIBLE);

        } else if (transferState.equalsIgnoreCase("VERIFY")) {
            btn_type3.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_SUBMIT_TXT"));
            otpArea.setVisibility(View.VISIBLE);
            moneyArea.setVisibility(View.GONE);
        }

    }

    public void goback() {
        if (transferMoneyAddDetailArea.getHeight() != 0 && Utils.checkText(transferState) && !transferState.equalsIgnoreCase("SEARCH")) {
            if (transferState.equalsIgnoreCase("ENTER_AMOUNT")) {
                transferState = "SEARCH";
                configureView();
            } else if (transferState.equalsIgnoreCase("VERIFY")) {
                transferState = "ENTER_AMOUNT";
                configureView();
            }

            return;

        }

        onBackPressed();
    }


    private void removeValues(boolean removeValues) {
        if (removeValues) {

            if (otpverificationCodeBox != null) {
                otpverificationCodeBox.setText("");
            }

            iUserId = "";
            eUserType = "";
            verificationCode = "";
            if (rg_whomType != null) {
                rg_whomType.clearCheck();
            }
        }
    }

    private void transferMoneyToWallet() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("fromUserId", generalFunc.getMemberId());
        parameters.put("fromUserType", Utils.userType);
        //parameters.put("transferType", emailRadioBtn.isChecked() ? "Email" : "Phone");
        parameters.put("searchUserType", userRadioBtn.isChecked() ? "Passenger" : Utils.userType);
        parameters.put("UserType", Utils.userType);
        if (transferState.equalsIgnoreCase("SEARCH")) {
            parameters.put("type", "GopayCheckPhoneEmail");
            parameters.put("vPhoneOrEmailTxt", detailBoxVal);
        } else if (transferState.equalsIgnoreCase("ENTER_AMOUNT")) {
            parameters.put("type", "GoPayVerifyAmount");
            parameters.put("isRegenerate", isRegenerate);
            parameters.put("fAmount", autofitEditText.getTxt());
            parameters.put("toUserId", iUserId);
            parameters.put("toUserType", eUserType);
        } else if (transferState.equalsIgnoreCase("VERIFY")) {
            parameters.put("type", "GoPayTransferAmount");
            parameters.put("toUserId", iUserId);
            parameters.put("toUserType", eUserType);
            parameters.put("fAmount", autofitEditText.getTxt());
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {

                    if (isRegenerate.equalsIgnoreCase("Yes")) {
                        isClicked = false;
                    }

                    if (responseString != null && !responseString.equals("")) {
                        String action = generalFunc.getJsonValue(Utils.action_str, responseString);
                        if (action.equals("1")) {
                            String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                            if (transferState.equalsIgnoreCase("SEARCH")) {

                                iUserId = generalFunc.getJsonValue("iUserId", message);
                                eUserType = generalFunc.getJsonValue("eUserType", message);

                                username = generalFunc.getJsonValue("vName", message);
                                userImage = generalFunc.getJsonValue("vImgName", message);
                                userEmail = generalFunc.getJsonValue("vEmail", message);
                                userPhone = generalFunc.getJsonValue("vPhone", message);

                                if (btn_type4 != null) {
                                    btn_type4.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_TO") + " " + username);
                                }
                                transferState = "ENTER_AMOUNT";
                                configureView();
                            } else if (transferState.equalsIgnoreCase("ENTER_AMOUNT")) {
                                if (isRegenerate.equalsIgnoreCase("Yes")) {
                                    otpverificationCodeBox.setText("");
                                    isRegenerate = "No";
                                    resendOtpArea.setVisibility(View.GONE);
                                    resendOtpArea.setOnClickListener(null);
                                }
                                transferState = "VERIFY";

                                verificationCode = generalFunc.getJsonValue("verificationCode", message);
                                String amount = String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.00, autofitEditText.getTxt()));
                                this.amount = generalFunc.getJsonValueStr("CurrencySymbol", obj_userProfile) + "" + generalFunc.convertNumberWithRTL(amount);
                                configureView();
                            } else if (transferState.equalsIgnoreCase("VERIFY")) {
                                if (isRegenerate.equalsIgnoreCase("Yes")) {
                                    isRegenerate = "No";
                                    resendOtpArea.setVisibility(View.GONE);
                                    resendOtpArea.setOnClickListener(null);
                                }


                                successDialog(generalFunc.retrieveLangLBl("", message), generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
                            }
                        } else {

                            String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                            String showAddMoney = generalFunc.getJsonValue("showAddMoney", responseString);

                            if (transferState.equalsIgnoreCase("ENTER_AMOUNT") && (message.equalsIgnoreCase("LBL_WALLET_AMOUNT_GREATER_THAN_ZERO") || showAddMoney.equalsIgnoreCase("Yes"))) {
                                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", message));

                                boolean isOnlyCashEnabled = generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).equalsIgnoreCase("Cash");

                                if (!isOnlyCashEnabled) {
                                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"));
                                }
                                generateAlert.setNegativeBtn(!isOnlyCashEnabled ? generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT") : generalFunc.retrieveLangLBl("", "LBL_OK"));

                                generateAlert.setBtnClickList(btn_id -> {

                                    if (btn_id == 1) {
                                        generateAlert.closeAlertBox();
                                        //must change


                                        openAddMoneyDialog();
                                        dialog_transfer.dismiss();
                                    } else {
                                        generateAlert.closeAlertBox();
                                    }

                                });

                                generateAlert.showAlertBox();
                                return;
                            } else if (transferState.equalsIgnoreCase("VERIFY")) {

                                if (message.equalsIgnoreCase("LBL_OTP_EXPIRED")) {
                                    isRegenerate = "Yes";
                                    resendOtpArea.setVisibility(View.VISIBLE);

                                    return;
                                }
                                //manage new for sucess dialog

                                if (dialog_transfer != null) {
                                    dialog_transfer.dismiss();
                                }
                                generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue("message_profile_data", responseString));

                                //transactionDate = generalFunc.getJsonValue("transactionDate", responseString);
                                //transactionDate = generalFunc.getDateFormatedType(generalFunc.getJsonValue("transactionDate", responseString), Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc));
                                transactionDate = generalFunc.getJsonValue("tDisplayDateTime", responseString);

                                fAmountWithSymbol = generalFunc.getJsonValue("fAmountWithSymbol", responseString);
                                openSucessDialog();
                            } else {

                                generalFunc.showGeneralMessage("", action.equalsIgnoreCase("2") ? message : generalFunc.retrieveLangLBl("", message));
                            }


                        }
                    } else {
                        generalFunc.showError();
                    }
                });


    }

    private void animateDialog() {
        String currentMobileNum = generalFunc.getJsonValueStr("vPhone", obj_userProfile);
//        String currentPhoneCode = generalFunc.getJsonValueStr("vPhoneCode", userProfileJsonObj);

        CustomDialog customDialog = new CustomDialog(getActContext());
        customDialog.setDetails(""/*generalFunc.retrieveLangLBl("","LBL_RETRIVE_OTP_TITLE_TXT")*/, generalFunc.retrieveLangLBl("", Utils.checkText(currentMobileNum) ? "LBL_TRANSFER_WALLET_OTP_EMAIL_NONMANDATORY_INFO_TXT" : "LBL_TRANSFER_WALLET_OTP_INFO_TXT"), generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), "", false, R.drawable.ic_normal_info, false, 2, false);
        customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
        customDialog.setRoundedViewBorderColor(R.color.white);
        customDialog.setImgStrokWidth(15);
        customDialog.setBtnRadius(10);
        customDialog.setIconTintColor(R.color.white);
        customDialog.setPositiveBtnBackColor(R.color.appThemeColor_1);
        customDialog.setPositiveBtnTextColor(R.color.white);
        customDialog.createDialog();
        customDialog.setPositiveButtonClick(() -> {

        });
        customDialog.setNegativeButtonClick(() -> {

        });
        customDialog.show();
    }

    private void successDialog(String message, String positiveBtnTxt) {
        if (isRegenerate.equalsIgnoreCase("yes")) {
            CustomDialog customDialog = new CustomDialog(getActContext());
            customDialog.setDetails(""/*generalFunc.retrieveLangLBl("","LBL_OTP_EXPIRED_TXT")*/, message, positiveBtnTxt, "", false, R.drawable.ic_hand_gesture, false, 2, false);
            customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
            customDialog.setRoundedViewBorderColor(R.color.white);
            customDialog.setImgStrokWidth(15);
            customDialog.setBtnRadius(10);
            customDialog.setIconTintColor(R.color.white);
            customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
            customDialog.setPositiveBtnTextColor(R.color.white);
            customDialog.createDialog();
            customDialog.setPositiveButtonClick(() -> {
                otpverificationCodeBox.setText("");
                resendOtpArea.setVisibility(View.VISIBLE);
                addToClickHandler(resendOtpArea);
            });
            customDialog.setNegativeButtonClick(() -> {

            });
            customDialog.show();

        } else {
            CustomDialog customDialog = new CustomDialog(getActContext());
            customDialog.setDetails(""/*generalFunc.retrieveLangLBl("","LBL_MONEY_TRANSFER_CONFIRMATION_TITLE_TXT")*/, message, positiveBtnTxt, "", false, R.drawable.ic_correct, false, 2, false);
            customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
            customDialog.setRoundedViewBorderColor(R.color.white);
            customDialog.setImgStrokWidth(15);
            customDialog.setBtnRadius(10);
            customDialog.setIconTintColor(R.color.white);
            customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
            customDialog.setPositiveBtnTextColor(R.color.white);
            customDialog.createDialog();
            customDialog.setPositiveButtonClick(() -> {
                transferState = "SEARCH";
                configureView();
                generalFunc.storeData(Utils.ISWALLETBALNCECHANGE, "Yes");
                list.clear();
                getRecentTransction(false);
                // getWalletBalDetails();
            });
            customDialog.setNegativeButtonClick(() -> {

            });
            customDialog.show();
        }
    }

    // Go Pay  implementation end

    public void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LEFT_MENU_WALLET"));
        yourBalTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BALANCE"));
        viewTransactionsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_TRANS_HISTORY"));
        btn_type1.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_TRANS_HISTORY"));
        withDrawTxt.setText((generalFunc.retrieveLangLBl("", "LBL_WITHDRAWL")));

        useBalanceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USE_WALLET_BALANCE_NOTE"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_money_str = generalFunc.retrieveLangLBl("", "LBL_ADD_CORRECT_DETAIL_TXT");
        requestTxt.setText(generalFunc.retrieveLangLBl("Withdraw", "LBL_WITHDRAW_REQUEST"));

        if (generalFunc.getJsonValueStr("eWalletAdjustment", obj_userProfile).equals("No")) {
            useBalChkBox.setChecked(false);
        } else {
            useBalChkBox.setChecked(true);
        }


        transferTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRANSFER"));
        transactionTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRANSACTIONS"));
        recentTransHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECENT_TRANSACTION"));
        helpTxt.setText((generalFunc.retrieveLangLBl("", "LBL_HELP_TXT")));
    }


    public void UpdateUserWalletAdjustment(boolean value) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateUserWalletAdjustment");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("eWalletAdjustment", value ? "Yes" : "No");


        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        closeLoader();

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                        if (isDataAvail) {

                            generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));

                            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_INFO_UPDATED_TXT"));

                        } else {

                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));

                            useBalChkBox.setOnCheckedChangeListener(null);
                            useBalChkBox.setChecked(value ? false : true);
                            useBalChkBox.setOnCheckedChangeListener(this);
                        }
                    } else {
                        closeLoader();
                        generalFunc.showError();
                        useBalChkBox.setOnCheckedChangeListener(null);
                        useBalChkBox.setChecked(value ? false : true);
                        useBalChkBox.setOnCheckedChangeListener(this);
                    }
                });
        exeWebServer.setCancelAble(false);
    }


    public void checkValues(AutoFitEditText rechargeBox) {

        double moneyAdded = GeneralFunctions.parseDoubleValue(0.0, rechargeBox.getTxt());
        boolean addMoneyAmountEntered = Utils.checkText(rechargeBox) ? (moneyAdded > 0 ? true : Utils.setErrorFields(rechargeBox, error_money_str))
                : Utils.setErrorFields(rechargeBox, required_str);

        if (!addMoneyAmountEntered) {
            return;
        }

        dialog_add_money.dismiss();
        String url = generalFunc.getJsonValueStr("PAYMENT_BASE_URL", obj_userProfile) + "&PAGE_TYPE=WALLET_MONEY_ADD" +
                "&currency=" + generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile) +
                "&AMOUNT=" + moneyAdded;
        url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
        url = url + "&GeneralUserType=" + Utils.app_type;
        url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
        url = url + "&ePaymentOption=" + "Card";
        url = url + "&vPayMethod=" + "Instant";
        url = url + "&SYSTEM_TYPE=" + "APP";
        url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();


        Bundle bn = new Bundle();
        bn.putString("url", url);
        bn.putBoolean("handleResponse", true);
        new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);

    }


    @Override
    public void onBackPressed() {

        if (paymentWebview.getVisibility() == View.VISIBLE) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_CANCEL_PAYMENT_PROCESS"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                if (buttonId == 1) {
                    paymentWebview.setVisibility(View.GONE);
                    paymentWebview.stopLoading();
                    loaderView.setVisibility(View.GONE);
                }
            });

            return;
        }
        super.onBackPressed();
    }

    public void closeLoader() {
        if (loading_wallet_history.getVisibility() == View.VISIBLE) {
            loading_wallet_history.setVisibility(View.GONE);
        }
    }

    public void generateErrorView() {

        closeLoader();
        SkeletonViewHandler.getInstance().hideSkeletonView();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);

        }
        errorView.setOnRetryListener(() -> getTransactionHistory(false));
    }

    public void getTransactionHistory(final boolean isLoadMore) {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);

        }
        if (loading_wallet_history.getVisibility() != View.VISIBLE && !isLoadMore) {
            loading_wallet_history.setVisibility(View.VISIBLE);

        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getTransactionHistory");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        closeLoader();


                        String LBL_BALANCE = generalFunc.getJsonValue("user_available_balance", responseString);
                        String user_available_balance_amount = generalFunc.getJsonValue("user_available_balance_amount", responseString);

                        Double userAvailableBalance = GeneralFunctions.parseDoubleValue(0, user_available_balance_amount);

                        if (userAvailableBalance > 0 && generalFunc.retrieveValue("ENABLE_WALLET_WITHDRAWAL_REQUEST").equalsIgnoreCase("Yes")) {
                            withDrawSquareArea.setVisibility(View.VISIBLE);
                        } else {
                            withDrawSquareArea.setVisibility(View.GONE);
                        }

                        yourBalTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USER_BALANCE"));
                        walletamountTxt.setText(generalFunc.convertNumberWithRTL(LBL_BALANCE));
                        SkeletonViewHandler.getInstance().hideSkeletonView();

                    } else {
                        if (!isLoadMore) {
                            generateErrorView();
                        }

                    }

                    mIsLoading = false;
                });

    }

    public Context getActContext() {
        return MyWalletActivity.this;
    }

    @Override
    protected void onResume() {
        super.onResume();

    }

    @Override
    protected void onDestroy() {
        // store prev service id
        if (getIntent().hasExtra("iServiceId")) {
            generalFunc.storeData(Utils.iServiceId_KEY, getIntent().getStringExtra("iServiceId"));
        }
        super.onDestroy();

    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (resultCode == RESULT_OK && requestCode == WEBVIEWPAYMENT) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.retrieveLangLBl("", "LBL_WALLET_MONEY_CREDITED")), "", generalFunc.retrieveLangLBl("", "LBL_OK"), i -> {
//                        isFinish = true;
                list.clear();
                getRecentTransction(false);

            });

        } else if (resultCode == RESULT_OK && requestCode == SEL_CARD) {
            getTransactionHistory(false);

        } else if (resultCode == RESULT_OK && requestCode == TRANSFER_MONEY) {
            list.clear();
            getRecentTransction(false);
        }

    }


    public void getWalletBalDetails() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetMemberWalletBalance");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {


                    if (responseString != null && !responseString.equals("")) {

                        closeLoader();

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                        if (isDataAvail) {
                            try {

                                String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                                walletamountTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("MemberBalance", responseString)));

                                if (!generalFunc.getJsonValue("user_available_balance", userProfileJson).equalsIgnoreCase(generalFunc.getJsonValue("MemberBalance", responseString))) {
                                    generalFunc.storeData(Utils.ISWALLETBALNCECHANGE, "Yes");
                                }


                            } catch (Exception e) {

                            }

                        }

                    } else {
                        closeLoader();
                        generalFunc.showError();
                    }
                });

    }

    @Override
    public void onCheckedChanged(CompoundButton compoundButton, boolean isCheck) {
        UpdateUserWalletAdjustment(isCheck);
    }

    @Override
    public void onTypeClick(TransactionTypesModel transactionTypesModel) {


        switch (transactionTypesModel.getCatType()) {
            case 1:
                crtSelectedType = "All";
                break;

            case 2:
                crtSelectedType = "CREDIT";
                break;
            case 3:
                crtSelectedType = "DEBIT";
                break;
        }

        if (!prvSelectedType.equals(crtSelectedType)) {
            if (currentCallExeWebServer != null) {
                currentCallExeWebServer.cancel(true);
                currentCallExeWebServer = null;
            }
            isTypeClick = true;
            getRecentTransction(false);
            prvSelectedType = crtSelectedType;
        }

        for (TransactionTypesModel selType : typeList) {
            if (selType.getCatType() == transactionTypesModel.getCatType()) {
                selType.setSelected(true);
            } else {
                selType.setSelected(false);
            }
        }
        recentTransactionsTypeRecycleAdapter.updatelist(typeList);
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.backImgView) {
            onBackPressed();
        } else if (id == btn_type1.getId()) {
            new ActUtils(getActContext()).startAct(MyWalletHistoryActivity.class);
        } else if (id == R.id.requestView || id == R.id.withDrawSquareArea) {
            Intent intent = new Intent(this, WithdrawBalanceActivity.class);
            Bundle bundle = new Bundle();
            bundle.putString("WITHDRAWABLE_AMOUNT", "" + WITHDRAWABLE_AMOUNT);
            bundle.putString("NON_WITHDRAWABLE_AMOUNT", "" + NON_WITHDRAWABLE_AMOUNT);
            bundle.putString("ACCOUNT_NO", "" + ACCOUNT_NO);
            bundle.putString("MemberBalance", "" + MemberBalance);
            bundle.putString("ORIG_WITHDRAWABLE_AMOUNT", "" + ORIG_WITHDRAWABLE_AMOUNT);
            bundle.putString("ORIG_NON_WITHDRAWABLE_AMOUNT", "" + ORIG_NON_WITHDRAWABLE_AMOUNT);
            bundle.putString("vAccountNumber", "" + vAccountNumber);
            intent.putExtras(bundle);
            accountNumber.launch(intent);
        } else if (id == R.id.viewTransactionsTxt) {
            new ActUtils(getActContext()).startAct(MyWalletHistoryActivity.class);
        } else if (id == R.id.ic_back_arrow) {
            goback();
        } else if (id == R.id.viewTransactionsBtnArea) {
            btn_type1.performClick();
        } else if (id == R.id.infoArea) {
            animateDialog();
        } else if (id == R.id.resendOtpArea) {
            if (!isClicked) {
                isClicked = true;
                isRegenerate = "Yes";
                transferState = "ENTER_AMOUNT";
                transferMoneyToWallet();
            }
        } else if (id == R.id.addMoneyArea) {
            if (dialog_add_money != null && dialog_add_money.isShowing()) {
                return;
            }
            openAddMoneyDialog();
        } else if (id == R.id.transerArea) {
            if (dialog_transfer != null && dialog_transfer.isShowing()) {
                return;
            }
            openTransferDialog();
        } else if (id == R.id.TransactionArea) {
            new ActUtils(getActContext()).startAct(MyWalletHistoryActivity.class);
        } else if (id == R.id.helpArea) {
            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
        } else if (id == R.id.seeAllArea) {
            Bundle bn = new Bundle();
            bn.putSerializable("seeAll", true);
            new ActUtils(getActContext()).startActWithData(MyWalletHistoryActivity.class, bn);
        } else if (id == R.id.helpmenu) {
            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
        }
    }


    public void getRecentTransction(final boolean isLoadMore) {

        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (loading_wallet_history.getVisibility() != View.VISIBLE && !isLoadMore) {
            if (list.size() == 0 && !isTypeClick && !Utils.checkText(prvSelectedType)) {
                SkeletonViewHandler.getInstance().ShowNormalSkeletonView(WalletContentArea, R.layout.wallet_shimmer_view_new);
            } else {
                recentTransactionRecyclerView.setVisibility(View.GONE);
                noTransactionTxt.setVisibility(View.GONE);
                loading_wallet_history.setVisibility(View.VISIBLE);
            }

        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getTransactionHistory");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("ListType", crtSelectedType);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        noTransactionTxt.setVisibility(View.GONE);


        currentCallExeWebServer = ApiHandler.execute(getActContext(), parameters,
                responseString -> {

                    noTransactionTxt.setVisibility(View.GONE);
                    if (responseString != null && !responseString.equals("")) {
                        isTypeClick = false;
                        currentCallExeWebServer = null;
                        closeLoader();
                        if (!isLoadMore) {
                            list.clear();
                        }

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                            String nextPage = generalFunc.getJsonValue("NextPage", responseString);
                            JSONArray arr_transhistory = generalFunc.getJsonArray(Utils.message_str, responseString);

                            walletamountTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("MemberBalance", responseString)));

                            if (!generalFunc.getJsonValueStr("user_available_balance", obj_userProfile).equalsIgnoreCase(generalFunc.getJsonValue("MemberBalance", responseString))) {
                                generalFunc.storeData(Utils.ISWALLETBALNCECHANGE, "Yes");
                            }
                            String LBL_BALANCE = generalFunc.getJsonValue("user_available_balance", responseString);
                            String user_available_balance_amount = generalFunc.getJsonValue("user_available_balance_amount", responseString);
                            Double userAvailableBalance = generalFunc.parseDoubleValue(0, user_available_balance_amount);

                            if (userAvailableBalance > 0 && generalFunc.retrieveValue("ENABLE_WALLET_WITHDRAWAL_REQUEST").equalsIgnoreCase("Yes")) {
                                withDrawSquareArea.setVisibility(View.VISIBLE);
                            } else {
                                withDrawSquareArea.setVisibility(View.GONE);
                            }
                            walletamountTxt.setText(generalFunc.convertNumberWithRTL(LBL_BALANCE));
                            WITHDRAWABLE_AMOUNT = generalFunc.getJsonValue("WITHDRAWABLE_AMOUNT", responseString);
                            NON_WITHDRAWABLE_AMOUNT = generalFunc.getJsonValue("NON_WITHDRAWABLE_AMOUNT", responseString);
                            ACCOUNT_NO = generalFunc.getJsonValue("ACCOUNT_NO", responseString);
                            MemberBalance = generalFunc.getJsonValue("MemberBalance", responseString);


                            ORIG_WITHDRAWABLE_AMOUNT = generalFunc.getJsonValue("ORIG_WITHDRAWABLE_AMOUNT", responseString);
                            ORIG_NON_WITHDRAWABLE_AMOUNT = generalFunc.getJsonValue("ORIG_NON_WITHDRAWABLE_AMOUNT", responseString);
                            vAccountNumber = generalFunc.getJsonValue("vAccountNumber", responseString);


                            if (arr_transhistory != null && arr_transhistory.length() > 0) {
                                for (int i = 0; i < arr_transhistory.length(); i++) {
                                    JSONObject obj_temp = generalFunc.getJsonObject(arr_transhistory, i);
                                    HashMap<String, String> map = new HashMap<>();
                                    map.put("iUserWalletId", generalFunc.getJsonValueStr("iUserWalletId", obj_temp));
                                    map.put("iUserId", generalFunc.getJsonValueStr("iUserId", obj_temp));
                                    map.put("eUserType", generalFunc.getJsonValueStr("eUserType", obj_temp));
                                    map.put("eType", generalFunc.getJsonValueStr("eType", obj_temp));
                                    map.put("iTripId", generalFunc.getJsonValueStr("iTripId", obj_temp));
                                    map.put("eFor", generalFunc.getJsonValueStr("eFor", obj_temp));
                                    String tDescription = generalFunc.getJsonValueStr("tDescription", obj_temp);
                                    map.put("tDescription", tDescription);
                                    map.put("tDescriptionConverted", generalFunc.convertNumberWithRTL(tDescription));
                                    map.put("ePaymentStatus", generalFunc.getJsonValueStr("ePaymentStatus", obj_temp));
                                    map.put("currentbal", generalFunc.getJsonValueStr("currentbal", obj_temp));
                                    map.put("LBL_Status", generalFunc.retrieveLangLBl("", "LBL_Status"));
                                    map.put("LBL_TRIP_NO", generalFunc.retrieveLangLBl("", "LBL_TRIP_NO"));
                                    map.put("LBL_BALANCE_TYPE", generalFunc.retrieveLangLBl("", "LBL_BALANCE_TYPE"));
                                    map.put("LBL_DESCRIPTION", generalFunc.retrieveLangLBl("", "LBL_DESCRIPTION"));
                                    map.put("LBL_AMOUNT", generalFunc.retrieveLangLBl("", "LBL_AMOUNT"));

                                    String dDateOrig = generalFunc.getJsonValueStr("dDateOrig", obj_temp);
                                    map.put("dDateOrig", dDateOrig);
                                    //map.put("listingFormattedDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dDateOrig, Utils.OriginalDateFormate, "dd MMM, yyyy(E)")));
                                    String iBalance = generalFunc.getJsonValueStr("iBalance", obj_temp);
                                    map.put("iBalance", iBalance);
                                    map.put("FormattediBalance", generalFunc.convertNumberWithRTL(iBalance));
                                    map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_temp));

                                    list.add(map);
                                }
                            }


                            if (!nextPage.equals("") && !nextPage.equals("0")) {
                                next_page_str = nextPage;
                                isNextPageAvailable = true;
                            } else {
                                removeNextPageConfig();
                            }
                            if (list.size() != 0) {
                                if (list.size() == arr_transhistory.length()) {
                                    wallethistoryRecyclerAdapter.notifyDataSetChanged();
                                } else {
                                    wallethistoryRecyclerAdapter.notifyItemRangeChanged
                                            (list.size() - arr_transhistory.length() - 1, arr_transhistory.length() + 1);
                                }
                            }
                            if (list.size() == 0) {
                                removeNextPageConfig();
                                noTransactionTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                                noTransactionTxt.setVisibility(View.VISIBLE);
                            }
                        } else {
                            String LBL_BALANCE = generalFunc.getJsonValue("user_available_balance", responseString);
                            yourBalTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USER_BALANCE"));
                            walletamountTxt.setText(generalFunc.convertNumberWithRTL(LBL_BALANCE));


                            if (list.size() == 0) {
                                removeNextPageConfig();
                                noTransactionTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                                noTransactionTxt.setVisibility(View.VISIBLE);

                            }
                        }
                        recentTransactionRecyclerView.setVisibility(View.VISIBLE);
                        SkeletonViewHandler.getInstance().hideSkeletonView();

                    } else {
                        if (!isLoadMore) {
                            removeNextPageConfig();
                            generateErrorView();
                        }

                    }
                    mIsLoading = false;
                    trasactionCategoryRecyclerView.smoothScrollToPosition(0);
                    trasactionCategoryRecyclerView.setVisibility(View.VISIBLE);
                });


    }

    public void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        wallethistoryRecyclerAdapter.removeFooterView();
    }

    Dialog dialog_add_money, dialog_transfer, dialog_sucess;

    MTextView otpInfoTxt;

    public void openTransferDialog() {

        dialog_transfer = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        dialog_transfer.setContentView(R.layout.design_transfer_money);


        /*Go Pay view initialization start*/
        sendMoneyTxt = dialog_transfer.findViewById(R.id.sendMoneyTxt);
        resendOtpArea = dialog_transfer.findViewById(R.id.resendOtpArea);
        otpArea = dialog_transfer.findViewById(R.id.otpArea);
        moneyArea = dialog_transfer.findViewById(R.id.moneyArea);


        whomTxt = dialog_transfer.findViewById(R.id.whomTxt);
        transferMoneyTagTxt = dialog_transfer.findViewById(R.id.transferMoneyTagTxt);
        driverRadioBtn = dialog_transfer.findViewById(R.id.driverRadioBtn);
        userRadioBtn = dialog_transfer.findViewById(R.id.userRadioBtn);

        rg_whomType = dialog_transfer.findViewById(R.id.rg_whomType);
        detailBox = dialog_transfer.findViewById(R.id.detailBox);
        verificationArea = dialog_transfer.findViewById(R.id.verificationArea);
        infoArea = dialog_transfer.findViewById(R.id.infoArea);
        otpverificationCodeBox = dialog_transfer.findViewById(R.id.otpverificationCodeBox);
        otpverificationCodeBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        moneyTitleTxt = dialog_transfer.findViewById(R.id.moneyTitleTxt);
        userNameTxt = dialog_transfer.findViewById(R.id.userNameTxt);
        toWhomTransferArea = dialog_transfer.findViewById(R.id.toWhomTransferArea);
        moneyDetailArea = dialog_transfer.findViewById(R.id.moneyDetailArea);

        transferMoneyAddDetailArea = dialog_transfer.findViewById(R.id.transferMoneyAddDetailArea);
        toUserImgView = dialog_transfer.findViewById(R.id.toUserImgView);
        btn_type3 = ((MaterialRippleLayout) dialog_transfer.findViewById(R.id.btn_type3)).getChildView();
        btn_type4 = ((MaterialRippleLayout) dialog_transfer.findViewById(R.id.btn_type4)).getChildView();
        btn_type4.setEnabled(false);
        btn_otp = ((MaterialRippleLayout) dialog_transfer.findViewById(R.id.btn_otp)).getChildView();
        MTextView cancelTxt = dialog_transfer.findViewById(R.id.cancelTxt);
        MTextView cancelTransTxt = dialog_transfer.findViewById(R.id.cancelTransTxt);
        MTextView cancelOtpTxt = dialog_transfer.findViewById(R.id.cancelOtpTxt);
        MTextView addMoneyNote = dialog_transfer.findViewById(R.id.addMoneyNote);
        transferMoneyToWallet = dialog_transfer.findViewById(R.id.transferMoneyToWallet);
        autofitEditText = dialog_transfer.findViewById(R.id.autofitEditText);
        ImageView backTansImage = dialog_transfer.findViewById(R.id.backTansImage);
        otpInfoTxt = dialog_transfer.findViewById(R.id.otpInfoTxt);
        MTextView currencyTxt = dialog_transfer.findViewById(R.id.currencyTxt);

        if (generalFunc.isRTLmode()) {
            backTansImage.setRotation(180);
        }

        currencyTxt.setText(generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        cancelTransTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        cancelOtpTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        btn_type3.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
        btn_otp.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_SUBMIT_TXT"));
        MTextView resendOtpTxt = dialog_transfer.findViewById(R.id.resendOtpTxt);
        resendOtpTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RESEND_OTP_TXT"));


        addMoneyNote.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_AMOUNT_TXT"));


        ic_back_arrow = dialog_transfer.findViewById(R.id.ic_back_arrow);


        addTransferArea = dialog_transfer.findViewById(R.id.addTransferArea);
        ProfileImageArea = dialog_transfer.findViewById(R.id.ProfileImageArea);

        addToClickHandler(infoArea);
        addToClickHandler(addTransferArea);

        rechargeBox = dialog_transfer.findViewById(R.id.rechargeBox);


        //rechargeBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_RECHARGE_AMOUNT_TXT"), generalFunc.retrieveLangLBl("", "LBL_RECHARGE_AMOUNT_TXT"));
        //  rechargeBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        autofitEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);

        autofitEditText.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        rechargeBox.setBackgroundResource(android.R.color.transparent);
        rechargeBox.setHideUnderline(true);
        rechargeBox.setTextSize(getActContext().getResources().getDimension(R.dimen._18ssp));
        autofitEditText.setText(defaultAmountVal);
        autofitEditText.setTextColor(getActContext().getResources().getColor(R.color.black));
        rechargeBox.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
        ImageView minusImageView = dialog_transfer.findViewById(R.id.minusImageView);
        ImageView addImageView = dialog_transfer.findViewById(R.id.addImageView);

        addImageView.setOnClickListener(view -> mangePluseView(autofitEditText));
        minusImageView.setOnClickListener(view -> mangeMinusView(autofitEditText));



        /*Go Pay Label Start*/
        sendMoneyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_MONEY"));
        whomTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRANSFER_TO_WHOM"));
        transferMoneyTagTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_MONEY_TXT1"));
        String currentMobileNum = generalFunc.getJsonValueStr("vPhone", obj_userProfile);
        otpInfoTxt.setText(generalFunc.retrieveLangLBl("", Utils.checkText(currentMobileNum) ? "LBL_TRANSFER_WALLET_OTP_EMAIL_NONMANDATORY_INFO_TXT" : "LBL_TRANSFER_WALLET_OTP_INFO_TXT"));
        otpInfoTxt.setVisibility(View.VISIBLE);

        String lblDriver = "LBL_DRIVER";
        if (ServiceModule.ServiceBid || ServiceModule.ServiceProvider || ServiceModule.VideoCall) {
            lblDriver = "LBL_PROVIDER";
        }
        driverRadioBtn.setText(generalFunc.retrieveLangLBl("", lblDriver));
        userRadioBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RIDER"));

        detailBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_GO_PAY_EMAIL_OR_PHONE_TXT"));

        otpverificationCodeBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_ENTER_GOPAY_VERIFICATION_CODE"));
        moneyTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRANSFER_MONEY_TXT"));


        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR_TXT");
        error_verification_code = generalFunc.retrieveLangLBl("", "LBL_VERIFICATION_CODE_INVALID");

        btn_type4.setId(Utils.generateViewId());

        btn_type3.setOnClickListener(v -> {

            transferState = "Search";


            if (rg_whomType.getCheckedRadioButtonId() != driverRadioBtn.getId() && rg_whomType.getCheckedRadioButtonId() != userRadioBtn.getId()) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_SELECT_ANY_MEMBER_OPTION_TXT"));
                return;
            }

            boolean detailEntered = Utils.checkText(detailBox) ? true : Utils.setErrorFields(detailBox, required_str);
            if (!detailEntered) {
                return;
            }
            String regexStr = "^[0-9]*$";

            if (detailBox.getText().toString().trim().replace("+", "").matches(regexStr)) {
                if (detailEntered) {
                    detailEntered = detailBox.length() >= 3 ? true : Utils.setErrorFields(detailBox, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
                }

            } else {
                detailEntered = Utils.checkText(detailBox) ?
                        (generalFunc.isEmailValid(Utils.getText(detailBox)) ? true : Utils.setErrorFields(detailBox, error_email_str))
                        : Utils.setErrorFields(detailBox, required_str);


            }
            if (!detailEntered) {
                return;
            }


            detailBoxVal = Utils.getText(detailBox);

            transferMoneyToWallet();

        });

        resendOtpArea.setOnClickListener(v -> {

            if (!isClicked) {
                isClicked = true;
                isRegenerate = "Yes";
                transferState = "ENTER_AMOUNT";
                transferMoneyToWallet();
            }
        });
        btn_type4.setOnClickListener(v -> {


            if (Utils.checkText(autofitEditText) && GeneralFunctions.parseDoubleValue(0, autofitEditText.getTxt()) > 0) {
            } else {
                return;

            }


            double moneyAdded = GeneralFunctions.parseDoubleValue(0, autofitEditText.getTxt());
            boolean addMoneyAmountEntered = Utils.checkText(autofitEditText) ? (moneyAdded > 0 || Utils.setErrorFields(autofitEditText, error_money_str))
                    : Utils.setErrorFields(autofitEditText, required_str);

            if (!addMoneyAmountEntered) {
                return;
            }

            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TRANSFER_TO_WALLET_TXT") + " " + generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TRANSFER_TO_WALLET_TXT1") + " " + username + " ?");
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
            generateAlert.setBtnClickList(btn_id -> {

                if (btn_id == 1) {
                    transferMoneyToWallet();

                } else {
                    generateAlert.closeAlertBox();
                }

            });

            generateAlert.showAlertBox();


        });

        btn_otp.setOnClickListener(v -> {

            boolean isCodeEntered = Utils.checkText(otpverificationCodeBox) ?
                    ((verificationCode.equalsIgnoreCase(Utils.getText(otpverificationCodeBox))) ? true
                            : Utils.setErrorFields(otpverificationCodeBox, error_verification_code)) : Utils.setErrorFields(otpverificationCodeBox, required_str);
            if (!isCodeEntered) {
                return;
            }
            transferMoneyToWallet();
        });

        autofitEditText.addTextChangedListener(btn_type4, false);


        /*Go Pay view Click handling End*/

        cancelTxt.setOnClickListener(view -> dialog_transfer.dismiss());
        cancelTransTxt.setOnClickListener(view -> dialog_transfer.dismiss());

        backTansImage.setOnClickListener(view -> {

            if (otpArea.getVisibility() == View.VISIBLE) {

                moneyArea.setVisibility(View.VISIBLE);
                otpArea.setVisibility(View.GONE);
                transferState = "ENTER_AMOUNT";
            } else {
                autofitEditText.setText(defaultAmountVal);
                transferMoneyToWallet.setVisibility(View.VISIBLE);
                addTransferArea.setVisibility(View.GONE);
                ProfileImageArea.setVisibility(View.GONE);
                transferState = "Search";

            }
        });

        cancelOtpTxt.setOnClickListener(view -> dialog_transfer.dismiss());

        dialog_transfer.setCanceledOnTouchOutside(true);
        Window window = dialog_transfer.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        dialog_transfer.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        LayoutDirection.setLayoutDirection(dialog_transfer);
        dialog_transfer.show();

    }

    public void openAddMoneyDialog() {
        dialog_add_money = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        dialog_add_money.setContentView(R.layout.add_money_layout);
        MTextView titleTxt = dialog_add_money.findViewById(R.id.titleTxt);
        MTextView addMoneyNote = dialog_add_money.findViewById(R.id.addMoneyNote);
        ImageView minusImageView = dialog_add_money.findViewById(R.id.minusImageView);
        ImageView addImageView = dialog_add_money.findViewById(R.id.addImageView);
        MTextView addMoneybtn1 = dialog_add_money.findViewById(R.id.addMoneybtn1);
        MTextView addMoneybtn2 = dialog_add_money.findViewById(R.id.addMoneybtn2);
        MTextView addMoneybtn3 = dialog_add_money.findViewById(R.id.addMoneybtn3);
        MTextView cancelTxt = dialog_add_money.findViewById(R.id.cancelTxt);
        MTextView currencyTxt = dialog_add_money.findViewById(R.id.currencyTxt);
        autofitEditText = dialog_add_money.findViewById(R.id.autofitEditText);

        rechargeBox = dialog_add_money.findViewById(R.id.rechargeBox);
        autofitEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL | InputType.TYPE_NUMBER_FLAG_SIGNED);

        currencyTxt.setText(generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile));

        autofitEditText.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        rechargeBox.setBackgroundResource(android.R.color.transparent);
        rechargeBox.setHideUnderline(true);
        rechargeBox.setTextSize(getActContext().getResources().getDimension(R.dimen._18ssp));
        autofitEditText.setText(defaultAmountVal);

        rechargeBox.setTextColor(getActContext().getResources().getColor(R.color.black));
        rechargeBox.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"));
        addMoneyNote.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY_MSG"));


        addMoneybtn1.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("WALLET_FIXED_AMOUNT_1", obj_userProfile)));
        addMoneybtn2.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("WALLET_FIXED_AMOUNT_2", obj_userProfile)));
        addMoneybtn3.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("WALLET_FIXED_AMOUNT_3", obj_userProfile)));


        MButton btn_type2 = ((MaterialRippleLayout) dialog_add_money.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setEnabled(false);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_DONE"));

        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        autofitEditText.addTextChangedListener(btn_type2, false);

        btn_type2.setOnClickListener(view -> manageButtonView(autofitEditText));
        cancelTxt.setOnClickListener(view -> dialog_add_money.dismiss());


        addImageView.setOnClickListener(view -> mangePluseView(autofitEditText));
        minusImageView.setOnClickListener(view -> mangeMinusView(autofitEditText));


        addMoneybtn1.setOnClickListener(v -> autofitEditText.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.00, generalFunc.getJsonValueStr("WALLET_FIXED_AMOUNT_1", obj_userProfile)))));
        addMoneybtn2.setOnClickListener(v -> autofitEditText.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.00, generalFunc.getJsonValueStr("WALLET_FIXED_AMOUNT_2", obj_userProfile)))));
        addMoneybtn3.setOnClickListener(v -> autofitEditText.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.00, generalFunc.getJsonValueStr("WALLET_FIXED_AMOUNT_3", obj_userProfile)))));


        //dialog_add_money.setCanceledOnTouchOutside(true);
        Window window = dialog_add_money.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        dialog_add_money.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        LayoutDirection.setLayoutDirection(dialog_add_money);
        dialog_add_money.show();
    }

    public void openSucessDialog() {

        dialog_sucess = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        dialog_sucess.setContentView(R.layout.sucess_layout);
        MTextView titleTxt = dialog_sucess.findViewById(R.id.titleTxt);
        MTextView msgTxt = dialog_sucess.findViewById(R.id.msgTxt);
        MTextView priceTxt = dialog_sucess.findViewById(R.id.priceTxt);
        MTextView nametxt = dialog_sucess.findViewById(R.id.nametxt);
        MTextView transDateTxt = dialog_sucess.findViewById(R.id.transDateTxt);
        MTextView transDateValTxt = dialog_sucess.findViewById(R.id.transDateValTxt);
        SelectableRoundedImageView UserImgView = dialog_sucess.findViewById(R.id.UserImgView);

        FrameLayout dialogImgAlpha = (FrameLayout) dialog_sucess.findViewById(R.id.dialogImgAlpha);
        dialogImgAlpha.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_circle));
        dialogImgAlpha.getBackground().setAlpha(70);

        transDateTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRANSACTION_DONE"));
        transDateValTxt.setText(generalFunc.convertNumberWithRTL(transactionDate));

        msgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_MONEY_TO") + " " + username);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUCCESSFULLY"));

        nametxt.setText(username);
        priceTxt.setText(Utils.checkText(fAmountWithSymbol) ? fAmountWithSymbol : amount);
        new LoadImage.builder(LoadImage.bind(Utils.checkText(userImage) ? userImage : "Temp"), UserImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        MButton btn_type2 = ((MaterialRippleLayout) dialog_sucess.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_DONE"));


        btn_type2.setOnClickListener(view -> {
            removeValues(true);
            list.clear();
            getRecentTransction(false);
            dialog_sucess.dismiss();
        });


        dialog_sucess.show();

    }

    public void manageButtonView(AutoFitEditText rechargeBox) {
        if (Utils.checkText(rechargeBox) && GeneralFunctions.parseDoubleValue(0, rechargeBox.getTxt()) > 0) {
            checkValues(rechargeBox);
            rechargeBox.setText(defaultAmountVal);
        }
    }

    public void mangeMinusView(AutoFitEditText rechargeBox) {
        if (Utils.checkText(rechargeBox) && GeneralFunctions.parseDoubleValue(0, rechargeBox.getTxt()) > 0) {
            rechargeBox.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, rechargeBox.getTxt()) - 1));
        } else {
            rechargeBox.setText(defaultAmountVal);
        }
    }

    public void mangePluseView(AutoFitEditText rechargeBox) {
        if (Utils.checkText(rechargeBox)) {
            rechargeBox.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, rechargeBox.getTxt()) + 1));
        } else {
            rechargeBox.setText("1.00");
        }
    }
}