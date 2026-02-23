package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.os.Bundle;
import android.text.InputType;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.adapter.files.EmergencyContactRecycleAdapter;
import com.countryview.view.CountryPicker;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityEmergencyContactBinding;
import com.buddyverse.providers.databinding.EmergencyContaxctLayoutBinding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class EmergencyContactActivity extends ParentActivity implements EmergencyContactRecycleAdapter.OnItemClickList {

    private ActivityEmergencyContactBinding binding;
    private MButton btn_type2;
    private AlertDialog alertDialog;

    private EmergencyContactRecycleAdapter adapter;
    private final ArrayList<HashMap<String, String>> list = new ArrayList<>();
    private CountryPicker countryPicker;
    private String vSImage = "", vPhoneCode = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_emergency_contact);

        addToClickHandler(binding.toolbarInclude.backImgView);
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMERGENCY_CONTACT"));

        if (ServiceModule.ServiceProvider || ServiceModule.ServiceBid) {
            binding.titleTxtContact.setText(generalFunc.retrieveLangLBl("", "LBL_FOR_SAFETY"));
        } else {
            binding.titleTxtContact.setText(generalFunc.retrieveLangLBl("", "LBL_EMERGENCY_CONTACT_TITLE"));
        }
        binding.subTitleTxt1.setText(generalFunc.retrieveLangLBl("", "LBL_EMERGENCY_CONTACT_SUB_TITLE1"));
        binding.subTitleTxt2.setText(generalFunc.retrieveLangLBl("", "LBL_EMERGENCY_CONTACT_SUB_TITLE2"));
        binding.notifyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_EMERGENCY_UP_TO_COUNT"));

        adapter = new EmergencyContactRecycleAdapter(generalFunc, list, this);
        binding.emeContactRecyclerView.setAdapter(adapter);

        btn_type2 = ((MaterialRippleLayout) binding.btnType2).getChildView();
        btn_type2.setId(Utils.generateViewId());
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_CONTACTS"));
        addToClickHandler(btn_type2);

        getContacts();
    }

    @Override
    public void onItemClick(int position) {
        buildWarningMessage(list.get(position).get("iEmergencyId"));
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getContacts() {
        binding.mainArea.setBackgroundColor(Color.parseColor("#EBEBEB"));
        btn_type2.parentView.setVisibility(View.GONE);
        binding.notifyTxt.setVisibility(View.GONE);
        binding.dataContainer.setVisibility(View.VISIBLE);
        binding.noContactArea.setVisibility(View.GONE);

        binding.noContactArea.setVisibility(View.GONE);
        binding.mainContainerView.setVisibility(View.GONE);
        binding.errorView.setVisibility(View.GONE);
        binding.mProgressBar.setVisibility(View.VISIBLE);

        if (!list.isEmpty()) {
            list.clear();
        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "loadEmergencyContacts");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            if (EmergencyContactActivity.this.isFinishing()) {
                return;
            }
            binding.mainContainerView.setVisibility(View.VISIBLE);
            binding.mProgressBar.setVisibility(View.GONE);

            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    JSONArray obj_arr = generalFunc.getJsonArray(Utils.message_str, responseObj);

                    for (int i = 0; i < obj_arr.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(obj_arr, i);

                        HashMap<String, String> map = new HashMap<>();

                        map.put("ContactName", generalFunc.getJsonValueStr("vName", obj_temp));
                        map.put("ContactPhone", generalFunc.getJsonValueStr("vPhone", obj_temp));
                        map.put("iEmergencyId", generalFunc.getJsonValueStr("iEmergencyId", obj_temp));

                        list.add(map);
                    }

                    adapter.notifyDataSetChanged();

                    if (obj_arr.length() >= 5) {
                        binding.notifyTxt.setVisibility(View.GONE);
                        btn_type2.parentView.setVisibility(View.GONE);
                    } else {
                        binding.notifyTxt.setVisibility(View.VISIBLE);
                        btn_type2.parentView.setVisibility(View.VISIBLE);
                    }

                } else {
                    binding.mainArea.setBackgroundColor(Color.parseColor("#FFFFFF"));
                    binding.noContactArea.setVisibility(View.VISIBLE);
                    binding.dataContainer.setVisibility(View.GONE);

                    binding.notifyTxt.setVisibility(View.VISIBLE);
                    btn_type2.parentView.setVisibility(View.VISIBLE);
                }
            } else {
                generateErrorView();
            }
        });

    }

    private void addContact(String contactName, String contactPhone) {
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "addEmergencyContacts");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("vName", contactName);
        parameters.put("Phone", contactPhone);
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    getContacts();
                    generalFunc.showMessage(generalFunc.getCurrentView(EmergencyContactActivity.this),
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void buildWarningMessage(final String iEmergencyId) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            generateAlert.closeAlertBox();
            if (btn_id == 1) {
                deleteContact(iEmergencyId);
            }
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_MSG_DELETE_EME_CONTACT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
        generateAlert.showAlertBox();
    }

    private void deleteContact(String iEmergencyId) {
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "deleteEmergencyContacts");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iEmergencyId", iEmergencyId);
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    getContacts();
                    generalFunc.showMessage(generalFunc.getCurrentView(EmergencyContactActivity.this),
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void generateErrorView() {
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        if (binding.errorView.getVisibility() != View.VISIBLE) {
            binding.errorView.setVisibility(View.VISIBLE);
        }
        binding.errorView.setOnRetryListener(this::getContacts);
    }

    private Context getActContext() {
        return EmergencyContactActivity.this;
    }

    @SuppressLint({"ClickableViewAccessibility", "SetTextI18n"})
    private void AddEmergencyContacts() {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull EmergencyContaxctLayoutBinding iBinding = EmergencyContaxctLayoutBinding.inflate(inflater, null, false);

        final String required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");

        int paddingValStart = (int) getResources().getDimension(R.dimen._35sdp);
        int paddingValEnd = (int) getResources().getDimension(R.dimen._12sdp);
        if (generalFunc.isRTLmode()) {
            iBinding.countryBox.mEditText.setPaddings(paddingValEnd, 0, paddingValStart, 0);
        } else {
            iBinding.countryBox.mEditText.setPaddings(paddingValStart, 0, paddingValEnd, 0);
        }

        iBinding.img.setImageResource(R.drawable.ic_contact_card);


        iBinding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_DETAILS_TXT"));
        iBinding.submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBMIT_BUTTON_TXT"));
        iBinding.cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        iBinding.nameBox.mEditText.setFloatingLabelText(generalFunc.retrieveLangLBl("", "LBL_FULL_NAME"));
        iBinding.nameBox.mEditText.setHint(generalFunc.retrieveLangLBl("", "LBL_FULL_NAME"));
        iBinding.nameBox.mEditText.setInputType(InputType.TYPE_CLASS_TEXT);

        iBinding.phoneBox.mEditText.setFloatingLabelText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HEADER_TXT"));
        iBinding.phoneBox.mEditText.setHint(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HEADER_TXT"));
        iBinding.phoneBox.mEditText.setInputType(InputType.TYPE_CLASS_PHONE);
        iBinding.phoneBox.mEditText.setImeOptions(EditorInfo.IME_ACTION_DONE);

        vSImage = generalFunc.retrieveValue(Utils.DefaultCountryImage);
        int imagewidth = (int) getResources().getDimension(R.dimen._30sdp);
        int imageheight = (int) getResources().getDimension(R.dimen._20sdp);
        String imgUrl = Utils.getResizeImgURL(getActContext(), vSImage, imagewidth, imageheight);
        new LoadImage.builder(LoadImage.bind(imgUrl), iBinding.countryimage).build();

        Utils.removeInput(iBinding.countryBox.mEditText);
        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            iBinding.countrydropimage.setVisibility(View.VISIBLE);
            iBinding.countryBox.mEditText.setOnClickListener(v -> {
                if (countryPicker != null) {
                    countryPicker = null;
                }
                countryPicker = new CountryPicker.Builder(getActContext()).showingDialCode(true)
                        .setLocale(MyUtils.getLocale()).showingFlag(true)
                        .enablingSearch(true)
                        .setCountrySelectionListener(country ->
                                setData(country.getCode(), country.getDialCode(),
                                        country.getFlagName(), iBinding.countryimage, iBinding.countryBox.mEditText))
                        .build();
                countryPicker.show(getActContext());
            });
            iBinding.countryBox.mEditText.setOnTouchListener(new setOnTouchList());
        } else {
            iBinding.countrydropimage.setVisibility(View.GONE);
        }

        if (!generalFunc.getJsonValue("vCode", obj_userProfile).equals("")) {
            vPhoneCode = generalFunc.getJsonValueStr("vCode", obj_userProfile);
            iBinding.countryBox.mEditText.setText("+" + generalFunc.convertNumberWithRTL(vPhoneCode));
        }

        if (generalFunc.getJsonValue("vSCountryImage", obj_userProfile) != null && !generalFunc.getJsonValueStr("vSCountryImage", obj_userProfile).equalsIgnoreCase("")) {
            vSImage = generalFunc.getJsonValueStr("vSCountryImage", obj_userProfile);
            imgUrl = Utils.getResizeImgURL(getActContext(), vSImage, imagewidth, imageheight);
            new LoadImage.builder(LoadImage.bind(imgUrl), iBinding.countryimage).build();
        }

        iBinding.cancelTxt.setOnClickListener(v -> alertDialog.dismiss());
        iBinding.submitTxt.setOnClickListener(v -> {

            boolean mobileEntered = Utils.checkText(iBinding.phoneBox.mEditText) ? true : Utils.setErrorFields(iBinding.phoneBox.mEditText, required_str);
            boolean NameEntered = Utils.checkText(iBinding.nameBox.mEditText) ? true : Utils.setErrorFields(iBinding.nameBox.mEditText, required_str);

            if (mobileEntered) {
                mobileEntered = iBinding.phoneBox.mEditText.length() >= 3 ? true : Utils.setErrorFields(iBinding.phoneBox.mEditText, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
            }

            if (!NameEntered || !mobileEntered) {
                return;
            } else {
                alertDialog.dismiss();
                addContact(Utils.getText(iBinding.nameBox.mEditText), "+" + vPhoneCode + " " + Utils.getText(iBinding.phoneBox.mEditText));
            }

        });

        builder.setView(iBinding.getRoot());
        alertDialog = builder.create();
        LayoutDirection.setLayoutDirection(alertDialog);
        alertDialog.setCancelable(false);
        alertDialog.setCanceledOnTouchOutside(false);
        alertDialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card_contact));
        alertDialog.show();
    }


    @SuppressLint("SetTextI18n")
    private void setData(String vCountryCode, String vPhoneCode, String vSImage, ImageView countryimage
            , MaterialEditText countryBox) {
        this.vPhoneCode = vPhoneCode;
        this.vSImage = vSImage;

        runOnUiThread(() -> {
            new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();

            countryBox.setText("+" + generalFunc.convertNumberWithRTL(vPhoneCode));
        });

    }

    private static class setOnTouchList implements View.OnTouchListener {

        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {
            if (motionEvent.getAction() == MotionEvent.ACTION_UP && !view.hasFocus()) {
                view.performClick();
            }
            return true;
        }
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == binding.toolbarInclude.backImgView.getId()) {
            EmergencyContactActivity.super.onBackPressed();
        } else if (i == btn_type2.getId()) {
            AddEmergencyContacts();
        }
    }

}