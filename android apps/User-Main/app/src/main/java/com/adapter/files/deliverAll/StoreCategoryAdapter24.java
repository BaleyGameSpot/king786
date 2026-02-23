package com.adapter.files.deliverAll;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.PagerSnapHelper;
import androidx.recyclerview.widget.RecyclerView;
import androidx.recyclerview.widget.SnapHelper;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.general.files.KmRecyclerView;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemRestaurantListHeaderDesign24Binding;
import com.buddyverse.main.databinding.ItemStoreCategory24Binding;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.jetbrains.annotations.Nullable;
import org.json.JSONArray;
import org.json.JSONObject;

public class StoreCategoryAdapter24 extends RecyclerView.Adapter<RecyclerView.ViewHolder> implements KmRecyclerView.KmStickyListener {

    private static final int TYPE_HEADER = 0, TYPE_ITEM = 1;
    private final ParentActivity act;
    private final GeneralFunctions generalFunc;
    @Nullable
    private final StoreAdapter24.StoreOnClickListener listener;
    private JSONArray mArray;
    private SnapHelper mSnapHelper;
    private final String LBL_SEE_ALL;

    public StoreCategoryAdapter24(@NonNull ParentActivity activity, @NonNull JSONArray mArray, @Nullable StoreAdapter24.StoreOnClickListener listener) {
        this.act = activity;
        this.generalFunc = activity.generalFunc;
        this.mArray = mArray;
        this.listener = listener;

        this.LBL_SEE_ALL = generalFunc.retrieveLangLBl("", "LBL_SEE_ALL");
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_HEADER) {
            return new HeaderViewHolder(ItemRestaurantListHeaderDesign24Binding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new ViewHolder(ItemStoreCategory24Binding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        if (holder instanceof HeaderViewHolder vHolder) {
            JSONObject itemObj = generalFunc.getJsonObject(mArray, position + 1);

            // Header Area
            headerDataSet(vHolder.binding.cateTitleTxt, vHolder.binding.cateDescTxt, vHolder.binding.seeAllTxt, itemObj);

        } else if (holder instanceof ViewHolder vHolder) {
            JSONObject itemObj = generalFunc.getJsonObject(mArray, position);
            // list Area
            JSONArray subDataArr = generalFunc.getJsonArray("subData", itemObj);
            if (subDataArr != null && subDataArr.length() > 0) {
                LinearLayoutManager mLayoutManager;
                int orientation;
                if (generalFunc.getJsonValueStr("eType", itemObj).equalsIgnoreCase("list_all")) {
                    orientation = LinearLayoutManager.VERTICAL;
                    mLayoutManager = new LinearLayoutManager(act, LinearLayoutManager.VERTICAL, false);
                } else {
                    if (mSnapHelper == null) {
                        mSnapHelper = new PagerSnapHelper();
                    }
                    mSnapHelper.attachToRecyclerView(null);
                    mSnapHelper.attachToRecyclerView(vHolder.binding.rvStoreList);

                    orientation = GridLayoutManager.HORIZONTAL;
                    mLayoutManager = new GridLayoutManager(act, subDataArr.length() > 1 ? 2 : 1, GridLayoutManager.HORIZONTAL, false);
                }
                StoreAdapter24 mCategoryWishStoreAdapter = new StoreAdapter24(act, subDataArr, orientation, listener);
                vHolder.binding.rvStoreList.setLayoutManager(mLayoutManager);
                vHolder.binding.rvStoreList.setAdapter(mCategoryWishStoreAdapter);
            }
        }
    }

    @Override
    public int getItemViewType(int position) {
        JSONObject itemObj = generalFunc.getJsonObject(mArray, position);
        if (generalFunc.getJsonValueStr("isHeader", itemObj).equalsIgnoreCase("Yes")) {
            return TYPE_HEADER;
        }
        return TYPE_ITEM;
    }

    @Override
    public int getItemCount() {
        return mArray != null ? mArray.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(@Nullable JSONArray mArray) {
        this.mArray = mArray;
        notifyDataSetChanged();
    }

    @Override
    public int getHeaderPositionForItem(int itemPosition) {
        int headerPosition = 0;
        for (int i = itemPosition; i > 0; i--) {
            if (isHeader(i)) {
                headerPosition = i;
                return headerPosition;
            }
        }
        return headerPosition;
    }

    @Override
    public int getHeaderLayout(int headerPosition) {
        return R.layout.item_restaurant_list_header_design_24;
    }

    @Override
    public void bindHeaderData(View viewH, int headerPosition) {
        MTextView cateTitleTxt = viewH.findViewById(R.id.cateTitleTxt);
        MTextView cateDescTxt = viewH.findViewById(R.id.cateDescTxt);
        MTextView seeAllTxt = viewH.findViewById(R.id.seeAllTxt);

        JSONObject itemObj = generalFunc.getJsonObject(mArray, headerPosition + 1);
        headerDataSet(cateTitleTxt, cateDescTxt, seeAllTxt, itemObj);
    }

    private void headerDataSet(MTextView catetitleTxt, MTextView catedescTxt, MTextView seeAllTxt, @NonNull JSONObject itemObj) {
        if (getItemCount() == 0) {
            return;
        }
        catetitleTxt.setText(generalFunc.getJsonValueStr("vTitle", itemObj));
        String vDescription = generalFunc.getJsonValueStr("vDescription", itemObj);
        if (Utils.checkText(vDescription)) {
            catedescTxt.setText(vDescription);
            catedescTxt.setVisibility(View.VISIBLE);
        } else {
            catedescTxt.setVisibility(View.GONE);
        }

        if (generalFunc.getJsonValueStr("IS_SHOW_ALL", itemObj).equalsIgnoreCase("Yes")) {
            seeAllTxt.setText(LBL_SEE_ALL);
            seeAllTxt.setVisibility(View.VISIBLE);
        } else {
            seeAllTxt.setVisibility(View.GONE);
        }
        seeAllTxt.setOnClickListener(view -> {
            if (listener != null) {
                listener.onItemStoreClick(itemObj);
            }
        });
    }

    @Override
    public boolean isHeader(int itemPosition) {
        return getItemViewType(itemPosition) == (TYPE_HEADER);
    }

    /////////////////////////////-----------------
    protected static class HeaderViewHolder extends RecyclerView.ViewHolder {
        private final ItemRestaurantListHeaderDesign24Binding binding;

        private HeaderViewHolder(ItemRestaurantListHeaderDesign24Binding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemStoreCategory24Binding binding;

        private ViewHolder(ItemStoreCategory24Binding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}