package com.act.rentItem.adapter;

import android.content.Context;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.act.homescreen23.adapter.HomeUtils;
import com.act.rentItem.RentItemNewPostActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemRentGalleryListBinding;
import com.utils.LoadImageGlide;
import com.utils.Utils;
import com.utils.VectorUtils;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentGalleryImagesAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnItemClickListener mItemClickListener;

    private final boolean isDelete;
    private final int width, v7sdp;

    public RentGalleryImagesAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isDelete, OnItemClickListener mItemClickListener) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isDelete = isDelete;
        this.mItemClickListener = mItemClickListener;

        this.v7sdp = mContext.getResources().getDimensionPixelSize(R.dimen._7sdp);

        int sWidth = (int) Utils.getScreenPixelWidth(mContext);
        this.width = (sWidth / 2) - mContext.getResources().getDimensionPixelSize(R.dimen._30sdp);
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemRentGalleryListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull final RecyclerView.ViewHolder holder, final int position) {

        if (holder instanceof final ViewHolder vHolder) {

            final HashMap<String, String> item = list.get(position);

            LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) vHolder.binding.cardArea.getLayoutParams();
            params.width = isDelete ? LinearLayout.LayoutParams.MATCH_PARENT : width;
            params.height = width + 1;
            vHolder.binding.cardArea.setLayoutParams(params);

            // item Space
            boolean isFirst = position == 0;
            boolean isLast = position == (list.size() - 1);
            int topSpace = 0;
            if (isDelete) {
                isFirst = (position % 2 == 0);
                isLast = (position % 2 == 1);
                if (position > 1) {
                    topSpace = (int) (v7sdp * 1.5);
                }
            }
            HomeUtils.itemSpace(generalFunc, vHolder.binding.mainArea, isDelete, isFirst, isLast, v7sdp, topSpace, 0);

            if (isDelete) {
                vHolder.binding.deleteImgView.setVisibility(View.VISIBLE);
                if (generalFunc.isRTLmode()) {
                    vHolder.binding.deleteImgView.setScaleX(-1);
                }
                vHolder.binding.deleteImgView.setOnClickListener(view -> {
                    if (mItemClickListener != null) {
                        mItemClickListener.onDeleteClick(view, position);
                    }
                });
            } else {
                vHolder.binding.deleteImgView.setVisibility(View.GONE);
            }

            vHolder.binding.exoPlay.setVisibility(View.GONE);

            String imageUrl = "";
            if (item.containsKey("eFileType")) {
                if (Objects.requireNonNull(item.get("eFileType")).equalsIgnoreCase("Image")) {
                    if (!TextUtils.isEmpty(item.get("vImage"))) {
                        imageUrl = Utils.getResizeImgURL(mContext, Objects.requireNonNull(item.get("vImage")), width, width);
                    }
                } else if (Objects.requireNonNull(item.get("eFileType")).equalsIgnoreCase("Video")) {

                    if (!TextUtils.isEmpty(item.get("ThumbImage"))) {
                        imageUrl = Utils.getResizeImgURL(mContext, Objects.requireNonNull(item.get("ThumbImage")), width, width);
                    }

                    VectorUtils.manageVectorImage(mContext, vHolder.binding.exoPlay, R.drawable.ic_play_video, R.drawable.ic_play_video_compat);
                    vHolder.binding.exoPlay.setVisibility(View.VISIBLE);
                }
            } else {
                if (!TextUtils.isEmpty(item.get("vImage"))) {
                    imageUrl = Utils.getResizeImgURL(mContext, Objects.requireNonNull(item.get("vImage")), width, width);
                }
            }
            new LoadImageGlide.builder(mContext, LoadImageGlide.bind(imageUrl), vHolder.binding.galleryImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();

            vHolder.binding.galleryImgView.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(view, position);
                }
            });

            if (position == (list.size() - 1)) {
                if (mContext instanceof RentItemNewPostActivity mActivity) {
                    mActivity.setPagerHeight();
                }
            }
        }
    }


    @Override
    public int getItemCount() {
        return list != null ? list.size() : 0;
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemRentGalleryListBinding binding;

        private ViewHolder(ItemRentGalleryListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onItemClickList(View v, int position);

        void onDeleteClick(View v, int position);
    }
}