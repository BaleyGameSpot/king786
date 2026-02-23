package com.adapter.files.deliverAll;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.PagerSnapHelper;
import androidx.recyclerview.widget.RecyclerView;
import androidx.recyclerview.widget.SnapHelper;

import com.general.files.GeneralFunctions;
import com.general.files.KmRecyclerView;
import com.buddyverse.main.R;
import com.model.MainCategoryModel;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;

public class MainCategoryAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> implements KmRecyclerView.KmStickyListener {

    private static final int TYPE_HEADER = 0;
    private static final int TYPE_ITEM = 1;
    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final String LBL_SEE_ALL;
    private final ArrayList<MainCategoryModel> list;
    private final CategoryOnClickListener categoryOnClickListener;
    private final RestaurantChildAdapter.RestaurantOnClickListener restaurantOnClickListener;
    private final RecyclerView.RecycledViewPool viewPool = new RecyclerView.RecycledViewPool();

    public MainCategoryAdapter(Context mContext, ArrayList<MainCategoryModel> list, GeneralFunctions generalFunc, CategoryOnClickListener categoryOnClickListener, RestaurantChildAdapter.RestaurantOnClickListener restaurantOnClickListener) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.categoryOnClickListener = categoryOnClickListener;
        this.restaurantOnClickListener = restaurantOnClickListener;
        this.LBL_SEE_ALL = generalFunc.retrieveLangLBl("", "LBL_SEE_ALL");
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        if (viewType == TYPE_HEADER) {
            return new ViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_restaurant_list_header_design_23, parent, false));
        } else {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_category_child, parent, false);
            ViewHolder viewHolder = new ViewHolder(view);
            viewHolder.child_category_rec.setRecycledViewPool(viewPool);
            return viewHolder;
        }
    }


    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        ViewHolder viewH = (ViewHolder) holder;
        if (list.get(position).getViewType() == MainCategoryModel.ViewType.Header) {

            HeaderDataSet(viewH.catetitleTxt, viewH.catedescTxt, viewH.seeAllTxt, viewH.seeAllImg, position);

            return;
        }

        if (viewH.child_category_rec != null) {

            viewH.child_category_rec.setAdapter(null);
            RestaurantChildAdapter restaurantAdapter;
            if (list.get(position).geteType().equalsIgnoreCase("list_all")) {
                restaurantAdapter = new RestaurantChildAdapter(mContext, generalFunc, list.get(position).getList(), position, LinearLayoutManager.VERTICAL, false);
                restaurantAdapter.setOnRestaurantItemClick(restaurantOnClickListener);
                LinearLayoutManager layoutManager = new LinearLayoutManager(mContext, LinearLayoutManager.VERTICAL, false);
                viewH.child_category_rec.setLayoutManager(layoutManager);
            } else {
                //Removing orientation condition to show layout like new ITEM_23 TYPE if its not comes under "list_all" type;
//                restaurantAdapter = new RestaurantChildAdapter(mContext, generalFunc, list.get(position).getList(), position, list.get(position).getList().size() > 2 ? LinearLayoutManager.HORIZONTAL : LinearLayoutManager.VERTICAL, false);
                restaurantAdapter = new RestaurantChildAdapter(mContext, generalFunc, list.get(position).getList(), position, LinearLayoutManager.HORIZONTAL, false);
                restaurantAdapter.setOnRestaurantItemClick(restaurantOnClickListener);
                GridLayoutManager mLayoutManager = new GridLayoutManager(mContext, list.get(position).getList().size() > 1 ? 2 : 1, GridLayoutManager.HORIZONTAL, false);
                viewH.child_category_rec.setLayoutManager(mLayoutManager);
            }
            viewH.child_category_rec.setAdapter(restaurantAdapter);
        }

    }

    @Override
    public int getItemViewType(int position) {
        if (list.size() <= 0) {
            return TYPE_HEADER;
        }
        if (list.get(position).getViewType() == MainCategoryModel.ViewType.Header) {
            return TYPE_HEADER;
        }
        return TYPE_ITEM;
    }

    @Override
    public int getItemCount() {
        return list.size();
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
        return R.layout.item_restaurant_list_header_design_23;
    }

    @Override
    public void bindHeaderData(View viewH, int headerPosition) {
        MTextView catetitleTxt = viewH.findViewById(R.id.catetitleTxt);
        MTextView catedescTxt = viewH.findViewById(R.id.catedescTxt);
        MTextView seeAllTxt = viewH.findViewById(R.id.seeAllTxt);
        ImageView seeAllImg = viewH.findViewById(R.id.seeAllImg);
        HeaderDataSet(catetitleTxt, catedescTxt, seeAllTxt, seeAllImg, headerPosition);
    }

    @Override
    public boolean isHeader(int itemPosition) {
        return getItemViewType(itemPosition) == (TYPE_HEADER);
    }

    public interface CategoryOnClickListener {
        void setOnCategoryClick(int position);
    }

    private void HeaderDataSet(MTextView catetitleTxt, MTextView catedescTxt, MTextView seeAllTxt, ImageView seeAllImg, int position) {
        if (list.size() <= 0) {
            return;
        }
        catetitleTxt.setText(list.get(position).getvTitle());
        if (list.get(position).getvDescription() != null && !list.get(position).getvDescription().isEmpty()) {
            catedescTxt.setText(list.get(position).getvDescription());
            catedescTxt.setVisibility(View.VISIBLE);
        } else {
            catedescTxt.setVisibility(View.GONE);
        }

        seeAllTxt.setText(LBL_SEE_ALL);

        if (list.get(position).getIsShowall().equalsIgnoreCase("Yes")) {
            seeAllTxt.setVisibility(View.VISIBLE);
//            seeAllImg.setVisibility(View.VISIBLE);
        } else {
            seeAllTxt.setVisibility(View.GONE);
            seeAllImg.setVisibility(View.GONE);
        }
        if (generalFunc.isRTLmode()) {
            seeAllImg.setRotation(180);
        }

        seeAllTxt.setOnClickListener(view -> categoryOnClickListener.setOnCategoryClick(position));
        seeAllImg.setOnClickListener(view -> categoryOnClickListener.setOnCategoryClick(position));
    }

    public class ViewHolder extends RecyclerView.ViewHolder {

        private final MTextView catetitleTxt, seeAllTxt, catedescTxt;
        private final ImageView seeAllImg;
        private final RecyclerView child_category_rec;

        public ViewHolder(View itemView) {
            super(itemView);
            catetitleTxt = itemView.findViewById(R.id.catetitleTxt);
            catedescTxt = itemView.findViewById(R.id.catedescTxt);
            seeAllTxt = itemView.findViewById(R.id.seeAllTxt);
            seeAllImg = itemView.findViewById(R.id.seeAllImg);
            child_category_rec = itemView.findViewById(R.id.child_category_rec);

            if (child_category_rec != null) {
                SnapHelper mSnapHelper = new PagerSnapHelper();
                mSnapHelper.attachToRecyclerView(child_category_rec);
            }
        }
    }

}