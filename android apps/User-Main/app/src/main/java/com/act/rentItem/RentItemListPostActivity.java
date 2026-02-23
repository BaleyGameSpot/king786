package com.act.rentItem;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.media3.common.util.UnstableApi;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.ContactUsActivity;
import com.act.homescreen23.adapter.HomeUtils;
import com.act.rentItem.adapter.RentItemListPostAdapter;
import com.activity.ParentActivity;
import com.dialogs.MyCommonDialog;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRentItemListPostBinding;
import com.buddyverse.main.databinding.ItemCarouselImageBinding;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentItemListPostActivity extends ParentActivity {

    private ActivityRentItemListPostBinding binding;
    private static final int GET_POST_REQ = 484848;
    private RentItemListPostAdapter mRentItemListPostAdapter;
    private final ArrayList<HashMap<String, String>> mPostList = new ArrayList<>();
    private String eType = "";
    boolean mIsLoading = false, isNextPageAvailable = false;
    private String next_page_str = "1";
    private boolean isNeedLoader = false;

    private int _11sdp, imgWidth, imgHeight;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_rent_item_list_post);

        eType = getIntent().getStringExtra("eType");

        _11sdp = getResources().getDimensionPixelSize(R.dimen._11sdp);
        imgWidth = (int) Utils.getScreenPixelWidth(this) - (_11sdp * 2);
        imgHeight = (int) Utils.getScreenPixelHeight(this) - (_11sdp * 2);

        toolbarData();
        addToClickHandler(binding.rlProgressbar);
        addToClickHandler(binding.closeView);

        mRentItemListPostAdapter = new RentItemListPostAdapter(this, false, generalFunc, mPostList, new RentItemListPostAdapter.OnItemClickListener() {
            @Override
            public void onDeleteButtonClick(int position, HashMap<String, String> mapData) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_RENT_DELETE_POST"),
                        generalFunc.retrieveLangLBl("", "LBL_NO"),
                        generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                            if (buttonId == 1) {
                                getMyPost(true, mapData.get("iRentItemPostId"));
                                mPostList.remove(position);
                            }
                        });
            }

            @Override
            public void onReviewButtonClick(int position, HashMap<String, String> mapData) {
                if (binding.loadingImages.getVisibility() == View.GONE) {
                    Bundle bn = new Bundle();
                    bn.putSerializable("rentEditHashMap", mapData);
                    new ActUtils(RentItemListPostActivity.this).startActWithData(RentItemReviewPostActivity.class, bn);
                }
            }

            @Override
            public void onEditButtonClick(int position, HashMap<String, String> mapData) {
                if (binding.loadingImages.getVisibility() == View.GONE) {
                    Bundle bn = new Bundle();
                    bn.putString("eType", eType);
                    bn.putSerializable("rentEditHashMap", mapData);
                    new ActUtils(RentItemListPostActivity.this).startActForResult(RentItemNewPostActivity.class, bn, GET_POST_REQ);
                }
            }

            @Override
            public void onContactUsClick(int position, HashMap<String, String> mapData) {
                new ActUtils(RentItemListPostActivity.this).startAct(ContactUsActivity.class);
            }

            @Override
            public void onImageClick(int position, JSONArray imgArr) {
                binding.carouselContainerView.setVisibility(View.VISIBLE);
                binding.carouselView.setViewListener(pos -> {
                    JSONObject obj_temp = generalFunc.getJsonObject(imgArr, pos);
                    String eFileType = generalFunc.getJsonValueStr("eFileType", obj_temp);
                    String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
                    String ThumbImage = generalFunc.getJsonValueStr("ThumbImage", obj_temp);
                    LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                    @NonNull ItemCarouselImageBinding iBinding = ItemCarouselImageBinding.inflate(inflater, null, false);

                    iBinding.imgViewCarousel.zoomImageView.setPadding(_11sdp, _11sdp, _11sdp, _11sdp);

                    if (eFileType.equalsIgnoreCase("Video")) {
                        iBinding.playIcon.setVisibility(View.VISIBLE);
                        iBinding.playIcon.setOnClickListener(v -> MyCommonDialog.showVideoDialog(RentItemListPostActivity.this, ThumbImage, vImage));
                        String imageUrl = Utils.getResizeImgURL(getActContext(), ThumbImage, imgWidth, 0, imgHeight);
                        HomeUtils.loadImg(getActContext(), iBinding.imgViewCarousel.zoomImageView, imageUrl, R.drawable.ic_novideo__icon, false, 0, 0);
                    } else {
                        iBinding.playIcon.setVisibility(View.GONE);
                        iBinding.playIcon.setOnClickListener(null);
                        String imageUrl = Utils.getResizeImgURL(getActContext(), vImage, imgWidth, 0, imgHeight);
                        HomeUtils.loadImg(getActContext(), iBinding.imgViewCarousel.zoomImageView, imageUrl, R.mipmap.ic_no_icon, false, 0, 0);
                    }

                    return iBinding.getRoot();
                });
                binding.carouselView.setPageCount(imgArr.length());
                binding.carouselView.setCurrentItem(0);

            }
        });
        binding.rvRentItemListPost.setAdapter(mRentItemListPostAdapter);
        getMyPost(false, "");
        binding.rvRentItemListPost.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (recyclerView.canScrollVertically(1)) {
                    int visibleItemCount = Objects.requireNonNull(binding.rvRentItemListPost.getLayoutManager()).getChildCount();
                    int totalItemCount = binding.rvRentItemListPost.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) binding.rvRentItemListPost.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if (((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable)) {
                        isNeedLoader = true;
                        mIsLoading = true;
                        mRentItemListPostAdapter.addFooterView();
                        binding.rvRentItemListPost.stopScroll();
                        getMyPost(false, "");

                    } else if (!isNextPageAvailable) {
                        mRentItemListPostAdapter.removeFooterView();
                    }
                }
            }
        });
    }

    private Context getActContext() {
        return RentItemListPostActivity.this;
    }

    private void toolbarData() {
        addToClickHandler(binding.toolbarLayout.backImgView);
        if (generalFunc.isRTLmode()) {
            binding.toolbarLayout.backImgView.setRotation(180);
        }

        if (eType.equalsIgnoreCase("RentEstate")) {
            binding.toolbarLayout.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_POST_REALESTATE_BY_USER"));
        } else if (eType.equalsIgnoreCase("RentCars")) {
            binding.toolbarLayout.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_POST_CARS_BY_USER"));
        } else if (eType.equalsIgnoreCase("RentItem")) {
            binding.toolbarLayout.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_POST_ITEM_BY_USER"));
        }


        addToClickHandler(binding.floatBtn);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getMyPost(boolean isDelete, String iRentItemPostId) {

        binding.loadingImages.setVisibility(isDelete ? View.GONE : View.VISIBLE);
        binding.rlProgressbar.setVisibility(isDelete ? View.GONE : View.VISIBLE);
        if (isNeedLoader) {
            binding.loadingImages.setVisibility(View.GONE);
            binding.rlProgressbar.setVisibility(View.GONE);
        }
        binding.noDataTxt.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("iMemberId", generalFunc.getMemberId());
        if (isDelete) {
            parameters.put("type", "DeletePost");
            parameters.put("iRentItemPostId", iRentItemPostId);
            parameters.put("eDeletedBy", "User");
        } else {
            parameters.put("type", "GetAllPost");
            parameters.put("page", next_page_str);
            binding.rvRentItemListPost.setVisibility(View.GONE);
        }
        parameters.put("eType", eType);

        ApiHandler.execute(this, parameters, isDelete, false, generalFunc, responseString -> {

            binding.loadingImages.setVisibility(View.GONE);
            binding.rlProgressbar.setVisibility(View.GONE);
            binding.rvRentItemListPost.setVisibility(View.VISIBLE);
            mIsLoading = false;

            if (responseString != null && !responseString.equals("")) {
                String nextPage = next_page_str;
                if (!isDelete) {
                    nextPage = generalFunc.getJsonValue("NextPage", responseString);
                }
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString) && !isDelete) {
                    JSONArray itemArr = generalFunc.getJsonArray(Utils.message_str, responseString);

                    MyUtils.createArrayListJSONArray(generalFunc, mPostList, itemArr);

                    binding.noDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                        mRentItemListPostAdapter.addFooterView();
                        getMyPost(false, "");
                    } else {
                        removeNextPageConfig();
                    }
                }
                binding.noDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                if (isDelete) {
                    generalFunc.showMessage(binding.mainContainer, generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message2", responseString)));
                    //generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message2", responseString)));
                    if (isNextPageAvailable) {
                        isNeedLoader = true;
                        mRentItemListPostAdapter.addFooterView();
                        getMyPost(false, "");
                    }
                }
                mRentItemListPostAdapter.notifyDataSetChanged();
            } else {
                removeNextPageConfig();
                generalFunc.showError(true);
            }
            binding.noDataTxt.setVisibility(mPostList.size() > 0 ? View.GONE : View.VISIBLE);
        });

    }

    private void removeNextPageConfig() {
        next_page_str = "1";
        isNextPageAvailable = false;
        mIsLoading = false;
        mRentItemListPostAdapter.removeFooterView();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == GET_POST_REQ && resultCode == RESULT_OK && data != null) {
            if (data.getBooleanExtra("isGetList", false)) {
                mPostList.clear();
                next_page_str = "1";
                isNeedLoader = false;
                binding.rlProgressbar.setVisibility(View.VISIBLE);
                binding.loadingImages.setVisibility(View.VISIBLE);
                getMyPost(false, "");
            }
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            if (binding.carouselContainerView.getVisibility() == View.VISIBLE) {
                return;
            }
            onBackPressed();
        } else if (i == R.id.floatBtn) {
            Bundle bn = new Bundle();
            bn.putString("iCategoryId", "");
            bn.putString("eType", eType);
            new ActUtils(this).startActForResult(RentItemNewPostActivity.class, bn, GET_POST_REQ);
        } else if (view.getId() == R.id.closeView) {
            if (binding.carouselContainerView.getVisibility() == View.VISIBLE) {
                binding.carouselContainerView.setVisibility(View.GONE);
            }
        }
    }

    public void pubNubMsgArrived(final String message) {

        runOnUiThread(() -> {

            String msgType = generalFunc.getJsonValue("MsgType", message);

            if (msgType.equals("PostRejectByAdmin") || msgType.equals("PostApprovedByAdmin") || msgType.equals("PostDeletedByAdmin")) {
                generalFunc.showGeneralMessage("", generalFunc.getJsonValue("vTitle", message), buttonId -> {
                    mPostList.clear();
                    next_page_str = "1";
                    isNeedLoader = false;
                    binding.rlProgressbar.setVisibility(View.VISIBLE);
                    binding.loadingImages.setVisibility(View.VISIBLE);
                    getMyPost(false, "");
                });
            }
        });
    }
}