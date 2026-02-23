package com.adapter.files.deliverAll;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.homescreen23.adapter.HomeUtils;
import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.SafetyDialog;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemStoreGrid24Binding;
import com.buddyverse.main.databinding.ItemStoreList24Binding;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.jetbrains.annotations.Nullable;
import org.json.JSONArray;
import org.json.JSONObject;

public class StoreAdapter24 extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM_GRID = 0, TYPE_ITEM_LIST = 1;
    private final ParentActivity act;
    private final GeneralFunctions generalFunc;
    private final StoreOnClickListener listener;
    private JSONArray mArray;
    private final int orientation;
    private final int _23sdp, _80sdp;
    private int itemWidth, itemHeight;

    private final String LBL_DELIVERY_TIME, LBL_MIN_ORDER_TXT, LBL_CLOSED_TXT, LBL_NOT_ACCEPT_ORDERS_TXT, LBL_SAFETY_NOTE_TITLE_LIST, ENABLE_FAVORITE_STORE_MODULE;

    public StoreAdapter24(@NonNull ParentActivity activity, @NonNull JSONArray mArray, int orientation, @Nullable StoreOnClickListener listener) {
        this.act = activity;
        this.generalFunc = activity.generalFunc;
        this.mArray = mArray;
        this.orientation = orientation;
        this.listener = listener;

        this.ENABLE_FAVORITE_STORE_MODULE = generalFunc.getJsonValueStr("ENABLE_FAVORITE_STORE_MODULE", activity.obj_userProfile);

        this.LBL_DELIVERY_TIME = generalFunc.retrieveLangLBl("", "LBL_DELIVERY_TIME");
        this.LBL_MIN_ORDER_TXT = generalFunc.retrieveLangLBl("", "LBL_MIN_ORDER_TXT");
        this.LBL_CLOSED_TXT = generalFunc.retrieveLangLBl("", "LBL_CLOSED_TXT");
        this.LBL_NOT_ACCEPT_ORDERS_TXT = generalFunc.retrieveLangLBl("", "LBL_NOT_ACCEPT_ORDERS_TXT");
        this.LBL_SAFETY_NOTE_TITLE_LIST = generalFunc.retrieveLangLBl("", "LBL_SAFETY_NOTE_TITLE_LIST");

        _23sdp = act.getResources().getDimensionPixelSize(R.dimen._23sdp);
        _80sdp = act.getResources().getDimensionPixelSize(R.dimen._80sdp);

        //
        if (orientation == LinearLayoutManager.HORIZONTAL) {
            itemWidth = (int) Utils.getScreenPixelWidth(act);
            itemWidth = (int) (itemWidth / 1.9);
            itemHeight = RelativeLayout.LayoutParams.WRAP_CONTENT;
        } else {
            itemWidth = RelativeLayout.LayoutParams.MATCH_PARENT;
            itemHeight = RelativeLayout.LayoutParams.WRAP_CONTENT;
        }
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_ITEM_GRID) {
            return new GridViewHolder(ItemStoreGrid24Binding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new ListViewHolder(ItemStoreList24Binding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    private void setItemWidth(@NonNull View viewArea) {
        viewArea.setLayoutParams(new RecyclerView.LayoutParams(itemWidth, itemHeight));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        JSONObject itemObj = generalFunc.getJsonObject(mArray, position);
        if (holder instanceof GridViewHolder vHolder) {
            setItemWidth(vHolder.binding.mainArea);

            imageArea(vHolder.binding.imgArea, vHolder.binding.imgView, itemObj, position);
            companyName(vHolder.binding.restaurantNameTxt, itemObj);
            favStoreArea(vHolder.binding.favImage, itemObj);

            ratingArea(vHolder.binding.restaurantRateTxt, vHolder.binding.ratingArea, itemObj);
            commonMText(vHolder.binding.RestCuisineTXT, vHolder.binding.pricePerPersonTxt, vHolder.binding.minOrderTxt, itemObj);
            commonIfArea(vHolder.binding.deliveryTimeTxt, generalFunc.getJsonValueStr("Restaurant_OrderPrepareTime", itemObj));

            common2(vHolder.binding.deliveryLBLTimeTxt, vHolder.binding.minOrderLBLTxt, vHolder.binding.perpersonlayout);
            commonIfArea1(vHolder.binding.offerTxt, vHolder.binding.offerArea, generalFunc.getJsonValueStr("Restaurant_OfferMessage", itemObj));

            storeStatusArea(vHolder.binding.resStatusTxt, itemObj);
            safetyUrlArea(vHolder.binding.resSsafetyTxt, vHolder.binding.safetyArea, vHolder.binding.safetylayout, vHolder.binding.safetyImage, itemObj);

            itemClick(vHolder.binding.restaurantAdptrLayout, itemObj);

        } else if (holder instanceof ListViewHolder vHolder) {
            setItemWidth(vHolder.binding.mainArea);

            imageArea(vHolder.binding.imgArea, vHolder.binding.imgView, itemObj, position);
            companyName(vHolder.binding.restaurantNameTxt, itemObj);
            favStoreArea(vHolder.binding.favImage, itemObj);

            ratingArea(vHolder.binding.restaurantRateTxt, vHolder.binding.ratingArea, itemObj);
            commonMText(vHolder.binding.RestCuisineTXT, vHolder.binding.pricePerPersonTxt, vHolder.binding.minOrderTxt, itemObj);
            commonIfArea(vHolder.binding.deliveryTimeTxt, generalFunc.getJsonValueStr("Restaurant_OrderPrepareTime", itemObj));

            common2(vHolder.binding.deliveryLBLTimeTxt, vHolder.binding.minOrderLBLTxt, vHolder.binding.perpersonlayout);
            commonIfArea1(vHolder.binding.offerTxt, vHolder.binding.offerArea, generalFunc.getJsonValueStr("Restaurant_OfferMessage", itemObj));

            storeStatusArea(vHolder.binding.resStatusTxt, itemObj);
            safetyUrlArea(vHolder.binding.resSsafetyTxt, vHolder.binding.safetyArea, vHolder.binding.safetylayout, vHolder.binding.safetyImage, itemObj);

            itemClick(vHolder.binding.restaurantAdptrLayout, itemObj);
        }
    }

    private void itemClick(@NonNull View itemArea, @NonNull JSONObject itemObj) {
        itemArea.setOnClickListener(v -> {
            if (listener != null) {
                listener.onItemStoreClick(itemObj);
            }
        });
    }

    private void imageArea(RelativeLayout imgArea, @NonNull AppCompatImageView imgView, @NonNull JSONObject itemObj, int position) {
        //
        int imgW;
        if (getItemViewType(position) == TYPE_ITEM_GRID) {
            imgW = itemWidth - _23sdp;
        } else {
            imgW = _80sdp;
        }
        int imgH = (int) (imgW / 1.33333333333);

        //
        LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) imgArea.getLayoutParams();
        mParams.width = imgW;
        mParams.height = imgH;
        imgArea.setLayoutParams(mParams);
        //
        String mUrl = generalFunc.getJsonValueStr("vImage", itemObj);
        HomeUtils.loadImg(act, imgView, mUrl, R.color.imageBg, true, imgW, imgH);
    }

    private void companyName(@NonNull MTextView mTextView, @NonNull JSONObject itemObj) {
        mTextView.setText(generalFunc.getJsonValueStr("vCompany", itemObj));
        mTextView.setSelected(true);
    }

    private void favStoreArea(@NonNull ImageView imageView, @NonNull JSONObject itemObj) {
        String fav = generalFunc.getJsonValueStr("eFavStore", itemObj);
        if (ENABLE_FAVORITE_STORE_MODULE.equalsIgnoreCase("Yes") && Utils.checkText(fav) && fav.equalsIgnoreCase("Yes")) {
            imageView.setVisibility(View.VISIBLE);
        } else {
            imageView.setVisibility(View.GONE);
        }
    }

    private void ratingArea(@NonNull MTextView mTextView, @NonNull View areaView1, @NonNull JSONObject itemObj) {
        String rating = generalFunc.getJsonValueStr("vAvgRating", itemObj);
        if (Utils.checkText(rating) && !rating.equalsIgnoreCase("0")) {
            mTextView.setText(generalFunc.convertNumberWithRTL(rating));
            areaView1.setVisibility(View.VISIBLE);
        } else {
            areaView1.setVisibility(View.GONE);
        }
    }

    private void commonIfArea(@NonNull MTextView mTextView, @NonNull String mValue) {
        if (Utils.checkText(mValue)) {
            mTextView.setText(mValue);
            mTextView.setVisibility(View.VISIBLE);
        } else {
            mTextView.setVisibility(View.GONE);
        }
    }

    private void commonIfArea1(@NonNull MTextView mTextView, @NonNull View areaView1, @NonNull String mValue) {
        if (Utils.checkText(mValue)) {
            mTextView.setText(mValue);
            mTextView.setSelected(true);
            areaView1.setVisibility(View.VISIBLE);
        } else {
            areaView1.setVisibility(View.INVISIBLE);
        }
    }

    private void commonMText(@NonNull MTextView mTextView1, @NonNull MTextView mTextView2, @NonNull MTextView mTextView3, @NonNull JSONObject itemObj) {
        mTextView1.setText(generalFunc.getJsonValueStr("Restaurant_Cuisine", itemObj));
        mTextView2.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("Restaurant_PricePerPerson", itemObj)));
        mTextView3.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("Restaurant_MinOrderValue_Orig", itemObj)));
    }

    private void common2(@NonNull MTextView mTextView1, @NonNull MTextView mTextView2, @NonNull View areaView) {
        mTextView1.setText(LBL_DELIVERY_TIME);
        mTextView2.setText(LBL_MIN_ORDER_TXT);
        if (generalFunc.getServiceId().equals("1") || generalFunc.getServiceId().equalsIgnoreCase("")) {
            areaView.setVisibility(View.VISIBLE);
        } else {
            areaView.setVisibility(View.INVISIBLE);
        }
    }

    @SuppressLint("SetTextI18n")
    private void storeStatusArea(@NonNull MTextView mTextView, @NonNull JSONObject itemObj) {
        if (generalFunc.getJsonValueStr("Restaurant_Status", itemObj).equalsIgnoreCase("Closed")) {
            mTextView.setVisibility(View.VISIBLE);
            if (generalFunc.getJsonValueStr("timeslotavailable", itemObj).equalsIgnoreCase("Yes")) {
                if (generalFunc.getJsonValueStr("eAvailable", itemObj).equalsIgnoreCase("No")) {
                    mTextView.setText(LBL_NOT_ACCEPT_ORDERS_TXT);
                }
            } else {
                String openTime = generalFunc.getJsonValueStr("Restaurant_Opentime", itemObj);
                if (Utils.checkText(openTime)) {
                    mTextView.setText(LBL_CLOSED_TXT + ": " + generalFunc.convertNumberWithRTL(openTime));
                } else {
                    mTextView.setText(LBL_CLOSED_TXT);
                }
            }
            mTextView.setSelected(true);
            mTextView.setTextColor(ContextCompat.getColor(act, R.color.redlight));
        } else {
            mTextView.setVisibility(View.INVISIBLE);
        }
    }

    private void safetyUrlArea(@NonNull MTextView mTextView, @NonNull View areaView1, @NonNull View areaView2, @NonNull AppCompatImageView imageView, @NonNull JSONObject itemObj) {

        if (generalFunc.getJsonValueStr("Restaurant_Safety_Status", itemObj).equalsIgnoreCase("Yes")) {
            mTextView.setText(LBL_SAFETY_NOTE_TITLE_LIST);
            areaView1.setVisibility(View.VISIBLE);
            areaView2.setVisibility(View.GONE);

            int grid = act.getResources().getDimensionPixelSize(R.dimen.fab_margin);
            String mUrl = generalFunc.getJsonValueStr("Restaurant_Safety_Icon", itemObj);
            HomeUtils.loadImg(act, imageView, mUrl, R.drawable.ic_safety, true, grid, grid);
            areaView1.setOnClickListener(v -> {

                Intent intent = new Intent(act, SafetyDialog.class);
                intent.putExtra("URL", generalFunc.getJsonValueStr("Restaurant_Safety_URL", itemObj));
                new ActUtils(act).startAct(intent);
                act.overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
            });
        } else {
            areaView1.setVisibility(View.GONE);
            areaView2.setVisibility(View.GONE);
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (orientation == LinearLayoutManager.HORIZONTAL) {
            return TYPE_ITEM_GRID;
        }
        return TYPE_ITEM_LIST;
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

    /////////////////////////////-----------------
    protected static class GridViewHolder extends RecyclerView.ViewHolder {
        private final ItemStoreGrid24Binding binding;

        private GridViewHolder(ItemStoreGrid24Binding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class ListViewHolder extends RecyclerView.ViewHolder {
        private final ItemStoreList24Binding binding;

        private ListViewHolder(ItemStoreList24Binding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface StoreOnClickListener {
        void onItemStoreClick(@NonNull JSONObject itemObj);
    }
}