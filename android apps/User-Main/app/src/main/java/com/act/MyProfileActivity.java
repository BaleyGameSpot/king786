package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.os.Bundle;
import android.view.KeyEvent;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.fragments.EditProfileFragment;
import com.general.files.ActUtils;
import com.general.files.FilePath;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.UploadProfileImage;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityMyProfileBinding;
import com.utils.CommonUtilities;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.HashMap;

public class MyProfileActivity extends ParentActivity {

    public boolean isEdit = false, isMobile = false, isEmail = false;
    private String SITE_TYPE = "", SITE_TYPE_DEMO_MSG = "";
    private ActivityMyProfileBinding binding;
    private MButton btn_type2;
    private int photoBtnId;
    private EditProfileFragment editProfileFrag;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        binding = DataBindingUtil.setContentView(this, R.layout.activity_my_profile);
        setSupportActionBar(binding.toolbarInclude.toolbar);

        isEdit = getIntent().getBooleanExtra("isEdit", false);
        isMobile = getIntent().getBooleanExtra("isMobile", false);
        isEmail = getIntent().getBooleanExtra("isEmail", false);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        photoBtnId = Utils.generateViewId();
        btn_type2.setId(photoBtnId);
        addToClickHandler(btn_type2);
        addToClickHandler(binding.profilePhotoDialog);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD"));

        addToClickHandler(binding.userImgArea);
        addToClickHandler(binding.cancelTxt);
        addToClickHandler(binding.toolbarInclude.backImgView);
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }

        binding.userProfileImgView.setImageResource(R.mipmap.ic_no_pic_user);
        binding.cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        binding.disclosureTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PROFILE_PHOTO_NOTE"));
        binding.profilePhotoHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PROFILE_PHOTO"));
        setImage();

        SITE_TYPE = generalFunc.getJsonValueStr("SITE_TYPE", obj_userProfile);
        SITE_TYPE_DEMO_MSG = generalFunc.getJsonValueStr("SITE_TYPE_DEMO_MSG", obj_userProfile);

        openEditProfileFragment();
    }

    private void setImage() {
        String url = CommonUtilities.USER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImgName", obj_userProfile);
        generalFunc.checkProfileImage(binding.userProfileImgView, url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);

        String vImgName_str = generalFunc.getJsonValueStr("vImgName", obj_userProfile);
        if (vImgName_str == null || vImgName_str.equals("") || vImgName_str.equals("NONE")) {
            binding.editIconImgView.setImageResource(R.drawable.ic_add_);
        } else {
            binding.editIconImgView.setImageResource(R.drawable.ic_edit_icon);
        }
    }

    public void changePageTitle(String title) {
        binding.toolbarInclude.titleTxt.setText(title);
    }

    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_MENU) {
            return true;
        }
        return super.onKeyDown(keyCode, event);
    }

    public Context getActContext() {
        return MyProfileActivity.this;
    }

    private void openEditProfileFragment() {
        if (editProfileFrag != null) {
            editProfileFrag = null;
            Utils.runGC();
        }
        editProfileFrag = new EditProfileFragment();
        getSupportFragmentManager().beginTransaction().replace(R.id.fragContainer, editProfileFrag).commit();
    }

    public EditProfileFragment getEditProfileFrag() {
        return this.editProfileFrag;
    }

    public void changeUserProfileJson(String userProfileJson) {
        Bundle bn = new Bundle();
        generalFunc.storeData(Utils.WALLET_ENABLE, generalFunc.getJsonValue("WALLET_ENABLE", userProfileJson));
        generalFunc.storeData(Utils.REFERRAL_SCHEME_ENABLE, generalFunc.getJsonValue("REFERRAL_SCHEME_ENABLE", userProfileJson));
        new ActUtils(getActContext()).setOkResult(bn);
        generalFunc.showMessage(getCurrView(), generalFunc.retrieveLangLBl("", "LBL_INFO_UPDATED_TXT"));
    }

    public View getCurrView() {
        return generalFunc.getCurrentView(MyProfileActivity.this);
    }

    private boolean isValidImageResolution(String path) {
        BitmapFactory.Options options = new BitmapFactory.Options();
        options.inJustDecodeBounds = true;

        BitmapFactory.decodeFile(path, options);
        int width = options.outWidth;
        int height = options.outHeight;
        return width >= Utils.ImageUpload_MINIMUM_WIDTH && height >= Utils.ImageUpload_MINIMUM_HEIGHT;
    }

    private void imageUpload(Uri fileUri) {
        if (SITE_TYPE.equalsIgnoreCase("Demo") && generalFunc.getJsonValue("vEmail", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)).equalsIgnoreCase("Driver@gmail.com")) {
            generalFunc.showGeneralMessage("", SITE_TYPE_DEMO_MSG);
            return;
        }

        if (fileUri == null) {
            generalFunc.showMessage(getCurrView(), generalFunc.retrieveLangLBl("", "LBL_ERROR_OCCURED"));
            return;
        }

        HashMap<String, String> paramsList = new HashMap<>() {{
            put("iMemberId", generalFunc.getMemberId());
            put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            put("GeneralUserType", Utils.app_type);
            put("GeneralMemberId", generalFunc.getMemberId());
            put("type", "uploadImage");
        }};


        String selectedImagePath = FilePath.getPath(getActContext(), fileUri);
        if (isValidImageResolution(selectedImagePath)) {
            new UploadProfileImage(MyProfileActivity.this, selectedImagePath, Utils.TempProfileImageName, paramsList).execute();
        } else {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Please select image which has minimum is 256 * 256 resolution.", "LBL_MIN_RES_IMAGE"));
        }
    }

    public void handleImgUploadResponse(String responseString) {

        if (responseString != null && !responseString.equals("")) {

            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

            if (isDataAvail) {
                generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                changeUserProfileJson(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                setImage();
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            }
        } else {
            generalFunc.showError();
        }
    }

    @SuppressLint("NonConstantResourceId")
    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();
        } else if (id == R.id.userImgArea) {
            if (!generalFunc.retrieveValue("IS_PROFILE_PHOTO_UPLOADED").equalsIgnoreCase("Yes")) {
                binding.profilePhotoDialog.setVisibility(View.VISIBLE);
            } else {
                getFileSelector().openFileSelection(FileSelector.FileType.CroppedImage);
            }
        } else if (id == R.id.cancelTxt) {
            binding.profilePhotoDialog.setVisibility(View.GONE);
        } else if (id == photoBtnId) {
            generalFunc.storeData("IS_PROFILE_PHOTO_UPLOADED", "Yes");
            binding.profilePhotoDialog.setVisibility(View.GONE);
            getFileSelector().openFileSelection(FileSelector.FileType.CroppedImage);
        }
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        imageUpload(mFileUri);
    }
}