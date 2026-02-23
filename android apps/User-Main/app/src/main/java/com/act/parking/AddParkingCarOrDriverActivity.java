package com.act.parking;

import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.appcompat.widget.Toolbar;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.UploadProfileImage;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityParkingAddCarDriverBinding;
import com.utils.LoadImageGlide;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.ArrayList;
import java.util.HashMap;

public class AddParkingCarOrDriverActivity extends ParentActivity {
    private ActivityParkingAddCarDriverBinding binding;
    MButton btn_type2;
    private String vImage = "";
    private boolean isUploadImageNew = true;
    private boolean isEdit = false;
    public Toolbar mToolbar;
    private String vehicleId = "";
    private String vehicleSizeId = "";
    private int selCurrentPosition = -1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_parking_add_car_driver);

        isEdit = getIntent().hasExtra("vehicleData");

        initialize();
        if (isEdit) {
            setData();
        }
    }

    private void initialize() {
        binding.LLAdditionalInfo.setVisibility(View.GONE);
        binding.carSizeLL.setVisibility(View.VISIBLE);
        binding.llButton.setVisibility(View.VISIBLE);
        binding.llToolbar.setVisibility(View.VISIBLE);

        mToolbar = findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        addToClickHandler(binding.llButton);
        addToClickHandler(binding.viewImg);
        addToClickHandler(binding.TxtCarSizeLL);
        binding.carSize.setHint(generalFunc.retrieveLangLBl("", "LBL_PARKING_SELECT_CAR_SIZE_TXT"));
        btn_type2 = ((MaterialRippleLayout) binding.btnType2).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_VEHICLE"));
        addToClickHandler(btn_type2);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        if (getIntent().getStringExtra("detailType").equalsIgnoreCase("car")) {
            binding.driverDetailsLL.setVisibility(View.GONE);
            binding.LLCarDetails.setVisibility(View.VISIBLE);
            binding.selectServiceTxt.setVisibility(View.GONE);
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_VEHICLE"));
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_VEHICLE"));
        } else if (getIntent().getStringExtra("detailType").equalsIgnoreCase("driver")) {
            binding.driverNameET.setText(getIntent().getStringExtra("vName"));
            binding.driverPhoneET.setText(getIntent().getStringExtra("vPhone"));
            binding.LLCarDetails.setVisibility(View.GONE);
            binding.carDetailsHTxt.setVisibility(View.GONE);
            binding.selectServiceTxt.setVisibility(View.VISIBLE);
            binding.driverDetailsLL.setVisibility(View.VISIBLE);
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DRIVER_TRACKING_COMPANY_TXT"));
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DRIVER_TRACKING_COMPANY_TXT"));
        }
        btn_type2.setId(Utils.generateViewId());
        binding.selectServiceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_DETAILS_TITLE"));
        binding.driverNameHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_NAME_TXT"));
        binding.driverPhoneHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_PHONE_NO_TXT"));
        binding.carDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_DETAILS_TITLE"));
        binding.carMakeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MAKE_BRAND_NAME_TXT"));
        binding.carModelHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MODEL"));
        binding.carNumberPlateHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_NUMBER_PLATE_TXT"));
        binding.carColorHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_COLOR_TXT"));
        binding.carImageHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_IMAGE_TXT"));
        binding.carNotesHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ADDITIONAL_NOTES_TXT"));

        binding.driverNameET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_NAME_HINT_TXT"));
        binding.carMakeET.setHint(generalFunc.retrieveLangLBl("", "LBL_MAKE_HINT_TXT"));
        binding.carModelET.setHint(generalFunc.retrieveLangLBl("", "LBL_MODEL_HINT_TXT"));
        binding.carNumberPlateET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_NUMBER_PLATE_HINT_TXT"));
        binding.carColorET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_COLOR_HINT_TXT"));
        binding.carNotesET.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ADDITIONAL_NOTES_HINT_TXT"));
        binding.carSizeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_CAR_SIZE_TXT"));

    }

    private void setData() {
        Bundle bundle = this.getIntent().getExtras();
        HashMap<String, String> mapData = (HashMap<String, String>) bundle.getSerializable("vehicleData");
        binding.carMakeET.setText(mapData.get("vMake"));
        binding.carModelET.setText(mapData.get("vModel"));
        binding.carNumberPlateET.setText(mapData.get("vCarNumberPlate"));
        binding.carColorET.setText(mapData.get("vCarColor"));
        binding.carSize.setText(mapData.get("vCarSize"));
        vehicleId = mapData.get("iParkingVehicleId");
        vImage = mapData.get("vImage");
        if (Utils.checkText(vImage)) {
            new LoadImageGlide.builder(this, LoadImageGlide.bind(vImage), binding.setImgView).setErrorImagePath(R.drawable.pro_deliver_all_grid_item_bg).setPlaceholderImagePath(R.drawable.pro_deliver_all_grid_item_bg).build();
            isUploadImageNew = false;
        }


    }

    private void addVehicle() {
        if (getIntent().getStringExtra("detailType").equalsIgnoreCase("car")) {
            if (checkDetails()) {
                HashMap<String, String> paramsList = new HashMap<String, String>() {{
                    put("type", "AddUserVehicleForParkingSpace");
                    put("vMake", binding.carMakeET.getText().toString());
                    put("vModel", binding.carModelET.getText().toString());
                    put("iParkingVehicleSizeId", vehicleSizeId);
                    put("iParkingVehicleId", vehicleId);
                    put("vCarNumberPlate", binding.carNumberPlateET.getText().toString());
                    put("vCarColor", binding.carColorET.getText().toString());
                }};
                UploadProfileImage uploadProfileImage;
                if (Utils.checkText(vImage)) {

                    if (isUploadImageNew) {
                        uploadProfileImage = new UploadProfileImage(true, this, vImage, "TempFile." + Utils.getFileExt(vImage), paramsList);
                    } else {
                        paramsList.put("vImage", vImage);
                        uploadProfileImage = new UploadProfileImage(true, this, "", "TempFile." + Utils.getFileExt(vImage), paramsList);
                    }
                } else {
                    uploadProfileImage = new UploadProfileImage(true, this, vImage, "TempFile." + Utils.getFileExt(vImage), paramsList);
                }
                uploadProfileImage.execute(binding.loading);
            }
        } else if (getIntent().getStringExtra("detailType").equalsIgnoreCase("driver")) {
            Bundle bn = new Bundle();
            bn.putString("vName", binding.driverNameET.getText().toString());
            bn.putString("vPhone", binding.driverPhoneET.getText().toString());
            bn.putString("detailType", "driver");
            new ActUtils(AddParkingCarOrDriverActivity.this).setOkResult(bn);
            finish();
        }
    }

    private boolean checkDetails() {
        boolean addDetail = false;
        if (!Utils.checkText(binding.carMakeET.getText().toString())) {
            generalFunc.showMessage(binding.selectServiceTxt, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
        } else if (!Utils.checkText(binding.carModelET.getText().toString())) {
            generalFunc.showMessage(binding.selectServiceTxt, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
        } else if (!Utils.checkText(vehicleSizeId)) {
            generalFunc.showMessage(binding.selectServiceTxt, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
        } else if (!Utils.checkText(binding.carNumberPlateET.getText().toString())) {
            generalFunc.showMessage(binding.selectServiceTxt, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
        } else if (!Utils.checkText(binding.carNumberPlateET.getText().toString())) {
            generalFunc.showMessage(binding.selectServiceTxt, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
        } else if (!Utils.checkText(vImage)) {
            generalFunc.showMessage(binding.selectServiceTxt, generalFunc.retrieveLangLBl("", "LBL_SELECT_IMAGE"));
        } else {
            addDetail = true;
        }

        return addDetail;
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == btn_type2.getId()) {
            addVehicle();
        } else if (i == binding.viewImg.getId()) {
            getFileSelector().openFileSelection(FileSelector.FileType.Image);
        } else if (i == binding.TxtCarSizeLL.getId()) {
            ArrayList<HashMap<String, String>> list;
            list = (ArrayList<HashMap<String, String>>) getIntent().getSerializableExtra("vehicleSizes");
            OpenListView.getInstance(this, generalFunc.retrieveLangLBl("", "LBL_PARKING_CHOOSE_VEHICLE_SIZE_TXT"), list, OpenListView.OpenDirection.CENTER, true, position -> {
                selCurrentPosition = position;
                binding.carSize.setText(list.get(position).get("size"));
                vehicleSizeId = list.get(position).get("iParkingVehicleSizeId");
            }).show(selCurrentPosition, "size");
        }
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        new LoadImageGlide.builder(this, LoadImageGlide.bind(mFilePath), binding.setImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
        isUploadImageNew = true;
        vImage = mFilePath;
    }

    public void handleImgUploadResponse(String responseString) {
        if (responseString != null && !responseString.equalsIgnoreCase("")) {
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                finish();
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), true);
            }
        } else {
            generalFunc.showError();
        }
    }

}