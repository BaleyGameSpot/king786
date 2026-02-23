package com.act.rentItem;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.countryview.view.CountryPicker;
import com.general.call.CommunicationManager;
import com.general.call.DefaultCommunicationHandler;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.SetOnTouchList;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRentItemInquiryBinding;
import com.service.handler.ApiHandler;
import com.utils.LoadImage;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import java.util.HashMap;
import java.util.Objects;

public class RentItemInquiry extends ParentActivity {

    private ActivityRentItemInquiryBinding binding;
    private HashMap<String, String> mRentEditHashMap;

    private String vPhoneCode = "", vSImage = "", required_str = "", error_email_str = "";
    private CountryPicker countryPicker;
    private boolean isDone = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_rent_item_inquiry);

        mRentEditHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("rentEditHashMap");
        if (mRentEditHashMap == null) {
            return;
        }

        initViews();
        dataSet();
    }

    @SuppressLint("ClickableViewAccessibility")
    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_SEND_INQUIRY_TXT"));

        binding.nameHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NAME_TXT"));
        binding.userNameBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_YOUR_NAME_TXT"));

        binding.emailHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_TEXT"));
        binding.userEmailBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_YOUR_EMAIL_TXT"));

        binding.userPhoneHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USER_PHONE_TXT"));
        binding.mobileBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_MOBILE_HINT"));
        binding.mobileBox.getLabelFocusAnimator().start();

        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR_TXT");

        binding.btnSubmit.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_SUBMIT_TXT"));
        addToClickHandler(binding.btnSubmit);

        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            binding.countryDropImg.setVisibility(View.VISIBLE);
            binding.countryCodeTxt.setOnTouchListener(new SetOnTouchList());
            addToClickHandler(binding.countryArea);
        } else {
            binding.countryDropImg.setVisibility(View.GONE);
        }

    }

    @SuppressLint("SetTextI18n")
    private void dataSet() {
        binding.userNameBox.setText(generalFunc.getJsonValueStr("vName", obj_userProfile) + " " + generalFunc.getJsonValueStr("vLastName", obj_userProfile));
        binding.userEmailBox.setText(generalFunc.getJsonValueStr("vEmail", obj_userProfile));
        binding.mobileBox.setText(generalFunc.getJsonValueStr("vPhone", obj_userProfile));

        vPhoneCode = generalFunc.getJsonValueStr("vPhoneCode", obj_userProfile);
        binding.countryCodeTxt.setText(generalFunc.convertNumberWithRTL("+" + vPhoneCode));

        int imagewidth = (int) getResources().getDimension(R.dimen._30sdp);
        int imageheight = (int) getResources().getDimension(R.dimen._20sdp);
        vSImage = generalFunc.getJsonValueStr("vSCountryImage", obj_userProfile);
        new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(this, vSImage, imagewidth, imageheight)), binding.countryImg).build();
    }


    private void sendInquiry() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "sendContactQueryForRent");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iRentItemOwnerId", mRentEditHashMap.get("iUserId"));
        parameters.put("iRentItemPostId", mRentEditHashMap.get("iRentItemPostId"));

        parameters.put("iName", Objects.requireNonNull(binding.userNameBox.getText()).toString().trim());
        parameters.put("iEmail", Objects.requireNonNull(binding.userEmailBox.getText()).toString().trim());

        parameters.put("iMobileNo", Objects.requireNonNull(binding.mobileBox.getText()).toString().trim());
        parameters.put("iPhoneCode", vPhoneCode);

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    isDone = true;
                    showBottomDialog(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void showBottomDialog(String msg) {
        CustomDialog customDialog = new CustomDialog(this, generalFunc);
        customDialog.setDetails(generalFunc.retrieveLangLBl("", "LBL_RENT_INQUIRY_SENT_TXT"),
                generalFunc.retrieveLangLBl("", msg),
                generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"),
                generalFunc.retrieveLangLBl("", "LBL_CALL_TXT"),
                false, R.drawable.ic_correct_2, false, 1, true);
        customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
        customDialog.setRoundedViewBorderColor(R.color.white);
        customDialog.setImgStrokWidth(15);
        customDialog.setBtnRadius(10);
        customDialog.setIconTintColor(R.color.white);
        customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
        customDialog.setPositiveBtnTextColor(R.color.white);
        customDialog.createDialog();
        customDialog.setNegativeButtonClick(() -> {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(mRentEditHashMap.get("iUserId"))
                    .setPhoneNumber(mRentEditHashMap.get("vUserPhone"))
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(mRentEditHashMap.get("vUserName"))
                    .setToMemberImage(mRentEditHashMap.get("vUserImage"))
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .build();
            String callingMethod = generalFunc.getJsonValueStr("CALLING_METHOD_BUY_SELL_RENT", obj_userProfile);
            if (callingMethod.equalsIgnoreCase("VOIP")) {
                CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.PHONE_CALL);
            } else if (callingMethod.equalsIgnoreCase("VIDEOCALL")) {
                CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.VIDEO_CALL);
            } else if (callingMethod.equalsIgnoreCase("VOIP-VIDEOCALL")) {
                CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.BOTH_CALL);
            } else if (!Utils.checkText(callingMethod) || callingMethod.equalsIgnoreCase("NORMAL")) {
                DefaultCommunicationHandler.getInstance().executeAction(MyApp.getInstance().getCurrentAct(), CommunicationManager.TYPE.PHONE_CALL, mDataProvider);
            }
        });
        customDialog.setPositiveButtonClick(() -> {
            (new ActUtils(this)).setOkResult();
            finish();
        });
        customDialog.show();
    }

    private void checkValues() {

        boolean nameEntered = Utils.checkText(binding.userNameBox) || Utils.setErrorFields(binding.userNameBox, required_str);
        boolean mobileEntered = Utils.checkText(binding.mobileBox) || Utils.setErrorFields(binding.mobileBox, required_str);

        boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, Objects.requireNonNull(binding.userEmailBox.getText()).toString().trim());

        boolean emailEntered = isEmailBlankAndOptional ? false : (Utils.checkText(binding.userEmailBox) ?
                (generalFunc.isEmailValid(binding.userEmailBox.getText().toString().trim()) ? true : false)
                : false);

        if (mobileEntered) {
            mobileEntered = binding.mobileBox.length() >= 3 || Utils.setErrorFields(binding.mobileBox, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
        }
        if (!nameEntered || !mobileEntered || !emailEntered) {
            generalFunc.showMessage(binding.userEmailBox, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
            return;
        }

        sendInquiry();
    }

    public void onClick(View view) {
        Utils.hideKeyboard(this);
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.countryArea.getId()) {
            if (countryPicker == null) {
                countryPicker = new CountryPicker.Builder(this).showingDialCode(true)
                        .setLocale(MyUtils.getLocale()).showingFlag(true)
                        .enablingSearch(true)
                        .setCountrySelectionListener(country -> setData(country.getCode(), country.getDialCode(), country.getFlagName()))
                        .build();
            }
            countryPicker.show(this);
        } else if (i == binding.btnSubmit.getId()) {
            checkValues();
        }
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
        if (isDone) {
            (new ActUtils(this)).setOkResult();
        }
    }

    @SuppressLint("SetTextI18n")
    private void setData(String vCountryCode, String vPhoneCode, String vSImage) {
        this.vPhoneCode = vPhoneCode;
        this.vSImage = vSImage;
        new LoadImage.builder(LoadImage.bind(vSImage), binding.countryImg).build();
        GeneralFunctions generalFunctions = new GeneralFunctions(MyApp.getInstance().getCurrentAct());
        binding.countryCodeTxt.setText("+" + generalFunctions.convertNumberWithRTL(vPhoneCode));
    }
}