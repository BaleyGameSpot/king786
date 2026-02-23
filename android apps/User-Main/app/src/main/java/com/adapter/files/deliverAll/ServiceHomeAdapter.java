package com.adapter.files.deliverAll;

import android.content.Context;

import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.ScaleAnimation;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import com.andremion.counterfab.CounterFab;
import com.buddyverse.main.R;
import com.general.files.GeneralFunctions;
import com.general.files.showTermsDialog;
import com.realmModel.Cart;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;

import java.util.ArrayList;
import java.util.HashMap;

import io.realm.RealmResults;

public class ServiceHomeAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {


    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list_item;
    Context mContext;
    OnItemClickList onItemClickList;

    String CAT_TYPE_MODE = "0";

    int bannerWidth;
    int bannerHeight;
    int bannerFactor;
    private RealmResults<Cart> realmCartList;
    int cnt = 0;
    boolean click = false;

    public ServiceHomeAdapter(Context mContext, ArrayList<HashMap<String, String>> list_item, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_item = list_item;
        this.generalFunc = generalFunc;
        bannerFactor = mContext.getResources().getDimensionPixelSize(R.dimen.category_banner_left_right_margin) * 2;
        bannerHeight = Utils.getHeightOfBanner(mContext, bannerFactor, "18:9");
        bannerWidth = Utils.getWidthOfBanner(mContext, bannerFactor);

    }


    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_service_banner_design, parent, false);
        BannerViewHolder bannerViewHolder = new BannerViewHolder(view);
        return bannerViewHolder;


    }

    public void setCategoryMode(String CAT_TYPE_MODE) {
        this.CAT_TYPE_MODE = CAT_TYPE_MODE;
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder parentViewHolder, final int position) {

        HashMap<String, String> item = list_item.get(position);


        BannerViewHolder holder = (BannerViewHolder) parentViewHolder;


        holder.seperatorView.setVisibility(View.GONE);

        int listSize = list_item.size();

        if (listSize > 1) {
            if (listSize - 1 != position) {
                holder.seperatorView.setVisibility(View.VISIBLE);
            }
        }
        if (generalFunc.isRTLmode()) {
            holder.bookNowImg.setScaleX(-1);
        }


        holder.serviceNameTxt.setText(item.get("vServiceName"));
        holder.bookNowTxt.setText(item.get("LBL_BOOK_NOW"));
        holder.fabcartIcon.setOnClickListener(v -> {
            if (onItemClickList != null) {
                onItemClickList.redirectToCheckout();
            }
        });

        CardView.LayoutParams layoutParams = (CardView.LayoutParams) holder.bannerAreaContainerView.getLayoutParams();

        layoutParams.height = Utils.getHeightOfBanner(mContext, mContext.getResources().getDimensionPixelSize(R.dimen.category_banner_left_right_margin) * 2, "18:9");

        int color1 = Color.parseColor("#000000");
        int color2 = Color.parseColor("#000000");
        int btnRadius = Utils.dipToPixels(mContext, 8);
        int strokeWidth = Utils.dipToPixels(mContext, 0);

        new CreateRoundedView(color1, btnRadius, strokeWidth, color1, holder.bookNowTxt);
        new CreateRoundedView(color2, btnRadius, strokeWidth, color1, holder.serviceNameTxt);


        String vImage = item.get("vImage");
        String imageURL = Utils.getResizeImgURL(mContext, vImage, bannerWidth, bannerHeight);

        new LoadImage.builder(LoadImage.bind(vImage.equals("") ? CommonUtilities.SERVER : imageURL), holder.bannerImgView).setPlaceholderImagePath(R.mipmap.ic_no_icon).setPicassoListener(new LoadImage.PicassoListener() {
            @Override
            public void onSuccess() {

            }

            @Override
            public void onError() {
                try {

                    if (!vImage.contains("http") && !vImage.equals("")) {

                        new LoadImage.builder(LoadImage.bind(vImage), holder.bannerImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
                    }
                } catch (Exception e1) {

                }

            }
        }).build();


        String eShowTermsStr = item.get("eShowTerms");
        boolean eShowTerms = Utils.checkText(eShowTermsStr) && eShowTermsStr.equalsIgnoreCase("yes") ? true : false;
        holder.containerView.setOnClickListener(view -> {
            if (onItemClickList != null) {
                if (eShowTerms && !CommonUtilities.ageRestrictServices.contains("1")) {
                    new showTermsDialog(mContext, generalFunc, position, item.get("vCategory"), click, new showTermsDialog.OnClickList() {
                        @Override
                        public void onClick() {
                            onItemClickList.onItemClick(position);
                        }
                    });

                } else {
                    onItemClickList.onItemClick(position);
                }
            }
        });

        if (CAT_TYPE_MODE.equals("0")) {

            holder.containerView.setOnTouchListener((view, motionEvent) -> {
                switch (motionEvent.getAction()) {
                    case MotionEvent.ACTION_DOWN:
                        scaleView(view, 0.97f, 0.97f);
                        break;
                    case MotionEvent.ACTION_UP:
                    case MotionEvent.ACTION_CANCEL:
                        scaleView(view, (float) 1.0, (float) 1.0);
                        break;
                }
                return false;
            });
        } else {
            Logger.d("TouchOnBanner", "Removed");
            holder.containerView.setOnTouchListener(null);
        }


    }

    public void scaleView(View v, float startScale, float endScale) {
        Animation anim = new ScaleAnimation(
                startScale, endScale, // Start and end values for the X axis scaling
                startScale, endScale, // Start and end values for the Y axis scaling
                Animation.RELATIVE_TO_SELF, 0.5f, // Pivot point of X scaling
                Animation.RELATIVE_TO_SELF, 0.5f); // Pivot point of Y scaling
        anim.setFillAfter(true); // Needed to keep the result of the animation
        anim.setDuration(100);
        v.startAnimation(anim);
    }

    @Override
    public int getItemCount() {
        return list_item.size();
    }


    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public void setrealmCartList(RealmResults<Cart> realmCartList) {
        this.realmCartList = realmCartList;
        cnt = 0;

        if (realmCartList != null) {
            for (int i = 0; i < realmCartList.size(); i++) {
                cnt = cnt + GeneralFunctions.parseIntegerValue(0, realmCartList.get(i).getQty());
            }
        }
    }

    public interface OnItemClickList {
        void onItemClick(int position);

        void redirectToCheckout();
    }


    public class BannerViewHolder extends RecyclerView.ViewHolder {


        public View containerView;
        public View bannerAreaContainerView;
        public View seperatorView;
        public SelectableRoundedImageView bannerImgView;
        public MTextView bookNowTxt, serviceNameTxt;
        ImageView bookNowImg;
        CounterFab fabcartIcon;
        RelativeLayout cartArea;

        public BannerViewHolder(View view) {
            super(view);
            containerView = view.findViewById(R.id.containerView);
            bannerImgView = (SelectableRoundedImageView) view.findViewById(R.id.bannerImgView);
            bookNowTxt = (MTextView) view.findViewById(R.id.bookNowTxt);
            serviceNameTxt = (MTextView) view.findViewById(R.id.serviceNameTxt);
            fabcartIcon = (CounterFab) view.findViewById(R.id.fabcartIcon);
            cartArea = (RelativeLayout) view.findViewById(R.id.cartArea);
            bannerAreaContainerView = view.findViewById(R.id.bannerAreaContainerView);
            seperatorView = view.findViewById(R.id.seperatorView);
            bookNowImg = view.findViewById(R.id.bookNowImg);
        }
    }


}
