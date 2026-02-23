package com.act;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.Drawable;
import android.graphics.drawable.GradientDrawable;
import android.graphics.drawable.LayerDrawable;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.widget.LinearLayout;

import androidx.activity.result.ActivityResult;
import androidx.activity.result.ActivityResultCallback;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.core.content.ContextCompat;
import androidx.viewpager.widget.ViewPager;

import com.ViewPagerCards.CardImagePagerAdapter;
import com.ViewPagerCards.ShadowTransformer;
import com.activity.ParentActivity;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.general.files.SmartLogin;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.anim.loader.AVLoadingIndicatorView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

import io.github.vejei.viewpagerindicator.indicator.RectIndicator;

public class AppLoginActivity extends ParentActivity implements ViewPager.OnPageChangeListener {

    MTextView introductondetailstext, languageText, currancyText, loginbtn, registerbtn, btnSmartLoginClickView;
    private LinearLayout languagearea, currencyarea;

    LinearLayout llSmartLoginView, llLoaderView;

    ArrayList<HashMap<String, String>> languageDataList = new ArrayList<>();
    ArrayList<HashMap<String, String>> currencyDataList = new ArrayList<>();
    String selected_language_code = "", selected_currency = "", selected_currency_symbol = "";

    AVLoadingIndicatorView loaderView;

    LinearLayout btnArea;
    ViewPager bannerViewPager;
    private CardImagePagerAdapter mCardAdapter;
    private ShadowTransformer mCardShadowTransformer;
    RectIndicator dotsIndicator;

    ArrayList<HashMap<String, String>> imagesList = new ArrayList<>();
    View bannerBackView, bottomView, nextBtn;

    private SmartLogin mSmartLogin;
    int selCurrancyPosition = -1, selLanguagePosition = -1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_app_login);

        MyUtils.setAppName(this, ((MTextView) findViewById(R.id.appNameTxt)));

        initView();
        setLabel();
        buildLanguageList();

        manageLaunchImages();
    }

    private void initView() {

        bannerViewPager = findViewById(R.id.bannerViewPager);
        bannerBackView = findViewById(R.id.bannerBackView);
        bottomView = findViewById(R.id.bottomView);
        nextBtn = findViewById(R.id.nextBtn);
        addToClickHandler(nextBtn);

//        manageVectorImage(findViewById(R.id.gradientArea), R.drawable.ic_gradient, R.drawable.ic_gradient_compat);

        dotsIndicator = findViewById(R.id.dots_indicator);
        bannerViewPager.addOnPageChangeListener(this);

        btnArea = findViewById(R.id.btnArea);
        introductondetailstext = findViewById(R.id.introductondetailstext);
        languageText = findViewById(R.id.languageText);
        currancyText = findViewById(R.id.currancyText);

        languagearea = findViewById(R.id.languagearea);
        currencyarea = findViewById(R.id.currencyarea);
        loginbtn = findViewById(R.id.loginbtn);
        registerbtn = findViewById(R.id.registerbtn);

        mCardAdapter = new CardImagePagerAdapter(getActContext());
        mCardShadowTransformer = new ShadowTransformer(bannerViewPager, mCardAdapter);

        loaderView = findViewById(R.id.loaderView);
        loaderView.setVisibility(View.GONE);

        mSmartLogin = new SmartLogin(getActContext(), generalFunc, new SmartLogin.OnEventListener() {
            @Override
            public void onPasswordViewEvent(Intent i) {
                someActivityResultLauncher.launch(i);
            }

            @Override
            public void onResumeEvent() {

            }

            @Override
            public void onAuthenticationSucceeded() {
                if (mSmartLogin.ResultOk()) {
                    signInUser();
                }
            }
        });
        llLoaderView = findViewById(R.id.llLoaderView);
        llLoaderView.setVisibility(View.GONE);
        llSmartLoginView = findViewById(R.id.llSmartLoginView);
        llSmartLoginView.setOnClickListener(v -> mSmartLogin.clickEvent());
        btnSmartLoginClickView = findViewById(R.id.btnSmartLoginClickView);
        btnSmartLoginClickView.setText(generalFunc.retrieveLangLBl("", "LBL_SMART_LOGIN_APPLOGIN"));

        addToClickHandler(loginbtn);
        addToClickHandler(registerbtn);
        addToClickHandler(languagearea);
        addToClickHandler(currencyarea);
    }

    private void setBannerBgDrawable(String color) {
        Drawable topBannerDrawable = ContextCompat.getDrawable(getActContext(), R.drawable.banner_top_view_curve);
        LayerDrawable bubble = (LayerDrawable) topBannerDrawable;
        if (bubble != null) {
            GradientDrawable solidColor = (GradientDrawable) bubble.findDrawableByLayerId(R.id.bannerBgView);
            solidColor.setColor(Color.parseColor(color));
        }
    }

    private void setBannerBGColor(String color) {
        try {
            Window window = getWindow();
            window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS);
            window.setStatusBarColor(Color.parseColor(color));
            setBannerBgDrawable(color);
        } catch (Exception e) {
            Logger.d("setBannerBGColor", "::" + e.getMessage());
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        mSmartLogin.isSmartLoginEnable(llSmartLoginView, null);
    }

    ActivityResultLauncher<Intent> someActivityResultLauncher = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), new ActivityResultCallback<ActivityResult>() {
                @Override
                public void onActivityResult(ActivityResult result) {
                    if (result.getResultCode() == RESULT_OK) {
                        if (mSmartLogin.ResultOk()) {
                            AppLoginActivity.this.signInUser();
                        }
                    }
                }
            });

    private void signInUser() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "signIn");
        parameters.put("vEmail", "");
        parameters.put("vPassword", "");
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.app_type);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        if (generalFunc.retrieveValue("ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD").equalsIgnoreCase("Yes")) {
            parameters.put("CountryCode", generalFunc.retrieveValue(Utils.DefaultCountryCode));
            parameters.put("PhoneCode", generalFunc.retrieveValue(Utils.DefaultPhoneCode));
        }
        llLoaderView.setVisibility(View.VISIBLE);
        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {

            llLoaderView.setVisibility(View.GONE);
            btnArea.setEnabled(true);
            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    new ConfigureMemberData(responseString, generalFunc, getActContext(), true);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                    new OpenMainProfile(getActContext(), generalFunc.getJsonValue(Utils.message_str, responseString), false, generalFunc).startProcess();
                } else {
                    if (generalFunc.getJsonValue("eStatus", responseString).equalsIgnoreCase("Deleted")) {
                        openContactUsDialog(responseString);
                    } else if (generalFunc.getJsonValue("eStatus", responseString).equalsIgnoreCase("Inactive")) {
                        openContactUsDialog(responseString);
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void openContactUsDialog(String responseString) {
        GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
        alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
        alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        alertBox.setBtnClickList(btn_id -> {
            alertBox.closeAlertBox();
            if (btn_id == 0) {
                new ActUtils(getActContext()).startAct(ContactUsActivity.class);
            }
        });
        alertBox.showAlertBox();
    }

    private void setLabel() {
        introductondetailstext.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_PASSENGER_INTRO_DETAILS"));
        loginbtn.setText(generalFunc.retrieveLangLBl("", "LBL_LOGIN"));
        registerbtn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_REGISTER_TXT"));

        if (!Utils.checkText(introductondetailstext.getText().toString())) {
            introductondetailstext.setVisibility(View.GONE);
        }
        generalFunc.storeData("IS_PROFILE_PHOTO_UPLOADED", "No");

        languageText.setText(generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        currancyText.setText(generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        if (generalFunc.isRTLmode()) {
            nextBtn.setRotation(180);
        }
    }

    private void buildLanguageList() {

        JSONArray languageList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.LANGUAGE_LIST_KEY));
        languageDataList.clear();
        if (languageList_arr != null) {
            for (int i = 0; i < languageList_arr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(languageList_arr, i);

                HashMap<String, String> mapData = new HashMap<>();
                mapData.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
                mapData.put("vCode", generalFunc.getJsonValueStr("vCode", obj_temp));
                mapData.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));
                mapData.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));

                if (Utils.getText(languageText).equalsIgnoreCase(generalFunc.getJsonValueStr("vCode", obj_temp))) {
                    selLanguagePosition = i;
                }

                languageDataList.add(mapData);
                if ((generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY)).equals(generalFunc.getJsonValue("vCode", obj_temp))) {
                    selected_language_code = generalFunc.getJsonValueStr("vCode", obj_temp);
                }
            }
        }
        if (languageDataList.size() < 2 || generalFunc.retrieveValue("LANGUAGE_OPTIONAL").equalsIgnoreCase("Yes")) {
            languagearea.setVisibility(View.GONE);
        }
        buildCurrencyList();
    }

    private void buildCurrencyList() {

        JSONArray currencyList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.CURRENCY_LIST_KEY));
        currencyDataList.clear();
        if (currencyList_arr != null) {
            for (int i = 0; i < currencyList_arr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(currencyList_arr, i);

                HashMap<String, String> mapData = new HashMap<>();
                mapData.put("vName", generalFunc.getJsonValueStr("vName", obj_temp));
                mapData.put("vCode", generalFunc.getJsonValueStr("vSymbol", obj_temp));
                mapData.put("vSymbol", generalFunc.getJsonValueStr("vSymbol", obj_temp));
                mapData.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));
                mapData.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));

                if (Utils.getText(currancyText).equalsIgnoreCase(generalFunc.getJsonValueStr("vName", obj_temp))) {
                    selCurrancyPosition = i;
                }
                currencyDataList.add(mapData);
            }
        }
        if (currencyDataList.size() < 2 || generalFunc.retrieveValue("CURRENCY_OPTIONAL").equalsIgnoreCase("Yes")) {
            currencyarea.setVisibility(View.GONE);
        }
    }

    private Context getActContext() {
        return AppLoginActivity.this;
    }

    private void showLanguageList() {

        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("Select", "LBL_SELECT_LANGUAGE_HINT_TXT"), languageDataList, OpenListView.OpenDirection.CENTER, true, position -> {

            currencyarea.setClickable(true);
            selLanguagePosition = position;
            HashMap<String, String> mapData = languageDataList.get(position);

            selected_language_code = mapData.get("vCode");

            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
                generalFunc.showGeneralMessage("",
                        generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
            } else {
                if (!generalFunc.retrieveValue(Utils.DEFAULT_LANGUAGE_VALUE).equals(mapData.get("vTitle"))) {
                    languageText.setText(mapData.get("vCode"));
                    generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
                    generalFunc.storeData(Utils.DEFAULT_LANGUAGE_VALUE, mapData.get("vTitle"));

                    changeLanguagedata(selected_language_code);
                }
            }
        }, true, generalFunc.retrieveLangLBl("", "LBL_LANG_PREFER"), true).show(selLanguagePosition, "vTitle");
    }

    private void showCurrencyList() {
        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_CURRENCY"), currencyDataList, OpenListView.OpenDirection.CENTER, true, position -> {
            MyUtils.configCurrencyChanged("Yes");

            currencyarea.setClickable(true);
            selCurrancyPosition = position;
            HashMap<String, String> mapData = currencyDataList.get(position);

            selected_currency_symbol = mapData.get("vSymbol");

            selected_currency = mapData.get("vName");
            currancyText.setText(mapData.get("vName"));

            generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
        }, true, generalFunc.retrieveLangLBl("", "LBL_CURRENCY_PREFER"), false).show(selCurrancyPosition, "vName");
    }

    private void manageLaunchImages() {
        JSONArray imagesArray = null;
        try {
            imagesArray = new JSONArray(generalFunc.retrieveValue("APP_LAUNCH_IMAGES"));
        } catch (JSONException e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

        if (imagesArray != null && imagesArray.length() > 0) {
            if (generalFunc.isRTLmode()) {
                for (int x = imagesArray.length() - 1; x >= 0; x--) {
                    setImagesData(generalFunc.getJsonObject(imagesArray, x));
                }
            } else {
                for (int i = 0; i < imagesArray.length(); i++) {
                    setImagesData(generalFunc.getJsonObject(imagesArray, i));
                }
            }
        }

        bannerViewPager.setAdapter(mCardAdapter);
        bannerViewPager.setPageTransformer(false, mCardShadowTransformer);
        bannerViewPager.setOffscreenPageLimit(3);
        if (imagesList.size() > 1) {
            //dotsIndicator.setViewPager(bannerViewPager);
            dotsIndicator.setVisibility(View.VISIBLE);
            try {
                dotsIndicator.setWithViewPager(bannerViewPager);
            } catch (Exception e) {
                Logger.d("BannerViewPager", "::" + e.getMessage());
            }
            dotsIndicator.setItemCount(imagesList.size());
            dotsIndicator.setAnimationMode(RectIndicator.AnimationMode.SLIDE);
            setBannerBGColor(imagesList.get(0).get("vBGColor"));
        } else {
            dotsIndicator.setVisibility(View.GONE);
        }

        if (generalFunc.isRTLmode()) {
            if (imagesList != null) {
                bannerViewPager.setCurrentItem(imagesList.size() - 1);
            }
        }
    }

    private void setImagesData(JSONObject imageObj) {
        HashMap<String, String> imagMap = new HashMap<>();
        imagMap.put("vImage", generalFunc.getJsonValueStr("vImage", imageObj));
        imagMap.put("tTitle", generalFunc.getJsonValueStr("tTitle", imageObj));
        imagMap.put("tSubtitle", generalFunc.getJsonValueStr("tSubtitle", imageObj));
        imagMap.put("vBGColor", generalFunc.getJsonValueStr("vBGColor", imageObj));
        imagesList.add(imagMap);
        mCardAdapter.addCardItem(imagMap, getActContext());
    }

    private void changeLanguagedata(String langcode) {
        MyUtils.configLangChanged("Yes");
        loaderView.setVisibility(View.VISIBLE);
        btnArea.setVisibility(View.GONE);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "changelanguagelabel");
        parameters.put("vLang", langcode);
        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.storeData(Utils.languageLabelsKey, generalFunc.getJsonValue(Utils.message_str, responseString));
                    generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", responseString));
                    generalFunc.storeData(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", responseString));
                    generalFunc.storeData("APP_LAUNCH_IMAGES", generalFunc.getJsonValue("APP_LAUNCH_IMAGES", responseString));
                    GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());

                    imagesList.clear();
                    MyApp.getInstance().refreshWithConfigData();
                } else {
                    loaderView.setVisibility(View.GONE);
                }
            } else {
                loaderView.setVisibility(View.GONE);
            }
        });

    }

    @Override
    public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

    }

    @Override
    public void onPageSelected(int position) {
        setBannerBGColor(imagesList.get(position).get("vBGColor"));
    }

    @Override
    public void onPageScrollStateChanged(int state) {

    }

    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == R.id.languagearea) {
            if (loaderView.getVisibility() == View.GONE) {
                languagearea.setEnabled(false);
                showLanguageList();
                manageBtn();
            }
        } else if (i == R.id.currencyarea) {
            if (loaderView.getVisibility() == View.GONE) {
                currencyarea.setEnabled(false);
                showCurrencyList();
                manageBtn();
            }
        } else if (i == R.id.loginbtn) {
            if (loaderView.getVisibility() == View.GONE) {
                Bundle bundle = new Bundle();
                bundle.putString("type", "login");
                new ActUtils(getActContext()).startActWithData(AppLoignRegisterActivity.class, bundle);
            }
        } else if (i == R.id.registerbtn) {
            if (loaderView.getVisibility() == View.GONE) {
                Bundle bundle = new Bundle();
                bundle.putString("type", "register");
                new ActUtils(getActContext()).startActWithData(AppLoignRegisterActivity.class, bundle);
            }
        } else if (i == R.id.nextBtn) {
            Bundle bundle = new Bundle();
            bundle.putString("type", "login");
            new ActUtils(getActContext()).startActWithData(MobileStegeActivity.class, bundle);
        }
    }

    private void manageBtn() {
        new Handler(Looper.myLooper()).postDelayed(() -> {
            languagearea.setEnabled(true);
            currencyarea.setEnabled(true);
        }, 500);
    }
}