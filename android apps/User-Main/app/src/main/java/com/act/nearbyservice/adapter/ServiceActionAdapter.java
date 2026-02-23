package com.act.nearbyservice.adapter;

import android.annotation.SuppressLint;
import android.graphics.drawable.Drawable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemNearByServiceActionBinding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class ServiceActionAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnItemClickListener mItemClickListener;

    public ServiceActionAdapter(GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> list, OnItemClickListener mItemClickListener) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.mItemClickListener = mItemClickListener;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ListViewHolder(ItemNearByServiceActionBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("RecyclerView")
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        HashMap<String, String> mapData = list.get(position);

        ListViewHolder listHolder = (ListViewHolder) holder;
        listHolder.binding.txtSubCategoryName.setText(mapData.get("vTitle"));

        //---------
        String imageUrl = mapData.get("vImage");
        if (!Utils.checkText(imageUrl)) {
            imageUrl = "Temp";
        }
        imageUrl = Utils.getResizeImgURL(holder.itemView.getContext(), imageUrl, listHolder.binding.imgServiceIcon.getWidth(), listHolder.binding.imgServiceIcon.getHeight());
        new LoadImage.builder(LoadImage.bind(imageUrl), listHolder.binding.imgServiceIcon).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
        /////---------

        listHolder.binding.rowArea.setOnClickListener(v -> mItemClickListener.onServiceActionClick(mapData, listHolder.binding.imgServiceIcon.getDrawable()));

        if (position == (getItemCount() - 1)) {
            listHolder.binding.viewLine.setVisibility(View.GONE);
        }
        if (generalFunc.isRTLmode()) {
            listHolder.binding.imgArrow.setRotation(0);
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class ListViewHolder extends RecyclerView.ViewHolder {
        private final ItemNearByServiceActionBinding binding;

        private ListViewHolder(ItemNearByServiceActionBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onServiceActionClick(HashMap<String, String> mapData, Drawable drawable);
    }
}