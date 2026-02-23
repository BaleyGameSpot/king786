package com.act.rentItem.adapter;

import android.content.Context;
import android.os.Handler;
import android.os.Looper;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FooterListBinding;
import com.buddyverse.main.databinding.ItemRentItemListPostBinding;
import com.utils.LoadImageGlide;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentItemListPostAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnItemClickListener mItemClickListener;

    private boolean isFooterEnabled;
    private FooterViewHolder footerHolder;

    private final String LBL_REVIEW_POST, LBL_DELETE, LBL_EDIT_POST, LBL_RENT_REJECT_REASON, LBL_RENT_CONTACT_US, LBL_RENEW_POST;
    private final int width, height;

    public RentItemListPostAdapter(@NonNull Context context, boolean isFooterEnabled, @NonNull GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list, @NonNull OnItemClickListener mItemClickListener) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.mItemClickListener = mItemClickListener;
        this.isFooterEnabled = isFooterEnabled;
        this.LBL_DELETE = generalFunc.retrieveLangLBl("", "LBL_DELETE");
        this.LBL_REVIEW_POST = generalFunc.retrieveLangLBl("", "LBL_REVIEW_POST");
        this.LBL_RENT_REJECT_REASON = generalFunc.retrieveLangLBl("", "LBL_RENT_REJECT_REASON");
        this.LBL_RENT_CONTACT_US = generalFunc.retrieveLangLBl("", "LBL_RENT_CONTACT_US");
        this.LBL_EDIT_POST = generalFunc.retrieveLangLBl("", "LBL_RENT_EDIT_INFORMATION");
        this.LBL_RENEW_POST = generalFunc.retrieveLangLBl("", "LBL_RENT_RENEW_TXT");
        this.width = (int) Utils.getScreenPixelWidth(context) - (context.getResources().getDimensionPixelSize(R.dimen._12sdp) * 2);
        this.height = Utils.getHeightOfBanner(context, 0, "16:9");
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {

        if (viewType == TYPE_FOOTER) {
            return new FooterViewHolder(FooterListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new ListViewHolder(ItemRentItemListPostBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        if (holder instanceof ListViewHolder listHolder) {

            HashMap<String, String> mapData = list.get(position);

            listHolder.binding.txtPostNo.setText(generalFunc.retrieveLangLBl("", "LBL_POST_BID_TXT")+ " " + mapData.get("vRentItemPostNo"));
            listHolder.binding.txtPostTitle.setText(mapData.get("vItemName"));

            if (Utils.checkText(mapData.get("eRentItemDurationDateTxt"))) {
                listHolder.binding.txtPostDurationStatus.setVisibility(View.VISIBLE);
                listHolder.binding.txtPostDurationStatus.setText(mapData.get("eRentItemDurationDateTxt"));
            } else {
                listHolder.binding.txtPostDurationStatus.setVisibility(View.GONE);
            }


            if (Utils.checkText(mapData.get("eStatus"))) {
                listHolder.binding.rentPostStatusTagTxt.setText(mapData.get("eStatus"));
                listHolder.binding.rentTagImage.setVisibility(View.VISIBLE);
            } else {
                listHolder.binding.rentTagImage.setVisibility(View.GONE);
            }

            listHolder.binding.txtPostDelete.setText(LBL_DELETE);
            listHolder.binding.txtPostDelete.setOnClickListener(v -> mItemClickListener.onDeleteButtonClick(position, mapData));

            if (Objects.requireNonNull(mapData.get("eStatusOrg")).equalsIgnoreCase("Approved")) {
                listHolder.binding.txtPostReview.setText(LBL_REVIEW_POST);
                listHolder.binding.txtPostReview.setOnClickListener(v -> mItemClickListener.onReviewButtonClick(position, mapData));
            } else if (Objects.requireNonNull(mapData.get("eStatusOrg")).equalsIgnoreCase("Expired")) {
                listHolder.binding.txtPostReview.setText(LBL_RENEW_POST);
                listHolder.binding.txtPostReview.setOnClickListener(v -> mItemClickListener.onEditButtonClick(position, mapData));
            } else {
                listHolder.binding.txtPostReview.setText(LBL_EDIT_POST);
                listHolder.binding.txtPostReview.setOnClickListener(v -> mItemClickListener.onEditButtonClick(position, mapData));
            }
            listHolder.binding.txtPostDelete.setVisibility(View.VISIBLE);
            listHolder.binding.txtPostReview.setVisibility(View.VISIBLE);

            listHolder.binding.txtPostReject.setVisibility(View.GONE);
            listHolder.binding.extraBtnView.setVisibility(View.GONE);
            listHolder.binding.extraView.setVisibility(View.GONE);
            listHolder.binding.txtContactUs.setVisibility(View.GONE);

            if (Objects.requireNonNull(mapData.get("eStatusOrg")).equalsIgnoreCase("Reject") || Objects.requireNonNull(mapData.get("eStatusOrg")).equalsIgnoreCase("Deleted")) {
                listHolder.binding.extraBtnView.setVisibility(View.VISIBLE);

                listHolder.binding.txtPostReject.setVisibility(View.VISIBLE);
                listHolder.binding.txtPostReject.setText(LBL_RENT_REJECT_REASON);
                listHolder.binding.txtPostReject.setOnClickListener(v -> generalFunc.showGeneralMessage("", mapData.get("vRejectReason")));

                if (Objects.requireNonNull(mapData.get("eStatusOrg")).equalsIgnoreCase("Deleted")) {
                    listHolder.binding.txtPostDelete.setVisibility(View.GONE);
                    listHolder.binding.txtPostReview.setVisibility(View.GONE);
                    listHolder.binding.extraBtnView.setVisibility(View.GONE);

                    listHolder.binding.extraView.setVisibility(View.VISIBLE);
                    listHolder.binding.txtContactUs.setVisibility(View.VISIBLE);
                    listHolder.binding.txtContactUs.setText(LBL_RENT_CONTACT_US);
                    listHolder.binding.txtContactUs.setOnClickListener(v -> mItemClickListener.onContactUsClick(position, mapData));
                }
            }

            JSONArray imagesArr = generalFunc.getJsonArray(mapData.get("Images"));
            String imageUrl = "";
            if (imagesArr != null && imagesArr.length() > 0) {
                JSONObject obj_temp = generalFunc.getJsonObject(imagesArr, 0);

                String eFileType = generalFunc.getJsonValueStr("eFileType", obj_temp);
                String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
                String ThumbImage = generalFunc.getJsonValueStr("ThumbImage", obj_temp);

                if (eFileType.equals("Image")) {
                    if (!TextUtils.isEmpty(vImage)) {
                        imageUrl = vImage;
                    }
                } else if (eFileType.equals("Video")) {

                    if (!TextUtils.isEmpty(ThumbImage)) {
                        imageUrl = ThumbImage;
                    }
                }
            }

            String finalBaseUrlL = imageUrl;
            new Handler(Looper.getMainLooper()).postDelayed(() -> {
                String finalLeftImage = Utils.getResizeImgURL(holder.itemView.getContext(), finalBaseUrlL, listHolder.binding.ivRentItemImageL.getWidth(), listHolder.binding.ivRentItemImageL.getMeasuredHeight());

                new LoadImageGlide.builder(holder.itemView.getContext(), LoadImageGlide.bind(finalLeftImage), listHolder.binding.ivRentItemImageL).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            }, 20);


            LinearLayout.LayoutParams topImgParams = (LinearLayout.LayoutParams) listHolder.binding.ivRentItemImage.getLayoutParams();
            topImgParams.width = width;
            topImgParams.height = height;
            listHolder.binding.ivRentItemImage.setLayoutParams(topImgParams);

            String finalTopImage = Utils.getResizeImgURL(holder.itemView.getContext(), imageUrl, topImgParams.width, topImgParams.height);
            new LoadImageGlide.builder(holder.itemView.getContext(), LoadImageGlide.bind(finalTopImage), listHolder.binding.ivRentItemImage).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();

            listHolder.binding.ivRentItemImageL.setOnClickListener(v -> mItemClickListener.onImageClick(position, imagesArr));
            if (generalFunc.isRTLmode()) {
                listHolder.binding.rentTagImage.setRotationY(180);
                listHolder.binding.ivRentItemImageL.setRotationY(180);
            }
        } else if (holder instanceof FooterViewHolder footerViewHolder) {
            this.footerHolder = footerViewHolder;
        }
    }

    @Override
    public int getItemViewType(int position) {
        assert list != null;
        if (position == list.size() && isFooterEnabled) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    @Override
    public int getItemCount() {
        return list != null ? isFooterEnabled ? list.size() + 1 : list.size() : 0;
    }

    public void addFooterView() {
        this.isFooterEnabled = true;
        if (footerHolder != null) {
            footerHolder.binding.progressContainer.setVisibility(View.VISIBLE);
        }
    }

    public void removeFooterView() {
        if (footerHolder != null) {
            footerHolder.binding.progressContainer.setVisibility(View.GONE);
        }
    }

    protected static class ListViewHolder extends RecyclerView.ViewHolder {
        private final ItemRentItemListPostBinding binding;

        private ListViewHolder(ItemRentItemListPostBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class FooterViewHolder extends RecyclerView.ViewHolder {
        private final FooterListBinding binding;

        private FooterViewHolder(FooterListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onDeleteButtonClick(int position, HashMap<String, String> mapData);

        void onReviewButtonClick(int position, HashMap<String, String> mapData);

        void onEditButtonClick(int position, HashMap<String, String> mapData);

        void onContactUsClick(int position, HashMap<String, String> mapData);
        void onImageClick(int position, JSONArray imgArr);
    }
}