package com.general.files;

import static android.text.Spanned.SPAN_INCLUSIVE_INCLUSIVE;

import android.app.Activity;
import android.content.Context;
import android.graphics.Color;
import android.os.Bundle;
import android.text.SpannableString;
import android.text.TextUtils;
import android.text.style.AbsoluteSizeSpan;
import android.text.style.ForegroundColorSpan;
import android.view.Gravity;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ListView;
import android.widget.RelativeLayout;

import androidx.core.view.GravityCompat;
import androidx.drawerlayout.widget.DrawerLayout;

import com.act.BookingActivity;
import com.act.BusinessProfileActivity;
import com.act.ContactUsActivity;
import com.act.DonationActivity;
import com.act.EmergencyContactActivity;
import com.act.FavouriteDriverActivity;
import com.act.HelpActivity23Pro;
import com.act.InviteFriendsActivity;
import com.act.MainActivity;
import com.act.MenuSettingActivity;
import com.act.MyProfileActivity;
import com.act.MyWalletActivity;
import com.act.NotificationActivity;
import com.act.PaymentWebviewActivity;
import com.act.StaticPageActivity;
import com.act.SupportActivity;
import com.act.UberXActivity;
import com.act.VerifyInfoActivity;
import com.act.deliverAll.EditCartActivity;
import com.adapter.files.DrawerAdapter;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.utils.CommonUtilities;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;

import org.json.JSONObject;

import java.util.ArrayList;

public class AddDrawer implements AdapterView.OnItemClickListener {

    public JSONObject obj_userProfile;
    public MTextView walletbalncetxt;
    Context mContext;
    View view;
    DrawerLayout mDrawerLayout;
    ListView menuListView;
    DrawerAdapter drawerAdapter;
    ArrayList<String[]> list_menu_items;
    GeneralFunctions generalFunc;
    DrawerClickListener drawerClickListener;
    boolean isMenuState = true;
    boolean isDriverAssigned = false;
    LinearLayout logoutarea;
    ImageView logoutimage;
    MTextView logoutTxt;
    ImageView imgSetting;
    LinearLayout left_linear;
    MainActivity mainActivity;
    boolean isback = false;

    /*Deliver All*/
    RelativeLayout header_area;
    InternetConnection internetConnection;
    ImageView backimageView;

    private static final int WEBVIEWPAYMENT = 001, FAVDRIVER = -1;

    public AddDrawer(Context mContext, String userProfileJson, boolean isback) {
        this.mContext = mContext;
        this.generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        this.obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        this.isback = isback;
        internetConnection = new InternetConnection(mContext);
        view = ((Activity) mContext).findViewById(android.R.id.content);
        mDrawerLayout = (DrawerLayout) view.findViewById(R.id.drawer_layout);
        menuListView = (ListView) view.findViewById(R.id.menuListView);
        logoutarea = (LinearLayout) view.findViewById(R.id.logoutarea);
        logoutimage = (ImageView) view.findViewById(R.id.logoutimage);
        logoutTxt = (MTextView) view.findViewById(R.id.logoutTxt);
        imgSetting = (ImageView) view.findViewById(R.id.imgSetting);
        left_linear = (LinearLayout) view.findViewById(R.id.left_linear);
        imgSetting.setOnClickListener(new setOnClickLst());
        logoutarea.setOnClickListener(new setOnClickLst());
        logoutimage.setImageDrawable(mContext.getResources().getDrawable(R.drawable.ic_menu_logout));
        logoutTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SIGNOUT_TXT"));
        walletbalncetxt = (MTextView) view.findViewById(R.id.walletbalncetxt);
        backimageView = (ImageView) view.findViewById(R.id.backimageView);

        if (generalFunc.isRTLmode()) {
            backimageView.setRotationY(180);
        }


        android.view.Display display = ((android.view.WindowManager) mContext.getSystemService(Context.WINDOW_SERVICE)).getDefaultDisplay();
        left_linear.getLayoutParams().width = display.getWidth() * 75 / 100;
        left_linear.requestLayout();

        if (mContext instanceof MainActivity) {
            mainActivity = (MainActivity) mContext;
        }

        if (isback) {
            mDrawerLayout.setDrawerLockMode(DrawerLayout.LOCK_MODE_LOCKED_CLOSED);
        }


        buildDrawer();
        setUserInfo();

        if (mContext instanceof UberXActivity/* || mContext instanceof MainActivity*/) {
            (view.findViewById(R.id.menuImgView)).setOnClickListener(new setOnClickLst());
        }
    }

    public void setMenuImgClick(View view, boolean isDriverAssigned) {
//        isMenuState = true;
        if (isDriverAssigned) {
            (view.findViewById(R.id.backImgView)).setOnClickListener(new setOnClickLst());

        } else {
            (view.findViewById(R.id.menuImgView)).setOnClickListener(new setOnClickLst());

        }
    }


    /* Deliver All */

    public AddDrawer(Context mContext, String userProfileJson) {
        this.mContext = mContext;
        this.generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        this.obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        internetConnection = new InternetConnection(mContext);
        view = ((Activity) mContext).findViewById(android.R.id.content);
        mDrawerLayout = (DrawerLayout) view.findViewById(R.id.drawer_layout);
        menuListView = (ListView) view.findViewById(R.id.menuListView);
        logoutarea = (LinearLayout) view.findViewById(R.id.logoutarea);
        logoutimage = (ImageView) view.findViewById(R.id.logoutimage);
        logoutTxt = (MTextView) view.findViewById(R.id.logoutTxt);
        imgSetting = (ImageView) view.findViewById(R.id.imgSetting);
        left_linear = (LinearLayout) view.findViewById(R.id.left_linear);
        header_area = (RelativeLayout) view.findViewById(R.id.header_area);
        imgSetting.setOnClickListener(new setOnClickLst());
        logoutarea.setOnClickListener(new setOnClickLst());
        logoutimage.setImageDrawable(mContext.getResources().getDrawable(R.drawable.ic_menu_logout));
        logoutTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SIGNOUT_TXT"));
        walletbalncetxt = (MTextView) view.findViewById(R.id.walletbalncetxt);


        android.view.Display display = ((android.view.WindowManager) mContext.getSystemService(Context.WINDOW_SERVICE)).getDefaultDisplay();
        left_linear.getLayoutParams().width = display.getWidth() * 75 / 100;
        left_linear.requestLayout();

        buildDrawer();
        (view.findViewById(R.id.menuImgView)).setOnClickListener(new setOnClickLst());
        Managemenu();
    }

    public void Managemenu() {
        if (generalFunc.getMemberId().equalsIgnoreCase("")) {
            logoutarea.setVisibility(View.GONE);
            walletbalncetxt.setVisibility(View.GONE);
        } else {
            walletbalncetxt.setVisibility(View.VISIBLE);
            logoutarea.setVisibility(View.VISIBLE);
        }
        buildDrawer();
    }

    public void changeUserProfileJson(String userProfileJson) {
        this.obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        setUserInfo();
    }

    public void configDrawer(boolean isHide) {

        (view.findViewById(R.id.left_linear)).setVisibility(View.GONE);
        DrawerLayout.LayoutParams params = (DrawerLayout.LayoutParams) (view.findViewById(R.id.left_linear)).getLayoutParams();
        params.gravity = isHide ? Gravity.NO_GRAVITY : GravityCompat.START;
        (view.findViewById(R.id.left_linear)).setLayoutParams(params);
    }

    public void setMenuState(boolean isMenuState) {
        this.isMenuState = isMenuState;

        if (!this.isMenuState) {
            ((ImageView) view.findViewById(R.id.menuImgView)).setImageResource(R.mipmap.ic_back_arrow);

            configDrawer(true);

        } else {
            ((ImageView) view.findViewById(R.id.menuImgView)).setImageResource(R.mipmap.ic_drawer_menu);

            configDrawer(false);
        }
    }

    public void setIsDriverAssigned(boolean isDriverAssigned) {
        this.isDriverAssigned = isDriverAssigned;
    }

    public void buildDrawer() {
        list_menu_items = new ArrayList();
        drawerAdapter = new DrawerAdapter(list_menu_items, mContext);

        menuListView.setAdapter(drawerAdapter);
        menuListView.setOnItemClickListener(this);

        String ENABLE_FAVORITE_DRIVER_MODULE_KEY = generalFunc.retrieveValue(Utils.ENABLE_FAVORITE_DRIVER_MODULE_KEY);
        boolean isDeliverOnlyEnabled = ServiceModule.isDeliverAllOnly();
        boolean isAnyDeliverOptionEnabled = ServiceModule.DeliverAll;


        if (isDeliverOnlyEnabled) {
            list_menu_items.add(new String[]{"" + R.drawable.ic_menu_cart, generalFunc.retrieveLangLBl("", "LBL_CART"), "" + Utils.MENU_CART});

            if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
                list_menu_items.add(new String[]{"" + R.drawable.ic_menu_profile, generalFunc.retrieveLangLBl("", "LBL_MY_PROFILE_HEADER_TXT"), "" + Utils.MENU_PROFILE});
            } else {
                list_menu_items.add(new String[]{"" + R.drawable.ic_menu_profile, generalFunc.retrieveLangLBl("", "LBL_LOGIN_SIGNUP"), "" + Utils.MENU_PROFILE});
            }

        }

        if (isAnyDeliverOptionEnabled && !isDeliverOnlyEnabled) {

            if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
                list_menu_items.add(new String[]{"" + R.drawable.ic_menu_profile, generalFunc.retrieveLangLBl("", "LBL_MY_PROFILE_HEADER_TXT"), "" + Utils.MENU_PROFILE});
            } else {
                list_menu_items.add(new String[]{"" + R.drawable.ic_menu_profile, generalFunc.retrieveLangLBl("", "LBL_LOGIN_SIGNUP"), "" + Utils.MENU_PROFILE});
            }


            if (ENABLE_FAVORITE_DRIVER_MODULE_KEY.equalsIgnoreCase("Yes") && !isDeliverOnlyEnabled) {
                list_menu_items.add(new String[]{"" + R.drawable.ic_menu_fav_driver, generalFunc.retrieveLangLBl("Favorite Driver", "LBL_MENU_FAV_DRIVER"), "" + FAVDRIVER});
            }

            list_menu_items.add(new String[]{"" + R.drawable.ic_menu_cart, generalFunc.retrieveLangLBl("", "LBL_CART"), "" + Utils.MENU_CART});

        }

        if (!isAnyDeliverOptionEnabled) {
            list_menu_items.add(new String[]{"" + R.drawable.ic_menu_profile, generalFunc.retrieveLangLBl("", "LBL_MY_PROFILE_HEADER_TXT"), "" + Utils.MENU_PROFILE});

            if (ENABLE_FAVORITE_DRIVER_MODULE_KEY.equalsIgnoreCase("Yes") && !isDeliverOnlyEnabled) {
                list_menu_items.add(new String[]{"" + R.drawable.ic_menu_fav_driver, generalFunc.retrieveLangLBl("Favorite Driver", "LBL_MENU_FAV_DRIVER"), "" + FAVDRIVER});
            }
        }


        if (!isDeliverOnlyEnabled) {

            if (generalFunc.getJsonValueStr("ENABLE_CORPORATE_PROFILE", obj_userProfile).equalsIgnoreCase("Yes")) {
                list_menu_items.add(new String[]{"" + R.drawable.ic_menu_business_profile, generalFunc.retrieveLangLBl("", "LBL_BUSINESS_PROFILE"), "" + Utils.MENU_BUSINESS_PROFILE});
            }

            String menuMsgYourTrips = "";
            if (ServiceModule.isRideOnly()) {
                menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_YOUR_TRIPS");
            } else if (ServiceModule.isDeliveronly()) {
                menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_YOUR_DELIVERY");
            } else if (ServiceModule.isServiceProviderOnly()) {
                menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_YOUR_BOOKING");
            } else {
                menuMsgYourTrips = generalFunc.retrieveLangLBl("", "LBL_YOUR_BOOKING");
            }

            list_menu_items.add(new String[]{"" + R.drawable.ic_menu_yourtrip, menuMsgYourTrips, "" + Utils.MENU_YOUR_TRIPS});


            if (!generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).equalsIgnoreCase("Cash")) {
                list_menu_items.add(new String[]{"" + R.drawable.ic_menu_card, generalFunc.retrieveLangLBl("Payment", "LBL_PAYMENT"), "" + Utils.MENU_PAYMENT});
            }
        }


        if (!generalFunc.getJsonValueStr(Utils.WALLET_ENABLE, obj_userProfile).equals("") &&
                generalFunc.getJsonValueStr(Utils.WALLET_ENABLE, obj_userProfile).equalsIgnoreCase("Yes")) {
            list_menu_items.add(new String[]{"" + R.drawable.ic_menu_wallet, generalFunc.retrieveLangLBl("", "LBL_LEFT_MENU_WALLET"), "" + Utils.MENU_WALLET});
        }

        if (!isDeliverOnlyEnabled) {
            list_menu_items.add(new String[]{"" + R.drawable.ic_menu_emergency, generalFunc.retrieveLangLBl("Emergency Contact", "LBL_EMERGENCY_CONTACT"), "" + Utils.MENU_EMERGENCY_CONTACT});
        }

        if (!generalFunc.getJsonValueStr(Utils.REFERRAL_SCHEME_ENABLE, obj_userProfile).equals("") && generalFunc.getJsonValueStr(Utils.REFERRAL_SCHEME_ENABLE, obj_userProfile).equalsIgnoreCase("Yes")) {
            list_menu_items.add(new String[]{"" + R.drawable.ic_menu_invite, generalFunc.retrieveLangLBl("Invite Friends", "LBL_INVITE_FRIEND_TXT"), "" + Utils.MENU_INVITE_FRIEND});
        }


        if (generalFunc.getJsonValueStr("ENABLE_NEWS_SECTION", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_NEWS_SECTION", obj_userProfile).equalsIgnoreCase("yes")) {
            list_menu_items.add(new String[]{"" + R.drawable.ic_notification_menu, generalFunc.retrieveLangLBl("Notifications", "LBL_NOTIFICATIONS"), "" + Utils.MENU_NOTIFICATION});
        }

        if (generalFunc.getJsonValueStr(Utils.DONATION_ENABLE, obj_userProfile).equalsIgnoreCase("Yes") /*&& APP_TYPE.equals(Utils.CabGeneralType_Ride)*/) {
            list_menu_items.add(new String[]{"" + R.drawable.ic_menu_donation, generalFunc.retrieveLangLBl("Donation", "LBL_DONATE"), "" + Utils.MENU_DONATION});
        }

        list_menu_items.add(new String[]{"" + R.drawable.ic_menu_support, generalFunc.retrieveLangLBl("Support", "LBL_SUPPORT_HEADER_TXT"), "" + Utils.MENU_SUPPORT});

        drawerAdapter.notifyDataSetChanged();
    }

    public void setUserInfo() {
        ((MTextView) view.findViewById(R.id.userNameTxt)).setText(generalFunc.getJsonValueStr("vName", obj_userProfile) + " "
                + generalFunc.getJsonValueStr("vLastName", obj_userProfile));
        //((MTextView) view.findViewById(R.id.walletbalncetxt)).setText(generalFunc.retrieveLangLBl("", "LBL_WALLET_BALANCE") + ": " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("user_available_balance", obj_userProfile)));
        setWalletInfo();

        String url = CommonUtilities.USER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValueStr("vImgName", obj_userProfile);
        generalFunc.checkProfileImage((SelectableRoundedImageView) view.findViewById(R.id.userImgView), url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);
    }

    public void setWalletInfo() {

        String text1 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("user_available_balance", obj_userProfile));
        String text2 = "\n" + generalFunc.retrieveLangLBl("wallet Balance", "LBL_WALLET_BALANCE");
        SpannableString span1 = new SpannableString(text1);
        span1.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(18, mContext)), 0, text1.length(), SPAN_INCLUSIVE_INCLUSIVE);

        SpannableString span2 = new SpannableString(text2);
        span2.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, mContext)), 0, text2.length(), SPAN_INCLUSIVE_INCLUSIVE);
        span2.setSpan(new ForegroundColorSpan(Color.parseColor("#808080")), 0, text2.length(), 0);
        CharSequence finalText = TextUtils.concat(span1, "", span2);

        walletbalncetxt.setText(finalText);
    }

    public void openMenuProfile() {
        Bundle bn = new Bundle();
        bn.putString("isDriverAssigned", "" + isDriverAssigned);
        new ActUtils(mContext).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
        // new ActUtils(mContext).startActForResult(MyProfileNewActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);

    }

    @Override
    public void onItemClick(AdapterView<?> parent, final View view, int position, long id) {
        int itemId = GeneralFunctions.parseIntegerValue(0, list_menu_items.get(position)[2]);
        Bundle bn = new Bundle();
        switch (itemId) {
            case Utils.MENU_CART:
                new ActUtils(mContext).startAct(EditCartActivity.class);

                break;

            case Utils.MENU_PROFILE:
                if (ServiceModule.isDeliverAllOnly()) {
                    if (generalFunc.getMemberId().equals("")) {
                        MyUtils.openMobileStageActivity(mContext);
                    } else {
                        //  menuListView.performItemClick(view, 2, Utils.MENU_PROFILE);
                        bn.putString("isProfile", "Yes");
                        //  menuListView.performItemClick(view, 2, Utils.MENU_PROFILE);
                        new ActUtils(mContext).startActWithData(MenuSettingActivity.class, bn);
                        closeDrawer();
                    }

                } else {
                    // menuListView.performItemClick(view, 0, Utils.MENU_PROFILE);
                    bn.putString("isProfile", "Yes");
                    //  menuListView.performItemClick(view, 2, Utils.MENU_PROFILE);
                    new ActUtils(mContext).startActWithData(MenuSettingActivity.class, bn);
                    closeDrawer();
                }
                break;
            case Utils.MENU_BUSINESS_PROFILE:
                new ActUtils(mContext).startActWithData(BusinessProfileActivity.class, bn);
                break;
            case Utils.MENU_VEHICLE:

                break;
            case Utils.MENU_RIDE_HISTORY:
                new ActUtils(mContext).startActWithData(BookingActivity.class, bn);
                break;
            case Utils.MENU_BOOKINGS:
                new ActUtils(mContext).startActWithData(BookingActivity.class, bn);
                break;
            case Utils.MENU_ONGOING_TRIPS:
                if (mainActivity != null && mainActivity.driverAssignedHeaderFrag != null) {
                    bn.putString("isTripRunning", "yes");
                }
                new ActUtils(mContext).startActForResult(BookingActivity.class, bn, Utils.ASSIGN_DRIVER_CODE);
                break;
            case Utils.MENU_ABOUT_US:
                new ActUtils(mContext).startAct(StaticPageActivity.class);
                break;
            case Utils.MENU_POLICY:
                (new ActUtils(mContext)).openURL(CommonUtilities.SERVER_URL + "privacy-policy");
                break;
            case Utils.MENU_PAYMENT:

//                bn.putBoolean("fromcabselection", false);
//                new ActUtils(mContext).startActForResult(CardPaymentActivity.class, bn, Utils.CARD_PAYMENT_REQ_CODE);
                String url = generalFunc.getJsonValue("PAYMENT_BASE_URL", obj_userProfile) + "&PAGE_TYPE=PAYMENT_LIST" +
                        "&currency=" + generalFunc.getJsonValue("vCurrencyPassenger", obj_userProfile);

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
                new ActUtils(mContext).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
                break;
            case Utils.MENU_CONTACT_US:
                new ActUtils(mContext).startAct(ContactUsActivity.class);
                break;
            case Utils.MENU_HELP:
                new ActUtils(mContext).startAct(HelpActivity23Pro.class);
                break;
            case Utils.MENU_EMERGENCY_CONTACT:
                new ActUtils(mContext).startAct(EmergencyContactActivity.class);
                break;
            case Utils.MENU_NOTIFICATION:
                new ActUtils(mContext).startAct(NotificationActivity.class);
                break;

            case Utils.MENU_DONATION:
                new ActUtils(mContext).startActWithData(DonationActivity.class, bn);
                break;

            case Utils.MENU_SUPPORT:
                new ActUtils(mContext).startAct(SupportActivity.class);
                break;

            case Utils.MENU_WALLET:
                new ActUtils(mContext).startActWithData(MyWalletActivity.class, bn);
                break;

            case FAVDRIVER:
                new ActUtils(mContext).startActWithData(FavouriteDriverActivity.class, bn);
                break;


            case Utils.MENU_ACCOUNT_VERIFY:
                if (!generalFunc.getJsonValueStr("eEmailVerified", obj_userProfile).equalsIgnoreCase("YES") ||
                        !generalFunc.getJsonValueStr("ePhoneVerified", obj_userProfile).equalsIgnoreCase("YES")) {

                    Bundle bn1 = new Bundle();
                    if (!generalFunc.getJsonValueStr("eEmailVerified", obj_userProfile).equalsIgnoreCase("YES") &&
                            !generalFunc.getJsonValueStr("ePhoneVerified", obj_userProfile).equalsIgnoreCase("YES")) {
                        bn1.putString("msg", "DO_EMAIL_PHONE_VERIFY");
                    } else if (!generalFunc.getJsonValueStr("eEmailVerified", obj_userProfile).equalsIgnoreCase("YES")) {
                        bn1.putString("msg", "DO_EMAIL_VERIFY");
                    } else if (!generalFunc.getJsonValueStr("ePhoneVerified", obj_userProfile).equalsIgnoreCase("YES")) {
                        bn1.putString("msg", "DO_PHONE_VERIFY");
                    }

                    new ActUtils(mContext).startActForResult(VerifyInfoActivity.class, bn1, Utils.VERIFY_INFO_REQ_CODE);
                }
                break;
            case Utils.MENU_YOUR_TRIPS:
                bn.putBoolean("isrestart", false);
                new ActUtils(mContext).startActWithData(BookingActivity.class, bn);
                break;
            case Utils.MENU_INVITE_FRIEND:
                if (ServiceModule.isDeliverAllOnly()) {
                    if (generalFunc.getMemberId().equals("")) {
                        MyUtils.openMobileStageActivity(mContext);
                    } else {
                        new ActUtils(mContext).startActWithData(InviteFriendsActivity.class, bn);
                    }
                } else {
                    new ActUtils(mContext).startActWithData(InviteFriendsActivity.class, bn);
                }


                break;
            case Utils.MENU_SIGN_OUT:

                final GenerateAlertBox generateAlert = new GenerateAlertBox(mContext);
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 0) {
                        generateAlert.closeAlertBox();
                    } else {
                        MyApp.getInstance().logOutFromDevice(false);
                    }

                });

                generateAlert.setContentMessage(generalFunc.retrieveLangLBl("Logout", "LBL_LOGOUT"), generalFunc.retrieveLangLBl("Are you sure you want to logout?", "LBL_WANT_LOGOUT_APP_TXT"));
                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
                generateAlert.showAlertBox();
                break;
        }
        closeDrawer();
    }

    public void closeDrawer() {
        if (isback) {
            return;
        }
        (view.findViewById(R.id.left_linear)).setVisibility(View.GONE);
        mDrawerLayout.closeDrawer(GravityCompat.START);
    }

    public boolean checkDrawerState(boolean isOpenDrawer) {
        if (mDrawerLayout.isDrawerOpen(GravityCompat.START)) {
            closeDrawer();
            return true;
        } else if (isOpenDrawer) {
            openDrawer();
        }
        return false;
    }

    public void openDrawer() {

        (view.findViewById(R.id.left_linear)).setVisibility(View.VISIBLE);
        mDrawerLayout.openDrawer(GravityCompat.START);
    }

    private void setMenuAction() {
        if (isMenuState) {
            openDrawer();
        } else {
            setMenuState(true);
            if (drawerClickListener != null) {
                drawerClickListener.onClick();
            }
        }
    }

    public void setItemClickList(DrawerClickListener itemClickList) {
        this.drawerClickListener = itemClickList;
    }

    public interface DrawerClickListener {
        void onClick();
    }

    public class setOnClickLst implements View.OnClickListener {

        @Override
        public void onClick(View v) {

            int id = v.getId();
            if (id == R.id.menuImgView) {
                setMenuAction();
            } else if (id == R.id.backImgView) {
                setMenuAction();
            } else if (id == R.id.imgSetting) {
                if (ServiceModule.isDeliverAllOnly()) {
                    if (generalFunc.getMemberId().equals("")) {
                        MyUtils.openMobileStageActivity(mContext);
                    } else {
                        //  menuListView.performItemClick(view, 2, Utils.MENU_PROFILE);
                        new ActUtils(mContext).startAct(MenuSettingActivity.class);
                        closeDrawer();
                    }

                } else {
                    // menuListView.performItemClick(view, 0, Utils.MENU_PROFILE);
                    new ActUtils(mContext).startAct(MenuSettingActivity.class);
                    closeDrawer();
                }
            } else if (id == R.id.logoutarea) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(mContext);
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 0) {
                        generateAlert.closeAlertBox();
                    } else {
                        if (internetConnection.isNetworkConnected()) {
                            MyApp.getInstance().logOutFromDevice(false);
                        } else {
                            generalFunc.showMessage(logoutarea, generalFunc.retrieveLangLBl("", "LBL_NO_INTERNET_TXT"));
                        }
                    }

                });
                generateAlert.setContentMessage(generalFunc.retrieveLangLBl("Logout", "LBL_LOGOUT"), generalFunc.retrieveLangLBl("Are you sure you want to logout?", "LBL_WANT_LOGOUT_APP_TXT"));
                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
                generateAlert.showAlertBox();
            }
        }
    }
}
