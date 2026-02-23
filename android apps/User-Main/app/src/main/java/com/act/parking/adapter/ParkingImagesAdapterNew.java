package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemParkingMediaBinding;
import com.utils.LoadImageGlide;
import com.utils.Utils;
import com.utils.VectorUtils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class ParkingImagesAdapterNew extends RecyclerView.Adapter<ParkingImagesAdapterNew.ViewHolder> {
    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final OnItemClickListener mItemClickListener;
    private final boolean showDelete;
    private int sWidth;
    private ArrayList<HashMap<String, String>> listData;
    boolean isReview;

    public ParkingImagesAdapterNew(@NonNull Context context, @NonNull GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> listData, int showItem, boolean showDelete, @NonNull OnItemClickListener mItemClickListener, boolean isReview) {
        this.mContext = context;
        this.generalFunc = generalFunc;
        this.listData = listData;
        this.mItemClickListener = mItemClickListener;
        this.showDelete = showDelete;
        this.isReview = isReview;


        this.sWidth = (int) Utils.getScreenPixelWidth(context);
        sWidth = (sWidth - context.getResources().getDimensionPixelSize(R.dimen._15sdp)) / showItem;
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemParkingMediaBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("RecyclerView")
    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        HashMap<String, String> mapData = listData.get(position);
        if (mapData != null) {
            holder.binding.deleteImgView.setVisibility(View.GONE);
            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) holder.binding.contentAreaView.getLayoutParams();
            if (isReview) {
                params.width = mContext.getResources().getDimensionPixelSize(R.dimen._70sdp);
                params.height = mContext.getResources().getDimensionPixelSize(R.dimen._70sdp);
                sWidth = mContext.getResources().getDimensionPixelSize(R.dimen._70sdp);
            } else {

                params.height = sWidth;
                params.width = sWidth;
            }
            holder.binding.contentAreaView.setLayoutParams(params);


            if (mapData.containsKey("add")) {
                holder.binding.cardArea.setVisibility(View.GONE);
                holder.binding.addView.setOnClickListener(view -> mItemClickListener.onItemClickList(position, mapData));
            } else {
                holder.binding.addView.setVisibility(View.GONE);
                holder.binding.cardArea.setOnClickListener(view -> mItemClickListener.onItemClickList(position, mapData));

                holder.binding.documentView.setVisibility(View.GONE);
                holder.binding.audioView.setVisibility(View.GONE);
                holder.binding.mediaImgView.setVisibility(View.GONE);
                holder.binding.exoPlay.setVisibility(View.GONE);

                if (mapData.containsKey("eMediaType") && mapData.get("eMediaType").equalsIgnoreCase("Document")) {
                    holder.binding.documentView.setVisibility(View.VISIBLE);

                } else if (mapData.containsKey("eMediaType") && mapData.get("eMediaType").equalsIgnoreCase("Audio")) {
                    holder.binding.audioView.setVisibility(View.VISIBLE);

                } else {
                    holder.binding.mediaImgView.setVisibility(View.VISIBLE);
                    new LoadImageGlide.builder(mContext, LoadImageGlide.bind(getImageURL(holder, mapData)), holder.binding.mediaImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
                }

                if (showDelete) {
                    holder.binding.deleteImgViewBottom.setVisibility(View.VISIBLE);
                    holder.binding.deleteImgViewBottom.setOnClickListener(v -> mItemClickListener.onDeleteClick(position, mapData));
                } else {
                    holder.binding.deleteImgViewBottom.setVisibility(View.GONE);
                }
            }
        }
    }

    private String getImageURL(ViewHolder holder, HashMap<String, String> mapData) {
        String imageUrl = "";
        holder.binding.exoPlay.setVisibility(View.GONE);
        if (mapData.containsKey("eMediaType")) {
            if (mapData.get("eMediaType").equalsIgnoreCase("Image")) {
                if (!TextUtils.isEmpty(mapData.get("vImage"))) {
                    imageUrl = Utils.getResizeImgURL(mContext, mapData.get("vImage"), sWidth, sWidth);
                }

            } else if (mapData.get("eMediaType").equalsIgnoreCase("Video")) {

                if (!TextUtils.isEmpty(mapData.get("thumnails"))) {
                    imageUrl = Utils.getResizeImgURL(mContext, mapData.get("thumnails"), sWidth, sWidth);
                }

                VectorUtils.manageVectorImage(mContext, holder.binding.exoPlay, R.drawable.ic_play_video, R.drawable.ic_play_video_compat);
                holder.binding.exoPlay.setVisibility(View.VISIBLE);
            }
        } else {
            if (!TextUtils.isEmpty(mapData.get("vImage"))) {
                imageUrl = Utils.getResizeImgURL(mContext, mapData.get("vImage"), sWidth, sWidth);
            }
        }
        return imageUrl;
    }

    @Override
    public int getItemCount() {
        return listData != null ? listData.size() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(ArrayList<HashMap<String, String>> listData) {
        this.listData = listData;
        notifyDataSetChanged();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemParkingMediaBinding binding;

        private ViewHolder(ItemParkingMediaBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onItemClickList(int position, HashMap<String, String> mapData);

        void onDeleteClick(int position, HashMap<String, String> mapData);
    }
}