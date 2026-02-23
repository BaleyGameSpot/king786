package com.act.rentItem.fragment;

import static android.app.Activity.RESULT_OK;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.media3.common.util.UnstableApi;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.ContactUsActivity;
import com.act.homescreen23.adapter.HomeUtils;
import com.act.rentItem.RentItemNewPostActivity;
import com.act.rentItem.RentItemReviewPostActivity;
import com.act.rentItem.adapter.RentItemListPostAdapter;
import com.dialogs.MyCommonDialog;
import com.dialogs.OpenListView;
import com.fragments.BaseFragment;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
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

public class RentItemListFragment extends BaseFragment {

    public GeneralFunctions generalFunc;
    String userProfileJson;
    ArrayList<HashMap<String, String>> subFilterlist;
    private static final int GET_POST_REQ = 484848;
    private boolean isNeedLoader = false, mIsLoading = false, isNextPageAvailable = false;
    private RentItemListPostAdapter mRentItemListPostAdapter;

    public int subFilterPosition = 0;

    ActivityRentItemListPostBinding binding;
    private String next_page_str = "1";
    private String eType = "";
    private final ArrayList<HashMap<String, String>> mPostList = new ArrayList<>();

    private int _11sdp, imgWidth, imgHeight;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

        _11sdp = getResources().getDimensionPixelSize(R.dimen._11sdp);
        imgWidth = (int) Utils.getScreenPixelWidth(getActContext()) - (_11sdp * 2);
        imgHeight = (int) Utils.getScreenPixelHeight(getActContext()) - (_11sdp * 2);
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {

        binding = DataBindingUtil.inflate(inflater, R.layout.activity_rent_item_list_post, container, false);

        mPostList.clear();
        toolbarData();
        binding.floatBtn.setVisibility(View.GONE);
        addToClickHandler(binding.closeView);
        addToClickHandler(binding.filterArea);
        mRentItemListPostAdapter = new RentItemListPostAdapter(getActContext(), false, generalFunc, mPostList, new RentItemListPostAdapter.OnItemClickListener() {
            @Override
            public void onDeleteButtonClick(int position, HashMap<String, String> mapData) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_RENT_DELETE_POST"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
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
                    new ActUtils(getActContext()).startActWithData(RentItemReviewPostActivity.class, bn);
                }
            }

            @Override
            public void onEditButtonClick(int position, HashMap<String, String> mapData) {
                if (binding.loadingImages.getVisibility() == View.GONE) {
                    Bundle bn = new Bundle();
                    bn.putString("eType", eType);
                    bn.putSerializable("rentEditHashMap", mapData);
                    new ActUtils(getActContext()).startActForResult(RentItemNewPostActivity.class, bn, GET_POST_REQ);
                }
            }

            @Override
            public void onContactUsClick(int position, HashMap<String, String> mapData) {
                new ActUtils(getActContext()).startAct(ContactUsActivity.class);
            }

            @Override
            public void onImageClick(int position, JSONArray imgArr) {
                if (imgArr != null && imgArr.length() > 0) {
                    binding.carouselContainerView.setVisibility(View.VISIBLE);
                    binding.carouselView.setViewListener(pos -> {
                        JSONObject obj_temp = generalFunc.getJsonObject(imgArr, pos);
                        String eFileType = generalFunc.getJsonValueStr("eFileType", obj_temp);
                        String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
                        String ThumbImage = generalFunc.getJsonValueStr("ThumbImage", obj_temp);
                        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                        @NonNull ItemCarouselImageBinding iBinding = ItemCarouselImageBinding.inflate(inflater, null, false);

                        iBinding.imgViewCarousel.zoomImageView.setPadding(_11sdp, _11sdp, _11sdp, _11sdp);

                        if (eFileType.equalsIgnoreCase("Video")) {
                            iBinding.playIcon.setVisibility(View.VISIBLE);
                            iBinding.playIcon.setOnClickListener(v -> MyCommonDialog.showVideoDialog(requireActivity(), ThumbImage, vImage));
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
        return binding.getRoot();
    }

    private void toolbarData() {
        binding.toolbarLayout.backImgView.setVisibility(View.GONE);
        binding.toolbarLayout.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BUY_SELL_RENT_POST_TXT"));
    }

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

        ApiHandler.execute(getActContext(), parameters, isDelete, false, generalFunc, responseString -> {

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
            binding.filterArea.setVisibility(View.VISIBLE);
            if (!isDelete) {
                BuildType(generalFunc.getJsonObject(responseString));
            }
            binding.noDataTxt.setVisibility(mPostList.size() > 0 ? View.GONE : View.VISIBLE);
            binding.filterArea.setClickable(true);
        });

    }


    public Context getActContext() {
        return getActivity();
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActContext());

        if (view.getId() == R.id.filterArea) {
            openFilterListView();
        } else if (view.getId() == R.id.closeView) {
            if (binding.carouselContainerView.getVisibility() == View.VISIBLE) {
                binding.carouselContainerView.setVisibility(View.GONE);
            }
        }
    }


    public void BuildType(JSONObject responseObj) {
        if (responseObj == null) return;
        JSONArray subFilterOptionArr = generalFunc.getJsonArray("subFilterOption", responseObj);
        String eFilterSel = generalFunc.getJsonValueStr("vSubFilterParam", responseObj);
        subFilterlist = new ArrayList<>();
        if (subFilterOptionArr != null) {
            int subFilterArrSize = subFilterOptionArr.length();
            if (subFilterArrSize > 0) {
                for (int i = 0; i < subFilterArrSize; i++) {
                    JSONObject obj_temp = generalFunc.getJsonObject(subFilterOptionArr, i);
                    HashMap<String, String> map = new HashMap<>();
                    String ListingsTitle = generalFunc.getJsonValueStr("ListingsTitle", obj_temp);
                    map.put("ListingsTitle", ListingsTitle);
                    String eItemType = generalFunc.getJsonValueStr("eType", obj_temp);
                    map.put("eType", eItemType);

                    if (eItemType.equalsIgnoreCase(eFilterSel)) {
                        eType = eItemType;
                        subFilterPosition = i;
                        binding.filterTxt.setText(ListingsTitle);
                    }
                    subFilterlist.add(map);
                }
            }
        }


    }

    private ArrayList<String> populateSubArrayList() {

        ArrayList<String> typeNameList = new ArrayList<>();
        if (subFilterlist != null && subFilterlist.size() > 0) {
            for (int i = 0; i < subFilterlist.size(); i++) {
                typeNameList.add((subFilterlist.get(i).get("ListingsTitle")));
            }
        }
        return typeNameList;
    }

    private int populatePos() {
        return subFilterPosition;
    }

    private void removeNextPageConfig() {
        next_page_str = "1";
        isNextPageAvailable = false;
        mIsLoading = false;
        mRentItemListPostAdapter.removeFooterView();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
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

    public void pubNubMsgArrived(final String message) {

        requireActivity().runOnUiThread(() -> {

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

    private void openFilterListView() {
        ArrayList<String> arrayList = populateSubArrayList();

        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("Select Type", "LBL_SELECT_TYPE"), arrayList, OpenListView.OpenDirection.BOTTOM, false, true, new OpenListView.OnItemClickList() {
            @Override
            public void onItemClick(int position) {
                subFilterPosition = position;
                eType = subFilterlist.get(position).get("eType");
                binding.filterTxt.setText(subFilterlist.get(position).get("ListingsTitle"));
                binding.rlProgressbar.setVisibility(View.VISIBLE);
                binding.loadingImages.setVisibility(View.VISIBLE);
                binding.filterArea.setClickable(false);
                mPostList.clear();
                getMyPost(false, "");
            }
        }).show(populatePos(), "ListingsTitle");
    }
}
