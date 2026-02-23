package com.act.parking.fragment;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.fragments.BaseFragment;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.UploadProfileImage;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentParkingPublishStep4Binding;
import com.act.parking.ParkingPublish;
import com.act.parking.ParkingPublishAndBooking;
import com.act.parking.ParkingUploadDocActivity;
import com.act.parking.adapter.ParkingDocumentAdapter;
import com.act.parking.adapter.ParkingImagesAdapterNew;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class ParkingPublishStep4Fragment extends BaseFragment {

    FragmentParkingPublishStep4Binding binding;
    private ParkingPublish mActivity;
    private GeneralFunctions generalFunc;
    ParkingImagesAdapterNew imageAdapter;
    private ParkingDocumentAdapter rideDocumentAdapter;
    private final ArrayList<HashMap<String, String>> listData = new ArrayList<>();
    private boolean isDone = true;
    public ArrayList<HashMap<String, String>> verificationDocumentList = new ArrayList<>();

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_parking_publish_step_4, container, false);
        setlabels();
        getDocumentsAndMedia();
        return binding.getRoot();
    }

    public void getDocumentsAndMedia() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "FetchParkingSpaceMedia");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        ApiHandler.execute(mActivity, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    listData.clear();
                    JSONArray messageArr = generalFunc.getJsonArray("VerificationDocs", responseString);
                    verificationDocumentList.clear();
                    if (messageArr != null && messageArr.length() > 0) {
                        MyUtils.createArrayListJSONArray(generalFunc, verificationDocumentList, messageArr);
                    }
                    rideDocumentAdapter.updateData();
                    mActivity.setPagerHeight();

                    JSONArray arr_data = generalFunc.getJsonArray(Utils.message_str, responseString);
                    String parkingImageId = "";
                    HashMap<String, String> mapData1 = new HashMap<>();
                    mapData1.put("add", "add");
                    listData.add(mapData1);
                    if (arr_data != null) {

                        for (int i = 0; i < arr_data.length(); i++) {
                            JSONObject obj_tmp = generalFunc.getJsonObject(arr_data, i);

                            String imageId = generalFunc.getJsonValueStr("iParkingSpaceImageId", obj_tmp);
                            if (parkingImageId.equalsIgnoreCase("")) {
                                parkingImageId = imageId;
                            } else {
                                parkingImageId = parkingImageId + "," + imageId;
                            }

                            HashMap<String, String> mapData = new HashMap<>();
                            mActivity.parkingPublishData.setParkingImageIds(parkingImageId);
                            MyUtils.createHashMap(generalFunc, mapData, obj_tmp);
                            mapData.put("isDelete", "Yes");
                            listData.add(mapData);
                        }
                    }
                    imageAdapter.notifyDataSetChanged();
                    mActivity.setPagerHeight();


                }
            }


        });
    }

    @Override
    public void onResume() {
        super.onResume();
        mActivity.binding.rlProgress.setVisibility(View.GONE);
        mActivity.setPagerHeight();
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof ParkingPublish) {
            mActivity = (ParkingPublish) requireActivity();
            generalFunc = mActivity.generalFunc;
        }
    }

    private void setlabels() {
        binding.verifyDocsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_VERIFY_DOCS_TXT"));
        binding.pricingStructureTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_PRICING_STRUCTURE_TXT"));
        binding.pricePerHourHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_PRICE_PER_HOUR_TXT"));
        binding.additionalInfoHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_ADDITIONAL_INFO_TXT"));
        binding.instructionsHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_ENTER_INSTRUCTION_TXT"));
        binding.uploadImagesHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_UPLOAD_MEDIA_TXT"));
        binding.currencyTxt.setText(mActivity.generalFunc.getJsonValueStr("vCurrencyPassenger", mActivity.obj_userProfile));
        binding.etPrice.setHint(generalFunc.retrieveLangLBl("", "LBL_PARKING_ENTER_AMOUNT_TXT"));
        binding.EtInstructions.setHint(generalFunc.retrieveLangLBl("", "LBL_PARKING_ENTER_INSTRUCTION_TXT"));
//        addToClickHandler(binding.addImg);
        imageAdapter = new ParkingImagesAdapterNew(mActivity, generalFunc, listData, 4, true, new ParkingImagesAdapterNew.OnItemClickListener() {
            @Override
            public void onItemClickList(int position, HashMap<String, String> mapData) {
                if (position == 0) {
                    // image
                    mActivity.getFileSelector().openFileSelection(FileSelector.FileType.Image);
                } else {
                    (new ActUtils(mActivity)).openURL(listData.get(position).get("vImage"));
                }
            }

            @Override
            public void onDeleteClick(int position, HashMap<String, String> mapData) {
                HashMap<String, String> parameters = new HashMap<>();
                parameters.put("type", "DeleteParkingSpaceMedia");
                parameters.put("UserType", Utils.app_type);
                parameters.put("iUserId", generalFunc.getMemberId());
                parameters.put("iParkingSpaceImageId", listData.get(position).get("iParkingSpaceImageId"));
                ApiHandler.execute(mActivity, parameters, true, false, generalFunc, responseString -> {
                    if (responseString != null && !responseString.equalsIgnoreCase("")) {
                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), buttonId -> {
                                getDocumentsAndMedia();
                            });
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        generalFunc.showError();
                    }

                });
            }
        }, false);
        binding.RvUploadImages.setAdapter(imageAdapter);
        rideDocumentAdapter = new ParkingDocumentAdapter(verificationDocumentList, new ParkingDocumentAdapter.OnItemClickListener() {
            @Override
            public void onItemClickList(HashMap<String, String> mapData) {
                Intent intent = new Intent(mActivity, ParkingUploadDocActivity.class);
                Bundle bn = new Bundle();
                bn.putBoolean("isOnlyShow", false);
                bn.putSerializable("documentDataHashMap", mapData);
                intent.putExtras(bn);
                launchActivity.launch(intent);
                isDone = false;
            }

            @Override
            public void onUpdateDocumentIds(String documentIds) {
                mActivity.parkingPublishData.setDocumentIds(documentIds);
            }
        });

        binding.RvDocuments.setAdapter(rideDocumentAdapter);
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        if (mActivity != null && result.getResultCode() == Activity.RESULT_OK) {
            isDone = true;
            getDocumentsAndMedia();
        }
    });

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();

    }

    public void configMedia(GeneralFunctions generalFunc, String selectedImagePath, FileSelector.FileType mFileType) {
        assert mActivity != null;

        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "UploadParkingSpaceMedia");
            put("iUserId", generalFunc.getMemberId());
            put("UserType", Utils.app_type);
        }};

        if (mFileType == FileSelector.FileType.Image) {
            new UploadProfileImage(true, requireActivity(), selectedImagePath, Utils.TempProfileImageName, paramsList).execute(true, generalFunc.retrieveLangLBl("", "LBL_IMAGE_UPLOADING"));
        }
    }


    public void checkPageNext() {
        if (mActivity != null) {
            if (Utils.checkText(binding.etPrice) && Utils.checkText(binding.EtInstructions)) {
                mActivity.parkingPublishData.setPricePerHour(binding.etPrice.getText().toString());
                mActivity.parkingPublishData.setParkingInstructions(binding.EtInstructions.getText().toString());
                checkData();
            } else {

                if (!Utils.checkText(binding.etPrice)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PARKING_ENTER_AMOUNT_VALIDATION_MSG"), buttonId -> {

                    });
                } else if (!Utils.checkText(binding.EtInstructions)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PARKING_ENTER_INSTRUCTION_VALIDATION_MSG"), buttonId -> {
                    });
                }
            }
        }
    }

    private void checkData() {
        if (Utils.checkText(mActivity.parkingPublishLocationData.getParkingLatitude()) && Utils.checkText(mActivity.parkingPublishLocationData.getParkingLongitude()) && Utils.checkText(mActivity.parkingPublishData.getParkingSpaceNo()) && Utils.checkText(mActivity.parkingPublishData.getVehicleSize()) && Utils.checkText(mActivity.parkingPublishData.getParkingInstructions()) && Utils.checkText(mActivity.parkingPublishData.getPricePerHour()) && Utils.checkText(mActivity.parkingPublishData.getParkingImageIds()) && Utils.checkText(mActivity.parkingPublishData.getFullAddress()) && Utils.checkText(mActivity.parkingPublishLocationData.getParkingAddress())) {

            addParking();

        } else if (!Utils.checkText(mActivity.parkingPublishData.getParkingImageIds())) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PARKING_UPLOAD_MEDIA_VALIDATION_MSG"), buttonId -> {
            });
        } else if (!Utils.checkText(mActivity.parkingPublishData.getDocumentIds())) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_UPLOAD_YOUR_DOCS"), buttonId -> {
            });
        }

    }

    private void addParking() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "AddParkingSpace");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("vLatitude", mActivity.parkingPublishLocationData.getParkingLatitude());
        parameters.put("vLongitude", mActivity.parkingPublishLocationData.getParkingLongitude());
        parameters.put("iParkingSpaceNo", mActivity.parkingPublishData.getParkingSpaceNo());
        parameters.put("iParkingVehicleSizeId", mActivity.parkingPublishData.getVehicleSize());
        parameters.put("tInstructions", mActivity.parkingPublishData.getParkingInstructions());
        parameters.put("tPriceDetails", mActivity.parkingPublishData.getPricePerHour());
        parameters.put("ParkingSpaceImageIds", mActivity.parkingPublishData.getParkingImageIds());
        parameters.put("tAddress", mActivity.parkingPublishData.getFullAddress());
        parameters.put("tLocation", mActivity.parkingPublishLocationData.getParkingAddress());
        mActivity.binding.rlProgress.setVisibility(View.VISIBLE);

        ApiHandler.execute(mActivity, parameters, true, false, generalFunc, responseString -> {
            mActivity.binding.rlProgress.setVisibility(View.GONE);
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                CustomDialog customDialog = new CustomDialog(mActivity, generalFunc);
                customDialog.setDetails(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)), generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), generalFunc.retrieveLangLBl("", "LBL_VIEW_PARKING_SPACE_TXT"), generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), false, R.drawable.ic_correct_2, false, 1, true);
                customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
                customDialog.setRoundedViewBorderColor(R.color.white);
                customDialog.setImgStrokWidth(15);
                customDialog.setBtnRadius(10);
                customDialog.setIconTintColor(R.color.white);
                customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
                customDialog.setPositiveBtnTextColor(R.color.white);
                customDialog.createDialog();
                customDialog.setPositiveButtonClick(() -> {
                    Bundle bn = new Bundle();
                    bn.putBoolean("isRestartApp", true);
                    new ActUtils(mActivity).startActWithData(ParkingPublishAndBooking.class, bn);
                });
                customDialog.setNegativeButtonClick(() -> MyApp.getInstance().restartWithGetDataApp());
                customDialog.show();
            }
        });
    }

}
