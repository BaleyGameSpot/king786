package com.adapter.files.deliverAll;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.os.Handler;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.SafetyDialog;
import com.google.android.material.imageview.ShapeableImageView;
import com.buddyverse.main.R;
import com.like.LikeButton;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;


public class RestaurantChildAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    ArrayList<HashMap<String, String>> resArrList;
    Context mContext;
    private final GeneralFunctions generalFunc;
    public RestaurantOnClickListener restaurantOnClickListener;
    boolean isFooterEnabled = false;
    FooterViewHolder footerHolder;
    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    private static final int TYPE_ITEM_23 = 3;
    View footerView;
    String ENABLE_FAVORITE_STORE_MODULE = "";
    int enabledColor, disabledColor, appCompactColor;
    PorterDuff.Mode porterDuffMode;
    int backColor;
    int strokeColor;
    int appThemeColor;
    int orientation;
    String LBL_DELIVERY_TIME, LBL_MIN_ORDER_TXT;
    int pos;
    String ENABLE_STORE_WISE_BANNER;


    public RestaurantChildAdapter(Context context, GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> mapArrayList, int pos, int orientation, boolean isFooterEnabled) {
        this.mContext = context;
        this.generalFunc = generalFunc;
        this.resArrList = mapArrayList;
        this.isFooterEnabled = isFooterEnabled;
        this.orientation = orientation;
        String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        ENABLE_STORE_WISE_BANNER = generalFunc.getJsonValue("ENABLE_STORE_WISE_BANNER", userProfileJson);
        ENABLE_FAVORITE_STORE_MODULE = generalFunc.getJsonValue("ENABLE_FAVORITE_STORE_MODULE", userProfileJson);

        enabledColor = mContext.getResources().getColor(R.color.black);
        disabledColor = mContext.getResources().getColor(R.color.gray);
        appCompactColor = ContextCompat.getColor(mContext, R.color.gray);
        porterDuffMode = PorterDuff.Mode.SRC_IN;
        backColor = mContext.getResources().getColor(R.color.appThemeColor_1);
        appThemeColor = mContext.getResources().getColor(R.color.appThemeColor_1);
        strokeColor = Color.parseColor("#ffffff");

        LBL_DELIVERY_TIME = generalFunc.retrieveLangLBl("", "LBL_DELIVERY_TIME");
        LBL_MIN_ORDER_TXT = generalFunc.retrieveLangLBl("", "LBL_MIN_ORDER_TXT");
        this.pos = pos;
    }


    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {


        if (viewType == TYPE_FOOTER) {
            View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false);
            this.footerView = v;
            return new FooterViewHolder(v);
        } else {
            View view;
            if (viewType == TYPE_ITEM_23) {
                view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_childrestaurant_list_design_23, parent, false);
            } else {
                view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_restaurant_list_design_new_vertical_23, parent, false);
            }
            return new ViewHolder(view);
        }
    }

    private void setItemWidth(ViewHolder viewHolder) {
        if (orientation == LinearLayoutManager.HORIZONTAL) {
            RecyclerView.LayoutParams layPar = new RecyclerView.LayoutParams(getWidth(), RelativeLayout.LayoutParams.WRAP_CONTENT);
            viewHolder.mainArea.setLayoutParams(layPar);
        } else {
            RecyclerView.LayoutParams layPar = new RecyclerView.LayoutParams(RelativeLayout.LayoutParams.MATCH_PARENT, RelativeLayout.LayoutParams.WRAP_CONTENT);
            viewHolder.mainArea.setLayoutParams(layPar);
        }
    }


    private int getWidth() {
        int sWidth = (int) Utils.getScreenPixelWidth(mContext);
        return (int) (sWidth / 1.9);
    }

    private int getWidthNewImage() {
        int sWidth = (int) Utils.getScreenPixelWidth(mContext);
        return (int) (sWidth / 1.9 - mContext.getResources().getDimensionPixelSize(R.dimen._20sdp));
    }


    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {

        if (holder instanceof ViewHolder) {
            setItemWidth((ViewHolder) holder);
            ViewHolder resholder = (ViewHolder) holder;
            HashMap<String, String> data = resArrList.get(position);
            resholder.restaurantNameTxt.setText(data.get("vCompany"));
            resholder.restaurantNameTxt.setSelected(true);
            String vAvgRating = data.get("vAvgRating");
            if (vAvgRating != null && !vAvgRating.equalsIgnoreCase("") && !vAvgRating.equalsIgnoreCase("0")) {
                resholder.restaurantRateTxt.setText(data.get("vAvgRatingConverted"));
                resholder.ratingArea.setVisibility(View.VISIBLE);
//                resholder.restaurantRateTxt.setVisibility(View.VISIBLE);
//                resholder.ratingImg.setVisibility(View.VISIBLE);
            } else {
//                resholder.restaurantRateTxt.setVisibility(View.GONE);
//                resholder.ratingImg.setVisibility(View.GONE);
                resholder.timer2Image.setVisibility(View.GONE);
                resholder.ratingArea.setVisibility(View.GONE);
            }
            resholder.RestCuisineTXT.setText(data.get("Restaurant_Cuisine"));
            resholder.pricePerPersonTxt.setText(data.get("Restaurant_PricePerPersonConverted"));
            resholder.deliveryTimeTxt.setText(data.get("Restaurant_OrderPrepareTimeConverted"));
            if (Utils.checkText(data.get("Restaurant_OrderPrepareTimeConverted"))) {
                resholder.deliveryTimeTxt.setVisibility(View.VISIBLE);
            } else {
                resholder.deliveryTimeTxt.setVisibility(View.GONE);
                resholder.timer2Image.setVisibility(View.GONE);
            }

            resholder.deliveryLBLTimeTxt.setText(LBL_DELIVERY_TIME);

            if (generalFunc.getServiceId().equals("1") || generalFunc.getServiceId().equalsIgnoreCase("")) {
                resholder.perpersonlayout.setVisibility(View.VISIBLE);
            } else {
                resholder.perpersonlayout.setVisibility(View.INVISIBLE);
            }

            if (Utils.checkText(data.get("Restaurant_OfferMessage_shortConverted"))) {
                resholder.offerArea.setVisibility(View.VISIBLE);
                resholder.offerTxt.setText(data.get("Restaurant_OfferMessage_shortConverted"));
                resholder.offerTxt.setSelected(true);
            } else {
                resholder.offerArea.setVisibility(View.INVISIBLE);
            }

            String image = data.get("vImage");
            String vImage = Utils.checkText(image) ? image : "http";

            new Handler().postDelayed(() -> {
                if (Utils.checkText(vImage)) {
                    String resizeImgURL;
                    if (getItemViewType(position) == TYPE_ITEM_23) {
                        resizeImgURL = Utils.getResizeImgURL(mContext, vImage, 0, resholder.imgView.getMeasuredHeight(), resholder.imgView.getMeasuredWidth());
                    } else {
                        resizeImgURL = Utils.getResizeImgURL(mContext, vImage, mContext.getResources().getDimensionPixelSize(R.dimen.restaurant_img_size_home_screen_new), mContext.getResources().getDimensionPixelSize(R.dimen.restaurant_img_size_home_screen_new));
                    }
                    new LoadImage.builder(LoadImage.bind(resizeImgURL), resholder.imgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
                }
            }, 20);


            String fav = data.get("eFavStore");
            //Fav Store Features
            if (ENABLE_FAVORITE_STORE_MODULE.equalsIgnoreCase("Yes") && Utils.checkText(fav) && fav.equalsIgnoreCase("Yes")) {
                resholder.favImage.setVisibility(View.VISIBLE);

            } else {
                resholder.favImage.setVisibility(View.GONE);
            }


            if (data.get("Restaurant_Status").equalsIgnoreCase("Closed")) {
                resholder.resStatusTxt.setVisibility(View.VISIBLE);
                if (data.get("timeslotavailable").equalsIgnoreCase("Yes")) {
                    if (data.get("eAvailable").equalsIgnoreCase("No")) {
                        resholder.resStatusTxt.setText(data.get("LBL_NOT_ACCEPT_ORDERS_TXT"));
                    }
                } else {
                    if (!data.get("Restaurant_Opentime").equalsIgnoreCase("")) {
                        resholder.resStatusTxt.setText(data.get("LBL_CLOSED_TXT") + ": " + data.get("Restaurant_OpentimeConverted"));
                    } else {
                        resholder.resStatusTxt.setText(data.get("LBL_CLOSED_TXT"));
                    }
                }
                resholder.resStatusTxt.setSelected(true);
                resholder.resStatusTxt.setTextColor(mContext.getResources().getColor(R.color.redlight));
            } else {
                resholder.resStatusTxt.setVisibility(View.INVISIBLE);
            }
            resholder.minOrderTxt.setText(data.get("Restaurant_MinOrderValueConverted"));
            resholder.minOrderLBLTxt.setText(LBL_MIN_ORDER_TXT);
            String url = data.get("Restaurant_Safety_URL");

            if ((data.get("Restaurant_Safety_Status") != null && data.get("Restaurant_Safety_Status").equalsIgnoreCase("Yes"))) {

//                if (generalFunc.isRTLmode()) {
//                    resholder.safetyArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.drawable_rounded_left_curve));
//                }
                resholder.safetyArea.setVisibility(View.VISIBLE);
                resholder.safetylayout.setVisibility(View.GONE);
                resholder.resSsafetyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SAFETY_NOTE_TITLE_LIST"));


                int grid = mContext.getResources().getDimensionPixelSize(R.dimen.fab_margin);
                String imageURL = Utils.getResizeImgURL(mContext, data.get("Restaurant_Safety_Icon"), grid, grid);
                new LoadImage.builder(LoadImage.bind(imageURL), resholder.safetyImage).setErrorImagePath(R.drawable.ic_safety).setPlaceholderImagePath(R.drawable.ic_safety).build();
                resholder.safetyArea.setOnClickListener(v -> {

                    Intent intent = new Intent(mContext, SafetyDialog.class);
                    intent.putExtra("URL", url);
                    new ActUtils(mContext).startAct(intent);
                    ((Activity) mContext).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);

                });

            } else {
                resholder.safetyArea.setVisibility(View.GONE);
                resholder.safetylayout.setVisibility(View.GONE);
            }

            resholder.restaurantAdptrLayout.setOnClickListener(v -> {
                if (restaurantOnClickListener != null) {
                    restaurantOnClickListener.setOnRestaurantCategoryclick(position, pos);
                }
            });
        } else {
            FooterViewHolder footerHolder = (FooterViewHolder) holder;
            this.footerHolder = footerHolder;
        }

    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled == true) {
            return TYPE_FOOTER;
        } else if (orientation == LinearLayoutManager.HORIZONTAL) {
            return TYPE_ITEM_23;
        }
        return TYPE_ITEM;
    }


    private boolean isPositionFooter(int position) {
        return position == resArrList.size();
    }


    @Override
    public int getItemCount() {
        if (isFooterEnabled == true) {
            return resArrList.size() + 1;
        } else {
            return resArrList.size();
        }
    }

    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.VISIBLE);
    }

    public void removeFooterView() {
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.GONE);
    }

    public class ViewHolder extends RecyclerView.ViewHolder {

        MTextView restaurantRateTxt;
        AppCompatImageView ratingImg;

        MTextView restaurantNameTxt, deliveryTimeTxt, minOrderTxt, RestCuisineTXT, pricePerPersonTxt, offerTxt, deliveryLBLTimeTxt, minOrderLBLTxt;
        MTextView resStatusTxt;
        ShapeableImageView imgView;
        LinearLayout offerArea;
        ViewGroup restaurantAdptrLayout;
        ImageView offerImage;
        View timer2Image;
        //Fav Store Features
        LikeButton likeButton;
        ImageView favImage;

        MTextView resSsafetyTxt;
        ImageView safetyImage;
        RelativeLayout safetyArea;
        LinearLayout safetylayout;
        LinearLayout perpersonlayout, ratingArea;
        RelativeLayout mainArea;

        public ViewHolder(View itemView) {
            super(itemView);

            imgView = (ShapeableImageView) itemView.findViewById(R.id.imgView);
            restaurantNameTxt = itemView.findViewById(R.id.restaurantNameTxt);
            resStatusTxt = itemView.findViewById(R.id.resStatusTxt);
            restaurantRateTxt = itemView.findViewById(R.id.restaurantRateTxt);
            ratingImg = itemView.findViewById(R.id.ratingImg);
            RestCuisineTXT = itemView.findViewById(R.id.RestCuisineTXT);
            offerTxt = itemView.findViewById(R.id.offerTxt);
            pricePerPersonTxt = itemView.findViewById(R.id.pricePerPersonTxt);
            deliveryTimeTxt = itemView.findViewById(R.id.deliveryTimeTxt);
            deliveryLBLTimeTxt = itemView.findViewById(R.id.deliveryLBLTimeTxt);
            minOrderTxt = itemView.findViewById(R.id.minOrderTxt);
            minOrderLBLTxt = itemView.findViewById(R.id.minOrderLBLTxt);
            ratingArea = itemView.findViewById(R.id.ratingArea);
            if (orientation == LinearLayoutManager.HORIZONTAL) {
                restaurantAdptrLayout = (RelativeLayout) itemView.findViewById(R.id.restaurantAdptrLayout);
            } else {
                restaurantAdptrLayout = (LinearLayout) itemView.findViewById(R.id.restaurantAdptrLayout);
            }
            offerArea = itemView.findViewById(R.id.offerArea);
            offerImage = itemView.findViewById(R.id.offerImage);
            timer2Image = itemView.findViewById(R.id.timer2Image);
            favImage = itemView.findViewById(R.id.favImage);
            likeButton = (LikeButton) itemView.findViewById(R.id.likeButton);

            resSsafetyTxt = itemView.findViewById(R.id.resSsafetyTxt);
            safetyImage = itemView.findViewById(R.id.safetyImage);
            safetylayout = itemView.findViewById(R.id.safetylayout);
            perpersonlayout = itemView.findViewById(R.id.perpersonlayout);
            mainArea = itemView.findViewById(R.id.mainArea);
            safetyArea = itemView.findViewById(R.id.safetyArea);
        }
    }

    public interface RestaurantOnClickListener {
        void setOnRestaurantCategoryclick(int position, int mainPos);

        //Fav Store Features
        void setOnRestaurantclick(int position, boolean liked);
    }

    public void setOnRestaurantItemClick(RestaurantOnClickListener onRestaurantItemClick) {
        this.restaurantOnClickListener = onRestaurantItemClick;
    }

    class FooterViewHolder extends RecyclerView.ViewHolder {
        LinearLayout progressArea;

        public FooterViewHolder(View itemView) {
            super(itemView);
            progressArea = (LinearLayout) itemView;
        }
    }
}
