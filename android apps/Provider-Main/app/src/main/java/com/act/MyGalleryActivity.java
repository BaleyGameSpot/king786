package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.graphics.PorterDuff;
import android.graphics.PorterDuffColorFilter;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.ScrollView;

import androidx.annotation.NonNull;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.viewpager.widget.ViewPager;

import com.activity.ParentActivity;
import com.adapter.files.GalleryImagesRecyclerAdapter;
import com.dialogs.MyCommonDialog;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.UploadProfileImage;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ItemCarouselImageBinding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.LoadImageGlide;
import com.utils.Utils;
import com.view.FloatingAction.FloatingActionButton;
import com.view.FloatingAction.FloatingActionMenu;
import com.view.MTextView;
import com.view.WKWebView;
import com.view.carouselview.CarouselView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;

public class MyGalleryActivity extends ParentActivity implements GalleryImagesRecyclerAdapter.OnItemClickListener, WKWebView.WebClient {

    RecyclerView galleryRecyclerView;
    MTextView titleTxt;
    ImageView backImgView, filterImageview;
    AppCompatImageView noImgView, closeImg;
    ProgressBar loading_images;
    CarouselView carouselView;
    FloatingActionMenu imgAddOptionMenu;
    FloatingActionButton cameraItem, galleryItem;

    private View carouselContainerView;
    GalleryImagesRecyclerAdapter adapter;
    ArrayList<HashMap<String, String>> listData = new ArrayList<>();
    String userProfileJson;
    ScrollView noImgScrollView;
    private WKWebView noteview;

    private int _11sdp, imgWidth, imgHeight;

    @SuppressLint("UnsafeOptInUsageError")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_my_gallery);

        _11sdp = getResources().getDimensionPixelSize(R.dimen._11sdp);
        imgWidth = (int) Utils.getScreenPixelWidth(getActContext()) - (_11sdp * 2);
        imgHeight = (int) Utils.getScreenPixelHeight(getActContext()) - (_11sdp * 2);

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        noImgView = (AppCompatImageView) findViewById(R.id.noImgView);
        galleryRecyclerView = (RecyclerView) findViewById(R.id.galleryRecyclerView);
        imgAddOptionMenu = (FloatingActionMenu) findViewById(R.id.imgAddOptionMenu);
        loading_images = (ProgressBar) findViewById(R.id.loading_images);
        carouselContainerView = findViewById(R.id.carouselContainerView);
        carouselView = (CarouselView) findViewById(R.id.carouselView);
        closeImg = (AppCompatImageView) findViewById(R.id.closeImg);
        filterImageview = (ImageView) findViewById(R.id.filterImageview);
        noteview = (WKWebView) findViewById(R.id.noteview);
        noteview.setWebClient(this);
        noImgScrollView = (ScrollView) findViewById(R.id.noImgScrollView);

        cameraItem = (FloatingActionButton) findViewById(R.id.cameraItem);
        galleryItem = (FloatingActionButton) findViewById(R.id.galleryItem);

        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

        adapter = new GalleryImagesRecyclerAdapter(getActContext(), listData, generalFunc, false);

        galleryRecyclerView.setAdapter(adapter);

        addToClickHandler(backImgView);
        addToClickHandler(cameraItem);
        addToClickHandler(galleryItem);
        addToClickHandler(closeImg);

        setLabels();

        if (ServiceModule.ServiceProvider) {
            filterImageview.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_menu_help));
            filterImageview.setVisibility(View.VISIBLE);
            addToClickHandler(filterImageview);
        }

        Drawable mGalleryDrawable = ContextCompat.getDrawable(getActContext(), R.mipmap.ic_gallery_fab);
        Drawable mCameraDrawable = ContextCompat.getDrawable(getActContext(), R.mipmap.ic_camera_fab);

        if (mGalleryDrawable != null && mCameraDrawable != null) {
            mGalleryDrawable.setColorFilter(new PorterDuffColorFilter(getResources().getColor(R.color.appThemeColor_TXT_1), PorterDuff.Mode.SRC_IN));
            mCameraDrawable.setColorFilter(new PorterDuffColorFilter(getResources().getColor(R.color.appThemeColor_TXT_1), PorterDuff.Mode.SRC_IN));
            galleryItem.setImageDrawable(mGalleryDrawable);
            cameraItem.setImageDrawable(mCameraDrawable);
        }

        GridLayoutManager gridLay = new GridLayoutManager(getActContext(), 3);

        galleryRecyclerView.setLayoutManager(gridLay);

        adapter.setOnItemClickListener(this);
        getImages();

        carouselView.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

            }

            @Override
            public void onPageSelected(int position) {
                manageIcon(position);
            }

            @Override
            public void onPageScrollStateChanged(int state) {

            }
        });
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }

        FloatingActionButton btnMenu = (FloatingActionButton) findViewById(R.id.btnMenu);
        if (generalFunc.getJsonValueStr("ENABLE_VIDEO_UPLOAD_SP", obj_userProfile).equalsIgnoreCase("Yes")) {
            imgAddOptionMenu.setVisibility(View.VISIBLE);
            btnMenu.setVisibility(View.GONE);
        } else {
            imgAddOptionMenu.setVisibility(View.GONE);
            btnMenu.setVisibility(View.VISIBLE);
            btnMenu.setOnClickListener(v -> getFileSelector().openFileSelection(FileSelector.FileType.Image));
        }
    }

    private void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Manage Gallery", "LBL_MANAGE_GALLARY"));
        cameraItem.setLabelText(generalFunc.retrieveLangLBl("", "LBL_PHOTO"));
        galleryItem.setLabelText(generalFunc.retrieveLangLBl("", "LBL_VIDEO"));
    }

    private void getImages() {
        loading_images.setVisibility(View.VISIBLE);
        noImgScrollView.setVisibility(View.GONE);
        noImgView.setVisibility(View.GONE);
        listData.clear();

        adapter.notifyDataSetChanged();

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getProviderImages");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("SelectedCabType", Utils.CabGeneralType_UberX);

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {
                    JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

                    if (responseStringObject != null && !responseStringObject.toString().equalsIgnoreCase("")) {
                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject.toString())) {
                            listData.clear();
                            JSONArray arr_data = generalFunc.getJsonArray(Utils.message_str, responseStringObject.toString());

                            if (arr_data != null) {
                                for (int i = 0; i < arr_data.length(); i++) {
                                    JSONObject obj_tmp = generalFunc.getJsonObject(arr_data, i);

                                    HashMap<String, String> mapData = new HashMap<>();
                                    Iterator<String> keysItr = obj_tmp.keys();
                                    while (keysItr.hasNext()) {
                                        String key = keysItr.next();
                                        String value = generalFunc.getJsonValueStr(key, obj_tmp);
                                        mapData.put(key, value);
                                    }
                                    listData.add(mapData);
                                }
                            }
                            adapter.notifyDataSetChanged();
                            if (listData.size() == 0) {
                                noteview.loadData(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_IMAGES_PROVIDER_INFO"));
                                noImgScrollView.setVisibility(View.VISIBLE);
                            }
                        } else {
                            noteview.loadData(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_IMAGES_PROVIDER_INFO"));
                            noImgScrollView.setVisibility(View.VISIBLE);
                        }
                    } else {
                        generalFunc.showError(true);
                    }
                    loading_images.setVisibility(View.GONE);
                });

    }

    private Context getActContext() {
        return MyGalleryActivity.this;
    }

    @SuppressLint("NonConstantResourceId")
    @Override
    public void onClick(View v) {

        int id = v.getId();
        if (id == R.id.backImgView) {
            MyGalleryActivity.super.onBackPressed();
        } else if (id == R.id.filterImageview) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_GALLERY_IMG_NOTE"));
        } else if (id == R.id.closeImg) {
            if (carouselContainerView.getVisibility() == View.VISIBLE) {
                carouselContainerView.setVisibility(View.GONE);
            }
        } else if (id == R.id.cameraItem) {
            imgAddOptionMenu.close(true);
            ImageSourceDialog(true);
        } else if (id == R.id.galleryItem) {
            imgAddOptionMenu.close(true);
            ImageSourceDialog(false);
        }
    }

    private void configProviderImage(String iImageId, String action_type, String selectedImagePath) {
        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "configProviderImages");
            put("iDriverId", generalFunc.getMemberId());
            put("UserType", Utils.app_type);
            put("action_type", action_type);
            put("iImageId", iImageId);
        }};

        UploadProfileImage uploadProfileImage = new UploadProfileImage(MyGalleryActivity.this, selectedImagePath, Utils.TempProfileImageName, paramsList, "GALLERY");
        uploadProfileImage.execute(Utils.checkText(selectedImagePath), generalFunc.retrieveLangLBl("", "LBL_IMAGES_UPLOADING"));

    }

    private void configProviderVideo(String iVideoId, String action_type, String selectedVideoPath) {

        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "configProviderImages");
            put("iDriverId", generalFunc.getMemberId());
            put("UserType", Utils.app_type);
            put("action_type", action_type);
            put("iImageId", iVideoId);
        }};

        /*ArrayList<String[]> paramsList = new ArrayList<>();
        paramsList.add(generalFunc.generateImageParams("type", "configProviderImages"));
        paramsList.add(generalFunc.generateImageParams("iDriverId", generalFunc.getMemberId()));
        paramsList.add(generalFunc.generateImageParams("UserType", Utils.app_type));
        paramsList.add(generalFunc.generateImageParams("action_type", action_type));
        paramsList.add(generalFunc.generateImageParams("iImageId", iVideoId));*/

        String videoFormat;
        int index = selectedVideoPath.lastIndexOf(".");
        if (index > 0) {
            videoFormat = selectedVideoPath.substring(index + 1, selectedVideoPath.length());
            UploadProfileImage uploadProfileImage = new UploadProfileImage(MyGalleryActivity.this, selectedVideoPath, "temp_video." + videoFormat, paramsList, "GALLERY");
            uploadProfileImage.execute(Utils.checkText(selectedVideoPath), generalFunc.retrieveLangLBl("", "LBL_VIDEO_UPLOADING"));
        }
    }

    public void handleImgUploadResponse(String responseString, String imageUploadedType) {
        if (responseString != null && !responseString.equals("")) {
            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

            if (isDataAvail) {
                getImages();
            }

            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
        } else {
            generalFunc.showError();
        }
    }


    @Override
    public void onItemClickList(View view, int pos) {
        manageIcon(pos);
        carouselContainerView.setVisibility(View.VISIBLE);
        carouselView.setViewListener(position -> {
            String eFileType = listData.get(position).get("eFileType");
            String vImage = listData.get(position).get("vImage");
            String ThumbImage = listData.get(position).get("ThumbImage");
            LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @NonNull ItemCarouselImageBinding iBinding = ItemCarouselImageBinding.inflate(inflater, null, false);

            iBinding.imgViewCarousel.zoomImageView.setPadding(_11sdp, _11sdp, _11sdp, _11sdp);

            if (eFileType.equalsIgnoreCase("Video")) {
                iBinding.playIcon.setVisibility(View.VISIBLE);
                iBinding.playIcon.setOnClickListener(v -> MyCommonDialog.showVideoDialog(this, ThumbImage, vImage));
                String imageUrl = Utils.getResizeImgURL(getActContext(), ThumbImage, imgWidth, 0, imgHeight);
                new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(imageUrl), iBinding.imgViewCarousel.zoomImageView)
                        .setErrorImagePath(R.drawable.ic_novideo__icon).setPlaceholderImagePath(R.drawable.ic_novideo__icon).build();
            } else {
                iBinding.playIcon.setVisibility(View.GONE);
                iBinding.playIcon.setOnClickListener(null);
                String imageUrl = Utils.getResizeImgURL(getActContext(), vImage, imgWidth, 0, imgHeight);
                new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(imageUrl), iBinding.imgViewCarousel.zoomImageView)
                        .setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
            }

            return iBinding.getRoot();
        });
        carouselView.setPageCount(listData.size());
        carouselView.setCurrentItem(pos);

    }

    private void manageIcon(int pos) {
        manageVectorImage(findViewById(R.id.playIconBtn), R.drawable.ic_play_video, R.drawable.ic_play_video_compat);
        if (listData.get(pos).get("eFileType").equals("Video")) {
            findViewById(R.id.playIconBtn).setVisibility(View.VISIBLE);
        } else {
            findViewById(R.id.playIconBtn).setVisibility(View.GONE);
        }
    }

    @Override
    public void onDeleteClick(View v, int position) {
        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DELETE_IMG_CONFIRM_NOTE"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {

            if (buttonId == 1) {
                configProviderImage(listData.get(position).get("iImageId"), "DELETE", "");
            }

        });
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        if (mFileType == FileSelector.FileType.Video) {
            configProviderVideo("", "ADD", mFilePath);
        } else {
            configProviderImage("", "ADD", mFilePath);
        }
    }

    @Override
    public void onBackPressed() {
        if (carouselContainerView.getVisibility() == View.VISIBLE) {
            carouselContainerView.setVisibility(View.GONE);
            return;
        }
        super.onBackPressed();
    }

    private void ImageSourceDialog(boolean isCamera) {
        if (isCamera) {
            getFileSelector().openFileSelection(FileSelector.FileType.Image);
        } else {
            getFileSelector().openFileSelection(FileSelector.FileType.Video);
        }
    }

    @Override
    public void onPageFinished(WKWebView view, String url) {
        noImgView.setVisibility(View.VISIBLE);
    }
}