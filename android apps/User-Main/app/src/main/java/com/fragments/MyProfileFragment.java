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
import android.util.Log;
import android.view.Gravity;
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
import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;

import com.act.BookingActivity;
import com.act.BusinessProfileActivity;
import com.act.ContactUsActivity;
import com.act.DonationActivity;
import com.act.EmergencyContactActivity;
import com.act.FavouriteDriverActivity;
import com.act.HelpActivity23Pro;
import com.act.InviteFriendsActivity;
import com.act.ManageAccountActivity;
import com.act.MenuSettingActivity;
import com.act.MyProfileActivity;
import com.act.MyWalletActivity;
import com.act.NotificationActivity;
import com.act.PaymentWebviewActivity;
import com.act.SearchPickupLocationActivity;
import com.act.StaticPageActivity;
import com.act.VerifyInfoActivity;
import com.act.deliverAll.EditCartActivity;
import com.act.giftcard.GiftCardRedeemActivity;
import com.act.giftcard.GiftCardSendActivity;
import com.act.rentItem.RentItemListPostActivity;
import com.act.rideSharingPro.ListOfDocumentActivity;
import com.act.rideSharingPro.RideSharingProPreferences;
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
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.main.R;
import com.kyleduo.switchbutton.SwitchButton;
import com.model.ContactModel;
import com.model.ServiceModule;
import com.realmModel.Cart;
import com.realmModel.Options;
import com.realmModel.Topping;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
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

import io.realm.Realm;
import io.realm.RealmResults;

public class MyProfileFragment extends BaseFragment {

    private static final String TAG = "MyProfileFragment";
    public String userProfileJson = "";
    public JSONObject obj_userProfile;
    GeneralFunctions generalFunc;

    ImageView backImg, editProfileImage, imgSmartLoginQuery;
    SelectableRoundedImageView userImgView, userImgView_toolbar;
    ImageView infoImg;
    MTextView userNameTxt, userNameTxt_toolbar, userEmailTxt, txtUserMobile, walletHTxt, walletVxt, generalSettingHTxt, accountHTxt;
    MTextView bookingTxt, inviteTxt, topupTxt;
    MTextView notificationHTxt, paymentHTxt, privacyHTxt, termsHTxt, myPaymentHTxt, mybookingHTxt, businessProfileHTxt, myCartHTxt, favDriverHTxt,
            addMoneyHTxt, sendMoneyHTxt, personalDetailsHTxt, changePasswordHTxt, changeCurrencyHTxt, changeLanguageHTxt, supportHTxt, livechatHTxt, contactUsHTxt;
    LinearLayout notificationArea, paymentMethodArea, privacyArea, myBookingArea, businessProfileArea, myCartArea, favDriverArea,
            addMoneyArea, sendMoneyArea, personalDetailsArea, changesPasswordArea, changesCurrancyArea, changeslanguageArea, termsArea, liveChatArea, contactUsArea, smartLoginArea, verifyEmailArea, verifyMobArea;
    View notificationView, paymentView, privacyView, myBookingView, businessProView, mycartView, favDriverView, addMoneyView, aboutUsView, myWalletView,
            sendMoneyView, personalDetailsView, changePasswordView, changeCurrencyView, changeLangView, termsView, livechatView, smartLoginView, verifyEmailView, verifyMobView, donationView;
    LinearLayout bookingArea, inviteArea, topUpArea, logOutArea, workArea;
    LinearLayout myWalletArea, inviteFriendArea, helpArea, aboutusArea, homeArea, headerwalletArea, emeContactArea, donationArea;
    MTextView mywalletHTxt, inviteHTxt, helpHTxt, aboutusHTxt, logoutTxt, otherHTxt, homeHTxt, workHTxt, favHTxt, workaddressTxt, homeaddressTxt, headerwalletTxt, emeContactHTxt, smartLoginHTxt, verifyEmailHTxt, verifyMobHTxt, donationHTxt;
    ImageView notificationArrow, paymentArrow, privacyArrow, termsArrow, mywalletArrow, inviteArrow, helpArrow, aboutusArrow,
            mybookingArrow, businessProArrow, mycartArrow, favDriverArrow, addMoneyArrow, sendMoneyArrow, personalDetailsArrow,
            changePasswordArrow, changeCurrencyArrow, changeLangArrow, livechatArrow, contactUsArrow, logoutArrow, homeArrow, workArrow, emeContactArrow, donationArrow, verifyMobsArrow, verifyEmailArrow;

    View view;
    InternetConnection internetConnection;
    String ENABLE_FAVORITE_DRIVER_MODULE_KEY = "";
    boolean isDeliverOnlyEnabled;
    boolean isAnyDeliverOptionEnabled;
    androidx.appcompat.app.AlertDialog alertDialog;
    String SITE_TYPE = "";
    String SITE_TYPE_DEMO_MSG = "";
    private SwitchButton smartLoginSwitchBtn;


    ArrayList<HashMap<String, String>> language_list = new ArrayList<>();
    String selected_language_code = "";
    String default_selected_language_code = "";
    ArrayList<HashMap<String, String>> currency_list = new ArrayList<>();

    private int selCurrancyPosition = -1, selLanguagePosition = -1;
    String selected_currency = "", default_selected_currency = "", selected_currency_symbol = "";

    LinearLayout toolbar_profile;
    LinearLayout headerArea;
    LinearLayout settingArea, walletArea, favArea, otherArea;
    MTextView signSignUpTxt;
    RelativeLayout profileArea;
    RealmResults<Cart> realmCartList;
    private static final int WEBVIEWPAYMENT = 001;

    LinearLayout rentItemModuleArea, GiftArea, sendGiftArea, redeemgiftArea;
    MTextView rentItemHeaderHTxt, sendGiftHTxt, redeemgiftHTxt, GiftHTxt;
    ImageView sendGiftArrow, redeemgiftArrow;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {

//        if (view != null) {
//            return view;
//        }
        view = inflater.inflate(R.layout.activity_my_profile_new, container, false);
        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());

        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        ENABLE_FAVORITE_DRIVER_MODULE_KEY = generalFunc.retrieveValue(Utils.ENABLE_FAVORITE_DRIVER_MODULE_KEY);
        isDeliverOnlyEnabled = generalFunc.isDeliverOnlyEnabled();
        isAnyDeliverOptionEnabled = generalFunc.isAnyDeliverOptionEnabled();
        obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        SITE_TYPE = generalFunc.getJsonValueStr("SITE_TYPE", obj_userProfile);
        SITE_TYPE_DEMO_MSG = generalFunc.getJsonValueStr("SITE_TYPE_DEMO_MSG", obj_userProfile);
        selected_currency = generalFunc.getJsonValue("vCurrencyPassenger", userProfileJson);
        default_selected_currency = selected_currency;

        if (selected_currency.equalsIgnoreCase("")) {

            selected_currency = generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE);
            default_selected_currency = selected_currency;

        }
        internetConnection = new InternetConnection(getActContext());
        initViews();
        setRentItem();
        setLabel();
        setuserInfo();
        manageView();
        buildLanguageList();
        Bundle bundle = getArguments();
        boolean isProfile = false;
        if (bundle != null) {
            isProfile = getArguments().getBoolean("isProfile", false);
        }
        if (isProfile) {
            personalDetailsArea.getParent().requestChildFocus(personalDetailsArea, personalDetailsArea);

        }
        if (bundle != null) {
            if (getArguments().getBoolean("isback", false)) {
                backImg.setVisibility(View.VISIBLE);

            }
        }

        realmCartList = getCartData();
        return view;

    }

    private void setRentItem() {
        rentItemHeaderHTxt = view.findViewById(R.id.rentItemHeaderHTxt);
        rentItemModuleArea = view.findViewById(R.id.rentItemModuleArea);
        rentItemHeaderHTxt.setVisibility(View.GONE);
        rentItemModuleArea.setVisibility(View.GONE);

        if (generalFunc.getJsonValue("ENABLE_RENT_ITEM_SERVICES", userProfileJson).equalsIgnoreCase("Yes") ||
                generalFunc.getJsonValue("ENABLE_RENT_ESTATE_SERVICES", userProfileJson).equalsIgnoreCase("Yes") ||
                generalFunc.getJsonValue("ENABLE_RENT_CARS_SERVICES", userProfileJson).equalsIgnoreCase("Yes")) {

            rentItemHeaderHTxt.setVisibility(View.VISIBLE);
            rentItemModuleArea.setVisibility(View.VISIBLE);

            rentItemHeaderHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_BUY_SELL_CARS"));

            LinearLayout rentGeneralItemArea = view.findViewById(R.id.rentGeneralItemArea);
            addToClickHandler(rentGeneralItemArea);
            MTextView rentGeneralItemHTxt = view.findViewById(R.id.rentGeneralItemHTxt);
            rentGeneralItemHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_GENERAL_ITEM_LISTING"));
            ImageView rentGeneralArrow = view.findViewById(R.id.rentGeneralArrow);

            LinearLayout rentPropertiesItemArea = view.findViewById(R.id.rentPropertiesItemArea);
            addToClickHandler(rentPropertiesItemArea);
            MTextView rentPropertiesHTxt = view.findViewById(R.id.rentPropertiesHTxt);
            rentPropertiesHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_PROPERTY_LISTING"));
            ImageView rentPropertiesArrow = view.findViewById(R.id.rentPropertiesArrow);

            LinearLayout rentCarItemArea = view.findViewById(R.id.rentCarItemArea);
            addToClickHandler(rentCarItemArea);
            MTextView rentCarItemHTxt = view.findViewById(R.id.rentCarItemHTxt);
            rentCarItemHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_CARS_LISTING"));
            ImageView rentCarArrow = view.findViewById(R.id.rentCarArrow);

            if (generalFunc.isRTLmode()) {
                rentGeneralArrow.setRotation(180);
                rentPropertiesArrow.setRotation(180);
                rentCarArrow.setRotation(180);
            }

            rentGeneralItemArea.setVisibility(View.GONE);
            rentPropertiesItemArea.setVisibility(View.GONE);
            rentCarItemArea.setVisibility(View.GONE);

            if (generalFunc.getJsonValue("ENABLE_RENT_ITEM_SERVICES", userProfileJson).equalsIgnoreCase("Yes")) {
                rentGeneralItemArea.setVisibility(View.VISIBLE);
            }
            if (generalFunc.getJsonValue("ENABLE_RENT_ESTATE_SERVICES", userProfileJson).equalsIgnoreCase("Yes")) {
                rentPropertiesItemArea.setVisibility(View.VISIBLE);
            }
            if (generalFunc.getJsonValue("ENABLE_RENT_CARS_SERVICES", userProfileJson).equalsIgnoreCase("Yes")) {
                rentCarItemArea.setVisibility(View.VISIBLE);
            }
        }
    }

    private RealmResults<Cart> getCartData() {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(Cart.class).findAll();
    }

    private void manageView() {
        if (generalFunc.getJsonValue("ENABLE_LIVE_CHAT", userProfileJson).equalsIgnoreCase("Yes")) {
            liveChatArea.setVisibility(View.VISIBLE);
            livechatView.setVisibility(View.VISIBLE);
        } else {
            liveChatArea.setVisibility(View.GONE);
            livechatView.setVisibility(View.GONE);
        }

        if (generalFunc.getJsonValue("showTermsCondition", userProfileJson).equalsIgnoreCase("Yes")) {
            termsArea.setVisibility(View.VISIBLE);
            termsView.setVisibility(View.VISIBLE);
        } else {
            termsArea.setVisibility(View.GONE);
            termsView.setVisibility(View.GONE);
        }

        if (generalFunc.getJsonValue("showPrivacyPolicy", userProfileJson).equalsIgnoreCase("Yes")) {
            privacyArea.setVisibility(View.VISIBLE);
            privacyView.setVisibility(View.VISIBLE);
        } else {
            privacyArea.setVisibility(View.GONE);
            privacyView.setVisibility(View.GONE);
        }

        if (generalFunc.getJsonValue("showAboutUs", userProfileJson).equalsIgnoreCase("Yes")) {
            aboutusArea.setVisibility(View.VISIBLE);
            aboutUsView.setVisibility(View.VISIBLE);
        } else {
            aboutusArea.setVisibility(View.GONE);
            aboutUsView.setVisibility(View.GONE);
        }

        if (ENABLE_FAVORITE_DRIVER_MODULE_KEY.equalsIgnoreCase("Yes")) {
            favDriverArea.setVisibility(View.VISIBLE);
            favDriverView.setVisibility(View.VISIBLE);

        } else {
            favDriverArea.setVisibility(View.GONE);
            favDriverView.setVisibility(View.GONE);

        }

//        if (generalFunc.getJsonValueStr(Utils.DONATION_ENABLE, obj_userProfile).equalsIgnoreCase("Yes")/* && !APP_TYPE.equals(Utils.CabGeneralTypeRide_Delivery_UberX)*/) {
        if (generalFunc.getJsonValueStr(Utils.DONATION_ENABLE, obj_userProfile).equalsIgnoreCase("Yes") && !generalFunc.getMemberId().equalsIgnoreCase("")) {
            donationView.setVisibility(View.VISIBLE);
            donationArea.setVisibility(View.VISIBLE);
        } else {
            donationView.setVisibility(View.GONE);
            donationArea.setVisibility(View.GONE);
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


        if (generalFunc.getJsonValueStr("ENABLE_CORPORATE_PROFILE", obj_userProfile).equalsIgnoreCase("Yes")) {
            businessProfileArea.setVisibility(View.VISIBLE);
            businessProView.setVisibility(View.VISIBLE);
        } else {
            businessProfileArea.setVisibility(View.GONE);
            businessProView.setVisibility(View.GONE);

        }

        if (generalFunc.isAnyDeliverOptionEnabled()) {
            generalSettingHTxt.setVisibility(View.VISIBLE);
            settingArea.setVisibility(View.VISIBLE);
            myCartArea.setVisibility(View.VISIBLE);
            mycartView.setVisibility(View.VISIBLE);
        } else {
            myCartArea.setVisibility(View.GONE);
            mycartView.setVisibility(View.GONE);
        }

        if (generalFunc.getJsonValue("ENABLE_NEWS_SECTION", userProfileJson) != null && generalFunc.getJsonValue("ENABLE_NEWS_SECTION", userProfileJson).equalsIgnoreCase("yes") && !generalFunc.getMemberId().equals("")) {
            notificationArea.setVisibility(View.VISIBLE);
            notificationView.setVisibility(View.VISIBLE);

        } else {
            notificationArea.setVisibility(View.GONE);
            notificationView.setVisibility(View.GONE);

        }
        if (!generalFunc.getJsonValueStr(Utils.REFERRAL_SCHEME_ENABLE, obj_userProfile).equals("") && generalFunc.getJsonValueStr(Utils.REFERRAL_SCHEME_ENABLE, obj_userProfile).equalsIgnoreCase("Yes") && !generalFunc.getMemberId().equals("")) {
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

        if (languageList_arr != null) {
            if (languageList_arr.length() < 2) {
                changeslanguageArea.setVisibility(View.GONE);
            } else {
                changeslanguageArea.setVisibility(View.VISIBLE);
            }
        } else {
            changeslanguageArea.setVisibility(View.VISIBLE);
        }


        if (ServiceModule.isDeliverAllOnly()) {

            if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
                signSignUpTxt.setVisibility(View.GONE);
                otherArea.setVisibility(View.VISIBLE);
                otherHTxt.setVisibility(View.VISIBLE);
                headerArea.setVisibility(View.VISIBLE);
                //  profileArea.getLayoutParams().height = Utils.dpToPx(160, getActContext());
                // profileArea.requestLayout();
                changesPasswordArea.setVisibility(View.VISIBLE);
                personalDetailsView.setVisibility(View.VISIBLE);
                personalDetailsArea.setVisibility(View.VISIBLE);
                changePasswordView.setVisibility(View.VISIBLE);
                emeContactArea.setVisibility(View.GONE);
            }
        }

        if (generalFunc.getJsonValue("ENABLE_GIFT_CARD_FEATURE", userProfileJson).equalsIgnoreCase("Yes") && !generalFunc.getMemberId().equalsIgnoreCase("")) {
            GiftArea.setVisibility(View.VISIBLE);
            GiftHTxt.setVisibility(View.VISIBLE);
        } else {
            GiftArea.setVisibility(View.GONE);
            GiftHTxt.setVisibility(View.GONE);
        }
        //Set Configure For Emergency Option
        /*if (generalFunc.retrieveValue("HIDE_EMERGENCY_CONTACT").equalsIgnoreCase("Yes")) {
            emeContactArea.setVisibility(View.GONE);
        }*/


    }

    private void initViews() {
        profileArea = view.findViewById(R.id.profileAreas);
        backImg = view.findViewById(R.id.backImg);
        signSignUpTxt = view.findViewById(R.id.signSignUpTxt);
        headerArea = view.findViewById(R.id.headerArea);
        settingArea = view.findViewById(R.id.settingArea);
        walletArea = view.findViewById(R.id.walletArea);
        otherArea = view.findViewById(R.id.otherArea);
        favArea = view.findViewById(R.id.favArea);
        editProfileImage = view.findViewById(R.id.editProfileImage);
        infoImg = view.findViewById(R.id.infoImg);
        addToClickHandler(infoImg);
        userImgView = view.findViewById(R.id.userImgView);
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
        emeContactHTxt = view.findViewById(R.id.emeContactHTxt);
        verifyEmailHTxt = view.findViewById(R.id.verifyEmailHTxt);
        verifyMobHTxt = view.findViewById(R.id.verifyMobHTxt);
        donationHTxt = view.findViewById(R.id.donationHTxt);
        generalSettingHTxt = view.findViewById(R.id.generalSettingHTxt);
        accountHTxt = view.findViewById(R.id.accountHTxt);
        notificationHTxt = view.findViewById(R.id.notificationHTxt);
        paymentHTxt = view.findViewById(R.id.paymentHTxt);
        privacyHTxt = view.findViewById(R.id.privacyHTxt);
        termsHTxt = view.findViewById(R.id.termsHTxt);
        logoutTxt = view.findViewById(R.id.logoutTxt);
        otherHTxt = view.findViewById(R.id.otherHTxt);
        homeHTxt = view.findViewById(R.id.homeHTxt);
        workHTxt = view.findViewById(R.id.workHTxt);
        favHTxt = view.findViewById(R.id.favHTxt);
        workaddressTxt = view.findViewById(R.id.workaddressTxt);
        homeaddressTxt = view.findViewById(R.id.homeaddressTxt);
        notificationArea = view.findViewById(R.id.notificationArea);
        paymentMethodArea = view.findViewById(R.id.paymentMethodArea);
        privacyArea = view.findViewById(R.id.privacyArea);

        GiftArea = view.findViewById(R.id.GiftArea);
        sendGiftArea = view.findViewById(R.id.sendGiftArea);
        redeemgiftArea = view.findViewById(R.id.redeemgiftArea);
        sendGiftHTxt = view.findViewById(R.id.sendGiftHTxt);
        redeemgiftHTxt = view.findViewById(R.id.redeemgiftHTxt);
        GiftHTxt = view.findViewById(R.id.GiftHTxt);
        sendGiftArrow = view.findViewById(R.id.sendGiftArrow);
        redeemgiftArrow = view.findViewById(R.id.redeemgiftArrow);

        logoutTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOGOUT"));
        otherHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));

        myBookingArea = view.findViewById(R.id.myBookingArea);
        businessProfileArea = view.findViewById(R.id.businessProfileArea);
        myCartArea = view.findViewById(R.id.myCartArea);
        favDriverArea = view.findViewById(R.id.favDriverArea);
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
        businessProView = view.findViewById(R.id.businessProView);
        mycartView = view.findViewById(R.id.mycartView);
        favDriverView = view.findViewById(R.id.favDriverView);
        addMoneyView = view.findViewById(R.id.addMoneyView);
        aboutUsView = view.findViewById(R.id.aboutUsView);
        myWalletView = view.findViewById(R.id.myWalletView);
        sendMoneyView = view.findViewById(R.id.sendMoneyView);
        personalDetailsView = view.findViewById(R.id.personalDetailsView);
        changePasswordView = view.findViewById(R.id.personalDetailsView);
        changeCurrencyView = view.findViewById(R.id.changeCurrencyView);
        changeLangView = view.findViewById(R.id.changeLangView);
        termsView = view.findViewById(R.id.termsView);
        livechatView = view.findViewById(R.id.livechatView);
        verifyEmailView = view.findViewById(R.id.verifyEmailView);
        verifyMobView = view.findViewById(R.id.verifyMobView);
        donationView = view.findViewById(R.id.donationView);
        bookingArea = view.findViewById(R.id.bookingArea);
        inviteArea = view.findViewById(R.id.inviteArea);
        topUpArea = view.findViewById(R.id.topUpArea);

        logOutArea = view.findViewById(R.id.logOutArea);
        workArea = view.findViewById(R.id.workArea);
        myPaymentHTxt = view.findViewById(R.id.myPaymentHTxt);
        mybookingHTxt = view.findViewById(R.id.mybookingHTxt);
        businessProfileHTxt = view.findViewById(R.id.businessProfileHTxt);
        myCartHTxt = view.findViewById(R.id.myCartHTxt);
        favDriverHTxt = view.findViewById(R.id.favDriverHTxt);
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
        donationArea = view.findViewById(R.id.donationArea);
        inviteFriendArea = view.findViewById(R.id.inviteFriendArea);
        helpArea = view.findViewById(R.id.helpArea);
        aboutusArea = view.findViewById(R.id.aboutusArea);
        homeArea = view.findViewById(R.id.homeArea);
        notificationArrow = view.findViewById(R.id.notificationArrow);
        paymentArrow = view.findViewById(R.id.paymentArrow);
        privacyArrow = view.findViewById(R.id.privacyArrow);
        termsArrow = view.findViewById(R.id.termsArrow);
        mywalletArrow = view.findViewById(R.id.mywalletArrow);
        inviteArrow = view.findViewById(R.id.inviteArrow);
        helpArrow = view.findViewById(R.id.helpArrow);
        aboutusArrow = view.findViewById(R.id.aboutusArrow);
        mybookingArrow = view.findViewById(R.id.mybookingArrow);
        businessProArrow = view.findViewById(R.id.businessProArrow);
        mycartArrow = view.findViewById(R.id.mycartArrow);
        favDriverArrow = view.findViewById(R.id.favDriverArrow);
        addMoneyArrow = view.findViewById(R.id.addMoneyArrow);
        sendMoneyArrow = view.findViewById(R.id.sendMoneyArrow);
        personalDetailsArrow = view.findViewById(R.id.personalDetailsArrow);
        changePasswordArrow = view.findViewById(R.id.changePasswordArrow);
        changeCurrencyArrow = view.findViewById(R.id.changeCurrencyArrow);
        changeLangArrow = view.findViewById(R.id.changeLangArrow);
        livechatArrow = view.findViewById(R.id.livechatArrow);
        contactUsArrow = view.findViewById(R.id.contactUsArrow);
        logoutArrow = view.findViewById(R.id.logoutArrow);
        homeArrow = view.findViewById(R.id.homeArrow);
        workArrow = view.findViewById(R.id.workArrow);
        emeContactArrow = view.findViewById(R.id.emeContactArrow);
        donationArrow = view.findViewById(R.id.donationArrow);
        verifyMobsArrow = view.findViewById(R.id.verifyMobsArrow);
        verifyEmailArrow = view.findViewById(R.id.verifyEmailArrow);


        mywalletHTxt = view.findViewById(R.id.mywalletHTxt);
        inviteHTxt = view.findViewById(R.id.inviteHTxt);
        helpHTxt = view.findViewById(R.id.helpHTxt);
        aboutusHTxt = view.findViewById(R.id.aboutusHTxt);
        toolbar_profile = view.findViewById(R.id.toolbar_profile);

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
            bottomInfoDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_NOTE_2_TXT"), R.raw.biometric, generalFunc.retrieveLangLBl("", "LBL_OK"), true);
        });


        addToClickHandler(notificationArea);
        addToClickHandler(paymentMethodArea);
        addToClickHandler(privacyArea);
        addToClickHandler(emeContactArea);
        addToClickHandler(donationArea);
        addToClickHandler(myBookingArea);
        addToClickHandler(businessProfileArea);
        addToClickHandler(bookingArea);
        addToClickHandler(inviteArea);
        addToClickHandler(topUpArea);
        addToClickHandler(logOutArea);
        addToClickHandler(workArea);
        addToClickHandler(myWalletArea);
        addToClickHandler(headerwalletArea);
        addToClickHandler(inviteFriendArea);
        addToClickHandler(helpArea);
        addToClickHandler(aboutusArea);
        addToClickHandler(homeArea);
        addToClickHandler(backImg);
        addToClickHandler(myCartArea);
        addToClickHandler(favDriverArea);
        addToClickHandler(addMoneyArea);
        addToClickHandler(sendMoneyArea);
        addToClickHandler(personalDetailsArea);
        addToClickHandler(changesPasswordArea);
        addToClickHandler(changesCurrancyArea);
        addToClickHandler(changeslanguageArea);
        addToClickHandler(termsArea);
        addToClickHandler(liveChatArea);
        addToClickHandler(contactUsArea);
        addToClickHandler(verifyEmailArea);
        addToClickHandler(verifyMobArea);
        addToClickHandler(editProfileImage);
        addToClickHandler(signSignUpTxt);
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
            businessProArrow.setRotation(180);
            mycartArrow.setRotation(180);
            favDriverArrow.setRotation(180);
            addMoneyArrow.setRotation(180);
            sendMoneyArrow.setRotation(180);
            personalDetailsArrow.setRotation(180);
            changePasswordArrow.setRotation(180);
            changeCurrencyArrow.setRotation(180);
            changeLangArrow.setRotation(180);
            livechatArrow.setRotation(180);
            contactUsArrow.setRotation(180);
            logoutArrow.setRotation(180);
            homeArrow.setRotation(180);
            workArrow.setRotation(180);
            emeContactArrow.setRotation(180);
            donationArrow.setRotation(180);
            verifyMobsArrow.setRotation(180);
            verifyEmailArrow.setRotation(180);
            sendGiftArrow.setRotation(180);
            redeemgiftArrow.setRotation(180);
        }


        NestedScrollView scroller = (NestedScrollView) view.findViewById(R.id.scroll);
        scroller.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {

            if (scrollY > oldScrollY) {

                Log.i(TAG, "Scroll DOWN");

                if (scrollY > getResources().getDimension(R.dimen._75sdp)) {
                    toolbar_profile.setVisibility(View.VISIBLE);
                }
            }
            if (scrollY < oldScrollY) {
                Log.i(TAG, "Scroll UP");

                if (scrollY < getResources().getDimension(R.dimen._75sdp)) {
                    toolbar_profile.setVisibility(View.GONE);
                }

            }

            if (scrollY == 0) {
                Log.i(TAG, "TOP SCROLL");

            }

            if (scrollY == (v.getMeasuredHeight() - v.getChildAt(0).getMeasuredHeight())) {
                Log.i(TAG, "BOTTOM SCROLL");
            }
        });


        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
            headerArea.setVisibility(View.GONE);
            generalSettingHTxt.setVisibility(View.GONE);
            settingArea.setVisibility(View.GONE);
            myPaymentHTxt.setVisibility(View.GONE);
            walletArea.setVisibility(View.GONE);
            favHTxt.setVisibility(View.GONE);
            favArea.setVisibility(View.GONE);
            otherHTxt.setVisibility(View.GONE);
            otherArea.setVisibility(View.GONE);
            changesPasswordArea.setVisibility(View.GONE);
            personalDetailsView.setVisibility(View.GONE);
            personalDetailsArea.setVisibility(View.GONE);
            changePasswordView.setVisibility(View.GONE);
            editProfileImage.setVisibility(View.GONE);
            verifyEmailArea.setVisibility(View.GONE);
            verifyEmailView.setVisibility(View.GONE);
            verifyMobArea.setVisibility(View.GONE);
            verifyMobView.setVisibility(View.GONE);
            emeContactArea.setVisibility(View.GONE);

        }

        if (ServiceModule.isOnlyBuySellRentEnable()) {
            bookingArea.setVisibility(View.GONE);
            myBookingArea.setVisibility(View.GONE);
            myBookingArea.setVisibility(View.GONE);
            businessProfileArea.setVisibility(View.GONE);
            myCartArea.setVisibility(View.GONE);
            favDriverArea.setVisibility(View.GONE);
            emeContactArea.setVisibility(View.GONE);
            favHTxt.setVisibility(View.GONE);
            favArea.setVisibility(View.GONE);
        }
        if (ServiceModule.isDeliverAllOnly()) {
            emeContactArea.setVisibility(View.GONE);
        }

    }

    private void setLabel() {
        verifyEmailHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_VERIFY"));
        smartLoginHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"));
        verifyMobHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_VERIFY"));
        walletHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WALLET_BALANCE"));
        emeContactHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMERGENCY_CONTACT"));
        topupTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOP_UP"));
        headerwalletTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WALLET_TXT"));
        inviteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INVITE"));

        if (ServiceModule.isDeliverAllOnly()) {
            mybookingHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS"));
            bookingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_ORDERS"));

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

        businessProfileHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BUSINESS_PROFILE"));
        myCartHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MY_CART"));
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
        favHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAV_LOCATIONS"));
        homeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_HOME_PLACE_TXT"));
        workHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_WORK_PLACE_TXT"));
        donationHTxt.setText(generalFunc.retrieveLangLBl("Donation", "LBL_DONATE"));
        signSignUpTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SIGN_IN_SIGN_IN_TXT") + " / " + generalFunc.retrieveLangLBl("", "LBL_SIGNUP_SIGNUP"));

        String APP_TYPE = generalFunc.getJsonValueStr("APP_TYPE", obj_userProfile);

        if (ServiceModule.ServiceProvider || ServiceModule.ServiceBid || ServiceModule.VideoCall) {
            favDriverHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAV_PROVIDER"));
        } else {
            favDriverHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAV_DRIVER"));
        }


    }

    @SuppressLint("SetTextI18n")
    private void setuserInfo() {

        userNameTxt.setText(generalFunc.getJsonValueStr("vName", obj_userProfile) + " " + generalFunc.getJsonValueStr("vLastName", obj_userProfile));
        userNameTxt_toolbar.setText(generalFunc.getJsonValueStr("vName", obj_userProfile) + " " + generalFunc.getJsonValueStr("vLastName", obj_userProfile));
        String vEmail = generalFunc.getJsonValueStr("vEmail", obj_userProfile);
        String vPhone = "(+" + generalFunc.getJsonValueStr("vPhoneCode", obj_userProfile) + ") " + generalFunc.getJsonValueStr("vPhone", obj_userProfile);
        if (Utils.checkText(vEmail)) {
            userEmailTxt.setText(vEmail);
            userEmailTxt.setVisibility(View.VISIBLE);
        } else {
            userEmailTxt.setVisibility(View.GONE);
        }

        if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
            txtUserMobile.setText(vPhone);
        }


        String url = CommonUtilities.USER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImgName", userProfileJson);
        generalFunc.checkProfileImage(userImgView, url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);
        generalFunc.checkProfileImage(userImgView_toolbar, url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);
        walletVxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("user_available_balance", obj_userProfile)));

        String work_address_str = generalFunc.retrieveValue("userWorkLocationAddress");
        String home_address_str = generalFunc.retrieveValue("userHomeLocationAddress");

        if (work_address_str != null && !work_address_str.equalsIgnoreCase("")) {
            workaddressTxt.setVisibility(View.VISIBLE);

            int padding = (int) getResources().getDimension(R.dimen._10sdp);
            workArea.setPadding(0, padding, 0, padding);
            workaddressTxt.setText(work_address_str);
            workHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WORK_PLACE"));

        } else {
            workaddressTxt.setVisibility(View.GONE);
            workArea.setPadding(0, 0, 0, 0);
            workHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_WORK_PLACE_TXT"));
        }
        if (home_address_str != null && !home_address_str.equalsIgnoreCase("")) {
            int padding = (int) getResources().getDimension(R.dimen._10sdp);
            homeArea.setPadding(0, padding, 0, padding);
            homeaddressTxt.setVisibility(View.VISIBLE);
            homeaddressTxt.setText(home_address_str);
            homeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_PLACE"));
        } else {
            homeaddressTxt.setVisibility(View.GONE);
            homeArea.setPadding(0, 0, 0, 0);
            homeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_HOME_PLACE_TXT"));
        }

        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
            userNameTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WELCOME_BACK"));
            userNameTxt_toolbar.setText(generalFunc.retrieveLangLBl("", "LBL_WELCOME_BACK"));
            signSignUpTxt.setVisibility(View.VISIBLE);
            signSignUpTxt.setVisibility(View.VISIBLE);

            profileArea.getLayoutParams().height = (int) getActContext().getResources().getDimension(R.dimen._110sdp);
            profileArea.requestLayout();

        }

        if (!generalFunc.getJsonValueStr("eEmailVerified", obj_userProfile).equalsIgnoreCase("YES") && !generalFunc.getMemberId().equalsIgnoreCase("")) {
            verifyEmailArea.setVisibility(View.VISIBLE);
            infoImg.setVisibility(View.VISIBLE);
            verifyEmailView.setVisibility(View.VISIBLE);
        } else {
            verifyEmailArea.setVisibility(View.GONE);
            infoImg.setVisibility(View.GONE);
            verifyEmailView.setVisibility(View.GONE);
        }


        if (!generalFunc.getJsonValueStr("ePhoneVerified", obj_userProfile).equalsIgnoreCase("YES") && !generalFunc.getMemberId().equalsIgnoreCase("")) {
            verifyMobArea.setVisibility(View.VISIBLE);
            verifyMobView.setVisibility(View.VISIBLE);
        } else {
            verifyMobArea.setVisibility(View.GONE);
            verifyMobView.setVisibility(View.GONE);
        }

        if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") && !generalFunc.getMemberId().equalsIgnoreCase("")) {
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


        if (generalFunc.getMemberId().equals("")) {
            editProfileImage.setImageResource(R.drawable.ic_edit);
        } else {
            if (generalFunc.retrieveValue(Utils.FEMALE_RIDE_REQ_ENABLE).equalsIgnoreCase("Yes") && generalFunc.getJsonValue("eGender", obj_userProfile).equals("") && generalFunc.retrieveValue(Utils.ONLYDELIVERALL_KEY).equalsIgnoreCase("No")) {
                editProfileImage.setImageResource(R.drawable.ic_settings_new);
            } else {
                editProfileImage.setImageResource(R.drawable.ic_edit);
            }
        }


        rideSharing();
    }

    private void rideSharing() {
        LinearLayout preferencesArea = view.findViewById(R.id.preferencesArea);
        preferencesArea.setVisibility(View.GONE);
        View preferencesView = view.findViewById(R.id.preferencesView);
        preferencesView.setVisibility(View.GONE);

        if (ServiceModule.EnableRideSharingPro) {
            preferencesView.setVisibility(View.VISIBLE);
            preferencesArea.setVisibility(View.VISIBLE);
            addToClickHandler(preferencesArea);

            MTextView preferencesHTxt = view.findViewById(R.id.preferencesHTxt);
            preferencesHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT_YOU_RIDE_SHARE_TEXT"));

            ImageView preferencesArrow = view.findViewById(R.id.preferencesArrow);
            if (generalFunc.isRTLmode()) {
                preferencesArrow.setRotation(180);
            }
        }
        if (ServiceModule.OnlyRideSharingPro) {
            bookingArea.setVisibility(View.GONE);

            myBookingArea.setVisibility(View.GONE);
            myBookingView.setVisibility(View.GONE);
            businessProfileArea.setVisibility(View.GONE);
            businessProView.setVisibility(View.GONE);

            myCartArea.setVisibility(View.GONE);
            mycartView.setVisibility(View.GONE);
            favDriverArea.setVisibility(View.GONE);
            favDriverView.setVisibility(View.GONE);

            rentItemHeaderHTxt.setVisibility(View.GONE);
            rentItemModuleArea.setVisibility(View.GONE);
            favHTxt.setVisibility(View.GONE);
            favArea.setVisibility(View.GONE);

        }

        LinearLayout manageDocArea = view.findViewById(R.id.manageDocArea);
        manageDocArea.setVisibility(View.GONE);
        View manageDocView = view.findViewById(R.id.manageDocView);
        manageDocView.setVisibility(View.GONE);

        if ((ServiceModule.rideShare || ServiceModule.OnlyRideSharingPro)) {
            if (generalFunc.getJsonValueStr("RIDE_SHARE_USER_DOCUMENT_ENABLED", obj_userProfile).equalsIgnoreCase("Yes")) {
                manageDocView.setVisibility(View.VISIBLE);
                manageDocArea.setVisibility(View.VISIBLE);
                addToClickHandler(manageDocArea);
            }

            MTextView manageDocHTxt = view.findViewById(R.id.manageDocHTxt);
            manageDocHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_DOCUMENT"));

            MTextView manageDocTxt = view.findViewById(R.id.manageDocTxt);
            manageDocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_MANAGE_DOC_MENU_HINT_TXT"));
            manageDocTxt.setVisibility(ServiceModule.OnlyRideSharingPro ? View.GONE : View.VISIBLE);

            MTextView preferenceInfoTxt = view.findViewById(R.id.preferenceInfoTxt);
            preferenceInfoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_MANAGE_DOC_MENU_HINT_TXT"));
            preferenceInfoTxt.setVisibility(ServiceModule.OnlyRideSharingPro ? View.GONE : View.VISIBLE);

            ImageView manageDocArrow = view.findViewById(R.id.manageDocArrow);
            if (generalFunc.isRTLmode()) {
                manageDocArrow.setRotation(180);
            }
        }
    }

    private void buildLanguageList() {
        JSONArray languageList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.LANGUAGE_LIST_KEY));

        language_list.clear();
        for (int i = 0; i < languageList_arr.length(); i++) {
            JSONObject obj_temp = generalFunc.getJsonObject(languageList_arr, i);


            if ((generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY)).equals(generalFunc.getJsonValueStr("vCode", obj_temp))) {
                selected_language_code = generalFunc.getJsonValueStr("vCode", obj_temp);

                default_selected_language_code = selected_language_code;
                selLanguagePosition = i;
            }

            HashMap<String, String> data = new HashMap<>();
            data.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
            data.put("vCode", generalFunc.getJsonValueStr("vCode", obj_temp));
            data.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));
            data.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));


            language_list.add(data);
        }
        if (language_list.size() < 2 /*|| generalFunc.retrieveValue("LANGUAGE_OPTIONAL").equalsIgnoreCase("Yes")*/) {
            changeslanguageArea.setVisibility(View.GONE);
        } else {
            changeslanguageArea.setVisibility(View.VISIBLE);

        }

        buildCurrencyList();

    }

    private void updateProfile(int position, String viewType) {
        if (viewType.equalsIgnoreCase(Utils.DEFAULT_LANGUAGE_VALUE)) {
            selLanguagePosition = position;
        } else if (viewType.equalsIgnoreCase(Utils.DEFAULT_CURRENCY_VALUE)) {
            selCurrancyPosition = position;
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateUserProfileDetail");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("vName", generalFunc.getJsonValue("vName", userProfileJson));
        parameters.put("vLastName", generalFunc.getJsonValue("vLastName", userProfileJson));
        parameters.put("vPhone", generalFunc.getJsonValue("vPhone", userProfileJson));
        parameters.put("vPhoneCode", generalFunc.getJsonValue("vPhoneCode", userProfileJson));
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
                    String vCurrencyPassenger = generalFunc.getJsonValue("vCurrencyPassenger", userProfileJson);

                    String messgeJson = generalFunc.getJsonValue(Utils.message_str, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, messgeJson);
                    responseString = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);


                    new ConfigureMemberData(responseString, generalFunc, getActContext(), false);

                    if (!currentLangCode.equals(selected_language_code) || !selected_currency.equals(vCurrencyPassenger)) {
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

    private void buildCurrencyList() {

        currency_list.clear();
        JSONArray currencyList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.CURRENCY_LIST_KEY));

        if (currencyList_arr != null) {
            for (int i = 0; i < currencyList_arr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(currencyList_arr, i);

                HashMap<String, String> data = new HashMap<>();
                data.put("vName", generalFunc.getJsonValueStr("vName", obj_temp));
                data.put("vCode", generalFunc.getJsonValueStr("vSymbol", obj_temp));
                data.put("vSymbol", generalFunc.getJsonValueStr("vSymbol", obj_temp));
                data.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));
                data.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));

                if (!selected_currency.equalsIgnoreCase("") && selected_currency.equalsIgnoreCase(generalFunc.getJsonValueStr("vName", obj_temp))) {
                    selCurrancyPosition = i;
                }
                currency_list.add(data);
            }

            if (currency_list.size() < 2 /*|| generalFunc.retrieveValue("CURRENCY_OPTIONAL").equalsIgnoreCase("Yes") */) {
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

        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        //  Logger.d("Onresume", ":: fragment called" + "::" + generalFunc.getJsonValueStr("user_available_balance", obj_userProfile));

        setuserInfo();
//        manageView();


    }

    private void showLanguageList() {
        // list_language.show();

        OpenListView.getInstance(getActContext(), getSelectLangText(), language_list, OpenListView.OpenDirection.CENTER, true, position -> {


            selected_language_code = language_list.get(position).get("vCode");
            generalFunc.storeData(Utils.DEFAULT_LANGUAGE_VALUE, language_list.get(position).get("vTitle"));


            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                selLanguagePosition = position;
                generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
                generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
                changeLanguagedata(selected_language_code, false);

            } else {
                showCartAlert(position, Utils.DEFAULT_LANGUAGE_VALUE);
//                updateProfile();
            }
        }, true, generalFunc.retrieveLangLBl("", "LBL_LANG_PREFER"), true).show(selLanguagePosition, "vTitle");
    }

    private void showCurrencyList() {


        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_CURRENCY"), currency_list, OpenListView.OpenDirection.CENTER, true, position -> {


            selected_currency_symbol = currency_list.get(position).get("vSymbol");
            selected_currency = currency_list.get(position).get("vName");
            if (generalFunc.getMemberId().equalsIgnoreCase("")) {
                selCurrancyPosition = position;
                generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
                generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
                changeLanguagedata(selected_language_code, false);
            } else {
                showCartAlert(position, Utils.DEFAULT_CURRENCY_VALUE);
//                updateProfile();
            }
        }, true, generalFunc.retrieveLangLBl("", "LBL_CURRENCY_PREFER"), true).show(selCurrancyPosition, "vName");
    }

    private void showCartAlert(int position, String viewType) {
        /** Below Code block Used when DeliverAll Enable **/
        if (realmCartList != null && realmCartList.size() > 0 && (!default_selected_currency.equalsIgnoreCase(selected_currency) || !default_selected_language_code.equalsIgnoreCase(selected_language_code)) && generalFunc.getJsonValue("DELIVERALL", userProfileJson) != null && generalFunc.getJsonValue("DELIVERALL", userProfileJson).equalsIgnoreCase("Yes")) {
            OpenListView.showCartAlert = true;
            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
            generateAlert.setCancelable(false);
            generateAlert.setBtnClickList(btn_id -> {
                if (btn_id == 0) {
                    OpenListView.mListDialog.dismiss();
                    generateAlert.closeAlertBox();
                } else {
                    updateProfile(position, viewType);
                }
                OpenListView.mListDialog = null;
                OpenListView.showCartAlert = false;
            });
            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("your cart is clear", "LBL_CART_REMOVE_NOTE"));
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
            generateAlert.showAlertBox();

            return;
        } else {
            updateProfile(position, viewType);
        }
        /** Above Code block Used when DeliverAll Enable **/
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

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null && !responseStringObject.toString().equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

                if (isDataAvail == true) {
                    alertDialog.dismiss();
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValueStr(Utils.message_str, responseStringObject));
                    userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                    obj_userProfile = generalFunc.getJsonObject(userProfileJson);
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
            try {
                MenuSettingActivity menuSettingActivity = (MenuSettingActivity) MyApp.getInstance().getCurrentAct();
                menuSettingActivity.onBackPressed();
            } catch (Exception e) {

            }
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
            if (generalFunc.getMemberId().equals("")) {
                MyUtils.openMobileStageActivity(getActContext());
            } else {
                if (generalFunc.getJsonValue("ENABLE_ACCOUNT_DELETION", userProfileJson).equalsIgnoreCase("Yes")) {
                    new ActUtils(getActContext()).startAct(ManageAccountActivity.class);
                } else {
                    new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
                }
            }
        } else if (id == R.id.editProfileImage) {
            if (generalFunc.getMemberId().equals("")) {
                MyUtils.openMobileStageActivity(getActContext());
            } else {

                if (generalFunc.retrieveValue(Utils.FEMALE_RIDE_REQ_ENABLE).equalsIgnoreCase("Yes") && generalFunc.getJsonValue("eGender", obj_userProfile).equals("") && generalFunc.retrieveValue(Utils.ONLYDELIVERALL_KEY).equalsIgnoreCase("No")) {
                    genderDailog();
                } else {
                    new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
                }
            }
        } else if (id == R.id.bookingArea || id == R.id.myBookingArea) {
            if (isDeliverOnlyEnabled && generalFunc.getMemberId().equals("")) {
                MyUtils.openMobileStageActivity(getActContext());
            } else {
                new ActUtils(getActContext()).startAct(BookingActivity.class);
            }
        } else if (id == R.id.businessProfileArea) {
            new ActUtils(getActContext()).startActWithData(BusinessProfileActivity.class, bn);
        } else if (id == R.id.myCartArea) {
            new ActUtils(getActContext()).startAct(EditCartActivity.class);
        } else if (id == R.id.emeContactArea) {
            new ActUtils(getActContext()).startAct(EmergencyContactActivity.class);
        } else if (id == R.id.donationArea) {
            new ActUtils(getActContext()).startActWithData(DonationActivity.class, bn);
        } else if (id == R.id.notificationArea) {
            new ActUtils(getActContext()).startAct(NotificationActivity.class);
        } else if (id == R.id.favDriverArea) {
            new ActUtils(getActContext()).startActWithData(FavouriteDriverActivity.class, bn);
        } else if (id == R.id.paymentMethodArea) {//  bn.putBoolean("fromcabselection", false);
            // new ActUtils(getActContext()).startActForResult(CardPaymentActivity.class, bn, Utils.CARD_PAYMENT_REQ_CODE);
            String url = generalFunc.getJsonValue("PAYMENT_BASE_URL", userProfileJson) + "&PAGE_TYPE=PAYMENT_LIST" +
                    "&currency=" + generalFunc.getJsonValue("vCurrencyPassenger", userProfileJson);

            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
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
        } else if (id == R.id.promocodeArea) {
        } else if (id == R.id.changesPasswordArea) {
            showPasswordBox();
        } else if (id == R.id.changesCurrancyArea) {
            showCurrencyList();
        } else if (id == R.id.changeslanguageArea) {
            showLanguageList();
        } else if (id == R.id.termsArea) {
            bn.putString("staticpage", "4");
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
        } else if (id == R.id.headerwalletArea || id == R.id.myWalletArea) {
            new ActUtils(getActContext()).startActForResult(MyWalletActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
        } else if (id == R.id.topUpArea || id == R.id.addMoneyArea) {
            bn.putBoolean("isAddMoney", true);
            new ActUtils(getActContext()).startActForResult(MyWalletActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
        } else if (id == R.id.sendMoneyArea) {
            bn.putBoolean("isSendMoney", true);
            new ActUtils(getActContext()).startActForResult(MyWalletActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
        } else if (id == R.id.inviteArea || id == R.id.inviteFriendArea) {
            if (generalFunc.isDeliverOnlyEnabled()) {
                if (generalFunc.getMemberId().equals("")) {
                    MyUtils.openMobileStageActivity(getActContext());
                } else {
                    new ActUtils(getActContext()).startActWithData(InviteFriendsActivity.class, bn);
                }
            } else {
                new ActUtils(getActContext()).startActWithData(InviteFriendsActivity.class, bn);
            }
        } else if (id == R.id.helpArea) {
            new ActUtils(getActContext()).startAct(HelpActivity23Pro.class);
        } else if (id == R.id.liveChatArea) {
            MyUtils.openLiveChatActivity(getActContext(), generalFunc, obj_userProfile);
        } else if (id == R.id.aboutusArea) {
            bn.putString("staticpage", "1");
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
        } else if (id == R.id.contactUsArea) {
            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
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
        } else if (id == R.id.homeArea) {
            if (generalFunc.getMemberId().equals("")) {
                MyUtils.openMobileStageActivity(getActContext());
            } else {
                bn.putString("isHome", "true");
                new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class, bn,
                        Utils.ADD_HOME_LOC_REQ_CODE);
            }
        } else if (id == R.id.workArea) {
            if (generalFunc.getMemberId().equals("")) {
                MyUtils.openMobileStageActivity(getActContext());
            } else {
                bn.putString("isWork", "true");
                new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class, bn,
                        Utils.ADD_WORK_LOC_REQ_CODE);
            }
        } else if (id == R.id.signSignUpTxt) {
            MyUtils.openMobileStageActivity(getActContext());
        } else if (id == R.id.verifyEmailArea) {
            bn.putString("msg", "DO_EMAIL_VERIFY");
            new ActUtils(getActContext()).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_MOBILE_REQ_CODE);
        } else if (id == R.id.verifyMobArea) {
            bn.putString("msg", "DO_PHONE_VERIFY");
            new ActUtils(getActContext()).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_MOBILE_REQ_CODE);
        } else if (id == R.id.rentGeneralItemArea) {
            bn.putString("eType", "RentItem");
            new ActUtils(getActContext()).startActWithData(RentItemListPostActivity.class, bn);
        } else if (id == R.id.rentPropertiesItemArea) {
            bn.putString("eType", "RentEstate");
            new ActUtils(getActContext()).startActWithData(RentItemListPostActivity.class, bn);
        } else if (id == R.id.rentCarItemArea) {
            bn.putString("eType", "RentCars");
            new ActUtils(getActContext()).startActWithData(RentItemListPostActivity.class, bn);
        } else if (id == R.id.sendGiftArea) {
            new ActUtils(getActContext()).startAct(GiftCardSendActivity.class);
        } else if (id == R.id.redeemgiftArea) {
            new ActUtils(getActContext()).startAct(GiftCardRedeemActivity.class);
        } else if (id == R.id.preferencesArea) {
            new ActUtils(getActContext()).startAct(RideSharingProPreferences.class);
        } else if (id == R.id.manageDocArea) {
            new ActUtils(getActContext()).startAct(ListOfDocumentActivity.class);
        }
    }

    private void genderDailog() {

        final Dialog builder = new Dialog(getActContext(), R.style.Theme_Dialog);
        builder.requestWindowFeature(Window.FEATURE_NO_TITLE);
        builder.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        builder.setContentView(R.layout.gender_view);
        builder.getWindow().setLayout(WindowManager.LayoutParams.MATCH_PARENT, WindowManager.LayoutParams.MATCH_PARENT);

        final MTextView genderTitleTxt = (MTextView) builder.findViewById(R.id.genderTitleTxt);
        final MTextView maleTxt = (MTextView) builder.findViewById(R.id.maleTxt);
        final MTextView femaleTxt = (MTextView) builder.findViewById(R.id.femaleTxt);
        final ImageView gendercancel = (ImageView) builder.findViewById(R.id.gendercancel);
        //final ImageView gendermale = (ImageView) builder.findViewById(R.id.gendermale);
        //final ImageView genderfemale = (ImageView) builder.findViewById(R.id.genderfemale);
        final LinearLayout male_area = (LinearLayout) builder.findViewById(R.id.male_area);
        final LinearLayout female_area = (LinearLayout) builder.findViewById(R.id.female_area);


        if (generalFunc.isRTLmode()) {

            //            ((LinearLayout)builder.findViewById(R.id.llCancelButton)).setRotation(180);
                                /*RelativeLayout.LayoutParams params1 = new RelativeLayout.LayoutParams(RelativeLayout.LayoutParams.MATCH_PARENT, RelativeLayout.LayoutParams.WRAP_CONTENT);
            params1.addRule(RelativeLayout.ALIGN_PARENT_START);
            gendercancel.setLayoutParams(params1);*/

            LinearLayout.LayoutParams params1 = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            params1.gravity = Gravity.START;
            gendercancel.setLayoutParams(params1);
        }

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

    private void callgederApi(String egender) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateUserGender");
        parameters.put("UserType", Utils.userType);
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("eGender", egender);


        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);


            String message = generalFunc.getJsonValue(Utils.message_str, responseString);
            if (isDataAvail) {
                generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                obj_userProfile = generalFunc.getJsonObject(userProfileJson);
                Bundle bn = new Bundle();
                new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);

            }


        });

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

                    Realm realm = MyApp.getRealmInstance();
                    realm.beginTransaction();
                    realm.delete(Cart.class);
                    realm.delete(Topping.class);
                    realm.delete(Options.class);
                    realm.commitTransaction();

                    generalFunc.storeData(Utils.languageLabelsKey, generalFunc.getJsonValue(Utils.message_str, responseString));
                    generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", responseString));
                    generalFunc.storeData(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", responseString));
                    generalFunc.storeData(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", responseString));
                    GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());

                    Gson gson = new Gson();
                    String data1 = generalFunc.retrieveValue(Utils.BFSE_SELECTED_CONTACT_KEY);
                    ContactModel contactdetails = gson.fromJson(data1, new TypeToken<ContactModel>() {
                            }.getType()
                    );
                    if (contactdetails != null && contactdetails.shouldremove) {
                        generalFunc.removeValue(Utils.BFSE_SELECTED_CONTACT_KEY);
                    }


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

                                new Handler().postDelayed(() -> generalFunc.restartApp(), 100);
                            }
                        });

                        return;
                    }


                    if (ServiceModule.isDeliverAllOnly() && generalFunc.getMemberId().equalsIgnoreCase("")) {
                        generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
                        new GetUserData(generalFunc, MyApp.getInstance().getApplicationContext()).GetConfigDataForLocalStorageWithRestart();
                    } else {

                        new Handler().postDelayed(() -> {
                            generalFunc.restartApp();
                        }, 1000);
                    }


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
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        setuserInfo();


    }
}
