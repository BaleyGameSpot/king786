package com.act.rentItem.fragment;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;

import com.general.files.ActUtils;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.UploadProfileImage;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRentItemPhotosBinding;
import com.act.rentItem.RentItemNewPostActivity;
import com.act.rentItem.adapter.RentGalleryImagesAdapter;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentItemPhotosFragment extends Fragment implements RentGalleryImagesAdapter.OnItemClickListener {

    private FragmentRentItemPhotosBinding binding;
    @Nullable
    private RentItemNewPostActivity mActivity;
    private RentGalleryImagesAdapter mAdapter;
    private final ArrayList<HashMap<String, String>> listData = new ArrayList<>();
    private boolean isSelectMode = false;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_rent_item_photos, container, false);

        assert mActivity != null;

        binding.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_CHOOSE_FILE"));
        binding.chooseArea.setOnClickListener(view1 -> mActivity.generalFunc.showGeneralMessage("", mActivity.generalFunc.retrieveLangLBl("", "LBL_SELECT_MEDIA_TYPE_TXT"),
                mActivity.generalFunc.retrieveLangLBl("", "LBL_VIDEO"),
                mActivity.generalFunc.retrieveLangLBl("", "LBL_IMAGE"), buttonId -> {

                    isSelectMode = true;
                    if (buttonId == 0) {
                        // video
                        mActivity.getFileSelector().openFileSelection(FileSelector.FileType.Video);
                    } else if (buttonId == 1) {
                        // image
                        mActivity.getFileSelector().openFileSelection(FileSelector.FileType.Image);
                    }
                }));
        binding.rvRentPostImages.setAdapter(mAdapter);

        return binding.getRoot();
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RentItemNewPostActivity) {
            mActivity = (RentItemNewPostActivity) requireActivity();

            mAdapter = new RentGalleryImagesAdapter(requireActivity(), listData, mActivity.generalFunc, true, this);
        }
    }


    @Override
    public void onResume() {
        super.onResume();
        if (mActivity != null && !isSelectMode) {
            getImages(mActivity.generalFunc);
        }
        if (mActivity != null) {
            mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_PHOTOS"));
        }
        isSelectMode = false;
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getImages(GeneralFunctions generalFunc) {
        assert mActivity != null;
        if (mActivity.mRentItemData.getiItemCategoryId() == null) {
            return;
        }

        mActivity.setPagerHeight();

        mActivity.loading.setVisibility(View.VISIBLE);
        binding.rvRentPostImages.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "RentItemImages");
        parameters.put("iUserId", generalFunc.getMemberId());

        parameters.put("iItemCategoryId", mActivity.mRentItemData.getiItemCategoryId());
        parameters.put("iItemSubCategoryId", mActivity.mRentItemData.getiItemSubCategoryId());

        parameters.put("iRentItemPostId", mActivity.mRentItemData.getiRentItemPostId() == null ? "" : mActivity.mRentItemData.getiRentItemPostId());
        parameters.put("iTmpRentItemPostId", mActivity.mRentItemData.getiTmpRentItemPostId() == null ? "" : mActivity.mRentItemData.getiTmpRentItemPostId());

        parameters.put("iImageId", "");
        parameters.put("action_type", "History");

        ApiHandler.execute(requireActivity(), parameters, responseString -> {

            mActivity.loading.setVisibility(View.GONE);
            binding.rvRentPostImages.setVisibility(View.VISIBLE);

            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    listData.clear();

                    JSONArray arr_data = generalFunc.getJsonArray("AllImages", responseString);

//                    HashMap<String, String> mapData1 = new HashMap<>();
//                    mapData1.put("add", "add");
//                    listData.add(mapData1);
                    String iRentImageId = "";
                    if (arr_data != null) {

                        for (int i = 0; i < arr_data.length(); i++) {
                            JSONObject obj_tmp = generalFunc.getJsonObject(arr_data, i);

                            String imageId = generalFunc.getJsonValueStr("iRentImageId", obj_tmp);
                            if (iRentImageId.equalsIgnoreCase("")) {
                                iRentImageId = imageId;
                            } else {
                                iRentImageId = iRentImageId + "," + imageId;
                            }

                            HashMap<String, String> mapData = new HashMap<>();
                            MyUtils.createHashMap(generalFunc, mapData, obj_tmp);
                            mapData.put("isDelete", "Yes");
                            listData.add(mapData);
                        }
                    }
                    mActivity.mRentItemData.setiRentImageId(iRentImageId);

                    mAdapter.notifyDataSetChanged();
                    mActivity.setPagerHeight();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    @Override
    public void onItemClickList(View v, int position) {
        //(new ActUtils(mActivity)).openURL(listData.get(position).get("vImage"));
        mActivity.showImage(listData.get(position).get("vImage"));
    }

    @Override
    public void onDeleteClick(View v, int position) {
        if (mActivity != null) {
            String msg = "";
            if (Objects.requireNonNull(listData.get(position).get("eFileType")).equalsIgnoreCase("Image")) {
                msg = mActivity.generalFunc.retrieveLangLBl("", "LBL_DELETE_IMG_CONFIRM_MSG");

            } else if (Objects.requireNonNull(listData.get(position).get("eFileType")).equalsIgnoreCase("Video")) {
                msg = mActivity.generalFunc.retrieveLangLBl("", "LBL_DELETE_VIDEO_CONFIRM_MSG");
            }

            mActivity.generalFunc.showGeneralMessage("", msg,
                    mActivity.generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"),
                    mActivity.generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"), buttonId -> {
                        if (buttonId == 1) {
                            deleteImage(mActivity.generalFunc, listData.get(position).get("iRentImageId"));
                        }
                    });
        }
    }

    private void deleteImage(GeneralFunctions generalFunc, String iImageId) {
        assert mActivity != null;

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "RentItemImages");
        parameters.put("iUserId", generalFunc.getMemberId());

        parameters.put("iItemCategoryId", mActivity.mRentItemData.getiItemCategoryId());
        parameters.put("iItemSubCategoryId", mActivity.mRentItemData.getiItemSubCategoryId());

        parameters.put("iRentItemPostId", mActivity.mRentItemData.getiRentItemPostId() == null ? "" : mActivity.mRentItemData.getiRentItemPostId());
        parameters.put("iTmpRentItemPostId", mActivity.mRentItemData.getiTmpRentItemPostId() == null ? "" : mActivity.mRentItemData.getiTmpRentItemPostId());

        parameters.put("iImageId", iImageId);
        parameters.put("action_type", "Delete");

        binding.rvRentPostImages.setVisibility(View.GONE);
        ApiHandler.execute(requireActivity(), parameters, true, false, generalFunc, responseString -> handleImgUploadResponse(generalFunc, responseString));
    }

    public void configMedia(GeneralFunctions generalFunc, String selectedImagePath, FileSelector.FileType mFileType) {
        assert mActivity != null;

        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "RentItemImages");
            put("iUserId", generalFunc.getMemberId());

            put("iItemCategoryId", mActivity.mRentItemData.getiItemCategoryId());
            put("iItemSubCategoryId", mActivity.mRentItemData.getiItemSubCategoryId());

            put("iRentItemPostId", mActivity.mRentItemData.getiRentItemPostId() == null ? "" : mActivity.mRentItemData.getiRentItemPostId());
            put("iTmpRentItemPostId", mActivity.mRentItemData.getiTmpRentItemPostId() == null ? "" : mActivity.mRentItemData.getiTmpRentItemPostId());

            put("iImageId", "");
            put("action_type", "Add");
        }};

        binding.rvRentPostImages.setVisibility(View.GONE);
        /////////////////////////////////////////////////////////////////////////////////
        if (mFileType == FileSelector.FileType.Image) {
            new UploadProfileImage(true, requireActivity(), selectedImagePath, Utils.TempProfileImageName, paramsList).execute();

        } else if (mFileType == FileSelector.FileType.Video) {
            String videoFormat;
            int index = selectedImagePath.lastIndexOf(".");
            if (index > 0) {
                videoFormat = selectedImagePath.substring(index + 1);
                new UploadProfileImage(true, requireActivity(), selectedImagePath, "temp_video." + videoFormat, paramsList).execute();
            }
        }
    }

    public void handleImgUploadResponse(GeneralFunctions generalFunc, String responseString) {
        if (responseString != null && !responseString.equals("")) {
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                generalFunc.showMessage(binding.rvRentPostImages, generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                getImages(generalFunc);
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            }
        } else {
            generalFunc.showError();
        }
    }

    public void checkPageNext() {
        if (mActivity != null && mActivity.loading.getVisibility() == View.GONE) {
            if (mActivity.mRentItemData.getiRentImageId() != null && mActivity.mRentItemData.getiRentImageId().equalsIgnoreCase("")) {
                mActivity.generalFunc.showMessage(mActivity.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_UPLOAD_IMAGE_NOTE"));
            } else {
                mActivity.setPageNext();
            }
        }
    }
}