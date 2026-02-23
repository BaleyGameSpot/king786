package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.providers.R;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.databinding.ItemBidAdditionalMediaBinding;
import com.utils.LoadImageGlide;
import com.utils.Utils;
import com.utils.VectorUtils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class BidAdditionalMediaAdapter extends RecyclerView.Adapter<BidAdditionalMediaAdapter.ViewHolder> {
    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final OnItemClickListener mItemClickListener;
    private final boolean showDelete;
    private int sWidth;
    @Nullable
    private JSONArray mediaListArray;

    public BidAdditionalMediaAdapter(@NonNull Context context, @NonNull GeneralFunctions generalFunc, @Nullable JSONArray mediaListArray, int showItem, boolean showDelete, @NonNull OnItemClickListener mItemClickListener) {
        this.mContext = context;
        this.generalFunc = generalFunc;
        this.mediaListArray = mediaListArray;
        this.mItemClickListener = mItemClickListener;
        this.showDelete = showDelete;

        this.sWidth = (int) Utils.getScreenPixelWidth(context);
        sWidth = (sWidth - context.getResources().getDimensionPixelSize(R.dimen._15sdp)) / showItem;
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemBidAdditionalMediaBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("RecyclerView")
    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        JSONObject itemObject = generalFunc.getJsonObject(mediaListArray, position);
        if (itemObject != null) {
            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) holder.binding.contentAreaView.getLayoutParams();
            params.height = sWidth;
            params.width = sWidth;
            holder.binding.contentAreaView.setLayoutParams(params);

            if (itemObject.has("Add")) {
                holder.binding.cardArea.setVisibility(View.GONE);
                holder.binding.addView.setOnClickListener(view -> mItemClickListener.onItemClickList(position, itemObject));
            } else {
                holder.binding.addView.setVisibility(View.GONE);
                holder.binding.cardArea.setOnClickListener(view -> mItemClickListener.onItemClickList(position, itemObject));

                holder.binding.documentView.setVisibility(View.GONE);
                holder.binding.audioView.setVisibility(View.GONE);

                if (generalFunc.getJsonValueStr("eMediaType", itemObject).equals("Document")) {
                    holder.binding.documentView.setVisibility(View.VISIBLE);

                } else if (generalFunc.getJsonValueStr("eMediaType", itemObject).equals("Audio")) {
                    holder.binding.audioView.setVisibility(View.VISIBLE);

                } else {
                    new LoadImageGlide.builder(mContext, LoadImageGlide.bind(getImageURL(holder, itemObject)), holder.binding.mediaImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
                }

                if (showDelete) {
                    holder.binding.deleteImgView.setVisibility(View.VISIBLE);
                    holder.binding.deleteImgView.setOnClickListener(v -> mItemClickListener.onDeleteClick(position, itemObject));
                } else {
                    holder.binding.deleteImgView.setVisibility(View.GONE);
                }
            }
        }
    }

    private String getImageURL(ViewHolder holder, JSONObject itemObject) {
        String imageUrl = "";
        holder.binding.exoPlay.setVisibility(View.GONE);
        if (itemObject.has("eMediaType")) {
            if (generalFunc.getJsonValueStr("eMediaType", itemObject).equals("Image")) {
                if (!TextUtils.isEmpty(generalFunc.getJsonValueStr("vImage", itemObject))) {
                    imageUrl = Utils.getResizeImgURL(mContext, generalFunc.getJsonValueStr("vImage", itemObject), sWidth, sWidth);
                }

            } else if (generalFunc.getJsonValueStr("eMediaType", itemObject).equals("Video")) {

                if (!TextUtils.isEmpty(generalFunc.getJsonValueStr("thumnails", itemObject))) {
                    imageUrl = Utils.getResizeImgURL(mContext, generalFunc.getJsonValueStr("thumnails", itemObject), sWidth, sWidth);
                }

                VectorUtils.manageVectorImage(mContext, holder.binding.exoPlay, R.drawable.ic_play_video, R.drawable.ic_play_video_compat);
                holder.binding.exoPlay.setVisibility(View.VISIBLE);
            }
        } else {
            if (!TextUtils.isEmpty(generalFunc.getJsonValueStr("vImage", itemObject))) {
                imageUrl = Utils.getResizeImgURL(mContext, generalFunc.getJsonValueStr("vImage", itemObject), sWidth, sWidth);
            }
        }
        return imageUrl;
    }

    @Override
    public int getItemCount() {
        return mediaListArray != null ? mediaListArray.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(JSONArray mediaDataArray) {
        this.mediaListArray = mediaDataArray;
        notifyDataSetChanged();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemBidAdditionalMediaBinding binding;

        private ViewHolder(ItemBidAdditionalMediaBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onItemClickList(int position, JSONObject itemObject);

        void onDeleteClick(int position, JSONObject itemObject);
    }
}