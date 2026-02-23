package com.fragments;

import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.InputFilter;
import android.text.InputType;
import android.text.TextUtils;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewTreeObserver;
import android.view.Window;
import android.widget.CompoundButton;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RadioButton;
import android.widget.RadioGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.AppCompatCheckBox;
import androidx.cardview.widget.CardView;
import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;
import androidx.recyclerview.widget.RecyclerView;

import com.act.ContactUsActivity;
import com.act.MyWalletHistoryActivity;
import com.act.PaymentWebviewActivity;
import com.act.deliverAll.ServiceHomeActivity;
import com.adapter.files.RecentTransactionsTypeRecycleAdapter;
import com.adapter.files.WalletHistoryRecycleAdapter;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.model.TransactionTypesModel;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
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
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Locale;

public class MyWalletFragment extends BaseFragment implements CompoundButton.OnCheckedChangeListener, RecentTransactionsTypeRecycleAdapter.OnTypeClickList {

    public static final int TRANSFER_MONEY = 87;
    private static final int WEBVIEWPAYMENT = 001;
    private static final int SEL_CARD = 004;
    private final long DELAY = 1000; // in ms
    public GeneralFunctions generalFunc;
    View view;
    MTextView titleTxt;
    ImageView backImgView;
    ProgressBar loading_wallet_history;
    MTextView viewTransactionsTxt, walletamountTxt;
    ErrorView errorView;
    String required_str = "";
    String error_money_str = "";
    String userProfileJson = "";
    boolean mIsLoading = false;
    String next_page_str = "0";
    ServiceHomeActivity serviceHomeActivity;
    AppCompatCheckBox useBalChkBox;
    MTextView useBalanceTxt;
    InternetConnection intCheck;
    AVLoadingIndicatorView loaderView;
    WKWebView paymentWebview;
    // Go Pay view declaration start
    LinearLayout addTransferArea, ProfileImageArea;
    String transferState = "SEARCH";
    MTextView sendMoneyTxt, transferMoneyTagTxt, seeAllTxt;
    RadioButton driverRadioBtn, userRadioBtn;
    RadioGroup rg_whomType;
    MaterialEditText detailBox, otpverificationCodeBox;
    FrameLayout verificationArea;
    LinearLayout infoArea;
    ImageView ic_back_arrow, transerCardArea, addMoneyCardArea, imgseeAll;
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
    LinearLayout addMoneyArea, transerArea, TransactionArea, helpArea, seeAllArea;
    MTextView transferTxt, transactionTxt, recentTransHTxt, noTransactionTxt, helpTxt, labletxt;
    ArrayList<HashMap<String, String>> list = new ArrayList<>();
    ArrayList<TransactionTypesModel> typeList = new ArrayList<TransactionTypesModel>();
    // Go Pay view declaration end
    boolean isNextPageAvailable = false;
    RecyclerView recentTransactionRecyclerView, trasactionCategoryRecyclerView;
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
    String amount = "";
    String transactionDate = "";
    String crtSelectedType = "All";
    LinearLayout WalletContentArea;
    Dialog dialog_add_money, dialog_transfer, dialog_sucess;
    MTextView otpInfoTxt;
    private MaterialEditText rechargeBox;
    private AutoFitEditText autofitEditText;
    private final String defaultAmountVal = AutoFitEditText.convertCommaToDecimal("0.00", false);
    private MTextView yourBalTxt;
    private MButton btn_type1;
    private MTextView addMoneyTxt;
    private WalletHistoryRecycleAdapter wallethistoryRecyclerAdapter;
    private RecentTransactionsTypeRecycleAdapter recentTransactionsTypeRecycleAdapter;
    private String fAmountWithSymbol;
    private ServerTask currentCallExeWebServer;
    boolean isTypeClick = false;

    private NestedScrollView nestedContentArea;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        view = inflater.inflate(R.layout.activity_mywallet, container, false);

        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());
        intCheck = new InternetConnection(getActContext());
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        requireActivity().getWindow().setStatusBarColor(getActContext().getResources().getColor(R.color.appThemeColor_1));

        WalletContentArea = view.findViewById(R.id.WalletContentArea);
        transerCardArea = view.findViewById(R.id.transerCardArea);
        addMoneyCardArea = view.findViewById(R.id.addMoneyCardArea);
        titleTxt = view.findViewById(R.id.titleTxt);
        backImgView = view.findViewById(R.id.backImgView);
        backImgView.setVisibility(View.GONE);

        if (getActivity() instanceof ServiceHomeActivity) {
            serviceHomeActivity = (ServiceHomeActivity) getActivity();
            generalFunc = serviceHomeActivity.generalFunc;
            backImgView.setVisibility(View.VISIBLE);
        }

        walletamountTxt = view.findViewById(R.id.walletamountTxt);
        loading_wallet_history = view.findViewById(R.id.loading_wallet_history);
        viewTransactionsTxt = view.findViewById(R.id.viewTransactionsTxt);
        errorView = view.findViewById(R.id.errorView);
        addMoneyTxt = view.findViewById(R.id.addMoneyTxt);
        addMoneyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY_TXT"));
        errorView = view.findViewById(R.id.errorView);
        nestedContentArea = view.findViewById(R.id.nestedContentArea);
        yourBalTxt = view.findViewById(R.id.yourBalTxt);
        seeAllTxt = view.findViewById(R.id.seeAllTxt);
        seeAllTxt.setText(generalFunc.retrieveLangLBl("See all", "LBL_SEE_ALL"));
        labletxt = view.findViewById(R.id.labletxt);
        labletxt.setText(generalFunc.retrieveLangLBl("My Wallet", "LBL_MY_WALLET"));
        btn_type1 = ((MaterialRippleLayout) view.findViewById(R.id.btn_type1)).getChildView();

        addMoneyArea = view.findViewById(R.id.addMoneyArea);
        transerArea = view.findViewById(R.id.transerArea);
        TransactionArea = view.findViewById(R.id.TransactionArea);
        helpArea = view.findViewById(R.id.helpArea);
        transferTxt = view.findViewById(R.id.transferTxt);
        transactionTxt = view.findViewById(R.id.transactionTxt);
        recentTransHTxt = view.findViewById(R.id.recentTransHTxt);
        noTransactionTxt = view.findViewById(R.id.noTransactionTxt);
        helpTxt = view.findViewById(R.id.helpTxt);
        seeAllArea = view.findViewById(R.id.seeAllArea);
        imgseeAll = view.findViewById(R.id.imgseeAll);
        recentTransactionRecyclerView = view.findViewById(R.id.recentTransactionRecyclerView);
        trasactionCategoryRecyclerView = view.findViewById(R.id.trasactionCategoryRecyclerView);
        addMoneyArea.setOnClickListener(new setOnClickList());
        transerArea.setOnClickListener(new setOnClickList());
        TransactionArea.setOnClickListener(new setOnClickList());
        helpArea.setOnClickListener(new setOnClickList());
        seeAllArea.setOnClickListener(new setOnClickList());

        if (generalFunc.isRTLmode()) {
            imgseeAll.setRotation(180);
        }

        useBalanceTxt = view.findViewById(R.id.useBalanceTxt);
        useBalChkBox = view.findViewById(R.id.useBalChkBox);
        paymentWebview = view.findViewById(R.id.paymentWebview);
        loaderView = view.findViewById(R.id.loaderView);
        backImgView.setOnClickListener(new setOnClickList());
        viewTransactionsTxt.setOnClickListener(new setOnClickList());
        btn_type1.setId(Utils.generateViewId());
        btn_type1.setOnClickListener(new setOnClickList());


        setLabels();

        useBalChkBox.setOnCheckedChangeListener(this);
        wallethistoryRecyclerAdapter = new

                WalletHistoryRecycleAdapter(getActContext(), list, generalFunc, false);
        typeList.clear();
        typeList.add(new
                TransactionTypesModel(0, generalFunc.retrieveLangLBl("All", "LBL_ALL"), true, 1));
        typeList.add(new
                TransactionTypesModel(R.drawable.ic_credit_new,
                generalFunc.retrieveLangLBl("Credit", "LBL_CREDIT"), false, 2));
        typeList.add(new
                TransactionTypesModel(R.drawable.ic_debit_new, generalFunc.retrieveLangLBl("Debit", "LBL_DEBIT"), false, 3));
        recentTransactionsTypeRecycleAdapter = new

                RecentTransactionsTypeRecycleAdapter(getActContext(), generalFunc, typeList);
        recentTransactionRecyclerView.setAdapter(wallethistoryRecyclerAdapter);
        trasactionCategoryRecyclerView.setAdapter(recentTransactionsTypeRecycleAdapter);
        trasactionCategoryRecyclerView.setAdapter(recentTransactionsTypeRecycleAdapter);
        trasactionCategoryRecyclerView.setVisibility(View.GONE);


        nestedContentArea.getViewTreeObserver().addOnScrollChangedListener(new ViewTreeObserver.OnScrollChangedListener() {
            @Override
            public void onScrollChanged() {
                View view = (View) nestedContentArea.getChildAt(nestedContentArea.getChildCount() - 1);

                int diff = (view.getBottom() - (nestedContentArea.getHeight() + nestedContentArea
                        .getScrollY()));

                if (diff == 0 && !(mIsLoading) && isNextPageAvailable == true) {
                    mIsLoading = true;
                    wallethistoryRecyclerAdapter.addFooterView();

                    getRecentTransction(true);

                } else if (isNextPageAvailable == false) {
                    wallethistoryRecyclerAdapter.removeFooterView();
                }
            }
        });

        recentTransactionsTypeRecycleAdapter.setOnItemClickList(this::onTypeClick);

        getRecentTransction(false);
        showHideButton("");

        return view;
    }

    private void showHideButton(String setView) {

        boolean isOnlyCashEnabled = generalFunc.getJsonValue("APP_PAYMENT_MODE", userProfileJson).equalsIgnoreCase("Cash");
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

    public Context getActContext() {
        return getActivity();
    }

    public void setLabels() {

        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LEFT_MENU_WALLET"));
        yourBalTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BALANCE"));
        viewTransactionsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_TRANS_HISTORY"));
        btn_type1.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_TRANS_HISTORY"));
        useBalanceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USE_WALLET_BALANCE_NOTE"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_money_str = generalFunc.retrieveLangLBl("", "LBL_ADD_CORRECT_DETAIL_TXT");


        useBalChkBox.setChecked(!generalFunc.getJsonValue("eWalletAdjustment", userProfileJson).equals("No"));


        transferTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRANSFER"));
        transactionTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRANSACTIONS"));
        helpTxt.setText((generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT")));
        recentTransHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECENT_TRANSACTION"));
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (resultCode == getActivity().RESULT_OK && requestCode == WEBVIEWPAYMENT) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.retrieveLangLBl("", "LBL_WALLET_MONEY_CREDITED")), "", generalFunc.retrieveLangLBl("", "LBL_OK"), i -> {
                list.clear();
                getRecentTransction(false);
            });

        }
        if (resultCode == getActivity().RESULT_OK && requestCode == SEL_CARD) {
            if (ServiceModule.isDeliverAllOnly()) {
                if (dialog_add_money != null) {
                    dialog_add_money.dismiss();
                }

            }
            getTransactionHistory(false);

        } else if (resultCode == getActivity().RESULT_OK && requestCode == TRANSFER_MONEY) {
            list.clear();
            getRecentTransction(false);


        }

    }

    @Override
    public void onResume() {
        super.onResume();

    }

    public void getWalletBalDetails() {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "GetMemberWalletBalance");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {


            if (responseString != null && !responseString.equals("")) {

                closeLoader();

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    try {

                        String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                        JSONObject object = generalFunc.getJsonObject(userProfileJson);

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

        String prvSelectedType = crtSelectedType;


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
        }


        for (TransactionTypesModel selType : typeList) {
            selType.setSelected(selType.getCatType() == transactionTypesModel.getCatType());
        }
        recentTransactionsTypeRecycleAdapter.updatelist(typeList);
    }

    private void animateDialog(LinearLayout infoArea) {
        String currentMobileNum = generalFunc.getJsonValue("vPhone", userProfileJson);
        CustomDialog customDialog = new CustomDialog(getActContext(), generalFunc);
        customDialog.setDetails(""/*generalFunc.retrieveLangLBl("","LBL_RETRIVE_OTP_TITLE_TXT")*/, generalFunc.retrieveLangLBl("", Utils.checkText(currentMobileNum) ? "LBL_TRANSFER_WALLET_OTP_EMAIL_NONMANDATORY_INFO_TXT" : "LBL_TRANSFER_WALLET_OTP_INFO_TXT"), generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), "", false, R.drawable.ic_normal_info, false, 2, false);
        customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
        customDialog.setRoundedViewBorderColor(R.color.white);
        customDialog.setImgStrokWidth(15);
        customDialog.setBtnRadius(10);
        customDialog.setIconTintColor(R.color.white);
        customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
        customDialog.setPositiveBtnTextColor(R.color.white);
        customDialog.createDialog();
        customDialog.setPositiveButtonClick(() -> {

        });
        customDialog.setNegativeButtonClick(() -> {

        });
        customDialog.show();
    }


    public void getRecentTransction(final boolean isLoadMore) {

        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }

        if (loading_wallet_history.getVisibility() != View.VISIBLE && !isLoadMore) {
            if (/*list.size() == 0 &&*/ !isTypeClick) {
                //WalletContentArea.setVisibility(View.GONE);
                SkeletonViewHandler.getInstance().ShowNormalSkeletonView(WalletContentArea, R.layout.wallet_shimmer_view_new);
            } else {
                recentTransactionRecyclerView.setVisibility(View.GONE);
                noTransactionTxt.setVisibility(View.GONE);
                loading_wallet_history.setVisibility(View.VISIBLE);
            }

        }

        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getTransactionHistory");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("ListType", crtSelectedType);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        noTransactionTxt.setVisibility(View.GONE);


        currentCallExeWebServer = ApiHandler.execute(getActContext(), parameters, responseString -> {

            noTransactionTxt.setVisibility(View.GONE);
            isTypeClick = false;
            if (responseString != null && !responseString.equals("")) {

                currentCallExeWebServer = null;
                closeLoader();

                if (!isLoadMore) {
                    list.clear();
                }

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString) == true) {

                    String nextPage = generalFunc.getJsonValue("NextPage", responseString);
                    JSONArray arr_transhistory = generalFunc.getJsonArray(Utils.message_str, responseString);
                    walletamountTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("MemberBalance", responseString)));

                    if (!generalFunc.getJsonValue("user_available_balance", userProfileJson).equalsIgnoreCase(generalFunc.getJsonValue("MemberBalance", responseString))) {
                        try {
                            JSONObject object = generalFunc.getJsonObject(userProfileJson);
                            object.put("user_available_balance", generalFunc.getJsonValue("MemberBalance", responseString));
                            generalFunc.storeData(Utils.USER_PROFILE_JSON, object.toString());
                        } catch (JSONException e) {
                            Logger.e("Exception", "::" + e.getMessage());
                        }
                        generalFunc.storeData(Utils.ISWALLETBALNCECHANGE, "Yes");
                    }

                    if (arr_transhistory != null && arr_transhistory.length() > 0) {
                        for (int i = 0; i < arr_transhistory.length(); i++) {
                            //   for (int i = 0; i < 10; i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_transhistory, i);
                            HashMap<String, String> map = new HashMap<String, String>();
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
                            //map.put("listingFormattedDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dDateOrig, Utils.OriginalDateFormate, Utils.dateFormateInList)));
                            //map.put("listingFormattedDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dDateOrig, Utils.OriginalDateFormate, "dd MMM, yyyy(E)")));
                            String iBalance = generalFunc.getJsonValueStr("iBalance", obj_temp);
                            map.put("iBalance", iBalance);
                            map.put("FormattediBalance", generalFunc.convertNumberWithRTL(iBalance));

                            map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                            map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", obj_temp));
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
                    recentTransactionRecyclerView.setVisibility(View.VISIBLE);
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

                SkeletonViewHandler.getInstance().hideSkeletonView(R.layout.wallet_shimmer_view_new);
            } else {
                if (isLoadMore == false) {
                    removeNextPageConfig();
                    generateErrorView();
                }

            }
            mIsLoading = false;
            trasactionCategoryRecyclerView.smoothScrollToPosition(0);
            trasactionCategoryRecyclerView.setVisibility(View.VISIBLE);
        });


    }

    public void UpdateUserWalletAdjustment(boolean value) {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "UpdateUserWalletAdjustment");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("eWalletAdjustment", value == true ? "Yes" : "No");

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                closeLoader();

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail == true) {

                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

                    generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_INFO_UPDATED_TXT"));

                } else {

                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));

                    useBalChkBox.setOnCheckedChangeListener(null);
                    useBalChkBox.setChecked(value != true);
                    useBalChkBox.setOnCheckedChangeListener(this);
                }
            } else {
                closeLoader();
                generalFunc.showError();
                useBalChkBox.setOnCheckedChangeListener(null);
                useBalChkBox.setChecked(value != true);
                useBalChkBox.setOnCheckedChangeListener(this);
            }
        });
        exeWebServer.setCancelAble(false);
    }

    public void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        wallethistoryRecyclerAdapter.removeFooterView();
    }

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
        if (ServiceModule.OnlyRideSharingPro || ServiceModule.isOnlyBuySellRentEnable()) {
            toWhomTransferArea.setVisibility(View.GONE);
        } else {
            toWhomTransferArea.setVisibility(View.VISIBLE);
        }
        transferMoneyAddDetailArea = dialog_transfer.findViewById(R.id.transferMoneyAddDetailArea);
        toUserImgView = dialog_transfer.findViewById(R.id.toUserImgView);
        btn_type3 = ((MaterialRippleLayout) dialog_transfer.findViewById(R.id.btn_type3)).getChildView();
        btn_type4 = ((MaterialRippleLayout) dialog_transfer.findViewById(R.id.btn_type4)).getChildView();
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


        currencyTxt.setText(generalFunc.getJsonValue("vCurrencyPassenger", userProfileJson));
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

        infoArea.setOnClickListener(new setOnClickList());
        addTransferArea.setOnClickListener(new setOnClickList());

        rechargeBox = dialog_transfer.findViewById(R.id.rechargeBox);
        autofitEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);

        autofitEditText.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        rechargeBox.setBackgroundResource(android.R.color.transparent);
        rechargeBox.setHideUnderline(true);
        rechargeBox.setTextSize(getActContext().getResources().getDimension(R.dimen._18ssp));
        autofitEditText.setText(defaultAmountVal);
        btn_type4.setEnabled(false);
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
        String currentMobileNum = generalFunc.getJsonValue("vPhone", userProfileJson);
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
        /*Go Pay Label End*/





        /*Go Pay view initialization end*/



        /*Go Pay view Click handling Start*/

        btn_type3.setOnClickListener(v -> {

            transferState = "Search";

            if (!ServiceModule.OnlyRideSharingPro && !ServiceModule.isOnlyBuySellRentEnable()) {
                if (rg_whomType.getCheckedRadioButtonId() != driverRadioBtn.getId() && rg_whomType.getCheckedRadioButtonId() != userRadioBtn.getId()) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_SELECT_ANY_MEMBER_OPTION_TXT"));
                    return;
                }
            }


            boolean detailEntered = Utils.checkText(detailBox) || Utils.setErrorFields(detailBox, required_str);
            if (detailEntered == false) {
                return;
            }
            String regexStr = "^[0-9]*$";

            if (detailBox.getText().toString().trim().replace("+", "").matches(regexStr)) {
                if (detailEntered) {
                    detailEntered = detailBox.length() >= 3 || Utils.setErrorFields(detailBox, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
                }

            } else {
                detailEntered = Utils.checkText(detailBox) ?
                        (generalFunc.isEmailValid(Utils.getText(detailBox)) || Utils.setErrorFields(detailBox, error_email_str))
                        : Utils.setErrorFields(detailBox, required_str);


            }
            if (detailEntered == false) {
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
                    ((verificationCode.equalsIgnoreCase(Utils.getText(otpverificationCodeBox))) || Utils.setErrorFields(otpverificationCodeBox, error_verification_code)) : Utils.setErrorFields(otpverificationCodeBox, required_str);
            if (isCodeEntered == false) {
                return;
            }
            transferMoneyToWallet();
        });

        autofitEditText.addTextChangedListener(btn_type4, false);


        /*Go Pay view Click handling End*/

        cancelTxt.setOnClickListener(view -> dialog_transfer.dismiss());
        cancelTransTxt.setOnClickListener(view -> {
            dialog_transfer.dismiss();
        });

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

        cancelOtpTxt.setOnClickListener(view -> {
            dialog_transfer.dismiss();
        });

        dialog_transfer.setCanceledOnTouchOutside(true);
        Window window = dialog_transfer.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.FILL_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        dialog_transfer.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        LayoutDirection.setLayoutDirection(dialog_transfer);
        dialog_transfer.show();

    }

    private void transferMoneyToWallet() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("fromUserId", generalFunc.getMemberId());
        parameters.put("fromUserType", Utils.userType);
        //parameters.put("transferType", emailRadioBtn.isChecked() ? "Email" : "Phone");
        if (ServiceModule.OnlyRideSharingPro || ServiceModule.isOnlyBuySellRentEnable()) {
            parameters.put("searchUserType", Utils.userType);
        } else {
            parameters.put("searchUserType", userRadioBtn.isChecked() ? Utils.userType : "Driver");
        }
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

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

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
                        this.amount = generalFunc.getJsonValue("CurrencySymbol", userProfileJson) + "" + generalFunc.convertNumberWithRTL(amount);
                        //transferMap.put("fAmount", generalFunc.getJsonValue("CurrencySymbol", userProfileJson) + "" + generalFunc.convertNumberWithRTL(amount));
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

                        boolean isOnlyCashEnabled = generalFunc.getJsonValue("APP_PAYMENT_MODE", userProfileJson).equalsIgnoreCase("Cash");

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

                        if (dialog_transfer != null) {
                            dialog_transfer.dismiss();
                        }
                        generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue("message_profile_data", responseString));

                        //transactionDate = generalFunc.getJsonValue("transactionDate", responseString);
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

    private void successDialog(String message, String positiveBtnTxt) {
        if (isRegenerate.equalsIgnoreCase("yes")) {
            CustomDialog customDialog = new CustomDialog(getActContext(), generalFunc);
            customDialog.setDetails(""/*generalFunc.retrieveLangLBl("","LBL_OTP_EXPIRED_TXT")*/, message, positiveBtnTxt, "", false, R.drawable.ic_hand_gesture, false, 2, false);
            customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
            customDialog.setRoundedViewBorderColor(R.color.white);
            customDialog.setImgStrokWidth(15);
            customDialog.setBtnRadius(10);
            customDialog.setIconTintColor(R.color.white);
            customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
            customDialog.setPositiveBtnTextColor(R.color.white);
            customDialog.createDialog();
            customDialog.setPositiveButtonClick(new com.general.files.Closure() {
                @Override
                public void exec() {
                    otpverificationCodeBox.setText("");
                    resendOtpArea.setVisibility(View.VISIBLE);
                    resendOtpArea.setOnClickListener(new setOnClickList());
                }
            });
            customDialog.setNegativeButtonClick(new com.general.files.Closure() {
                @Override
                public void exec() {

                }
            });
            customDialog.show();

        } else {
            CustomDialog customDialog = new CustomDialog(getActContext(), generalFunc);
            customDialog.setDetails(""/*generalFunc.retrieveLangLBl("","LBL_MONEY_TRANSFER_CONFIRMATION_TITLE_TXT")*/, message, positiveBtnTxt, "", false, R.drawable.ic_correct, false, 2, false);
            customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
            customDialog.setRoundedViewBorderColor(R.color.white);
            customDialog.setImgStrokWidth(15);
            customDialog.setBtnRadius(10);
            customDialog.setIconTintColor(R.color.white);
            customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
            customDialog.setPositiveBtnTextColor(R.color.white);
            customDialog.createDialog();
            customDialog.setPositiveButtonClick(new com.general.files.Closure() {
                @Override
                public void exec() {
                    transferState = "SEARCH";
                    configureView();
                    generalFunc.storeData(Utils.ISWALLETBALNCECHANGE, "Yes");
                    list.clear();
                    getRecentTransction(false);
                    //getWalletBalDetails();
                }
            });
            customDialog.setNegativeButtonClick(new com.general.files.Closure() {
                @Override
                public void exec() {

                }
            });
            customDialog.show();
        }
    }

    public void getTransactionHistory(final boolean isLoadMore) {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);

        }
        if (loading_wallet_history.getVisibility() != View.VISIBLE && !isLoadMore) {
            loading_wallet_history.setVisibility(View.VISIBLE);

        }

        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getTransactionHistory");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                closeLoader();


                String LBL_BALANCE = generalFunc.getJsonValue("user_available_balance", responseString);

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
        autofitEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);

        currencyTxt.setText(generalFunc.getJsonValue("vCurrencyPassenger", userProfileJson));

        autofitEditText.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        rechargeBox.setBackgroundResource(android.R.color.transparent);
        rechargeBox.setHideUnderline(true);
        rechargeBox.setTextSize(getActContext().getResources().getDimension(R.dimen._18ssp));
        autofitEditText.setText(defaultAmountVal);

        rechargeBox.setTextColor(getActContext().getResources().getColor(R.color.black));
        rechargeBox.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"));
        addMoneyNote.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY_MSG"));


        addMoneybtn1.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("WALLET_FIXED_AMOUNT_1", userProfileJson)));
        addMoneybtn2.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("WALLET_FIXED_AMOUNT_2", userProfileJson)));
        addMoneybtn3.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("WALLET_FIXED_AMOUNT_3", userProfileJson)));


        MButton btn_type2 = ((MaterialRippleLayout) dialog_add_money.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setEnabled(false);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_DONE"));

        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        autofitEditText.addTextChangedListener(btn_type2, false);

        btn_type2.setOnClickListener(view -> manageButtonView(autofitEditText));
        cancelTxt.setOnClickListener(view -> dialog_add_money.dismiss());


        addImageView.setOnClickListener(view -> mangePluseView(autofitEditText));
        minusImageView.setOnClickListener(view -> mangeMinusView(autofitEditText));


        addMoneybtn1.setOnClickListener(v -> autofitEditText.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.00, generalFunc.getJsonValue("WALLET_FIXED_AMOUNT_1", userProfileJson)))));
        addMoneybtn2.setOnClickListener(v -> autofitEditText.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.00, generalFunc.getJsonValue("WALLET_FIXED_AMOUNT_2", userProfileJson)))));
        addMoneybtn3.setOnClickListener(v -> autofitEditText.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.00, generalFunc.getJsonValue("WALLET_FIXED_AMOUNT_3", userProfileJson)))));

        Window window = dialog_add_money.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.FILL_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
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
        //transDateValTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(transactionDate, Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));
        transDateValTxt.setText(transactionDate);
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

    private void removeValues(boolean removeValues) {
        if (removeValues) {
            otpverificationCodeBox.setText("");

            iUserId = "";
            eUserType = "";
            verificationCode = "";
            rg_whomType.clearCheck();
        }
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

    public void checkValues(AutoFitEditText rechargeBox) {

        double moneyAdded = GeneralFunctions.parseDoubleValue(0.0, rechargeBox.getTxt());
        boolean addMoneyAmountEntered = Utils.checkText(rechargeBox) ? (moneyAdded > 0 ? true : Utils.setErrorFields(rechargeBox, error_money_str))
                : Utils.setErrorFields(rechargeBox, required_str);

        if (!addMoneyAmountEntered) {
            return;
        }

        dialog_add_money.dismiss();
        String url = generalFunc.getJsonValue("PAYMENT_BASE_URL", userProfileJson) + "&PAGE_TYPE=WALLET_MONEY_ADD" +
                "&currency=" + generalFunc.getJsonValue("vCurrencyPassenger", userProfileJson) +
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


    public class setOnClickList implements View.OnClickListener {

        @Override
        public void onClick(View view) {
            Utils.hideKeyboard(getActContext());
            int id = view.getId();
            if (id == R.id.viewTransactionsTxt) {
                new ActUtils(getActContext()).startAct(MyWalletHistoryActivity.class);
            } else if (id == btn_type1.getId()) {
                new ActUtils(getActContext()).startAct(MyWalletHistoryActivity.class);
            } else if (id == R.id.viewTransactionsBtnArea) {
                btn_type1.performClick();
            } else if (id == R.id.infoArea) {
                animateDialog(infoArea);
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
            }
        }

    }

}
