package com.fragments;

import android.annotation.SuppressLint;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.Typeface;
import android.graphics.drawable.ColorDrawable;
import android.os.Bundle;
import android.os.Handler;
import android.text.InputType;
import android.text.method.PasswordTransformationMethod;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.WindowManager;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.cardview.widget.CardView;
import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;

import com.act.AppLoginActivity;
import com.act.BankDetailActivity;
import com.act.BookingsActivity;
import com.act.ContactUsActivity;
import com.act.DriverFeedbackActivity;
import com.act.DriverRewardActivity;
import com.act.EmergencyContactActivity;
import com.act.HelpActivity23Pro;
import com.act.InviteFriendsActivity;
import com.act.ListOfDocumentActivity;
import com.act.ManageAccountActivity;
import com.act.ManageVehiclesActivity;
import com.act.MyGalleryActivity;
import com.act.MyProfileActivity;
import com.act.MyWalletActivity;
import com.act.NotificationActivity;
import com.act.PaymentWebviewActivity;
import com.act.PrefranceActivity;
import com.act.SetAvailabilityActivity;
import com.act.StaticPageActivity;
import com.act.StatisticsActivity;
import com.act.SubscriptionActivity;
import com.act.UfxCategoryActivity;
import com.act.UploadDocTypeWiseActivity;
import com.act.VerifyInfoActivity;
import com.act.WayBillActivity;
import com.act.deliverAll.LiveTaskListActivity;
import com.act.giftcard.GiftCardRedeemActivity;
import com.act.giftcard.GiftCardSendActivity;
import com.dialogs.BottomInfoDialog;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.GetUserData;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.PasswordViewHideManager;
import com.general.files.TrendyDialog;
import com.buddyverse.providers.R;
import com.kyleduo.switchbutton.SwitchButton;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class MyProfileFragment extends BaseFragment {

    private static final String TAG = "MyProfileFragment";
    public String userProfileJson = "";
    public JSONObject obj_userProfile;
    GeneralFunctions generalFunc;

    ImageView backImg, editProfileImage, imgSmartLoginQuery;
    SelectableRoundedImageView userImgView, userImgView_toolbar;
    MTextView userNameTxt, userNameTxt_toolbar, userEmailTxt, txtUserMobile, walletHTxt, walletVxt, generalSettingHTxt, accountHTxt;
    MTextView bookingTxt, inviteTxt, topupTxt;
    MTextView notificationHTxt, paymentHTxt, privacyHTxt, termsHTxt, myPaymentHTxt, mybookingHTxt,
            addMoneyHTxt, sendMoneyHTxt, personalDetailsHTxt, changePasswordHTxt, changeCurrencyHTxt, changeLanguageHTxt, supportHTxt, livechatHTxt, contactUsHTxt;
    LinearLayout notificationArea, paymentMethodArea, privacyArea, myBookingArea,
            addMoneyArea, sendMoneyArea, personalDetailsArea, changesPasswordArea, changesCurrancyArea, changeslanguageArea, termsArea, liveChatArea, contactUsArea, smartLoginArea, verifyEmailArea, verifyMobArea;
    View notificationView, paymentView, privacyView, myBookingView, addMoneyView, aboutUsView, myWalletView, statisticsView, userFeedbackView, wayBillView, inviteView, manageDocView,
            sendMoneyView, changeCurrencyView, changeLangView, termsView, livechatView, mySubView, myServiceView, manageGallleryView, myAvailView, smartLoginView, verifyEmailView, verifyMobView;
    LinearLayout bookingArea, inviteArea, topUpArea, logOutArea;
    LinearLayout myWalletArea, inviteFriendArea, helpArea, aboutusArea, headerwalletArea, emeContactArea, bankDetailsArea, manageDocArea, mySubArea,
            myServiceArea, manageGalleryArea, myAvailArea, statisticsArea, userFeedbackArea, wayBillArea, rewardViewArea, walletArea;
    MTextView mywalletHTxt, inviteHTxt, helpHTxt, aboutusHTxt, logoutTxt, otherHTxt, headerwalletTxt, emeContactHTxt, bankDetailsHTxt, manageDocHTxt,
            mySubHTxt, myServiceHTxt, manageGalleryHTxt, myAvailHTxt, statisticsHTxt, userFeedbackHTxt, wayBillHTxt, smartLoginHTxt, verifyEmailHTxt, verifyMobHTxt;
    ImageView notificationArrow, paymentArrow, privacyArrow, termsArrow, mywalletArrow, inviteArrow, helpArrow, aboutusArrow, statisticsArrow, wayBillArrow,
            mybookingArrow, addMoneyArrow, sendMoneyArrow, personalDetailsArrow, manageDocArrow, mySubArrow, myServiceArrow, myAvailArrow, userFeedbackArrow,
            changePasswordArrow, changeCurrencyArrow, changeLangArrow, livechatArrow, contactUsArrow, logoutArrow, emeContactArrow, bankDetailsArrow, manageGalleryArrow, verifyMobsArrow, verifyEmailArrow;

    View view;
    InternetConnection internetConnection;
    String ENABLE_FAVORITE_DRIVER_MODULE_KEY = "";
    boolean isAnyDeliverOptionEnabled;
    AlertDialog alertDialog;
    String SITE_TYPE = "";
    String SITE_TYPE_DEMO_MSG = "";
    private SwitchButton smartLoginSwitchBtn;


    ArrayList<HashMap<String, String>> language_list = new ArrayList<>();
    String selected_language_code = "";
    String default_selected_language_code = "";

    ArrayList<HashMap<String, String>> currency_list = new ArrayList<>();

    String selected_currency = "";
    String default_selected_currency = "";
    String selected_currency_symbol = "";
    ImageView infoImg;

    private int selCurrancyPosition = -1, selLanguagePosition = -1;

    LinearLayout toolbar_profile;
    int MY_BOOKING_REQ_CODE = 788;
    private boolean isUfxServicesEnabled = true;
    private static final int WEBVIEWPAYMENT = 001;

    LinearLayout GiftArea, sendGiftArea, redeemgiftArea;
    MTextView sendGiftHTxt, redeemgiftHTxt, GiftHTxt;
    ImageView sendGiftArrow, redeemgiftArrow;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {

//        if (view != null) {
//            return view;
//        }
        view = inflater.inflate(R.layout.activity_my_profile_new, container, false);
        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());

        updateUserProfileObj();

        internetConnection = new InternetConnection(getActContext());
        initViews();
        setLabel();
        setuserInfo();
        manageView();
        buildLanguageList();
        //Set Configure For Emergency Option
        /*if (generalFunc.retrieveValue("HIDE_EMERGENCY_CONTACT").equalsIgnoreCase("Yes")) {
            emeContactArea.setVisibility(View.GONE);
        }*/

        if (ServiceModule.IsTrackingProvider) {
            editProfileImage.setVisibility(View.GONE);
            CardView headerArea = view.findViewById(R.id.headerArea);
            headerArea.setVisibility(View.GONE);

            RelativeLayout profileArea = view.findViewById(R.id.profileArea);
            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) profileArea.getLayoutParams();
            params.height = (int) getResources().getDimensionPixelSize(R.dimen._90sdp);
            profileArea.setLayoutParams(params);

            mybookingHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRIP"));

            myServiceArea.setVisibility(View.GONE);
            myServiceView.setVisibility(View.GONE);
            manageGalleryArea.setVisibility(View.GONE);
            manageGallleryView.setVisibility(View.GONE);
            myAvailArea.setVisibility(View.GONE);
            myAvailView.setVisibility(View.GONE);
            statisticsArea.setVisibility(View.GONE);
            statisticsView.setVisibility(View.GONE);
            userFeedbackArea.setVisibility(View.GONE);
            userFeedbackView.setVisibility(View.GONE);
            wayBillArea.setVisibility(View.GONE);
            wayBillView.setVisibility(View.GONE);
            inviteFriendArea.setVisibility(View.GONE);
            inviteView.setVisibility(View.GONE);
            manageDocArea.setVisibility(View.GONE);
            manageDocView.setVisibility(View.GONE);
            mySubArea.setVisibility(View.GONE);
            mySubView.setVisibility(View.GONE);
            changesCurrancyArea.setVisibility(View.GONE);
            changeCurrencyView.setVisibility(View.GONE);
            changeLangView.setVisibility(View.GONE);
            rewardViewArea.setVisibility(View.GONE);

            myPaymentHTxt.setVisibility(View.GONE);
            walletArea.setVisibility(View.GONE);
            GiftArea.setVisibility(View.GONE);
            GiftHTxt.setVisibility(View.GONE);
        }

        return view;

    }

    private void manageView() {
        if (generalFunc.getJsonValue("ENABLE_LIVE_CHAT", userProfileJson).equalsIgnoreCase("Yes")) {
            liveChatArea.setVisibility(View.VISIBLE);
            livechatView.setVisibility(View.VISIBLE);
        } else {
            liveChatArea.setVisibility(View.GONE);
            livechatView.setVisibility(View.GONE);
        }

        if (!generalFunc.getJsonValue("showTermsCondition", userProfileJson).equalsIgnoreCase("No")) {
            termsArea.setVisibility(View.VISIBLE);
            termsView.setVisibility(View.VISIBLE);
        } else {
            termsArea.setVisibility(View.GONE);
            termsView.setVisibility(View.GONE);
        }

        if (!generalFunc.getJsonValue("showPrivacyPolicy", userProfileJson).equalsIgnoreCase("No")) {
            privacyArea.setVisibility(View.VISIBLE);
            privacyView.setVisibility(View.VISIBLE);
        } else {
            privacyArea.setVisibility(View.GONE);
            privacyView.setVisibility(View.GONE);
        }

        if (!generalFunc.getJsonValue("showAboutUs", userProfileJson).equalsIgnoreCase("No")) {
            aboutusArea.setVisibility(View.VISIBLE);
            aboutUsView.setVisibility(View.VISIBLE);
        } else {
            aboutusArea.setVisibility(View.GONE);
            aboutUsView.setVisibility(View.GONE);
        }

        if (!generalFunc.getJsonValueStr(Utils.WALLET_ENABLE, obj_userProfile).equals("") &&
                generalFunc.getJsonValueStr(Utils.WALLET_ENABLE, obj_userProfile).equalsIgnoreCase("Yes")) {
            myWalletArea.setVisibility(View.VISIBLE);
            headerwalletArea.setVisibility(View.VISIBLE);
            myWalletView.setVisibility(View.VISIBLE);
            addMoneyArea.setVisibility(View.VISIBLE);
            sendMoneyArea.setVisibility(View.VISIBLE);
            addMoneyView.setVisibility(View.VISIBLE);
            sendMoneyView.setVisibility(View.VISIBLE);
            topUpArea.setVisibility(View.VISIBLE);


        } else {
            myWalletArea.setVisibility(View.GONE);
            headerwalletArea.setVisibility(View.GONE);
            myWalletView.setVisibility(View.GONE);
            addMoneyArea.setVisibility(View.GONE);
            sendMoneyArea.setVisibility(View.GONE);
            addMoneyView.setVisibility(View.GONE);
            sendMoneyView.setVisibility(View.GONE);
            topUpArea.setVisibility(View.GONE);
        }


        if (generalFunc.retrieveValue(Utils.DRIVER_SUBSCRIPTION_ENABLE_KEY).equalsIgnoreCase("Yes")) {
            mySubArea.setVisibility(View.VISIBLE);
            mySubView.setVisibility(View.VISIBLE);
        } else {
            mySubArea.setVisibility(View.GONE);
            mySubView.setVisibility(View.GONE);

        }

        if (generalFunc.getJsonValue("ENABLE_NEWS_SECTION", userProfileJson) != null && generalFunc.getJsonValue("ENABLE_NEWS_SECTION", userProfileJson).equalsIgnoreCase("yes")) {
            notificationArea.setVisibility(View.VISIBLE);
            notificationView.setVisibility(View.VISIBLE);

        } else {
            notificationArea.setVisibility(View.GONE);
            notificationView.setVisibility(View.GONE);

        }
        if (!generalFunc.getJsonValueStr(Utils.REFERRAL_SCHEME_ENABLE, obj_userProfile).equals("") && generalFunc.getJsonValueStr(Utils.REFERRAL_SCHEME_ENABLE, obj_userProfile).equalsIgnoreCase("Yes")) {
            inviteFriendArea.setVisibility(View.VISIBLE);
            inviteArea.setVisibility(View.VISIBLE);
        } else {
            inviteFriendArea.setVisibility(View.GONE);
            inviteArea.setVisibility(View.GONE);
        }

        if (generalFunc.getJsonValueStr("CARD_SAVE_ENABLE", obj_userProfile).equalsIgnoreCase("yes")) {
            paymentMethodArea.setVisibility(View.VISIBLE);
            paymentView.setVisibility(View.VISIBLE);
        } else {
            paymentMethodArea.setVisibility(View.GONE);
            paymentView.setVisibility(View.GONE);
        }

        JSONArray currencyList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.CURRENCY_LIST_KEY));

        if (currencyList_arr != null) {
            if (currencyList_arr.length() < 2) {
                changesCurrancyArea.setVisibility(View.GONE);
                changeCurrencyView.setVisibility(View.GONE);
            } else {
                changesCurrancyArea.setVisibility(View.VISIBLE);
                changeCurrencyView.setVisibility(View.VISIBLE);
            }
        }

        HashMap<String, String> data = new HashMap<>();
        data.put(Utils.LANGUAGE_LIST_KEY, "");
        data.put(Utils.LANGUAGE_CODE_KEY, "");
        data = generalFunc.retrieveValue(data);

        JSONArray languageList_arr = generalFunc.getJsonArray(data.get(Utils.LANGUAGE_LIST_KEY));

        if (languageList_arr.length() < 2) {
            changeslanguageArea.setVisibility(View.GONE);
        } else {
            changeslanguageArea.setVisibility(View.VISIBLE);
        }

        if (generalFunc.getJsonValue("ENABLE_GIFT_CARD_FEATURE", userProfileJson).equalsIgnoreCase("Yes")) {
            GiftArea.setVisibility(View.VISIBLE);
            GiftHTxt.setVisibility(View.VISIBLE);
        } else {
            GiftArea.setVisibility(View.GONE);
            GiftHTxt.setVisibility(View.GONE);
        }
    }

    private void initViews() {
        backImg = view.findViewById(R.id.backImg);
        editProfileImage = view.findViewById(R.id.editProfileImage);
        userImgView = view.findViewById(R.id.userImgView);
        infoImg = view.findViewById(R.id.infoImg);
        addToClickHandler(infoImg);
        userImgView_toolbar = view.findViewById(R.id.userImgView_toolbar);
        userNameTxt = view.findViewById(R.id.userNameTxt);
        userNameTxt_toolbar = view.findViewById(R.id.userNameTxt_toolbar);
        userEmailTxt = view.findViewById(R.id.userEmailTxt);
        txtUserMobile = view.findViewById(R.id.txtUserMobile);
        walletHTxt = view.findViewById(R.id.walletHTxt);
        walletVxt = view.findViewById(R.id.walletVxt);
        bookingTxt = view.findViewById(R.id.bookingTxt);
        inviteTxt = view.findViewById(R.id.inviteTxt);
        topupTxt = view.findViewById(R.id.topupTxt);
        headerwalletTxt = view.findViewById(R.id.headerwalletTxt);
        generalSettingHTxt = view.findViewById(R.id.generalSettingHTxt);
        accountHTxt = view.findViewById(R.id.accountHTxt);
        notificationHTxt = view.findViewById(R.id.notificationHTxt);
        paymentHTxt = view.findViewById(R.id.paymentHTxt);
        privacyHTxt = view.findViewById(R.id.privacyHTxt);
        termsHTxt = view.findViewById(R.id.termsHTxt);
        logoutTxt = view.findViewById(R.id.logoutTxt);
        otherHTxt = view.findViewById(R.id.otherHTxt);

        GiftArea = view.findViewById(R.id.GiftArea);
        sendGiftArea = view.findViewById(R.id.sendGiftArea);
        redeemgiftArea = view.findViewById(R.id.redeemgiftArea);
        sendGiftHTxt = view.findViewById(R.id.sendGiftHTxt);
        redeemgiftHTxt = view.findViewById(R.id.redeemgiftHTxt);
        GiftHTxt = view.findViewById(R.id.GiftHTxt);
        sendGiftArrow = view.findViewById(R.id.sendGiftArrow);
        redeemgiftArrow = view.findViewById(R.id.redeemgiftArrow);

        notificationArea = view.findViewById(R.id.notificationArea);
        paymentMethodArea = view.findViewById(R.id.paymentMethodArea);
        privacyArea = view.findViewById(R.id.privacyArea);
        logoutTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOGOUT"));
        otherHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));

        myBookingArea = view.findViewById(R.id.myBookingArea);

        addMoneyArea = view.findViewById(R.id.addMoneyArea);
        sendMoneyArea = view.findViewById(R.id.sendMoneyArea);
        personalDetailsArea = view.findViewById(R.id.personalDetailsArea);
        changesPasswordArea = view.findViewById(R.id.changesPasswordArea);
        changesCurrancyArea = view.findViewById(R.id.changesCurrancyArea);
        changeslanguageArea = view.findViewById(R.id.changeslanguageArea);
        termsArea = view.findViewById(R.id.termsArea);
        liveChatArea = view.findViewById(R.id.liveChatArea);
        contactUsArea = view.findViewById(R.id.contactUsArea);
        verifyEmailArea = view.findViewById(R.id.verifyEmailArea);
        verifyMobArea = view.findViewById(R.id.verifyMobArea);
        notificationView = view.findViewById(R.id.notificationView);
        paymentView = view.findViewById(R.id.paymentView);
        privacyView = view.findViewById(R.id.privacyView);
        myBookingView = view.findViewById(R.id.myBookingView);
        addMoneyView = view.findViewById(R.id.addMoneyView);
        aboutUsView = view.findViewById(R.id.aboutUsView);
        myWalletView = view.findViewById(R.id.myWalletView);
        statisticsView = view.findViewById(R.id.statisticsView);
        userFeedbackView = view.findViewById(R.id.userFeedbackView);
        wayBillView = view.findViewById(R.id.wayBillView);
        inviteView = view.findViewById(R.id.inviteView);
        manageDocView = view.findViewById(R.id.manageDocView);
        walletArea = view.findViewById(R.id.walletArea);
        sendMoneyView = view.findViewById(R.id.sendMoneyView);
        changeCurrencyView = view.findViewById(R.id.changeCurrencyView);
        changeLangView = view.findViewById(R.id.changeLangView);
        termsView = view.findViewById(R.id.termsView);
        livechatView = view.findViewById(R.id.livechatView);
        mySubView = view.findViewById(R.id.mySubView);
        myServiceView = view.findViewById(R.id.myServiceView);
        manageGallleryView = view.findViewById(R.id.manageGallleryView);
        myAvailView = view.findViewById(R.id.myAvailView);
        verifyEmailView = view.findViewById(R.id.verifyEmailView);
        verifyMobView = view.findViewById(R.id.verifyMobView);
        bookingArea = view.findViewById(R.id.bookingArea);
        inviteArea = view.findViewById(R.id.inviteArea);
        topUpArea = view.findViewById(R.id.topUpArea);

        logOutArea = view.findViewById(R.id.logOutArea);

        myPaymentHTxt = view.findViewById(R.id.myPaymentHTxt);
        mybookingHTxt = view.findViewById(R.id.mybookingHTxt);
        addMoneyHTxt = view.findViewById(R.id.addMoneyHTxt);
        sendMoneyHTxt = view.findViewById(R.id.sendMoneyHTxt);
        personalDetailsHTxt = view.findViewById(R.id.personalDetailsHTxt);
        changePasswordHTxt = view.findViewById(R.id.changePasswordHTxt);
        changeCurrencyHTxt = view.findViewById(R.id.changeCurrencyHTxt);
        changeLanguageHTxt = view.findViewById(R.id.changeLanguageHTxt);
        supportHTxt = view.findViewById(R.id.supportHTxt);
        livechatHTxt = view.findViewById(R.id.livechatHTxt);
        contactUsHTxt = view.findViewById(R.id.contactUsHTxt);
        myWalletArea = view.findViewById(R.id.myWalletArea);
        headerwalletArea = view.findViewById(R.id.headerwalletArea);
        emeContactArea = view.findViewById(R.id.emeContactArea);
        bankDetailsArea = view.findViewById(R.id.bankDetailsArea);
        manageDocArea = view.findViewById(R.id.manageDocArea);
        mySubArea = view.findViewById(R.id.mySubArea);
        myServiceArea = view.findViewById(R.id.myServiceArea);
        manageGalleryArea = view.findViewById(R.id.manageGalleryArea);
        myAvailArea = view.findViewById(R.id.myAvailArea);
        statisticsArea = view.findViewById(R.id.statisticsArea);
        userFeedbackArea = view.findViewById(R.id.userFeedbackArea);
        wayBillArea = view.findViewById(R.id.wayBillArea);
        inviteFriendArea = view.findViewById(R.id.inviteFriendArea);
        helpArea = view.findViewById(R.id.helpArea);
        aboutusArea = view.findViewById(R.id.aboutusArea);

        notificationArrow = view.findViewById(R.id.notificationArrow);
        paymentArrow = view.findViewById(R.id.paymentArrow);
        privacyArrow = view.findViewById(R.id.privacyArrow);
        termsArrow = view.findViewById(R.id.termsArrow);
        mywalletArrow = view.findViewById(R.id.mywalletArrow);
        inviteArrow = view.findViewById(R.id.inviteArrow);
        helpArrow = view.findViewById(R.id.helpArrow);
        aboutusArrow = view.findViewById(R.id.aboutusArrow);
        statisticsArrow = view.findViewById(R.id.statisticsArrow);
        mybookingArrow = view.findViewById(R.id.mybookingArrow);
        addMoneyArrow = view.findViewById(R.id.addMoneyArrow);
        sendMoneyArrow = view.findViewById(R.id.sendMoneyArrow);
        personalDetailsArrow = view.findViewById(R.id.personalDetailsArrow);
        changePasswordArrow = view.findViewById(R.id.changePasswordArrow);
        changeCurrencyArrow = view.findViewById(R.id.changeCurrencyArrow);
        changeLangArrow = view.findViewById(R.id.changeLangArrow);
        livechatArrow = view.findViewById(R.id.livechatArrow);
        contactUsArrow = view.findViewById(R.id.contactUsArrow);
        logoutArrow = view.findViewById(R.id.logoutArrow);
        emeContactArrow = view.findViewById(R.id.emeContactArrow);
        bankDetailsArrow = view.findViewById(R.id.bankDetailsArrow);
        manageGalleryArrow = view.findViewById(R.id.manageGalleryArrow);
        mySubArrow = view.findViewById(R.id.mySubArrow);
        myServiceArrow = view.findViewById(R.id.myServiceArrow);
        manageDocArrow = view.findViewById(R.id.manageDocArrow);
        myAvailArrow = view.findViewById(R.id.myAvailArrow);
        userFeedbackArrow = view.findViewById(R.id.userFeedbackArrow);
        wayBillArrow = view.findViewById(R.id.wayBillArrow);
        verifyMobsArrow = view.findViewById(R.id.verifyMobsArrow);
        verifyEmailArrow = view.findViewById(R.id.verifyEmailArrow);


        mywalletHTxt = view.findViewById(R.id.mywalletHTxt);
        inviteHTxt = view.findViewById(R.id.inviteHTxt);
        emeContactHTxt = view.findViewById(R.id.emeContactHTxt);
        bankDetailsHTxt = view.findViewById(R.id.bankDetailsHTxt);
        manageDocHTxt = view.findViewById(R.id.manageDocHTxt);
        mySubHTxt = view.findViewById(R.id.mySubHTxt);
        myServiceHTxt = view.findViewById(R.id.myServiceHTxt);
        manageGalleryHTxt = view.findViewById(R.id.manageGalleryHTxt);
        myAvailHTxt = view.findViewById(R.id.myAvailHTxt);
        statisticsHTxt = view.findViewById(R.id.statisticsHTxt);
        userFeedbackHTxt = view.findViewById(R.id.userFeedbackHTxt);
        wayBillHTxt = view.findViewById(R.id.wayBillHTxt);
        helpHTxt = view.findViewById(R.id.helpHTxt);
        aboutusHTxt = view.findViewById(R.id.aboutusHTxt);
        toolbar_profile = view.findViewById(R.id.toolbar_profile);
        verifyEmailHTxt = view.findViewById(R.id.verifyEmailHTxt);
        verifyMobHTxt = view.findViewById(R.id.verifyMobHTxt);

        smartLoginArea = view.findViewById(R.id.smartLoginArea);
        smartLoginView = view.findViewById(R.id.smartLoginView);
        smartLoginHTxt = view.findViewById(R.id.smartLoginHTxt);
        smartLoginSwitchBtn = view.findViewById(R.id.smartLoginSwitchBtn);
        smartLoginSwitchBtn.setOnCheckedChangeListener((buttonView, isChecked) -> {
            if (isChecked) {
                generalFunc.storeData("isSmartLogin", "Yes");
                generalFunc.storeData("isUserSmartLogin", "Yes");
            } else {
                generalFunc.storeData("isSmartLogin", "No");
                generalFunc.storeData("isUserSmartLogin", "No");
            }
        });
        imgSmartLoginQuery = view.findViewById(R.id.imgSmartLoginQuery);
        imgSmartLoginQuery.setOnClickListener(v -> {
            BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(getActContext(), generalFunc);
            bottomInfoDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_NOTE_2_TXT"),
                    R.raw.biometric, false, generalFunc.retrieveLangLBl("", "LBL_OK"), "", true);
        });


        addToClickHandler(notificationArea);
        addToClickHandler(paymentMethodArea);
        addToClickHandler(privacyArea);
        addToClickHandler(myBookingArea);
        addToClickHandler(bookingArea);
        addToClickHandler(inviteArea);
        addToClickHandler(topUpArea);
        addToClickHandler(logOutArea);
        addToClickHandler(myWalletArea);
        addToClickHandler(headerwalletArea);
        addToClickHandler(emeContactArea);
        addToClickHandler(inviteFriendArea);
        addToClickHandler(helpArea);
        addToClickHandler(aboutusArea);
        addToClickHandler(backImg);
        addToClickHandler(addMoneyArea);
        addToClickHandler(sendMoneyArea);
        addToClickHandler(personalDetailsArea);
        addToClickHandler(changesPasswordArea);
        addToClickHandler(changesCurrancyArea);
        addToClickHandler(changeslanguageArea);
        addToClickHandler(termsArea);
        addToClickHandler(liveChatArea);
        addToClickHandler(contactUsArea);
        addToClickHandler(verifyMobArea);
        addToClickHandler(editProfileImage);
        addToClickHandler(bankDetailsArea);
        addToClickHandler(manageDocArea);
        addToClickHandler(myServiceArea);
        addToClickHandler(manageGalleryArea);
        addToClickHandler(myAvailArea);
        addToClickHandler(statisticsArea);
        addToClickHandler(userFeedbackArea);
        addToClickHandler(wayBillArea);
        addToClickHandler(verifyEmailArea);
        addToClickHandler(verifyMobArea);
        addToClickHandler(mySubArea);

        addToClickHandler(sendGiftArea);
        addToClickHandler(redeemgiftArea);

        if (generalFunc.isRTLmode()) {
            backImg.setRotation(0);
            notificationArrow.setRotation(180);
            paymentArrow.setRotation(180);
            privacyArrow.setRotation(180);
            termsArrow.setRotation(180);
            mywalletArrow.setRotation(180);
            inviteArrow.setRotation(180);
            helpArrow.setRotation(180);
            aboutusArrow.setRotation(180);
            mybookingArrow.setRotation(180);
            addMoneyArrow.setRotation(180);
            sendMoneyArrow.setRotation(180);
            personalDetailsArrow.setRotation(180);
            changePasswordArrow.setRotation(180);
            changeCurrencyArrow.setRotation(180);
            changeLangArrow.setRotation(180);
            livechatArrow.setRotation(180);
            contactUsArrow.setRotation(180);
            logoutArrow.setRotation(180);
            emeContactArrow.setRotation(180);
            bankDetailsArrow.setRotation(180);
            manageGalleryArrow.setRotation(180);
            manageDocArrow.setRotation(180);
            mySubArrow.setRotation(180);
            myServiceArrow.setRotation(180);
            myAvailArrow.setRotation(180);
            userFeedbackArrow.setRotation(180);
            statisticsArrow.setRotation(180);
            wayBillArrow.setRotation(180);
            verifyEmailArrow.setRotation(180);
            verifyMobsArrow.setRotation(180);
            sendGiftArrow.setRotation(180);
            redeemgiftArrow.setRotation(180);
        }


        NestedScrollView scroller = (NestedScrollView) view.findViewById(R.id.scroll);
        scroller.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {

            if (scrollY > oldScrollY) {

                if (scrollY > getResources().getDimension(R.dimen._75sdp)) {
                    toolbar_profile.setVisibility(View.VISIBLE);
                }
            }
            if (scrollY < oldScrollY) {
                if (scrollY < getResources().getDimension(R.dimen._75sdp)) {
                    toolbar_profile.setVisibility(View.GONE);
                }

            }

            if (scrollY == 0) {
                Logger.d(TAG, "TOP SCROLL");

            }

            if (scrollY == (v.getMeasuredHeight() - v.getChildAt(0).getMeasuredHeight())) {
                Logger.d(TAG, "BOTTOM SCROLL");
            }
        });
        if (ServiceModule.isDeliverAllOnly()) {
            emeContactArea.setVisibility(View.GONE);
        }

        setRewardView();
    }

    private void setRewardView() {
        rewardViewArea = view.findViewById(R.id.rewardViewArea);
        LinearLayout llRewardView = view.findViewById(R.id.llRewardView);
        llRewardView.setVisibility(View.GONE);
        rewardViewArea.setVisibility(View.GONE);
        if (generalFunc.getJsonValue("ENABLE_DRIVER_REWARD_MODULE", userProfileJson).equalsIgnoreCase("Yes")) {
            rewardViewArea.setVisibility(View.VISIBLE);
            rewardViewArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(DriverRewardActivity.class));
            JSONObject rewardObj = generalFunc.getJsonObject(generalFunc.getJsonValue("reward", userProfileJson));
            MTextView rewardHTxt = view.findViewById(R.id.rewardHTxt);
            rewardHTxt.setText(generalFunc.getJsonValueStr("default_reward_title", rewardObj));
            ImageView rewardArrow = view.findViewById(R.id.rewardArrow);
            if (generalFunc.isRTLmode()) {
                rewardArrow.setRotation(180);
            }
            ImageView ivRewordImg = view.findViewById(R.id.ivRewardImg);
            MTextView txtRewardTitle = view.findViewById(R.id.txtRewardTitle);
            if (generalFunc.getJsonValueStr("reward_earned", rewardObj).equalsIgnoreCase("Yes")) {
                llRewardView.setVisibility(View.VISIBLE);
                String img = generalFunc.getJsonValueStr("REWARD_ACHIEVED_VIMAGE", rewardObj);
                if (Utils.checkText(img)) {
                    new LoadImage.builder(LoadImage.bind(img), ivRewordImg).build();
                }
                txtRewardTitle.setText(generalFunc.getJsonValueStr("REWARD_ACHIEVED_TEXT", rewardObj));
                llRewardView.setOnClickListener(v -> new ActUtils(getActContext()).startAct(DriverRewardActivity.class));
            }
        }
    }

    private void setLabel() {
        walletHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WALLET_BALANCE"));
        verifyEmailHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_VERIFY"));
        smartLoginHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"));
        verifyMobHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_VERIFY"));


        if (ServiceModule.ServiceProvider && isUfxServicesEnabled) {
            myServiceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_SERVICES"));
        } else {
            myServiceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_VEHICLES"));
        }

        if (((ServiceModule.ServiceProvider && isUfxServicesEnabled)) && generalFunc.getJsonValueStr("SERVICE_PROVIDER_FLOW", obj_userProfile).equalsIgnoreCase("PROVIDER")) {
            manageGallleryView.setVisibility(View.VISIBLE);
            manageGalleryArea.setVisibility(View.VISIBLE);
        } else {
            manageGallleryView.setVisibility(View.GONE);
            manageGalleryArea.setVisibility(View.GONE);
        }

        if (ServiceModule.ServiceProvider) {
            myAvailArea.setVisibility(View.VISIBLE);
            myAvailView.setVisibility(View.VISIBLE);
        } else {
            myAvailView.setVisibility(View.GONE);
            myAvailArea.setVisibility(View.GONE);
        }


        if (ServiceModule.isRideOnly()) {
            userFeedbackHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDER_FEEDBACK"));
        } else if (ServiceModule.isDeliveronly()) {
            userFeedbackHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SENDER_fEEDBACK"));
        } else {
            userFeedbackHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USER_FEEDBACK"));
        }

        if (generalFunc.getJsonValueStr("WAYBILL_ENABLE", obj_userProfile) != null && generalFunc.getJsonValueStr("WAYBILL_ENABLE", obj_userProfile).equalsIgnoreCase("yes")) {
            wayBillArea.setVisibility(View.VISIBLE);
            wayBillView.setVisibility(View.VISIBLE);
        } else {
            wayBillArea.setVisibility(View.GONE);
            wayBillView.setVisibility(View.GONE);
        }

        wayBillHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MENU_WAY_BILL"));
        statisticsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STATISTICS"));


        myAvailHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_AVAILABILITY"));
        manageGalleryHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_GALLARY"));
        mySubHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_SUBSCRIPTION"));
        manageDocHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_DOCUMENT"));
        bankDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BANK_DETAIL"));
        emeContactHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMERGENCY_CONTACT"));
        topupTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOP_UP"));
        headerwalletTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WALLET_TXT"));
        inviteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INVITE"));
        if (ServiceModule.isDeliverAllOnly()) {
            mybookingHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS_TXT"));
            bookingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS_TXT"));

        } else {
            mybookingHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_BOOKINGS"));
            bookingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BOOKING"));
        }
        generalSettingHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GENERAL_SETTING"));
        notificationHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NOTIFICATIONS"));
        paymentHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PAYMENT_METHOD"));
        privacyHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PRIVACY_POLICY_TEXT"));
        termsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TERMS_CONDITION"));
        myPaymentHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PAYMENT"));
        mywalletHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_WALLET"));
        inviteHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INVITE_FRIEND_TXT"));
        // helpHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HELP_CENTER"));
        helpHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAQ_TXT"));
        aboutusHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT_US_TXT"));
        addMoneyHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"));
        sendMoneyHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_MONEY"));
        accountHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ACCOUNT_SETTING"));

        sendGiftHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GIFT_CARDT_SEND_TXT"));
        redeemgiftHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GIFT_CARD_REDEEM_GIFT_CARD_TXT"));
        GiftHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GIFT_CARD_TXT"));

        if (generalFunc.getJsonValue("ENABLE_ACCOUNT_DELETION", userProfileJson).equalsIgnoreCase("Yes")) {
            personalDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_ACCOUNT_TXT"));
        } else {
            personalDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PERSONAL_DETAILS"));
        }

        changePasswordHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHANGE_PASSWORD_TXT"));
        changeCurrencyHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHANGE_CURRENCY"));
        changeLanguageHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHANGE_LANGUAGE"));
        supportHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUPPORT"));
        livechatHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LIVE_CHAT"));
        contactUsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        logoutTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOGOUT"));
        otherHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));


    }

    @SuppressLint("SetTextI18n")
    private void setuserInfo() {
        String vName = generalFunc.getJsonValueStr("vName", obj_userProfile);
        String vLastName = generalFunc.getJsonValueStr("vLastName", obj_userProfile);

        userNameTxt.setText(vName + " " + vLastName);
        userNameTxt_toolbar.setText(vName + " " + vLastName);
        String vEmail = generalFunc.getJsonValueStr("vEmail", obj_userProfile);
        String vPhone = "(+" + generalFunc.getJsonValueStr("vCode", obj_userProfile) + ") " + generalFunc.getJsonValueStr("vPhone", obj_userProfile);
        if (Utils.checkText(vEmail)) {
            userEmailTxt.setText(vEmail);
            userEmailTxt.setVisibility(View.VISIBLE);
        } else {
            userEmailTxt.setVisibility(View.GONE);
        }
        txtUserMobile.setText(vPhone);

        String url = CommonUtilities.PROVIDER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImage", userProfileJson);
        generalFunc.checkProfileImage(userImgView, url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);
        generalFunc.checkProfileImage(userImgView_toolbar, url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);
        walletVxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("user_available_balance", obj_userProfile)));
        if (!generalFunc.getJsonValueStr("eEmailVerified", obj_userProfile).equalsIgnoreCase("YES")) {
            verifyEmailArea.setVisibility(View.VISIBLE);
            infoImg.setVisibility(View.VISIBLE);
            verifyEmailView.setVisibility(View.VISIBLE);
        } else {
            verifyEmailArea.setVisibility(View.GONE);
            infoImg.setVisibility(View.GONE);
            verifyEmailView.setVisibility(View.GONE);
        }

        if (!generalFunc.getJsonValueStr("ePhoneVerified", obj_userProfile).equalsIgnoreCase("YES")) {
            verifyMobArea.setVisibility(View.VISIBLE);
            verifyMobView.setVisibility(View.VISIBLE);
        } else {
            verifyMobArea.setVisibility(View.GONE);
            verifyMobView.setVisibility(View.GONE);
        }

        if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes")) {
            smartLoginArea.setVisibility(View.VISIBLE);
            smartLoginView.setVisibility(View.VISIBLE);
        } else {
            smartLoginArea.setVisibility(View.GONE);
            smartLoginView.setVisibility(View.GONE);
        }

        smartLoginSwitchBtn.setCheckedNoEvent(generalFunc.retrieveValue("isSmartLogin").equalsIgnoreCase("Yes"));

        boolean isTransferMoneyEnabled = generalFunc.retrieveValue(Utils.ENABLE_GOPAY_KEY).equalsIgnoreCase("Yes");

        if (!isTransferMoneyEnabled) {
            sendMoneyArea.setVisibility(View.GONE);
            sendMoneyView.setVisibility(View.GONE);

        }

        boolean isOnlyCashEnabled = generalFunc.getJsonValue("APP_PAYMENT_MODE", userProfileJson).equalsIgnoreCase("Cash");
        if (isOnlyCashEnabled) {
            addMoneyArea.setVisibility(View.GONE);
            topUpArea.setVisibility(View.GONE);
            addMoneyView.setVisibility(View.GONE);
        } else {
            addMoneyArea.setVisibility(View.VISIBLE);
            topUpArea.setVisibility(View.VISIBLE);
            addMoneyView.setVisibility(View.VISIBLE);
        }


        boolean FEMALE_RIDE_REQ_ENABLE = generalFunc.retrieveValue(Utils.FEMALE_RIDE_REQ_ENABLE).equalsIgnoreCase("yes");
        String IS_RIDE_MODULE_AVAIL = generalFunc.retrieveValue("IS_RIDE_MODULE_AVAIL");
        String eGender = generalFunc.getJsonValueStr("eGender", obj_userProfile);

        int drawable = R.drawable.ic_edit;
        if (IS_RIDE_MODULE_AVAIL.equalsIgnoreCase("Yes")) {
            if (!FEMALE_RIDE_REQ_ENABLE || eGender.equalsIgnoreCase("Male")) {
                drawable = R.drawable.ic_edit;
            } else {
                drawable = R.drawable.ic_settings_new;
            }
        }
        editProfileImage.setImageResource(drawable);

    }

    private void buildLanguageList() {
        language_list.clear();
        JSONArray languageList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.LANGUAGE_LIST_KEY));

        for (int i = 0; i < languageList_arr.length(); i++) {
            JSONObject obj_temp = generalFunc.getJsonObject(languageList_arr, i);

            String vCode = generalFunc.getJsonValueStr("vCode", obj_temp);
            if ((generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY)).equals(vCode)) {
                selected_language_code = vCode;

                default_selected_language_code = selected_language_code;
                selLanguagePosition = i;
            }

            HashMap<String, String> data = new HashMap<>();
            data.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
            data.put("vCode", vCode);
            data.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));
            data.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));


            language_list.add(data);
        }
        if (language_list.size() < 2) {
            changeslanguageArea.setVisibility(View.GONE);
        } else {
            changeslanguageArea.setVisibility(View.VISIBLE);

        }
        if (language_list.size() < 2) {
            changeslanguageArea.setVisibility(View.GONE);
        } else {
            changeslanguageArea.setVisibility(View.VISIBLE);

        }

        buildCurrencyList();

    }

    private void updateProfile() {


        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateUserProfileDetail");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("vName", generalFunc.getJsonValue("vName", userProfileJson));
        parameters.put("vLastName", generalFunc.getJsonValue("vLastName", userProfileJson));
        parameters.put("vPhone", generalFunc.getJsonValue("vPhone", userProfileJson));
        parameters.put("vPhoneCode", generalFunc.getJsonValue("vCode", userProfileJson));
        parameters.put("vCountry", generalFunc.getJsonValue("vCountry", userProfileJson));
        parameters.put("vEmail", generalFunc.getJsonValue("vEmail", userProfileJson));
        parameters.put("CurrencyCode", selected_currency);
        parameters.put("LanguageCode", selected_language_code);
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    String currentLangCode = generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY);
                    String vCurrencyPassenger = generalFunc.getJsonValue("vCurrencyDriver", userProfileJson);

                    String messgeJson = generalFunc.getJsonValue(Utils.message_str, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, messgeJson);
                    responseString = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);


                    if (!currentLangCode.equals(selected_language_code) || !selected_currency.equals(vCurrencyPassenger)) {
                        new ConfigureMemberData(responseString, generalFunc, getActContext(), false);
                        changeLanguagedata(selected_language_code, true);
                    }

                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void buildCurrencyList() {
        currency_list.clear();
        JSONArray currencyList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.CURRENCY_LIST_KEY));
        if (currencyList_arr != null) {
            for (int i = 0; i < currencyList_arr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(currencyList_arr, i);

                HashMap<String, String> data = new HashMap<>();

                data.put("vName", generalFunc.getJsonValueStr("vName", obj_temp));
                data.put("vCode", generalFunc.getJsonValueStr("vSymbol", obj_temp));
                data.put("vSymbol", generalFunc.getJsonValueStr("vSymbol", obj_temp));
                data.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));
                data.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));
                if (!selected_currency.equalsIgnoreCase("") && selected_currency.equalsIgnoreCase(generalFunc.getJsonValueStr("vName", obj_temp))) {
                    selCurrancyPosition = i;
                }
                currency_list.add(data);
            }

            if (currency_list.size() < 2) {
                changeCurrencyView.setVisibility(View.GONE);
                changesCurrancyArea.setVisibility(View.GONE);
            } else {
                changeCurrencyView.setVisibility(View.VISIBLE);
                changesCurrancyArea.setVisibility(View.VISIBLE);

            }
        } else {
            changeCurrencyView.setVisibility(View.GONE);
            changesCurrancyArea.setVisibility(View.GONE);
        }

    }

    @Override
    public void onResume() {
        super.onResume();

        updateUserProfileObj();
        Logger.d("Onresume", ":: fragment called" + "::" + generalFunc.getJsonValueStr("user_available_balance", obj_userProfile));


        setuserInfo();
    }

    private void updateUserProfileObj() {
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        String UFX_SERVICE_AVAILABLE = generalFunc.getJsonValueStr("UFX_SERVICE_AVAILABLE", obj_userProfile);
        isUfxServicesEnabled = !Utils.checkText(UFX_SERVICE_AVAILABLE) || UFX_SERVICE_AVAILABLE.equalsIgnoreCase("Yes");
        ENABLE_FAVORITE_DRIVER_MODULE_KEY = generalFunc.retrieveValue(Utils.ENABLE_FAVORITE_DRIVER_MODULE_KEY);
        isAnyDeliverOptionEnabled = ServiceModule.DeliverAll;
        SITE_TYPE = generalFunc.getJsonValueStr("SITE_TYPE", obj_userProfile);
        SITE_TYPE_DEMO_MSG = generalFunc.getJsonValueStr("SITE_TYPE_DEMO_MSG", obj_userProfile);
        selected_currency = generalFunc.getJsonValue("vCurrencyDriver", userProfileJson);
        default_selected_currency = selected_currency;
    }

    private void showLanguageList() {


//        OpenListView.getInstance(getActContext(), getSelectLangText(), language_list, OpenListView.OpenDirection.CENTER, true, position -> {
//
//
//            selLanguagePosition = position;
//            selected_language_code = language_list.get(selLanguagePosition).get("vCode");
//            generalFunc.storeData(Utils.DEFAULT_LANGUAGE_VALUE, language_list.get(selLanguagePosition).get("vTitle"));
//
//            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
//                generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
//                generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
//                changeLanguagedata(selected_language_code,false);
//            } else {
//                updateProfile();
//            }
//        }).show(selLanguagePosition, "vTitle");


        OpenListView.getInstance(getActContext(), getSelectLangText(), language_list, OpenListView.OpenDirection.CENTER, true, position -> {


            selLanguagePosition = position;
            selected_language_code = language_list.get(selLanguagePosition).get("vCode");
            generalFunc.storeData(Utils.DEFAULT_LANGUAGE_VALUE, language_list.get(selLanguagePosition).get("vTitle"));

            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
                generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
                changeLanguagedata(selected_language_code, false);
            } else {
                updateProfile();
            }

        }, true, generalFunc.retrieveLangLBl("", "LBL_LANG_PREFER"), true).show(selLanguagePosition, "vTitle");
    }

    private void showCurrencyList() {

//        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_CURRENCY"), currency_list, OpenListView.OpenDirection.CENTER, true, position -> {
//
//
//            selCurrancyPosition = position;
//            selected_currency_symbol = currency_list.get(selCurrancyPosition).get("vSymbol");
//            selected_currency = currency_list.get(selCurrancyPosition).get("vName");
//            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
//                generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
//                generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
//                changeLanguagedata(selected_language_code, false);
//
//            } else {
//                updateProfile();
//            }
//        }).show(selCurrancyPosition, "vName");

        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_CURRENCY"), currency_list, OpenListView.OpenDirection.CENTER, true, position -> {

            selCurrancyPosition = position;
            selected_currency_symbol = currency_list.get(selCurrancyPosition).get("vSymbol");
            selected_currency = currency_list.get(selCurrancyPosition).get("vName");
            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
                generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
                changeLanguagedata(selected_language_code, false);

            } else {
                updateProfile();
            }
        }, true, generalFunc.retrieveLangLBl("", "LBL_CURRENCY_PREFER"), true).show(selCurrancyPosition, "vName");

    }

    private void showPasswordBox() {
        if (alertDialog != null && alertDialog.isShowing()) {
            return;
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.change_passoword_layout, null);

        final String required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        final String noWhiteSpace = generalFunc.retrieveLangLBl("", "LBL_ERROR_NO_SPACE_IN_PASS");
        final String pass_length = generalFunc.retrieveLangLBl("", "LBL_ERROR_PASS_LENGTH_PREFIX")
                + " " + Utils.minPasswordLength + " " + generalFunc.retrieveLangLBl("", "LBL_ERROR_PASS_LENGTH_SUFFIX");
        final String vPassword = generalFunc.getJsonValueStr("vPassword", obj_userProfile);

        final MaterialEditText previous_passwordBox = (MaterialEditText) dialogView.findViewById(R.id.editBox);
        setCommandEditView(previous_passwordBox, generalFunc.retrieveLangLBl("", "LBL_CURR_PASS_HEADER"), generalFunc.retrieveLangLBl("", "LBL_CURR_PASS_HEADER"));

        if (vPassword.equals("")) {
            previous_passwordBox.setVisibility(View.GONE);
        }

        final MaterialEditText newPasswordBox = (MaterialEditText) dialogView.findViewById(R.id.newPasswordBox);
        setCommandEditView(newPasswordBox, generalFunc.retrieveLangLBl("", "LBL_UPDATE_PASSWORD_HEADER_TXT"), generalFunc.retrieveLangLBl("", "LBL_UPDATE_PASSWORD_HINT_TXT"));

        ImageView cancelImg = (ImageView) dialogView.findViewById(R.id.cancelImg);
        MTextView submitTxt = (MTextView) dialogView.findViewById(R.id.submitTxt);
        MTextView cancelTxt = (MTextView) dialogView.findViewById(R.id.cancelTxt);
        MTextView subTitleTxt = (MTextView) dialogView.findViewById(R.id.subTitleTxt);
        subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHANGE_PASSWORD_TXT"));

        submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        final MaterialEditText reNewPasswordBox = (MaterialEditText) dialogView.findViewById(R.id.reNewPasswordBox);
        setCommandEditView(reNewPasswordBox, generalFunc.retrieveLangLBl("", "LBL_UPDATE_CONFIRM_PASSWORD_HEADER_TXT"), generalFunc.retrieveLangLBl("", "LBL_UPDATE_CONFIRM_PASSWORD_HEADER_TXT"));

        builder.setView(dialogView);


        new PasswordViewHideManager(getActContext(), previous_passwordBox, generalFunc);
        new PasswordViewHideManager(getActContext(), newPasswordBox, generalFunc);
        new PasswordViewHideManager(getActContext(), reNewPasswordBox, generalFunc);


        cancelImg.setOnClickListener(v -> alertDialog.dismiss());
        cancelTxt.setOnClickListener(v -> alertDialog.dismiss());
        submitTxt.setOnClickListener(v -> {

            boolean isCurrentPasswordEnter = Utils.checkText(previous_passwordBox) ?
                    (Utils.getText(previous_passwordBox).contains(" ") ? Utils.setErrorFields(previous_passwordBox, noWhiteSpace)
                            : (Utils.getText(previous_passwordBox).length() >= Utils.minPasswordLength || Utils.setErrorFields(previous_passwordBox, pass_length)))
                    : Utils.setErrorFields(previous_passwordBox, required_str);

            boolean isNewPasswordEnter = Utils.checkText(newPasswordBox) ?
                    (Utils.getText(newPasswordBox).contains(" ") ? Utils.setErrorFields(newPasswordBox, noWhiteSpace)
                            : (Utils.getText(newPasswordBox).length() >= Utils.minPasswordLength || Utils.setErrorFields(newPasswordBox, pass_length)))
                    : Utils.setErrorFields(newPasswordBox, required_str);

            boolean isReNewPasswordEnter = Utils.checkText(reNewPasswordBox) ?
                    (Utils.getText(reNewPasswordBox).contains(" ") ? Utils.setErrorFields(reNewPasswordBox, noWhiteSpace)
                            : (Utils.getText(reNewPasswordBox).length() >= Utils.minPasswordLength || Utils.setErrorFields(reNewPasswordBox, pass_length)))
                    : Utils.setErrorFields(reNewPasswordBox, required_str);

            if ((!vPassword.equals("") && !isCurrentPasswordEnter) || !isNewPasswordEnter || !isReNewPasswordEnter) {
                return;
            }

            if (!Utils.getText(newPasswordBox).equals(Utils.getText(reNewPasswordBox))) {
                Utils.setErrorFields(reNewPasswordBox, generalFunc.retrieveLangLBl("", "LBL_VERIFY_PASSWORD_ERROR_TXT"));
                return;
            }

            changePassword(Utils.getText(previous_passwordBox), Utils.getText(newPasswordBox), previous_passwordBox);

        });

        builder.setView(dialogView);
        alertDialog = builder.create();
        LayoutDirection.setLayoutDirection(alertDialog);
        alertDialog.setCancelable(false);
        alertDialog.setCanceledOnTouchOutside(false);
        alertDialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        alertDialog.show();
    }

    private void setCommandEditView(MaterialEditText editText, String floatingLabelText, String hintText) {
        editText.setFloatingLabelText(floatingLabelText);
        editText.setHint(hintText);
        editText.setTypeface(Typeface.DEFAULT);
        editText.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_NUMBER_VARIATION_PASSWORD);
        editText.setTransformationMethod(new AsteriskPasswordTransformationMethod());
    }

    private static class AsteriskPasswordTransformationMethod extends PasswordTransformationMethod {
        @Override
        public CharSequence getTransformation(CharSequence source, View view) {
            return new PasswordCharSequence(source);
        }

        private class PasswordCharSequence implements CharSequence {
            private CharSequence mSource;

            public PasswordCharSequence(CharSequence source) {
                mSource = source; // Store char sequence
            }

            public char charAt(int index) {
                return '*'; // This is the important part
            }

            public int length() {
                return mSource.length(); // Return default
            }

            public CharSequence subSequence(int start, int end) {
                return mSource.subSequence(start, end); // Return default
            }
        }
    }

    private void changePassword(String currentPassword, String password, MaterialEditText previous_passwordBox) {

        if (SITE_TYPE.equals("Demo")) {
            generalFunc.showGeneralMessage("", SITE_TYPE_DEMO_MSG);
            return;
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updatePassword");
        parameters.put("UserID", generalFunc.getMemberId());
        parameters.put("pass", password);
        parameters.put("CurrentPassword", currentPassword);
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

                    if (responseStringObject != null) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

                        if (isDataAvail) {
                            alertDialog.dismiss();
                            generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValueStr(Utils.message_str, responseStringObject));
                            updateUserProfileObj();
                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str_one, responseStringObject)));
                        } else {
                            previous_passwordBox.setText("");

                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }

    @SuppressLint("NonConstantResourceId")
    public void onClickView(View view) {
        Utils.hideKeyboard(getActContext());
        Bundle bn = new Bundle();
        int id = view.getId();
        if (id == R.id.backImg) {
            //onBackPressed();
        } else if (id == R.id.infoImg) {
            TrendyDialog customDialog = new TrendyDialog(getActContext());
            customDialog.setDetails(generalFunc.retrieveLangLBl("", "LBL_EMAIL_VERIFY"), generalFunc.retrieveLangLBl("", "LBL_EMAIL_VERIFY_NOTE_TXT"), generalFunc.retrieveLangLBl("Continue", "LBL_CONTINUE_BTN"), true, getActContext().getResources().getDrawable(R.drawable.ic_verify_email));
            customDialog.setNegativeBtnText(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));
            customDialog.setNegativeButtonVisibility(View.VISIBLE);
            customDialog.setTitleTextVisibility(View.VISIBLE);
            customDialog.showDialog();
            customDialog.setNegativeBtnClick(() -> {

            });
            customDialog.setPositiveBtnClick(() -> verifyEmailArea.performClick());
        } else if (id == R.id.personalDetailsArea) {
            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                new ActUtils(getActContext()).startAct(AppLoginActivity.class);
            } else {
                if (generalFunc.getJsonValue("ENABLE_ACCOUNT_DELETION", userProfileJson).equalsIgnoreCase("Yes")) {
                    new ActUtils(getActContext()).startAct(ManageAccountActivity.class);
                } else {
                    new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
                }
            }
        } else if (id == R.id.editProfileImage) {
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));

            String FEMALE_RIDE_REQ_ENABLE = generalFunc.retrieveValue(Utils.FEMALE_RIDE_REQ_ENABLE);
            String IS_RIDE_MODULE_AVAIL = generalFunc.retrieveValue("IS_RIDE_MODULE_AVAIL");
            String eGender = generalFunc.getJsonValueStr("eGender", obj_userProfile);

            if (FEMALE_RIDE_REQ_ENABLE.equalsIgnoreCase("yes") && !ServiceModule.isDeliverAllOnly() && IS_RIDE_MODULE_AVAIL.equalsIgnoreCase("Yes")) {

                String STORE_PERSONAL_DRIVER = generalFunc.getJsonValue("STORE_PERSONAL_DRIVER", userProfileJson);
                if (eGender.equalsIgnoreCase("") && STORE_PERSONAL_DRIVER.equalsIgnoreCase("No")) {
                    genderDailog();

                } else if (!FEMALE_RIDE_REQ_ENABLE.equalsIgnoreCase("yes") || eGender.equalsIgnoreCase("Male")) {
                    new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
                } else {
                    new ActUtils(getActContext()).startAct(PrefranceActivity.class);
                }
            } else {
                new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
            }
        } else if (id == R.id.bookingArea || id == R.id.myBookingArea) {
            new ActUtils(getActContext()).startActForResult(BookingsActivity.class, bn, MY_BOOKING_REQ_CODE);
        } else if (id == R.id.notificationArea) {
            new ActUtils(getActContext()).startAct(NotificationActivity.class);
        } else if (id == R.id.paymentMethodArea) {
            String url = generalFunc.getJsonValue("PAYMENT_BASE_URL", userProfileJson) + "&PAGE_TYPE=PAYMENT_LIST" +
                    "&currency=" + generalFunc.getJsonValue("vCurrencyDriver", userProfileJson);
            url = url + "&tSessionId=" + (generalFunc.getMemberId().equalsIgnoreCase("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();
            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
        } else if (id == R.id.privacyArea) {
            bn.putString("staticpage", "33");
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
        } else if (id == R.id.changesPasswordArea) {
            showPasswordBox();
        } else if (id == R.id.changesCurrancyArea) {
            showCurrencyList();
        } else if (id == R.id.changeslanguageArea) {
            showLanguageList();
        } else if (id == R.id.termsArea) {
            bn.putString("staticpage", "4");
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
        } else if (id == R.id.manageGalleryArea) {
            new ActUtils(getActContext()).startAct(MyGalleryActivity.class);
        } else if (id == R.id.headerwalletArea || id == R.id.myWalletArea) {
            new ActUtils(getActContext()).startActForResult(MyWalletActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
        } else if (id == R.id.topUpArea || id == R.id.addMoneyArea) {
            bn.putBoolean("isAddMoney", true);
            new ActUtils(getActContext()).startActForResult(MyWalletActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
        } else if (id == R.id.sendMoneyArea) {
            bn.putBoolean("isSendMoney", true);
            new ActUtils(getActContext()).startActForResult(MyWalletActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
        } else if (id == R.id.inviteArea || id == R.id.inviteFriendArea) {
            new ActUtils(getActContext()).startActWithData(InviteFriendsActivity.class, bn);
        } else if (id == R.id.helpArea) {
            new ActUtils(getActContext()).startAct(HelpActivity23Pro.class);
        } else if (id == R.id.liveChatArea) {
            MyUtils.openLiveChatActivity(getActContext(), generalFunc, obj_userProfile);
        } else if (id == R.id.aboutusArea) {
            bn.putString("staticpage", "1");
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
        } else if (id == R.id.contactUsArea) {
            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
        } else if (id == R.id.emeContactArea) {
            new ActUtils(getActContext()).startAct(EmergencyContactActivity.class);
        } else if (id == R.id.bankDetailsArea) {
            new ActUtils(getActContext()).startActWithData(BankDetailActivity.class, bn);
        } else if (id == R.id.mySubArea) {
            new ActUtils(getActContext()).startAct(SubscriptionActivity.class);
        } else if (id == R.id.myServiceArea) {
            bn.putString("iDriverVehicleId", generalFunc.getJsonValueStr("iDriverVehicleId", obj_userProfile));
            if (getActContext() instanceof LiveTaskListActivity) {
                bn.putString("isDriverOnline", "true");
            }
            if (ServiceModule.isDeliverAllOnly()) {
                new ActUtils(getActContext()).startActWithData(ManageVehiclesActivity.class, bn);
            } else {
                String eShowVehicles = generalFunc.getJsonValueStr("eShowVehicles", obj_userProfile);
                if (ServiceModule.ServiceBid) {
                    if (ServiceModule.ServiceProviderProduct) {
                        bn.putString("selView", "vehicle");
                        bn.putInt("totalVehicles", 1);
                        bn.putString("UBERX_PARENT_CAT_ID", generalFunc.getJsonValueStr("UBERX_PARENT_CAT_ID", obj_userProfile));
                        new ActUtils(getActContext()).startActWithData(UploadDocTypeWiseActivity.class, bn);
                    } else {
                        bn.putString("selView", "vehicle");
                        bn.putInt("totalVehicles", 1);
                        bn.putString("UBERX_PARENT_CAT_ID", ServiceModule.ServiceProvider ? generalFunc.getJsonValueStr("UBERX_PARENT_CAT_ID", obj_userProfile) : "");
                        new ActUtils(getActContext()).startActWithData(UploadDocTypeWiseActivity.class, bn);
                    }
                } else {
                    if (ServiceModule.ServiceProviderProduct || ((ServiceModule.RideDeliveryUbexProduct && isUfxServicesEnabled) && eShowVehicles.equalsIgnoreCase("No"))) {
                        bn.putString("UBERX_PARENT_CAT_ID", generalFunc.getJsonValueStr("UBERX_PARENT_CAT_ID", obj_userProfile));
                        (new ActUtils(getActContext())).startActWithData(UfxCategoryActivity.class, bn);
                    } else if ((ServiceModule.ServiceProvider && isUfxServicesEnabled) && eShowVehicles.equalsIgnoreCase("Yes")) {
                        bn.putString("selView", "vehicle");
                        bn.putInt("totalVehicles", 1);
                        bn.putString("UBERX_PARENT_CAT_ID", ServiceModule.ServiceProvider ? generalFunc.getJsonValueStr("UBERX_PARENT_CAT_ID", obj_userProfile) : "");
                        new ActUtils(getActContext()).startActWithData(UploadDocTypeWiseActivity.class, bn);
                    } else {
                        new ActUtils(getActContext()).startActWithData(ManageVehiclesActivity.class, bn);
                    }
                }
            }
        } else if (id == R.id.manageDocArea) {
            bn.putString("PAGE_TYPE", "Driver");
            bn.putString("iDriverVehicleId", "");
            bn.putString("doc_file", "");
            bn.putString("iDriverVehicleId", "");
            new ActUtils(getActContext()).startActWithData(ListOfDocumentActivity.class, bn);
        } else if (id == R.id.userFeedbackArea) {
            new ActUtils(getActContext()).startActWithData(DriverFeedbackActivity.class, bn);
        } else if (id == R.id.myAvailArea) {
            new ActUtils(getActContext()).startAct(SetAvailabilityActivity.class);
        } else if (id == R.id.statisticsArea) {
            new ActUtils(getActContext()).startActWithData(StatisticsActivity.class, bn);
        } else if (id == R.id.wayBillArea) {
            JSONObject last_trip_data = generalFunc.getJsonObject("TripDetails", obj_userProfile);
            if (generalFunc.getJsonValueStr("eSystem", last_trip_data).equalsIgnoreCase(Utils.eSystem_Type) || ServiceModule.isDeliverAllOnly()) {
                bn.putString("eSystem", "yes");
            }
            new ActUtils(getActContext()).startActWithData(WayBillActivity.class, bn);
        } else if (id == R.id.logOutArea) {
            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
            generateAlert.setCancelable(false);
            generateAlert.setBtnClickList(btn_id -> {
                if (btn_id == 0) {
                    generateAlert.closeAlertBox();
                } else {
                    if (internetConnection.isNetworkConnected()) {
                        MyApp.getInstance().logOutFromDevice(false);
                    } else {
                        generalFunc.showMessage(logOutArea, generalFunc.retrieveLangLBl("", "LBL_NO_INTERNET_TXT"));
                    }
                }

            });
            generateAlert.setContentMessage(generalFunc.retrieveLangLBl("Logout", "LBL_LOGOUT"), generalFunc.retrieveLangLBl("Are you sure you want to logout?", "LBL_WANT_LOGOUT_APP_TXT"));
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
            generateAlert.showAlertBox();
        } else if (id == R.id.verifyEmailArea) {
            bn.putString("msg", "DO_EMAIL_VERIFY");
            new ActUtils(getActContext()).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_MOBILE_REQ_CODE);
        } else if (id == R.id.verifyMobArea) {
            bn.putString("msg", "DO_PHONE_VERIFY");
            new ActUtils(getActContext()).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_MOBILE_REQ_CODE);
        } else if (id == R.id.sendGiftArea) {
            new ActUtils(getActContext()).startAct(GiftCardSendActivity.class);
        } else if (id == R.id.redeemgiftArea) {
            new ActUtils(getActContext()).startAct(GiftCardRedeemActivity.class);
        }
    }

    private void callgederApi(String egender) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateUserGender");
        parameters.put("UserType", Utils.userType);
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("eGender", egender);


        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

            String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObject);
            if (isDataAvail) {
                Bundle bn = new Bundle();
                generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
            }
        });

    }

    private void genderDailog() {


        final Dialog builder = new Dialog(getActContext(), R.style.Theme_Dialog);
        builder.requestWindowFeature(Window.FEATURE_NO_TITLE);
        builder.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        builder.setContentView(R.layout.gender_view);
        builder.getWindow().setLayout(WindowManager.LayoutParams.MATCH_PARENT, WindowManager.LayoutParams.MATCH_PARENT);

//        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
//        View dialogView = inflater.inflate(R.layout.gender_view, null);

        final MTextView genderTitleTxt = (MTextView) builder.findViewById(R.id.genderTitleTxt);
        final MTextView maleTxt = (MTextView) builder.findViewById(R.id.maleTxt);
        final MTextView femaleTxt = (MTextView) builder.findViewById(R.id.femaleTxt);
        final ImageView gendercancel = (ImageView) builder.findViewById(R.id.gendercancel);
        //final ImageView gendermale = (ImageView) builder.findViewById(R.id.gendermale);
        //final ImageView genderfemale = (ImageView) builder.findViewById(R.id.genderfemale);
        final LinearLayout male_area = (LinearLayout) builder.findViewById(R.id.male_area);
        final LinearLayout female_area = (LinearLayout) builder.findViewById(R.id.female_area);

        genderTitleTxt.setText(generalFunc.retrieveLangLBl("Select your gender to continue", "LBL_SELECT_GENDER"));
        maleTxt.setText(generalFunc.retrieveLangLBl("Male", "LBL_MALE_TXT"));
        femaleTxt.setText(generalFunc.retrieveLangLBl("FeMale", "LBL_FEMALE_TXT"));

        gendercancel.setOnClickListener(v -> builder.dismiss());

        male_area.setOnClickListener(v -> {
            callgederApi("Male");
            builder.dismiss();

        });
        female_area.setOnClickListener(v -> {
            callgederApi("Female");
            builder.dismiss();

        });

        builder.show();

    }

    private String getSelectLangText() {
        return ("" + generalFunc.retrieveLangLBl("Select", "LBL_SELECT_LANGUAGE_HINT_TXT"));
    }

    private void changeLanguagedata(String langcode, boolean showDialog) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "changelanguagelabel");
        parameters.put("vLang", langcode);
        ApiHandler.execute(getActContext(), parameters, responseString -> {
            if (responseString != null && !responseString.equals("")) {

                MyApp.getInstance().resetAppServices();

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {


                    generalFunc.storeData(Utils.languageLabelsKey, generalFunc.getJsonValue(Utils.message_str, responseString));
                    generalFunc.storeData(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", responseString));
                    generalFunc.storeData(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", responseString));
                    GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());

                    if (showDialog) {
                        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());

                        GenerateAlertBox alertBox = generalFunc.notifyRestartApp();
                        alertBox.setCancelable(false);
                        alertBox.setBtnClickList(btn_id -> {

                            if (btn_id == 1) {
                                //  generalFunc.restartApp();
                                generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
                                generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
                                GetUserData getUserData = new GetUserData(generalFunc, MyApp.getInstance().getApplicationContext());
                                getUserData.GetConfigDataForLocalStorage();

                                if (OpenListView.mListDialog != null) {
                                    OpenListView.mListDialog.dismiss();
                                }
                                OpenListView.mListDialog = null;
                                new Handler().postDelayed(() -> generalFunc.restartApp(), 100);

                            }
                        });
                        return;
                    }
                    new Handler().postDelayed(() -> generalFunc.restartApp(), 100);

                }
            }
        });

    }

    private Context getActContext() {
        return getActivity();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        updateUserProfileObj();
        setuserInfo();
    }
}
