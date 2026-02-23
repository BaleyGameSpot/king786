package com.act.rentItem.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentCategorySubCategoryAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_GRID = 1;

    private final Context mContext;
    private final GeneralFunctions generalFunc;

    private final ArrayList<HashMap<String, String>> list;
    private final OnItemClickListener mItemClickListener;
    private int selCatPos = -1, selSubCatPos = -1;

    public RentCategorySubCategoryAdapter(Context mContext, GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> list, OnItemClickListener mItemClickListener) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
        this.list = list;
        this.mItemClickListener = mItemClickListener;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_GRID) {
            return new GridViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_rent_item_category, parent, false));
        } else {
            return new ListViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_rent_item_sub_category, parent, false));
        }
    }

    @SuppressLint("RecyclerView")
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        HashMap<String, String> mapData = list.get(position);

        if (holder instanceof GridViewHolder) {
            GridViewHolder grideholder = (GridViewHolder) holder;

            grideholder.txtCategoryName.setText(mapData.get("vTitle"));

            if (mapData.containsKey("isCheck")) {
                if (Objects.requireNonNull(mapData.get("isCheck")).equalsIgnoreCase("Yes")) {
                    selCatPos = position;
                }
            }

            String imageUrl;

            if (position == selCatPos) {
                imageUrl = mapData.get("vImage1");
                grideholder.mainArea.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(holder.itemView.getContext(), R.color.appThemeColor_1)));
                grideholder.txtCategoryName.setTextColor(ContextCompat.getColor(mContext, R.color.white));
            } else {
                imageUrl = mapData.get("vImage");
                grideholder.mainArea.setBackgroundTintList(null);
                grideholder.txtCategoryName.setTextColor(ContextCompat.getColor(mContext, R.color.black));
            }

            if (!Utils.checkText(imageUrl)) {
                imageUrl = "Temp";
            }
            new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, imageUrl, grideholder.imgCategory.getWidth(), grideholder.imgCategory.getHeight())), grideholder.imgCategory).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

            grideholder.mainArea.setOnClickListener(view -> {
                mItemClickListener.onCategoryClickList(position, selCatPos);
                selCatPos = position;
            });
        } else {

            //////////////////================================================================================================================================
            ListViewHolder listholder = (ListViewHolder) holder;
            listholder.txtSubCategoryName.setText(mapData.get("vTitle"));

            if (mapData.containsKey("isCheck")) {
                if (Objects.requireNonNull(mapData.get("isCheck")).equalsIgnoreCase("Yes")) {
                    selSubCatPos = position;
                }
            }

            if (position == selSubCatPos) {
                listholder.rowArea.setBackgroundColor(ContextCompat.getColor(mContext, R.color.rentSubCategorySelectColor));
                listholder.txtSubCategoryName.setTextColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1));
            } else {
                listholder.rowArea.setBackgroundColor(ContextCompat.getColor(mContext, R.color.white));
                listholder.txtSubCategoryName.setTextColor(ContextCompat.getColor(mContext, R.color.black));
            }

            listholder.rowArea.setOnClickListener(v -> {
                mItemClickListener.onSubCategoryClickList(position, selSubCatPos);
                selSubCatPos = position;
            });
            if (position == (getItemCount() - 1)) {
                listholder.viewLine.setVisibility(View.GONE);
            } else {
                listholder.viewLine.setVisibility(View.VISIBLE);
            }
            if (generalFunc.isRTLmode()) {
                listholder.imgArrow.setRotation(180);
            }
        }
    }

    private static class GridViewHolder extends RecyclerView.ViewHolder {

        private final MTextView txtCategoryName;
        private final ImageView imgCategory;
        private final LinearLayout mainArea;

        private GridViewHolder(View itemView) {
            super(itemView);

            txtCategoryName = itemView.findViewById(R.id.txtCategoryName);
            imgCategory = itemView.findViewById(R.id.imgCategory);
            mainArea = itemView.findViewById(R.id.mainArea);
        }
    }

    private static class ListViewHolder extends RecyclerView.ViewHolder {

        private final LinearLayout rowArea;
        private final MTextView txtSubCategoryName;
        private final ImageView imgArrow;
        private final View viewLine;

        private ListViewHolder(View itemView) {
            super(itemView);

            rowArea = itemView.findViewById(R.id.rowArea);
            txtSubCategoryName = itemView.findViewById(R.id.txtSubCategoryName);
            imgArrow = itemView.findViewById(R.id.imgArrow);
            viewLine = itemView.findViewById(R.id.viewLine);
        }
    }

    @Override
    public int getItemViewType(int position) {
        String rowType = list.get(position).containsKey("showType") ? list.get(position).get("showType") : "";
        if (rowType != null && rowType.equalsIgnoreCase("GRID")) {
            return TYPE_GRID;
        } else {
            return 0;
        }

    }

    public void resetSubCatPos() {
        this.selSubCatPos = -1;
    }

    public interface OnItemClickListener {
        void onCategoryClickList(int position, int selCatPos);

        void onSubCategoryClickList(int position, int selSubCatPos);
    }

    @Override
    public int getItemCount() {
        return list.size();
    }
}