package com.adapter.files;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.graphics.drawable.BitmapDrawable;
import android.os.Build;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.ScaleAnimation;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.ViewPagerCards.RoundCornerDrawable;
import com.bumptech.glide.Glide;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.NonBreakingPeriodTextView;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;


public class UberXCategory22Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_GRID = 0;
    private final int TYPE_BANNER = 1;
    private final int TYPE_LIST_HORIZONTAL = 2;
    private final int TYPE_ICON = 3;
    private final int TYPE_BUTTON = 4;
    private final int TYPE_BANNER_LIST = 5;
    private final int TYPE_ICON_LIST = 6;
    private final int NO_TYPE = -1;
    private final int TYPE_HEADER = -1;


    public GeneralFunctions generalFunc;
    ArrayList<ArrayList<HashMap<String, String>>> list_item;
    ArrayList<HashMap<String, String>> list_category;
    Context mContext;
    OnItemClickList onItemClickList;

    String CAT_TYPE_MODE = "0";

    boolean isForceToGrid = false;
    boolean ismultiSelect = false;
    String userProfileJson;

    int dimension;
    int bannerHeight;
    int bannerWidth;
    int width;
    int margin;
    int grid;
    boolean click = false;
    //added for manage Listing
    String APP_HOME_PAGE_LIST_VIEW_ENABLED;
    String LBL_DELIVERY_SERVICES;
    boolean isFirst = true;
    int isFirstPos = 0;
    int daynamic_height, daynamic_width;
    boolean isDaynamic = false;
    boolean isGrid = false;

    public UberXCategory22Adapter(Context mContext, ArrayList<HashMap<String, String>> list_category, ArrayList<ArrayList<HashMap<String, String>>> list_item, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_category = list_category;
        this.list_item = list_item;
        this.generalFunc = generalFunc;
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        APP_HOME_PAGE_LIST_VIEW_ENABLED = generalFunc.getJsonValue("APP_HOME_PAGE_LIST_VIEW_ENABLED", userProfileJson);
        LBL_DELIVERY_SERVICES = generalFunc.retrieveLangLBl("", "LBL_DELIVERY_SERVICES");
        if (generalFunc.getJsonValue("SERVICE_PROVIDER_FLOW", userProfileJson).equalsIgnoreCase("PROVIDER")) {
            ismultiSelect = true;
        }
        dimension = mContext.getResources().getDimensionPixelSize(R.dimen.category_grid_size);
        margin = mContext.getResources().getDimensionPixelSize(R.dimen.category_banner_left_right_margin);
        grid = mContext.getResources().getDimensionPixelSize(R.dimen._66sdp);
        width = margin;
        bannerWidth = (int) (Utils.getScreenPixelWidth(mContext) - mContext.getResources().getDimensionPixelSize(R.dimen._20sdp));
        bannerHeight = Utils.getHeightOfBanner(mContext, mContext.getResources().getDimensionPixelSize(R.dimen._120sdp), "16:9");
        daynamic_height = mContext.getResources().getDimensionPixelSize(R.dimen._100sdp);
        daynamic_width = mContext.getResources().getDimensionPixelSize(R.dimen._150sdp);
    }

    private RecyclerView.RecycledViewPool viewPool = new RecyclerView.RecycledViewPool();

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

        if (viewType == TYPE_BANNER_LIST) {

            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_home_banner_list, parent, false);

            BannerListViewHolder bannerListViewHolder = new BannerListViewHolder(view);
            return bannerListViewHolder;
        } else if (viewType == TYPE_ICON_LIST) {

//            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_home_icon_list, parent, false);
//
//            IconListViewHolder iconListViewHolder = new IconListViewHolder(view);
//            return iconListViewHolder;

            isDaynamic = true;
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_service_icon_list, parent, false);
            IconListViewHolder iconListViewHolder = new IconListViewHolder(view);
            iconListViewHolder.dataListRecyclerView.setRecycledViewPool(viewPool);
            return iconListViewHolder;
        } else if (viewType == TYPE_BANNER) {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_banner_short_home, parent, false);

            BannerViewHolder bannerViewHolder = new BannerViewHolder(view);
            return bannerViewHolder;
        } else if (viewType == TYPE_GRID) {
            isGrid = true;
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_uberx_grid_list_design, parent, false);
            ViewHolder viewHolder = new ViewHolder(view);
            return viewHolder;
        } else if (viewType == TYPE_LIST_HORIZONTAL) {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_uberx_list_design, parent, false);
//            if (generalFunc.getJsonValue("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP", userProfileJson) != null && generalFunc.getJsonValue("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP", userProfileJson).equalsIgnoreCase("Yes")) {
            isDaynamic = true;
            view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_service, parent, false);
            // }
            ListViewHolderHorizontal listViewHolderHorizontal = new ListViewHolderHorizontal(view);
            listViewHolderHorizontal.dataListRecyclerView.setRecycledViewPool(viewPool);
            return listViewHolderHorizontal;
        } else if (viewType == TYPE_HEADER) {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_home_label, parent, false);
            HeaderViewHolder headerViewHolder = new HeaderViewHolder(view);
            return headerViewHolder;
        } else if (viewType == TYPE_ICON) {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_uberx_icon_list_design, parent, false);
            IconViewHolderHorizontal iconViewHolderHorizontal = new IconViewHolderHorizontal(view);
            iconViewHolderHorizontal.dataListRecyclerView.setRecycledViewPool(viewPool);
            return iconViewHolderHorizontal;
        } else if (viewType == TYPE_BUTTON) {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_button_home, parent, false);
            ButtonViewHolder buttonViewHolder = new ButtonViewHolder(view);
            return buttonViewHolder;
        } else {
            View view = LayoutInflater.from(parent.getContext()).inflate(CAT_TYPE_MODE.equals("0") ? R.layout.item_uberx_cat_grid_design : R.layout.item_uberx_cat_list_design, parent, false);
            ViewHolder viewHolder = new ViewHolder(view);
            return viewHolder;
        }
    }

    public void setCategoryMode(String CAT_TYPE_MODE) {
        this.CAT_TYPE_MODE = CAT_TYPE_MODE;
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder parentViewHolder, final int position) {

        HashMap<String, String> category = list_category.get(position);


        if (parentViewHolder instanceof BannerListViewHolder) {
            BannerListViewHolder bannerListViewHolder = (BannerListViewHolder) parentViewHolder;
            bannerListViewHolder.serviceDescTxt.setText(category.get("tListDescription"));
            bannerListViewHolder.serviceNameTxt.setText(category.get("vCategoryName"));
            String vLogo_image = category.get("vListLogo");

            String imageURL = Utils.getResizeImgURL(mContext, vLogo_image, grid, grid);
            new LoadImage.builder(LoadImage.bind(imageURL), bannerListViewHolder.serviceImg).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

            bannerListViewHolder.containerView.setOnClickListener(v -> {
                if (onItemClickList != null) {
                    onItemClickList.onItemClick(position, -1);
                }

            });
        } else if (parentViewHolder instanceof IconListViewHolder) {
            IconListViewHolder viewHolder = (IconListViewHolder) parentViewHolder;


            // iconListViewHolder.serviceNameTxt.setText(category.get("vCategoryName"));
//            new LoadImage.builder(LoadImage.bind(vBannerImage), iconListViewHolder.serviceImg).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
            ArrayList<HashMap<String, String>> item = list_item.get(position);


            UberXIconCategoryInnerAdapter ufxCatAdapter = new UberXIconCategoryInnerAdapter(getActContext(), item, position, generalFunc, category.get("vBgColor"), category.get("eShowType"));
           // LinearLayoutManager linearLayoutManager = new LinearLayoutManager(mContext, LinearLayoutManager.HORIZONTAL, false);
           // viewHolder.dataListRecyclerView.setLayoutManager(linearLayoutManager);
            viewHolder.dataListRecyclerView.setLayoutManager(new GridLayoutManager(getActContext(), 3));
            viewHolder.dataListRecyclerView.setAdapter(ufxCatAdapter);
            viewHolder.dataListRecyclerView.getRecycledViewPool().clear();

            ufxCatAdapter.setOnItemClickList(new UberXIconCategoryInnerAdapter.OnItemClickList() {
                @Override
                public void onItemClick(int position, int parentPos) {
                    onItemClickList.onItemClick(position, parentPos);
                }

                @Override
                public void onMultiItem(String id, boolean b) {

                }
            });

        } else if (parentViewHolder instanceof ButtonViewHolder) {
            ButtonViewHolder buttonViewHolder = (ButtonViewHolder) parentViewHolder;
            if (category.get("vButtonBgColor") != null && !category.get("vButtonBgColor").equalsIgnoreCase("")) {
                buttonViewHolder.buttonHome.getBackground().setTint(Color.parseColor(category.get("vButtonBgColor")));
            }
            if (category.get("vButtonTextColor") != null && !category.get("vButtonTextColor").equalsIgnoreCase("")) {
                buttonViewHolder.buttonHome.setTextColor(Color.parseColor(category.get("vButtonTextColor")));
            }
            buttonViewHolder.buttonHome.setText(category.get("vCategoryName"));

            if (category.get("vBgColor") != null && !category.get("vBgColor").equalsIgnoreCase("")) {
                buttonViewHolder.contentView.setBackgroundColor(Color.parseColor(category.get("vBgColor")));
            }

            buttonViewHolder.contentView.setOnClickListener(view -> {
                if (onItemClickList != null) {
                    onItemClickList.onItemClick(position, -1);
                }
            });
            Logger.d("Screen Timer", "::" + "Set Data Completed");
        } else if (parentViewHolder instanceof HeaderViewHolder) {
            HeaderViewHolder viewHolder = (HeaderViewHolder) parentViewHolder;
            viewHolder.selectServiceTxt.setText(category.get("vTitle"));
            if (category.get("vBgColor") != null && !category.get("vBgColor").equalsIgnoreCase("")) {
                viewHolder.contentView.setBackgroundColor(Color.parseColor(category.get("vBgColor")));
            }
        } else if (parentViewHolder instanceof IconViewHolderHorizontal) {

            ArrayList<HashMap<String, String>> item = list_item.get(position);
            IconViewHolderHorizontal viewHolder = (IconViewHolderHorizontal) parentViewHolder;


            UberXHomeIconAdapter ufxCatAdapter = new UberXHomeIconAdapter(getActContext(), item, position, generalFunc);

            viewHolder.dataListRecyclerView.setAdapter(ufxCatAdapter);
            viewHolder.dataListRecyclerView.getRecycledViewPool().clear();
            //ufxCatAdapter.notifyDataSetChanged();
            ufxCatAdapter.setOnItemClickList(new UberXCategoryInnerAdapter.OnItemClickList() {
                @Override
                public void onItemClick(int position, int parentPos) {
                    //Toast.makeText(getActContext(), "Click On onItemClickHandle Adapter", Toast.LENGTH_SHORT).show();
                    onItemClickList.onItemClick(position, parentPos);
                }

                @Override
                public void onMultiItem(String id, boolean b) {

                }
            });

            if (category.get("vBgColor") != null && !category.get("vBgColor").equalsIgnoreCase("")) {
                viewHolder.contentView.setBackgroundColor(Color.parseColor(category.get("vBgColor")));
            }

        } else if (parentViewHolder instanceof ListViewHolderHorizontal) {
            ArrayList<HashMap<String, String>> item = list_item.get(position);
            String vIconImage = category.get("vIconImage");
            String vCategoryName = category.get("vCategoryName");
            String vBgColor = category.get("vBgColor");
            ListViewHolderHorizontal viewHolder = (ListViewHolderHorizontal) parentViewHolder;

            if (isFirstPos == position) {
                isFirst = true;
            }
            if (isFirst) {
                isFirst = false;
                isFirstPos = position;
            } else {
            }

            viewHolder.viewRoundCurve.setBackground(getActContext().getResources().getDrawable(R.drawable.left_curve_card_new));
            if (generalFunc.isRTLmode()) {
                viewHolder.viewRoundCurve.setBackground(getActContext().getResources().getDrawable(R.drawable.right_curve_card));
            }
            if (vBgColor != null && !vBgColor.equalsIgnoreCase("")) {
                viewHolder.viewRoundCurve.getBackground().setTint(Color.parseColor(vBgColor));
            }
            Glide.with(getActContext())
                    .load(vIconImage)
                    .into(viewHolder.catLabelView);
            viewHolder.catLabelTxt.setText(vCategoryName);

            if (category.get("vTextColor") != null && !category.get("vTextColor").equalsIgnoreCase("")) {
                viewHolder.catLabelTxt.setTextColor(Color.parseColor(category.get("vTextColor")));
            }


            UberXCategoryInnerAdapter ufxCatAdapter = new UberXCategoryInnerAdapter(getActContext(), item, position, generalFunc, category.get("vBgColor"), category.get("eShowType"));
            if (category.get("eShowType").equalsIgnoreCase("CubeXList")) {
                viewHolder.dataListRecyclerView.setLayoutManager(new GridLayoutManager(getActContext(), 2));
            } else {
                LinearLayoutManager linearLayoutManager = new LinearLayoutManager(mContext, LinearLayoutManager.HORIZONTAL, false);
                viewHolder.dataListRecyclerView.setLayoutManager(linearLayoutManager);
            }
            viewHolder.dataListRecyclerView.setAdapter(ufxCatAdapter);
            viewHolder.dataListRecyclerView.getRecycledViewPool().clear();

            ufxCatAdapter.setOnItemClickList(new UberXCategoryInnerAdapter.OnItemClickList() {
                @Override
                public void onItemClick(int position, int parentPos) {
                    onItemClickList.onItemClick(position, parentPos);
                }

                @Override
                public void onMultiItem(String id, boolean b) {

                }
            });
        } else if (parentViewHolder instanceof BannerViewHolder) {
            String vBannerImage = category.get("vBannerImage");
            String vCategoryName = category.get("vCategoryName");
            String ePromoteBanner = category.get("ePromoteBanner");
            String vBannerBgColor = category.get("vBannerBgColor");
            String vBgColor = category.get("vBgColor");
            BannerViewHolder holder = (BannerViewHolder) parentViewHolder;

            if (category.get("vBgColor") != null && !category.get("vBgColor").equalsIgnoreCase("")) {
                holder.contentView.setBackgroundColor(Color.parseColor(category.get("vBgColor")));
            }

            if (ePromoteBanner.equalsIgnoreCase("Yes")) {
                if (generalFunc.isRTLmode()) {
                    holder.bookNowImg.setRotation(0);
                } else {
                    holder.bookNowImg.setRotation(180);
                }
                holder.bookNowImg.setColorFilter(Color.parseColor(vBannerBgColor), android.graphics.PorterDuff.Mode.MULTIPLY);

                holder.bookNowImgBottom.setVisibility(View.GONE);
                holder.bookNowImg.setVisibility(View.GONE);
                bannerHeight = (int) (bannerWidth / 2.90);
                Logger.d("BannerimageURL", "::" + bannerWidth + "," + bannerHeight);
                String imageURL = Utils.getResizeImgURL(mContext, vBannerImage, bannerWidth, bannerHeight);
                Logger.d("BannerimageURL", "::" + imageURL);
                holder.transparentView.setMinimumHeight(bannerHeight);

                RelativeLayout.LayoutParams bannerLayoutParams = (RelativeLayout.LayoutParams) holder.bannerImgView.getLayoutParams();
                bannerLayoutParams.height = bannerHeight;

                holder.bannerImgView.setLayoutParams(bannerLayoutParams);


                new LoadImage.builder(LoadImage.bind(vBannerImage.equals("") ? CommonUtilities.SERVER : imageURL), holder.bannerImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).setPicassoListener(new LoadImage.PicassoListener() {
                    @Override
                    public void onSuccess() {
                        try {

                            Logger.d("BannerimageURL", "bannerImgView Height::" + holder.bannerImgView.getHeight());
                            if (Build.VERSION.SDK_INT < Build.VERSION_CODES.LOLLIPOP) {
                                holder.bannerImgView.invalidate();
                                BitmapDrawable drawable = (BitmapDrawable) holder.bannerImgView.getDrawable();
                                Bitmap bitmap = drawable.getBitmap();
                                RoundCornerDrawable round = new RoundCornerDrawable(bitmap, mContext.getResources().getDimension(R.dimen._5sdp), 0);
                                holder.bannerImgView.setVisibility(View.GONE);
                                holder.containerView.setBackground(round);
                            }
                        } catch (Exception e) {
                            Logger.e("Exception","::"+e.getMessage());
                        }
                    }

                    @Override
                    public void onError() {

                    }
                }).build();

                int btnRadius = Utils.dipToPixels(mContext, 14);
                String color = category.get("vBannerBgColor");
                color = "#80" + color.replace("#", "");
                new CreateRoundedView(Color.parseColor(color), btnRadius, 0, Color.parseColor(color), holder.transparentView);


                holder.bannerTitle.setText(vCategoryName);


            } else {

                holder.bookNowImg.setVisibility(View.GONE);
                holder.bookNowImgBottom.setVisibility(View.GONE);
                bannerHeight = (int) (bannerWidth / 2.20);
                Logger.d("BannerimageURL", "::" + bannerWidth + "," + bannerHeight);
                String imageURL = Utils.getResizeImgURL(mContext, vBannerImage, bannerWidth, bannerHeight);
                Logger.d("BannerimageURL", "::" + imageURL);

                RelativeLayout.LayoutParams bannerLayoutParams = (RelativeLayout.LayoutParams) holder.bannerImgView.getLayoutParams();
                bannerLayoutParams.height = bannerHeight;

                holder.bannerImgView.setLayoutParams(bannerLayoutParams);

                new LoadImage.builder(LoadImage.bind(vBannerImage.equals("") ? CommonUtilities.SERVER : imageURL), holder.bannerImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).setPicassoListener(new LoadImage.PicassoListener() {
                    @Override
                    public void onSuccess() {
                        try {
                            if (Build.VERSION.SDK_INT < Build.VERSION_CODES.LOLLIPOP) {
                                holder.bannerImgView.invalidate();
                                BitmapDrawable drawable = (BitmapDrawable) holder.bannerImgView.getDrawable();
                                Bitmap bitmap = drawable.getBitmap();
                                RoundCornerDrawable round = new RoundCornerDrawable(bitmap, mContext.getResources().getDimension(R.dimen._5sdp), 0);
                                holder.bannerImgView.setVisibility(View.GONE);
                                holder.containerView.setBackground(round);
                            }
                        } catch (Exception e) {
                            Logger.e("Exception","::"+e.getMessage());
                        }
                    }

                    @Override
                    public void onError() {

                    }
                }).build();


                holder.transparentView.setBackgroundColor(ContextCompat.getColor(mContext,
                        R.color.transparent_full));
            }

            holder.containerView.setOnClickListener(view -> {
                if (onItemClickList != null) {
                    onItemClickList.onItemClick(position, -1);

                }
            });


        } else if (parentViewHolder instanceof ViewHolder) {
            ViewHolder holder = (ViewHolder) parentViewHolder;

            ArrayList<HashMap<String, String>> item = list_item.get(position);


            UberXCategoryGrid22Adapter ufxCatAdapter = new UberXCategoryGrid22Adapter(getActContext(), position, item, generalFunc);
            ufxCatAdapter.setOnItemClickList(new UberXCategoryGrid22Adapter.OnItemClickList() {


                @Override
                public void onItemClick(int position, int parentPos) {
                    onItemClickList.onItemClick(position, parentPos);
                }

                @Override
                public void onMultiItem(String id, boolean b) {

                }
            });

            holder.dataListRecyclerView.setLayoutManager(new GridLayoutManager(getActContext(), 4));
            holder.dataListRecyclerView.setAdapter(ufxCatAdapter);
            holder.dataListRecyclerView.getRecycledViewPool().clear();

            if (category.get("vBgColor") != null && !category.get("vBgColor").equalsIgnoreCase("")) {
                holder.contentView.setBackgroundColor(Color.parseColor(category.get("vBgColor")));
            }

        }
    }

    private Context getActContext() {
        return mContext;
    }

    private class SetOnTouchList implements View.OnTouchListener {
        int viewType;
        int item_position;
        boolean isBlockDownEvent = false;

        public SetOnTouchList(int viewType, int item_position, boolean isBlockDownEvent) {
            this.item_position = item_position;
            this.viewType = viewType;
            this.isBlockDownEvent = isBlockDownEvent;
        }

        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {

            switch (motionEvent.getAction()) {
                case MotionEvent.ACTION_DOWN:
                    if (!isBlockDownEvent) {
                        scaleView(view, viewType == TYPE_BANNER ? 0.97f : 0.85f, viewType == TYPE_BANNER ? 0.97f : 0.85f, motionEvent.getAction(), viewType, item_position);
                    }
                    break;

                case MotionEvent.ACTION_CANCEL:
                    scaleView(view, (float) 1.0, (float) 1.0, motionEvent.getAction(), viewType, item_position);
                    break;
            }
            return true;
        }
    }

    public void scaleView(View v, float startScale, float endScale, int motionEvent, int viewType, int item_position) {
        v.setOnTouchListener(new SetOnTouchList(viewType, item_position, true));
        Animation anim = new ScaleAnimation(
                startScale, endScale, // Start and end values for the X axis scaling
                startScale, endScale, // Start and end values for the Y axis scaling
                Animation.RELATIVE_TO_SELF, 0.5f, // Pivot point of X scaling
                Animation.RELATIVE_TO_SELF, 0.5f); // Pivot point of Y scaling
        anim.setFillAfter(true); // Needed to keep the result of the animation
        anim.setDuration(100);
        anim.setAnimationListener(new Animation.AnimationListener() {
            @Override
            public void onAnimationStart(Animation animation) {
            }

            @Override
            public void onAnimationEnd(Animation animation) {
                if (motionEvent == MotionEvent.ACTION_UP && list_item.size() > item_position) {
                    v.performClick();
                }
                v.setOnTouchListener(new SetOnTouchList(viewType, item_position, false));
            }

            @Override
            public void onAnimationRepeat(Animation animation) {
            }
        });
        v.startAnimation(anim);
    }

    @Override
    public int getItemCount() {
        return list_category.size();
    }

    @Override
    public int getItemViewType(int position) {
        //if (CAT_TYPE_MODE.equals("0")) {
        HashMap<String, String> dataMap = list_category.get(position);
        String eShowType = dataMap.get("eShowType");

        if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("Service_List")) {
            return TYPE_BANNER_LIST;
        } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("Service_Icon")) {
            return TYPE_ICON_LIST;
        } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("Banner")) {
            return TYPE_BANNER;
        } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("Banner") && isForceToGrid == false) {
            return TYPE_BANNER;
        } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("ICON")) {
            // return TYPE_GRID;
            return TYPE_ICON;
        } else if (eShowType != null && !eShowType.equals("") && (eShowType.equalsIgnoreCase("List") && isForceToGrid == false || eShowType.equalsIgnoreCase("CubeXList")) && isForceToGrid == false) {
            return TYPE_LIST_HORIZONTAL;
        } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("Header")) {
            return TYPE_HEADER;
        } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("Button")) {
            return TYPE_BUTTON;
        } else if (eShowType != null && !eShowType.equals("") && eShowType.equalsIgnoreCase("Grid")) {
            return TYPE_GRID;
        } else {
            return NO_TYPE;
        }
        //}
        //return NO_TYPE;
    }

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnItemClickList {
        void onItemClick(int position, int parentPos);

        void onMultiItem(String id, boolean b);
    }

    public class ListViewHolderHorizontal extends RecyclerView.ViewHolder {
        View viewRoundCurve;
        ImageView catLabelView;
        NonBreakingPeriodTextView catLabelTxt;
        RecyclerView dataListRecyclerView;

        public ListViewHolderHorizontal(View view) {
            super(view);
            viewRoundCurve = view.findViewById(R.id.viewRoundCurve);
            catLabelView = (ImageView) view.findViewById(R.id.catLabelView);
            catLabelTxt = (NonBreakingPeriodTextView) view.findViewById(R.id.catLabelTxt);
            dataListRecyclerView = (RecyclerView) view.findViewById(R.id.dataListRecyclerView);

            if (isDaynamic) {
                //rtlcatImgView = (SelectableRoundedImageView) view.findViewById(R.id.rtlcatImgView);
            }
        }
    }

    public class IconViewHolderHorizontal extends RecyclerView.ViewHolder {

        RecyclerView dataListRecyclerView;
        View contentView;

        public IconViewHolderHorizontal(View view) {
            super(view);
            dataListRecyclerView = (RecyclerView) view.findViewById(R.id.dataListRecyclerView);
            contentView = view.findViewById(R.id.contentView);

        }
    }


    public class HeaderViewHolder extends RecyclerView.ViewHolder {

        MTextView selectServiceTxt;
        View contentView;

        public HeaderViewHolder(View view) {
            super(view);
            selectServiceTxt = (MTextView) view.findViewById(R.id.selectServiceTxt);
            contentView = view.findViewById(R.id.contentView);
        }
    }

    public class ViewHolder extends RecyclerView.ViewHolder {
        RecyclerView dataListRecyclerView;
        View contentView;

        public ViewHolder(View view) {
            super(view);
            dataListRecyclerView = (RecyclerView) view.findViewById(R.id.dataListRecyclerView);
            contentView = view.findViewById(R.id.contentView);
        }
    }

    public class BannerListViewHolder extends RecyclerView.ViewHolder {
        public View containerView;

        public ImageView serviceImg;
        MTextView serviceNameTxt, serviceDescTxt;

        public BannerListViewHolder(View view) {
            super(view);
            containerView = view.findViewById(R.id.containerView);
            serviceImg = view.findViewById(R.id.serviceImg);
            serviceNameTxt = view.findViewById(R.id.serviceNameTxt);
            serviceDescTxt = view.findViewById(R.id.serviceDescTxt);

        }
    }

    public class IconListViewHolder extends RecyclerView.ViewHolder {
        View viewRoundCurve;
        ImageView catLabelView;
        NonBreakingPeriodTextView catLabelTxt;
        RecyclerView dataListRecyclerView;

        public IconListViewHolder(View view) {
            super(view);
            viewRoundCurve = view.findViewById(R.id.viewRoundCurve);
            catLabelView = (ImageView) view.findViewById(R.id.catLabelView);
            catLabelTxt = (NonBreakingPeriodTextView) view.findViewById(R.id.catLabelTxt);
            dataListRecyclerView = (RecyclerView) view.findViewById(R.id.dataListRecyclerView);

            if (isDaynamic) {
                //rtlcatImgView = (SelectableRoundedImageView) view.findViewById(R.id.rtlcatImgView);
            }
        }

    }

    public class BannerViewHolder extends RecyclerView.ViewHolder {
        public View containerView;
        public View contentView;
        public View transparentView;
        public ImageView bannerImgView;
        MTextView bannerTitle;
        ImageView bookNowImg, bookNowImgBottom;

        public BannerViewHolder(View view) {
            super(view);
            containerView = view.findViewById(R.id.containerView);
            bannerImgView = view.findViewById(R.id.bannerImgView);
            contentView = view.findViewById(R.id.contentView);
            transparentView = view.findViewById(R.id.transparentView);
            bannerTitle = view.findViewById(R.id.bannerTitle);
            bookNowImg = view.findViewById(R.id.bookNowImg);
            bookNowImgBottom = view.findViewById(R.id.bookNowImgBottom);
        }
    }

    public class ButtonViewHolder extends RecyclerView.ViewHolder {
        MTextView buttonHome;
        View contentView;

        public ButtonViewHolder(View view) {
            super(view);
            buttonHome = view.findViewById(R.id.buttonHome);
            contentView = view.findViewById(R.id.contentView);
        }
    }


}
