package com.act.trackService;

import static com.utils.Utils.isValidImageResolution;

import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.FilePath;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.UploadProfileImage;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityTrackAnyProfileVehicleBinding;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONObject;

import java.util.HashMap;

public class TrackAnyProfileVehicle extends ParentActivity {

    private ActivityTrackAnyProfileVehicleBinding binding;
    private HashMap<String, String> trackAnyHashMap;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_track_any_profile_vehicle);

        trackAnyHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("trackAnyHashMap");
        if (trackAnyHashMap == null) {
            return;
        }

        MTextView titleTxt = findViewById(R.id.titleTxt);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);

        binding.profileView.setVisibility(View.GONE);
        binding.vehicleView.setVisibility(View.GONE);

        boolean isVehicleView = getIntent().getBooleanExtra("isVehicleView", false);

        if (isVehicleView) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VEHICLE_DETAILS_TXT"));
            binding.vehicleView.setVisibility(View.VISIBLE);
            vehicleView();
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_PROFILE_TXT"));
            binding.profileView.setVisibility(View.VISIBLE);
            profileView();
        }
    }

    private void profileView() {
        addToClickHandler(binding.userImgView);
        binding.editIconImgView.setColorFilter(getResources().getColor(R.color.appThemeColor_1));
        setImage(trackAnyHashMap.get("vImage"));

        /////
        setText(binding.txtUserNameH, binding.txtUserNameV, "LBL_TRACK_SERVICE_COMPANY_USER_NAME_TXT", trackAnyHashMap.get("userName"));
        setText(binding.txtUserPhoneH, binding.txtUserPhoneV, "LBL_TRACK_SERVICE_PHONE_NO_TXT", trackAnyHashMap.get("userPhone"));
        setText(binding.txtInviteCodeH, binding.txtInviteCodeV, "LBL_TRACK_SERVICE_INVITE_CODE_TXT", trackAnyHashMap.get("vInviteCode"));
        setText(binding.txtUserLocationH, binding.txtUserLocationV, "LBL_TRACK_SERVICE_USER_LOC_TXT", trackAnyHashMap.get("userAddress"));

    }

    private void setText(MTextView txtH, MTextView txtV, String LBL, String value) {
        txtH.setText(generalFunc.retrieveLangLBl("", LBL));
        txtV.setText(value);
    }

    private void vehicleView() {
        setText(binding.txtVehicleMakeH, binding.txtVehicleMakeV, "LBL_CAR_MAKE_ADMIN", trackAnyHashMap.get("vMake"));
        setText(binding.txtVehicleLiceNoH, binding.txtVehicleLiceNoV, "LBL_VEHICLE_LICENSE_NO_TXT", trackAnyHashMap.get("vLicencePlateNo"));
        setText(binding.txtDriverNameH, binding.txtDriverNameV, "LBL_TRACK_SERVICE_COMPANY_DRIVER_NAME_TXT", trackAnyHashMap.get("driverName"));
        setText(binding.txtDriverPhoneH, binding.txtDriverPhoneV, "LBL_TRACK_SERVICE_DRIVER_PHONE_NO_TXT", trackAnyHashMap.get("driverPhone"));
        setText(binding.txtOrgNameH, binding.txtOrgNameV, "LBL_TRACK_SERVICE_ORG_NAME_TXT", trackAnyHashMap.get("orgName"));
        setText(binding.txtOrgPhoneH, binding.txtOrgPhoneV, "LBL_TRACK_SERVICE_ORG_PHONE_NO_TXT", trackAnyHashMap.get("orgPhone"));
        setText(binding.txtOrgAddressH, binding.txtOrgAddressV, "LBL_TRACK_SERVICE_ORG_ADDRESS_TXT", trackAnyHashMap.get("orgAddress"));

        addToClickHandler(binding.imgCallDriver);
        addToClickHandler(binding.imgCallOrg);
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.userImgView.getId()) {
            getFileSelector().openFileSelection(FileSelector.FileType.CroppedImage);

        } else if (i == binding.imgCallDriver.getId()) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setPhoneNumber(trackAnyHashMap.get("driverPhone"))
                    .setToMemberName(trackAnyHashMap.get("driverName"))
                    .setMedia(CommunicationManager.MEDIA.DEFAULT)
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.OTHER);
        } else if (i == binding.imgCallOrg.getId()) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setPhoneNumber(trackAnyHashMap.get("orgPhone"))
                    .setToMemberName(trackAnyHashMap.get("orgName"))
                    .setMedia(CommunicationManager.MEDIA.DEFAULT)
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.OTHER);
        }
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        if (mFileUri == null) {
            generalFunc.showMessage(binding.editIconImgView, generalFunc.retrieveLangLBl("", "LBL_ERROR_OCCURED"));
            return;
        }

        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "uploadImageForTrackingUser");
            put("iTrackServiceUserId", trackAnyHashMap.get("iTrackServiceUserId"));

            put("iMemberId", generalFunc.getMemberId());
            put("GeneralMemberId", generalFunc.getMemberId());
            put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            put("GeneralUserType", Utils.app_type);
        }};

        String selectedImagePath = FilePath.getPath(this, mFileUri);
        if (isValidImageResolution(selectedImagePath)) {
            new UploadProfileImage(this, selectedImagePath, Utils.TempProfileImageName, paramsList).execute();
        } else {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_MIN_RES_IMAGE"));
        }
    }

    public void handleImgUploadResponse(String responseString) {
        if (responseString != null && !responseString.equals("")) {
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                JSONObject userArr = generalFunc.getJsonObject(Utils.message_str, responseString);
                setImage(generalFunc.getJsonValueStr("vImage", userArr));
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            }
        } else {
            generalFunc.showError();
        }
    }

    private void setImage(String vUserImage) {
        if (!Utils.checkText(vUserImage)) {
            vUserImage = "Temp";
            binding.editIconImgView.setImageResource(R.drawable.ic_pic_add);
        } else {
            binding.editIconImgView.setImageResource(R.mipmap.ic_edit);
        }
        generalFunc.checkProfileImage(binding.userImgView, vUserImage, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);
    }
}