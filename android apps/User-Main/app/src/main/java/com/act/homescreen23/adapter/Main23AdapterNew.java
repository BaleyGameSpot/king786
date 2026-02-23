package com.act.homescreen23.adapter;

import android.annotation.SuppressLint;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.graphics.drawable.Drawable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.PagerSnapHelper;
import androidx.recyclerview.widget.RecyclerView;
import androidx.recyclerview.widget.SnapHelper;

import com.act.UberXHomeActivity;
import com.act.homescreen24.adapter.CardIconTextView24Adapter;
import com.act.homescreen24.adapter.IconTextView24Adapter;
import com.act.homescreen24.adapter.StoreItemView24Adapter;
import com.general.files.AutoSlideView;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityUberXhome23RideDeliveryBinding;
import com.buddyverse.main.databinding.Item23GridListProdeliveryonlyBinding;
import com.buddyverse.main.databinding.Item23ListBuySellRentOnlyBinding;
import com.buddyverse.main.databinding.Item23RecentLocationViewBinding;
import com.buddyverse.main.databinding.Item23RecyclerViewBinding;
import com.buddyverse.main.databinding.Item23TitleViewBinding;
import com.buddyverse.main.databinding.Item24SerachBarBinding;
import com.model.ServiceModule;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class Main23AdapterNew extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_TITLE = 0;
    private final int TYPE_BANNER = 1;
    private final int TYPE_BANNER_TEXT = 2;
    private final int TYPE_GRID = 3;
    private final int TYPE_GRID_BANNER_TEXT = 4;
    private final int TYPE_GRID_ICON_VIEW = 5;
    private final int TYPE_LIST = 6;
    private final int TYPE_RECENT_LOCATION = 7;
    private final int TYPE_NEW_LISTING_VIEW = 8;
    private final int TYPE_SEARCHBAR_VIEW = 9;
    private final int TYPE_ICON_TEXT_VIEW = 10;
    private final int TYPE_CARD_ICON_TEXT_VIEW = 11;
    private final int TYPE_LIST_BTN_VIEW = 12;
    private final int TYPE_STORE_ITEM_VIEW = 13;

    private final UberXHomeActivity mActivity;
    @Nullable
    private JSONArray mainArray;
    private final OnClickListener listener;
    private SnapHelper mSnapHelper;
    private String mResponseString;
    @Nullable
    private AutoSlideView mAutoSlideView;

    public Main23AdapterNew(@NonNull UberXHomeActivity activity, @NonNull JSONArray list, @NonNull OnClickListener listener) {
        this.mActivity = activity;
        this.mainArray = list;
        this.listener = listener;
    }

    public void setResponseString(String responseString) {
        this.mResponseString = responseString;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_TITLE) {
            return new TitleViewHolder(Item23TitleViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_BANNER) {
            return new BannerViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_BANNER_TEXT) {
            return new BannerTextViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_GRID) {
            return new GridViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_GRID_BANNER_TEXT) {
            return new GridTextBannerViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_GRID_ICON_VIEW) {
            return new GridIconViewHolder(Item23GridListProdeliveryonlyBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_LIST) {
            return new ListViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_RECENT_LOCATION) {
            return new RecentLocationViewHolder(Item23RecentLocationViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_NEW_LISTING_VIEW) {
            return new NewListingViewHolder(Item23ListBuySellRentOnlyBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_SEARCHBAR_VIEW) {
            return new SearchViewHolder(Item24SerachBarBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_ICON_TEXT_VIEW) {
            return new IconTextViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_CARD_ICON_TEXT_VIEW) {
            return new CardIconTextViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_LIST_BTN_VIEW) {
            return new ListBtnViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_STORE_ITEM_VIEW) {
            return new StoreItemViewHolder(Item23RecyclerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else {
            return new TitleViewHolder(Item23TitleViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(final @NonNull RecyclerView.ViewHolder holder, final int position) {

        JSONObject itemObject = mActivity.generalFunc.getJsonObject(mainArray, position);
        JSONObject layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", itemObject);

        if (holder instanceof TitleViewHolder vHolder) {

            HomeUtils.setSpace(mActivity, vHolder.binding.mainArea, layoutDetailsObj);

            String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", itemObject);
            String vSubtitle = mActivity.generalFunc.getJsonValueStr("vSubtitle", itemObject);
            String seeAllTxt = mActivity.generalFunc.getJsonValueStr("SeeAllTxt", itemObject);

            String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
            String vSubTitleFont = mActivity.generalFunc.getJsonValueStr("vSubTitleFont", layoutDetailsObj);
            String SeeAllTxtFont = mActivity.generalFunc.getJsonValueStr("SeeAllTxtFont", layoutDetailsObj);

            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);
            String vTxtSubTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtSubTitleColor", layoutDetailsObj);
            String SeeAllTxtColor = mActivity.generalFunc.getJsonValueStr("SeeAllTxtColor", layoutDetailsObj);

            HomeUtils.manageTextView(mActivity, vHolder.binding.titleTxt, vTitle, vTitleFont, vTxtTitleColor);
            HomeUtils.manageTextView(mActivity, vHolder.binding.subTitleTxt, vSubtitle, vSubTitleFont, vTxtSubTitleColor);
            HomeUtils.manageTextView(mActivity, vHolder.binding.seeAllTxt, seeAllTxt, SeeAllTxtFont, SeeAllTxtColor);

            if (Utils.checkText(seeAllTxt)) {
                vHolder.binding.seeAllTxt.setOnClickListener(v -> listener.onSeeAllClick(position, itemObject));
            }

            JSONObject moreObj = mActivity.generalFunc.getJsonObject("moreData", itemObject);
            if (moreObj != null) {
                vHolder.binding.titleTxt.setOnClickListener(v -> listener.onItemClick(position, moreObj));
                vHolder.binding.moreImg.setOnClickListener(v -> listener.onItemClick(position, moreObj));
                vHolder.binding.moreImg.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.titleTxt.setOnClickListener(null);
                vHolder.binding.moreImg.setVisibility(View.GONE);
            }

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof BannerViewHolder vHolder) {
            // Banner View
            HomeUtils.setSpace(mActivity, vHolder.binding.rvView, layoutDetailsObj);
            vHolder.binding.rvView.setLayoutManager(new LinearLayoutManager(mActivity, LinearLayoutManager.HORIZONTAL, false));

            BannerView23Adapter adapter = new BannerView23Adapter(mActivity, itemObject, listener::onItemClick);

            int displayCount = GeneralFunctions.parseIntegerValue(0, mActivity.generalFunc.getJsonValueStr("displayCount", layoutDetailsObj));
            String isCarousel = mActivity.generalFunc.getJsonValueStr("isCarousel", layoutDetailsObj);
            if (displayCount == 1 && isCarousel.equalsIgnoreCase("Yes")) {
                if (mSnapHelper == null) {
                    mSnapHelper = new PagerSnapHelper();
                }
                mSnapHelper.attachToRecyclerView(null);
                mSnapHelper.attachToRecyclerView(vHolder.binding.rvView);

                if (mAutoSlideView == null) {
                    mAutoSlideView = new AutoSlideView(5 * 1000);
                }
                mAutoSlideView.nextPosition = 0;
                mAutoSlideView.setAutoSlideRV(vHolder.binding.rvView);
            }

            vHolder.binding.rvView.setAdapter(adapter);

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof BannerTextViewHolder vHolder) {
            // Banner Text View
            HomeUtils.setSpace(mActivity, vHolder.binding.rvView, layoutDetailsObj);

            vHolder.binding.rvView.setLayoutManager(new GridLayoutManager(mActivity, mActivity.generalFunc.getJsonArray("imagesArr", itemObject).length()));
            vHolder.binding.rvView.setAdapter(new BannerTextView23Adapter(mActivity, itemObject, listener::onItemClick));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof GridViewHolder vHolder) {

            if (mActivity.generalFunc.getJsonArray("servicesArr", itemObject) == null) {
                return;
            }

            // Grid View
            HomeUtils.setSpace(mActivity, vHolder.binding.rvView, layoutDetailsObj);

            LinearLayoutManager mLayoutManager;
            if (mActivity.generalFunc.getJsonValueStr("eLayoutType", itemObject).equalsIgnoreCase("vertical")) {
                if (mActivity.generalFunc.getJsonValueStr("isBoxStructure", layoutDetailsObj).equalsIgnoreCase("Yes")) {
                    mLayoutManager = new GridLayoutManager(mActivity, 3);

                } else if (mActivity.generalFunc.getJsonValueStr("isBoxImageView", layoutDetailsObj).equalsIgnoreCase("Yes")) {
                    mLayoutManager = new GridLayoutManager(mActivity, mActivity.generalFunc.getJsonArray("servicesArr", itemObject).length());

                } else {
                    mLayoutManager = new GridLayoutManager(mActivity, 4);
                }
            } else {
                mLayoutManager = new GridLayoutManager(mActivity, (mActivity.generalFunc.getJsonArray("servicesArr", itemObject).length() > 0 && mActivity.generalFunc.getJsonArray("servicesArr", itemObject).length() < 3) ? 3 : mActivity.generalFunc.getJsonArray("servicesArr", itemObject).length());
            }
            vHolder.binding.rvView.setLayoutManager(mLayoutManager);
            vHolder.binding.rvView.setAdapter(new GridView23Adapter(mActivity, itemObject, listener::onItemClick));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof GridTextBannerViewHolder vHolder) {
            // Grid Text Banner View
            HomeUtils.setSpace(mActivity, vHolder.binding.rvView, layoutDetailsObj);

            vHolder.binding.rvView.setLayoutManager(new GridLayoutManager(mActivity, mActivity.generalFunc.getJsonArray("imagesArr", itemObject).length()));
            vHolder.binding.rvView.setAdapter(new GridViewTextBanner23Adapter(mActivity, itemObject, listener::onItemClick));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof GridIconViewHolder vHolder) {
            // Grid Text Banner View
            HomeUtils.setSpace(mActivity, vHolder.binding.mainArea, layoutDetailsObj);

            String vBtnTxt = mActivity.generalFunc.getJsonValueStr("vBtnTxt", itemObject);
            String btnTxtFont = mActivity.generalFunc.getJsonValueStr("btnTxtFont", layoutDetailsObj);
            String btnTxtColor = mActivity.generalFunc.getJsonValueStr("btnTxtColor", layoutDetailsObj);

            HomeUtils.manageTextView(mActivity, vHolder.binding.sendParcelBtn, vBtnTxt, btnTxtFont, btnTxtColor);

            // Btn Area
            HomeUtils.btnArea(mActivity, vHolder.binding.sendParcelBtn, layoutDetailsObj);

            vHolder.binding.rvGridView.setLayoutManager(new GridLayoutManager(mActivity, 4));
            vHolder.binding.rvGridView.setAdapter(new GridView23Adapter(mActivity, itemObject, null));

            vHolder.binding.sendParcelBtn.setOnClickListener(view -> listener.onItemClick(position, mActivity.generalFunc.getJsonObject(mResponseString)));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof ListViewHolder vHolder) {
            // List View
            HomeUtils.setSpace(mActivity, vHolder.binding.rvView, layoutDetailsObj);

            vHolder.binding.rvView.setLayoutManager(new LinearLayoutManager(mActivity, LinearLayoutManager.HORIZONTAL, false));
            vHolder.binding.rvView.setAdapter(new List23Adapter(mActivity, itemObject, listener::onItemClick));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof RecentLocationViewHolder vHolder) {
            // where to area
            HomeUtils.setSpace(mActivity, vHolder.binding.mainArea, layoutDetailsObj);

            String vBgColor = mActivity.generalFunc.getJsonValueStr("vBgColor", layoutDetailsObj);

            // Where To
            String vTitle = mActivity.generalFunc.retrieveLangLBl("", "LBL_WHERE_TO");
            String vLocTitleFont = mActivity.generalFunc.getJsonValueStr("vLocTitleFont", layoutDetailsObj);
            String vLocTitleColor = mActivity.generalFunc.getJsonValueStr("vLocTitleColor", layoutDetailsObj);

            // Now Txt
            String nowTitle = mActivity.generalFunc.retrieveLangLBl("", "LBL_NOW");
            String nowFont = mActivity.generalFunc.getJsonValueStr("btnTxtFont", layoutDetailsObj);
            String nowColor = mActivity.generalFunc.getJsonValueStr("btnTxtColor", layoutDetailsObj);

            if (ServiceModule.isRideOnly()) {
                ActivityUberXhome23RideDeliveryBinding rideView = null;
                if (mActivity.homeDynamic_23_fragment != null) {
                    rideView = mActivity.homeDynamic_23_fragment.binding.rideDelivery23Area;
                }
//                else if (mActivity.homeDynamic_24_fragment != null) {
//                    rideView = mActivity.homeDynamic_24_fragment.binding.UFX23ProArea;
//                }

                if (rideView != null) {
                    rideView.whereTOArea.setVisibility(View.VISIBLE);

                    if (Utils.checkText(vBgColor)) {
                        rideView.whereTOArea.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(vBgColor)));
                    }

                    // Where To
                    HomeUtils.manageTextView(mActivity, rideView.whereToTxt, vTitle, vLocTitleFont, vLocTitleColor);

                    // Now Txt
                    HomeUtils.manageTextView(mActivity, rideView.nowTxt, nowTitle, nowFont, nowColor);
                    if (Utils.checkText(nowColor)) {
                        Drawable[] drawables = rideView.nowTxt.getCompoundDrawablesRelative();
                        for (Drawable drawable : drawables) {
                            if (drawable != null) {
                                drawable.setColorFilter(Color.parseColor(nowColor), PorterDuff.Mode.MULTIPLY);
                            }
                        }
                    }

                    // Now Btn Area
                    HomeUtils.btnArea(mActivity, rideView.nowBtnArea, layoutDetailsObj);

                    if (mActivity.generalFunc.getJsonValueStr("RIDE_LATER_BOOKING_ENABLED", mActivity.obj_userProfile).equalsIgnoreCase("Yes")) {
                        rideView.nowBtnArea.setVisibility(View.VISIBLE);
                    } else {
                        rideView.nowBtnArea.setVisibility(View.GONE);
                    }
                }


            }

            String whereToArea = mActivity.generalFunc.getJsonValueStr("isShowEnterLocation", itemObject);
            if (Utils.checkText(whereToArea) && whereToArea.equalsIgnoreCase("Yes")) {
                vHolder.binding.whereToArea.setVisibility(View.VISIBLE);
                if (Utils.checkText(vBgColor)) {
                    vHolder.binding.whereToArea.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(vBgColor)));
                }

                // Where To
                HomeUtils.manageTextView(mActivity, vHolder.binding.whereToTxt, vTitle, vLocTitleFont, vLocTitleColor);
                vHolder.binding.whereToTxt.setOnClickListener(v -> listener.onWhereToClick(position, itemObject));

                // Now Txt
                HomeUtils.manageTextView(mActivity, vHolder.binding.nowTxt, nowTitle, nowFont, nowColor);
                if (Utils.checkText(nowColor)) {
                    Drawable[] drawables = vHolder.binding.nowTxt.getCompoundDrawablesRelative();
                    for (Drawable drawable : drawables) {
                        if (drawable != null) {
                            drawable.setColorFilter(Color.parseColor(nowColor), PorterDuff.Mode.SRC_IN);
                        }
                    }
                }

                // Now Btn Area
                HomeUtils.btnArea(mActivity, vHolder.binding.nowBtnArea, layoutDetailsObj);

                vHolder.binding.nowBtnArea.setOnClickListener(v -> listener.onNowClick(position, itemObject));
                if (mActivity.generalFunc.getJsonValueStr("RIDE_LATER_BOOKING_ENABLED", mActivity.obj_userProfile).equalsIgnoreCase("Yes")) {
                    vHolder.binding.nowBtnArea.setVisibility(View.VISIBLE);
                } else {
                    vHolder.binding.nowBtnArea.setVisibility(View.GONE);
                }

            } else {
                vHolder.binding.whereToArea.setVisibility(View.GONE);
            }

            /// recent list data
            JSONArray dArray = mActivity.generalFunc.getJsonArray("DestinationLocations", itemObject);
            if (dArray != null && dArray.length() > 0) {
                RecentLocationList23Adapter mAdapter = new RecentLocationList23Adapter(mActivity, mActivity.generalFunc, itemObject, listener::onItemClick);
                vHolder.binding.rvRecentLocationList.setAdapter(mAdapter);
            }

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof NewListingViewHolder vHolder) {

            //New Listing
            HomeUtils.setSpace(mActivity, vHolder.binding.mainArea, layoutDetailsObj);

            String vBgColor = mActivity.generalFunc.getJsonValueStr("vBgColor", layoutDetailsObj);
            if (Utils.checkText(vBgColor)) {
                vHolder.binding.InnerArea.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(vBgColor)));
            }

            String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", itemObject);
            String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);
            HomeUtils.manageTextView(mActivity, vHolder.binding.titleTxtView, vTitle, vTitleFont, vTxtTitleColor);

            String vBtnTxt = mActivity.generalFunc.getJsonValueStr("vBtnTxt", itemObject);
            String btnTxtFont = mActivity.generalFunc.getJsonValueStr("btnTxtFont", layoutDetailsObj);
            String btnTxtColor = mActivity.generalFunc.getJsonValueStr("btnTxtColor", layoutDetailsObj);
            HomeUtils.manageTextView(mActivity, vHolder.binding.newListingButton, vBtnTxt, btnTxtFont, btnTxtColor);

            // Btn Area
            HomeUtils.btnArea(mActivity, vHolder.binding.btnArea, layoutDetailsObj);

            vHolder.binding.newListingButton.setOnClickListener(view -> listener.onNewListingClick(position, itemObject));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof SearchViewHolder vHolder) {

            //Search View
            HomeUtils.setSpace(mActivity, vHolder.binding.mainArea, layoutDetailsObj);

            HomeUtils.mainArea(0, mActivity, vHolder.binding.searchArea, layoutDetailsObj);

            String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", itemObject);
            String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);
            HomeUtils.manageTextView(mActivity, vHolder.binding.searchTxtView, vTitle, vTitleFont, vTxtTitleColor);

            vHolder.binding.searchArea.setOnClickListener(view -> listener.onSearchAreaClick(position, itemObject));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof IconTextViewHolder vHolder) {

            // Icon Text View
            HomeUtils.setSpace(mActivity, vHolder.binding.rvView, layoutDetailsObj);
            vHolder.binding.rvView.setLayoutManager(new GridLayoutManager(mActivity, mActivity.generalFunc.getJsonArray("imagesArr", itemObject).length()));
            vHolder.binding.rvView.setAdapter(new IconTextView24Adapter(mActivity, itemObject, listener::onItemClick));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof CardIconTextViewHolder vHolder) {

            // Icon Text View
            HomeUtils.setSpace(mActivity, vHolder.binding.rvView, layoutDetailsObj);
            vHolder.binding.rvView.setLayoutManager(new LinearLayoutManager(mActivity, LinearLayoutManager.HORIZONTAL, false));
            vHolder.binding.rvView.setAdapter(new CardIconTextView24Adapter(mActivity, itemObject, listener::onItemClick));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof ListBtnViewHolder vHolder) {

            // List Btn View
            HomeUtils.setSpace(mActivity, vHolder.binding.mainArea, layoutDetailsObj);

            vHolder.binding.rvBg.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.rounded_view_basket));
            HomeUtils.mainArea(0, mActivity, vHolder.binding.rvBg, layoutDetailsObj);

            vHolder.binding.rvView.setLayoutManager(new LinearLayoutManager(mActivity, LinearLayoutManager.VERTICAL, false));
            vHolder.binding.rvView.setAdapter(new List23Adapter(mActivity, itemObject, listener::onItemClick));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof StoreItemViewHolder vHolder) {

            if (mActivity.generalFunc.getJsonArray("servicesArr", itemObject) == null) {
                return;
            }

            // Grid View
            HomeUtils.setSpace(mActivity, vHolder.binding.rvView, layoutDetailsObj);

            int spanCount = GeneralFunctions.parseIntegerValue(3, mActivity.generalFunc.getJsonValueStr("GridCount", layoutDetailsObj));
            LinearLayoutManager mLayoutManager;
            if (mActivity.generalFunc.getJsonValueStr("eLayoutType", itemObject).equalsIgnoreCase("vertical")) {
                mLayoutManager = new GridLayoutManager(mActivity, spanCount);
            } else {
                mLayoutManager = new LinearLayoutManager(mActivity, LinearLayoutManager.HORIZONTAL, false);
            }
            vHolder.binding.rvView.setLayoutManager(mLayoutManager);
            vHolder.binding.rvView.setAdapter(new StoreItemView24Adapter(mActivity, itemObject, spanCount, listener::onItemClick));

            /////////------------------------------------------------------------------------------

        }
    }

    @Override
    public int getItemViewType(int position) {
        JSONObject itemObject = mActivity.generalFunc.getJsonObject(mainArray, position);
        String eShowType = mActivity.generalFunc.getJsonValueStr("eViewType", itemObject);

        if (eShowType != null && eShowType.equalsIgnoreCase("TitleView")) {
            return TYPE_TITLE;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("BannerView")) {
            return TYPE_BANNER;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("TextBannerView")) {
            return TYPE_BANNER_TEXT;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("GridView")) {
            return TYPE_GRID;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("TextBannerGridView")) {
            return TYPE_GRID_BANNER_TEXT;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("GridIconView")) {
            return TYPE_GRID_ICON_VIEW;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("ListView")) {
            return TYPE_LIST;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("DestinationDetailView")) {
            return TYPE_RECENT_LOCATION;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("NewListingView")) {
            return TYPE_NEW_LISTING_VIEW;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("SearchBar")) {
            return TYPE_SEARCHBAR_VIEW;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("IconTextView")) {
            return TYPE_ICON_TEXT_VIEW;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("CardIconTextView")) {
            return TYPE_CARD_ICON_TEXT_VIEW;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("ListBtnView")) {
            return TYPE_LIST_BTN_VIEW;

        } else if (eShowType != null && eShowType.equalsIgnoreCase("StoreItemView")) {
            return TYPE_STORE_ITEM_VIEW;

        } else {
            return TYPE_TITLE;
        }
    }

    @Override
    public int getItemCount() {
        return mainArray != null ? mainArray.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(JSONArray homeScreenDataArray) {
        if (mSnapHelper != null) {
            mSnapHelper.attachToRecyclerView(null);
            mSnapHelper = null;
        }
        if (mAutoSlideView != null) {
            mAutoSlideView.removeAll();
        }
        this.mainArray = homeScreenDataArray;
        notifyDataSetChanged();
    }

    /////////////////////////////-----------------//////////////////////////////////////////////
    private static class TitleViewHolder extends RecyclerView.ViewHolder {
        private final Item23TitleViewBinding binding;

        private TitleViewHolder(Item23TitleViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class BannerViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private BannerViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class BannerTextViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private BannerTextViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class GridViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private GridViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class GridTextBannerViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private GridTextBannerViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class GridIconViewHolder extends RecyclerView.ViewHolder {
        private final Item23GridListProdeliveryonlyBinding binding;

        private GridIconViewHolder(Item23GridListProdeliveryonlyBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class ListViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private ListViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class RecentLocationViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecentLocationViewBinding binding;

        private RecentLocationViewHolder(Item23RecentLocationViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class NewListingViewHolder extends RecyclerView.ViewHolder {
        private final Item23ListBuySellRentOnlyBinding binding;

        private NewListingViewHolder(Item23ListBuySellRentOnlyBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class SearchViewHolder extends RecyclerView.ViewHolder {
        private final Item24SerachBarBinding binding;

        private SearchViewHolder(Item24SerachBarBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class IconTextViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private IconTextViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class CardIconTextViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private CardIconTextViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class ListBtnViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private ListBtnViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class StoreItemViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecyclerViewBinding binding;

        private StoreItemViewHolder(Item23RecyclerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onItemClick(int position, JSONObject jsonObject);

        void onSeeAllClick(int position, JSONObject itemObject);

        void onWhereToClick(int position, JSONObject jsonObject);

        void onNowClick(int position, JSONObject jsonObject);

        void onNewListingClick(int position, JSONObject itemObject);

        void onSearchAreaClick(int position, JSONObject itemObject);
    }
}