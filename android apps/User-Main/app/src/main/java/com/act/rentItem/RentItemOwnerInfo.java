package com.act.rentItem;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;

import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.act.rentItem.adapter.RentItemDataRecommendedAdapter;
import com.activity.ParentActivity;
import com.general.call.CommunicationManager;
import com.general.call.DefaultCommunicationHandler;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.floatingactionbutton.FloatingActionButton;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.LoadImage;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.HashMap;

public class RentItemOwnerInfo extends ParentActivity {

    private static final int DATA_UPDATE_REQ = 787848;
    private HashMap<String, String> mRentEditHashMap;
    private ProgressBar loading_images;
    private MTextView noDataTxt;
    private final ArrayList<HashMap<String, String>> rentItemDataList = new ArrayList<>();
    private RentItemDataRecommendedAdapter mRentItemDataRecommendedAdapter;
    FloatingActionButton callFloatBtn;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_rent_item_owner_info);

        mRentEditHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("rentEditHashMap");
        if (mRentEditHashMap == null) {
            return;
        }
        ImageView backImgView = findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        callFloatBtn = findViewById(R.id.callFloatBtn);
        addToClickHandler(callFloatBtn);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
            callFloatBtn.setRotation(270);
        }
        callFloatBtn.setVisibility(mRentEditHashMap.get("eIsUserNumberDisplay").equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);

        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_OWNER_INFO"));

        View viewGradient = findViewById(R.id.viewGradient);
        manageVectorImage(viewGradient, R.drawable.ic_gradient_gray, R.drawable.ic_gradient_gray_compat);

        MTextView otherItemsHTxt = findViewById(R.id.otherItemsHTxt);
        otherItemsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_OTHER_ITEM"));

        // Owner info
        new LoadImage.builder(LoadImage.bind(mRentEditHashMap.get("vUserPhone").equalsIgnoreCase("") ? "http" : mRentEditHashMap.get("vUserPhone")),
                findViewById(R.id.ownerImgView)).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        MTextView txtOwnerName = findViewById(R.id.txtOwnerName);
        MTextView txtOwnerNumber = findViewById(R.id.txtOwnerNumber);
        txtOwnerName.setText(mRentEditHashMap.get("vUserName"));
        txtOwnerNumber.setText(mRentEditHashMap.get("vUserPhone"));

        loading_images = findViewById(R.id.loading_images);
        noDataTxt = findViewById(R.id.noDataTxt);
        loading_images.setVisibility(View.GONE);
        noDataTxt.setVisibility(View.GONE);

        RecyclerView rvRentItemOwnerListPost = findViewById(R.id.rvRentItemOwnerListPost);
        mRentItemDataRecommendedAdapter = new RentItemDataRecommendedAdapter(this, generalFunc, 1, rentItemDataList, (position, mapData) -> {
            Bundle bn = new Bundle();
            bn.putBoolean("isFavouriteView", true);
            bn.putSerializable("rentEditHashMap", mapData);
            new ActUtils(this).startActForResult(RentItemReviewPostActivity.class, bn, DATA_UPDATE_REQ);
        });
        rvRentItemOwnerListPost.setAdapter(mRentItemDataRecommendedAdapter);

        getPostsOwner();
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getPostsOwner() {

        loading_images.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getAllPostOwner");
        parameters.put("iMemberId", mRentEditHashMap.get("iUserId"));
        parameters.put("iRentItemPostId", mRentEditHashMap.get("iRentItemPostId"));

        ApiHandler.execute(this, parameters, responseString -> {

            loading_images.setVisibility(View.GONE);
            noDataTxt.setVisibility(View.GONE);

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    //handling category Rent Item Data
                    JSONArray rentItemDataArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    rentItemDataList.clear();
                    if (rentItemDataArray != null && rentItemDataArray.length() > 0) {

                        MyUtils.createArrayListJSONArray(generalFunc, rentItemDataList, rentItemDataArray);
                    } else {
                        noDataTxt.setVisibility(View.VISIBLE);
                        noDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                    mRentItemDataRecommendedAdapter.notifyDataSetChanged();
                } else {
                    noDataTxt.setVisibility(View.VISIBLE);
                    noDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

        sendMail();
    }

    private void sendMail() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "sendContactQueryForRent");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iRentItemOwnerId", mRentEditHashMap.get("iUserId"));
        parameters.put("iRentItemPostId", mRentEditHashMap.get("iRentItemPostId"));

        ApiHandler.execute(this, parameters, responseString -> {
            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showMessage(callFloatBtn, generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == DATA_UPDATE_REQ) {
            getPostsOwner();
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == R.id.callFloatBtn) {
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
        }
    }
}