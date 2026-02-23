package com.act.nearbyservice.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FooterListBinding;
import com.buddyverse.main.databinding.ItemNearByServicesBinding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class NearByServiceAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;

    private final GeneralFunctions generalFunc;
    private final OnClickListener onClickListener;
    private final ArrayList<HashMap<String, String>> list;

    private boolean isFooterEnabled = false;
    private FooterViewHolder footerHolder;

    public NearByServiceAdapter(@NonNull GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list, @NonNull OnClickListener listener) {
        this.list = list;
        this.generalFunc = generalFunc;
        this.onClickListener = listener;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int i) {
        if (i == TYPE_FOOTER) {
            return new FooterViewHolder(FooterListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new DataViewHolder(ItemNearByServicesBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        if (holder instanceof DataViewHolder vHolder) {
            HashMap<String, String> mapData = list.get(position);

            vHolder.binding.restaurantNameTxt.setText(mapData.get("vTitle"));
            vHolder.binding.placesLocationTXT.setText(mapData.get("vAddress"));
            vHolder.binding.deliveryTimeTxt.setText(mapData.get("duration"));

            vHolder.binding.statusMessageTxt.setText(mapData.get("statusMessage"));
            if (Utils.checkText(mapData.get("placesStatus")) && Objects.requireNonNull(mapData.get("placesStatus")).equalsIgnoreCase("Open")) {
                vHolder.binding.statusMessageTxt.setTextColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.Green));
            } else {
                vHolder.binding.statusMessageTxt.setTextColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.Red));
            }

            JSONArray imagesArr = generalFunc.getJsonArray(mapData.get("vImages"));
            String imageUrl = null;
            if (imagesArr != null && imagesArr.length() > 0) {
                imageUrl = (String) generalFunc.getValueFromJsonArr(imagesArr, 0);
            }
            if (!Utils.checkText(imageUrl)) {
                imageUrl = "Temp";
            }
            imageUrl = Utils.getResizeImgURL(holder.itemView.getContext(), imageUrl, vHolder.binding.ivNearByService.getWidth(), vHolder.binding.ivNearByService.getHeight());
            new LoadImage.builder(LoadImage.bind(imageUrl), vHolder.binding.ivNearByService).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();

            if (generalFunc.isRTLmode()) {
                vHolder.binding.ivNearByService.setRotationY(180);
            }

            vHolder.binding.nearByItemArea.setOnClickListener(v -> onClickListener.onItemClick(position, mapData));

        } else if (holder instanceof FooterViewHolder viewHolder) {
            this.footerHolder = viewHolder;
        }
    }

    @Override
    public int getItemCount() {
        if (isFooterEnabled) {
            return list.size() + 1;
        } else {
            return list.size();
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (position == list.size() && isFooterEnabled) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null) {
            footerHolder.binding.progressContainer.setVisibility(View.VISIBLE);
        }
    }

    public void removeFooterView() {
        if (footerHolder != null) {
            footerHolder.binding.progressContainer.setVisibility(View.GONE);
        }
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {
        private final ItemNearByServicesBinding binding;

        private DataViewHolder(ItemNearByServicesBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class FooterViewHolder extends RecyclerView.ViewHolder {
        private final FooterListBinding binding;

        private FooterViewHolder(@NonNull FooterListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onItemClick(int position, HashMap<String, String> mapData);
    }
}