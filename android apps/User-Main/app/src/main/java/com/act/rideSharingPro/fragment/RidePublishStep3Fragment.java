package com.act.rideSharingPro.fragment;

import android.annotation.SuppressLint;
import android.net.Uri;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.countryview.view.CountryPicker;
import com.fragments.BaseFragment;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRidePublishStep3Binding;
import com.utils.LoadImage;
import com.utils.LoadImageGlide;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class RidePublishStep3Fragment extends BaseFragment {

    private FragmentRidePublishStep3Binding binding;
    @Nullable
    private RideSharingProHomeActivity mActivity;
    private String vPhoneCode = "", vSImage = "";
    private CountryPicker countryPicker;
    private boolean isFirstTime;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_publish_step_3, container, false);

        assert mActivity != null;

        setDriverDetails(mActivity.generalFunc, mActivity.obj_userProfile);
        setCarDetails(mActivity.generalFunc);

        return binding.getRoot();
    }

    @SuppressLint("SetTextI18n")
    private void setDriverDetails(GeneralFunctions generalFunc, JSONObject obj_userProfile) {

        binding.driverNameHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_NAME_TXT"));
        binding.driverNameET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_NAME_HINT_TXT"));
        binding.driverNameET.setText(generalFunc.getJsonValueStr("vName", obj_userProfile) + " " + generalFunc.getJsonValueStr("vLastName", obj_userProfile));

        binding.driverPhoneHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_PHONE_NO_TXT"));
        int imagewidth = (int) getResources().getDimension(R.dimen._30sdp);
        int imageheight = (int) getResources().getDimension(R.dimen._20sdp);
        vSImage = generalFunc.getJsonValueStr("vSCountryImage", obj_userProfile);
        new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mActivity, vSImage, imagewidth, imageheight)), binding.countryImg).build();

        vPhoneCode = generalFunc.getJsonValueStr("vPhoneCode", obj_userProfile);
        binding.countryCodeTxt.setText(generalFunc.convertNumberWithRTL("+" + vPhoneCode));
        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            binding.countryDropImg.setVisibility(View.VISIBLE);
            addToClickHandler(binding.countryArea);
        } else {
            binding.countryDropImg.setVisibility(View.GONE);
        }
        binding.driverPhoneET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_PHONE_NO_HINT_TXT"));
        binding.driverPhoneET.getLabelFocusAnimator().start();
        binding.driverPhoneET.setText(generalFunc.getJsonValueStr("vPhone", obj_userProfile));
    }

    @SuppressLint("SetTextI18n")
    private void setCarDetails(GeneralFunctions generalFunc) {
        assert mActivity != null;

        binding.carDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_DETAILS_TITLE"));
        binding.carMakeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MAKE_BRAND_NAME_TXT"));
        binding.carModelHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MODEL"));
        binding.carNumberPlateHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_NUMBER_PLATE_TXT"));
        binding.carColorHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_COLOR_TXT"));
        binding.carImageHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_IMAGE_TXT"));
        binding.carNotesHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ADDITIONAL_NOTES_TXT"));

        binding.carMakeET.setHint(generalFunc.retrieveLangLBl("", "LBL_MAKE_HINT_TXT"));
        binding.carModelET.setHint(generalFunc.retrieveLangLBl("", "LBL_MODEL_HINT_TXT"));
        binding.carNumberPlateET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_NUMBER_PLATE_HINT_TXT"));
        binding.carColorET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_COLOR_HINT_TXT"));
        binding.carNotesET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ADDITIONAL_NOTES_HINT_TXT"));
        MyUtils.editBoxMultiLine(binding.carNotesET);

        binding.selectFileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_FILE"));
        addToClickHandler(binding.chooseFileView);
        addToClickHandler(binding.viewImg);
        addToClickHandler(binding.editDocImg);

        if (Utils.checkText(mActivity.rsPublishFragment.vImage)) {
            binding.chooseFileView.setVisibility(View.GONE);
            binding.viewImg.setVisibility(View.VISIBLE);

            new LoadImageGlide.builder(mActivity, LoadImageGlide.bind(mActivity.rsPublishFragment.vImage), binding.setImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            mActivity.rsPublishFragment.isUploadImageNew = false;
        } else {
            binding.chooseFileView.setVisibility(View.VISIBLE);
            binding.viewImg.setVisibility(View.GONE);
        }
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RideSharingProHomeActivity) {
            mActivity = (RideSharingProHomeActivity) requireActivity();
        }
    }

    @Override
    public void onResume() {
        super.onResume();

        assert mActivity != null;
        binding.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_DETAILS_TITLE"));
        mActivity.rsPublishFragment.binding.headerHTxt.setText(Utils.getText(binding.selectServiceTxt));

        JSONObject jsonObject = mActivity.generalFunc.getJsonObject(mActivity.rsPublishFragment.mPublishData.getCarDetails());
        if (jsonObject != null) {
            if (!isFirstTime) {
                isFirstTime = true;
                binding.carMakeET.setText(mActivity.generalFunc.getJsonValueStr("cMake", jsonObject));
                binding.carModelET.setText(mActivity.generalFunc.getJsonValueStr("cModel", jsonObject));
                binding.carNumberPlateET.setText(mActivity.generalFunc.getJsonValueStr("cNumberPlate", jsonObject));
                binding.carColorET.setText(mActivity.generalFunc.getJsonValueStr("cColor", jsonObject));
                binding.carNotesET.setText(mActivity.generalFunc.getJsonValueStr("cNote", jsonObject));
                mActivity.rsPublishFragment.vImage = mActivity.generalFunc.getJsonValueStr("cImage", jsonObject);
                mActivity.rsPublishFragment.cImageName = mActivity.generalFunc.getJsonValueStr("cImageName", jsonObject);

                if (Utils.checkText(mActivity.rsPublishFragment.vImage)) {
                    binding.chooseFileView.setVisibility(View.GONE);
                    binding.viewImg.setVisibility(View.VISIBLE);

                    new LoadImageGlide.builder(mActivity, LoadImageGlide.bind(mActivity.rsPublishFragment.vImage), binding.setImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
                }
            }
        }
    }

    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {

        assert mActivity != null;
        mActivity.rsPublishFragment.isUploadImageNew = true;
        mActivity.rsPublishFragment.vImage = mFilePath;
        binding.viewImg.setVisibility(View.VISIBLE);

        new LoadImageGlide.builder(mActivity, LoadImageGlide.bind(mFilePath), binding.setImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();

        mActivity.rsPublishFragment.setPagerHeight();
    }

    @SuppressLint("SetTextI18n")
    public void onClickView(View view) {
        assert mActivity != null;
        Utils.hideKeyboard(mActivity);
        int i = view.getId();
        if (i == binding.countryArea.getId()) {
            if (countryPicker == null) {
                countryPicker = new CountryPicker.Builder(mActivity).showingDialCode(true)
                        .setLocale(MyUtils.getLocale()).showingFlag(true)
                        .enablingSearch(true)
                        .setCountrySelectionListener(country -> {
                            this.vPhoneCode = country.getDialCode();
                            this.vSImage = country.getFlagName();
                            new LoadImage.builder(LoadImage.bind(vSImage), binding.countryImg).build();
                            binding.countryCodeTxt.setText("+" + mActivity.generalFunc.convertNumberWithRTL(vPhoneCode));
                        })
                        .build();
            }
            countryPicker.show(mActivity);

        } else if (i == binding.chooseFileView.getId() || i == binding.editDocImg.getId() || i == binding.viewImg.getId()) {
            mActivity.getFileSelector().openFileSelection(FileSelector.FileType.Image);
        }
    }

    public void checkPageNext() {
        if (mActivity != null) {
            boolean allDetailsEntered = Utils.checkText(mActivity.rsPublishFragment.vImage)
                    && Utils.checkText(binding.driverNameET)
                    && Utils.checkText(binding.driverPhoneET)
                    && Utils.checkText(binding.carMakeET)
                    && Utils.checkText(binding.carModelET)
                    && Utils.checkText(binding.carNumberPlateET)
                    && Utils.checkText(binding.carColorET)
                    && Utils.checkText(binding.carNotesET);

            if (allDetailsEntered) {

                JSONArray jaStore = new JSONArray();
                jaStore.put(setData("dName", binding.driverNameET));
                jaStore.put(setData("dPhoneCode", null));
                jaStore.put(setData("dPhone", binding.driverPhoneET));

                jaStore.put(setData("cMake", binding.carMakeET));
                jaStore.put(setData("cModel", binding.carModelET));
                jaStore.put(setData("cNumberPlate", binding.carNumberPlateET));
                jaStore.put(setData("cColor", binding.carColorET));
                jaStore.put(setData("cNote", binding.carNotesET));

                mActivity.rsPublishFragment.mPublishData.setDynamicDetailsArray(jaStore);
                mActivity.rsPublishFragment.setPageNext();
            } else {
                mActivity.generalFunc.showMessage(binding.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                mActivity.rsPublishFragment.setPagerHeight();
            }
        }
    }

    private JSONObject setData(@NonNull String fieldName, @Nullable MaterialEditText editText) {
        JSONObject storeObj = new JSONObject();
        try {
            storeObj.put("iData", fieldName);
            if (editText == null) {
                if (fieldName.equalsIgnoreCase("dPhoneCode")) {
                    storeObj.put("value", vPhoneCode);
                }
            } else {
                storeObj.put("value", Utils.getText(editText));
            }
        } catch (JSONException e) {
            throw new RuntimeException(e);
        }
        return storeObj;
    }
}